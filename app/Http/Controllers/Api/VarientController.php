<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VarientResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Image;
use App\Models\Varient;
use Illuminate\Http\Request;

class VarientController extends Controller
{
    /**
     * Display a listing of the resource.
     */



    public function index(Product $product)
    {
        return VarientResource::collection($product->variants()->paginate(15));
    }







    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Product $product)
    {
        try {
            // Validate request data
            $validatedData = $request->validate([
                'color' => 'required|string|max:100',
                'volume' => 'required|string|max:100',
                'varient' => 'required|string|max:100',
                'cost_price' => 'required|numeric|min:0',
                'price' => [
                    'required',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value < $request->cost_price) {
                            $fail('The price must be greater than or equal to the cost price.');
                        }
                    }
                ],
                'selling_price' => [
                    'required',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value <= $request->cost_price) {
                            $fail('The selling price must be greater than the cost price.');
                        }
                        if ($value <= $request->price) {
                            $fail('The selling price must be greater than the price.');
                        }
                    }
                ],
                'weight' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:20',
                'product_images' => 'required|array',
                'product_images.*' => 'image',
            ], [
                'stock.min' => 'The stock must be at least 20 units.',
                'selling_price.gt' => 'The selling price must be greater than the price.',
            ]);

            // Ensure product exists
            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            // Check if the variant already exists
            $existingVariant = Varient::where('product_id', $product->id)
                ->where('color', $validatedData['color'])
                ->where('volume', $validatedData['volume'])
                ->where('varient', $validatedData['varient'])
                ->where('weight', $validatedData['weight'])
                ->first();

            if ($existingVariant) {
                return response()->json(['message' => 'Variant with these attributes already exists'], 400);
            }

            // Retrieve category name
            $categoryName = $product->category ? $product->category->Categ : 'UnknownCategory';

            // Generate SKU
            $colorPart = strtoupper(substr($validatedData['color'], 0, 3));
            $volumePart = strtoupper(substr($validatedData['volume'], 0, 3));
            $variantId = $product->id;
            $skuCode = "WEMARKA-{$categoryName}-{$validatedData['varient']}-{$colorPart}-{$volumePart}ML-{$variantId}";

            // Start a transaction to ensure data integrity
            DB::beginTransaction();

            // Create the new variant
            $variant = new Varient();
            $variant->color = $validatedData['color'];
            $variant->volume = $validatedData['volume'];
            $variant->varient = $validatedData['varient'];
            $variant->weight = $validatedData['weight'];
            $variant->cost_price = $validatedData['cost_price'];
            $variant->price = $validatedData['price'];
            $variant->selling_price = $validatedData['selling_price'];
            $variant->stock = $validatedData['stock'];
            $variant->product_id = $product->id;
            $variant->Sku_code = $skuCode;
            $variant->save();

            // Handle image uploads
            $imageUrls = [];
            foreach ($validatedData['product_images'] as $image) {
                if ($image->isValid()) {
                    $imagePath = $image->store('variant_images', 'public');

                    // Save image record
                    $imageRecord = new Image();
                    $imageRecord->varient_id = $variant->id;
                    $imageRecord->image_url = asset('storage/' . $imagePath);
                    $imageRecord->save();

                    $imageUrls[] = $imageRecord->image_url;
                } else {
                    throw new \Exception("Image upload failed.");
                }
            }

            // Commit transaction if everything is successful
            DB::commit();

            // Return success response
            return response()->json([
                'message' => 'Variant added successfully!',
                'variant' => $variant,
                'image_urls' => $imageUrls,
            ], 201);
        } catch (\Exception $e) {
            // Rollback transaction if an error occurs
            DB::rollBack();
            return response()->json([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }




    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product, $id)
    {
        try {
            // Find the variant by its ID and ensure it belongs to the specified product using query builder
            $variant = DB::table('varients')
            ->where('id', $id)
                ->where('product_id', $product->id)
                ->first();

            if (!$variant) {
                return response()->json(['message' => 'Variant not found or does not belong to the specified product'], 404);
            }

            // Validate the incoming request data
            $validatedData = $request->validate([
                'color' => 'nullable|string|max:100',
                'volume' => 'nullable|string|max:100',
                'varient' => 'nullable|string|max:100',
                'cost_price' => 'nullable|numeric|min:0',
                'price' => 'nullable|numeric|min:0|gte:cost_price',
                'Selling_price' => 'nullable|numeric|min:0',
                'weight' => 'nullable|numeric|min:0',
                'stock' => 'nullable|integer|min:10',
            ], [
                'price.gte' => 'The price must be at least equal to the cost price.',
                'Selling_price.min' => 'The selling price must be greater than both the cost price and the price.',
            ]);

            // Prepare the data to update, removing null values
            $dataToUpdate = array_filter($validatedData, function ($value) {
                return !is_null($value);
            });

            // Check if any changes were made by comparing incoming data with existing variant
            $noChangesDetected = true;
            foreach ($dataToUpdate as $key => $value) {
                if ($variant->{$key} !== $value) {
                    $noChangesDetected = false;
                    break; // Stop checking if a change is found
                }
            }

            // If no changes were detected, return the response
            if ($noChangesDetected) {
                return response()->json([
                    'message' => 'No changes detected',
                ], 200);
            }

            // Check if SKU needs to be regenerated
            $skuFields = ['color', 'volume', 'varient', 'product_id'];
            $shouldRegenerateSKU = false;

            foreach ($skuFields as $field) {
                if (isset($dataToUpdate[$field]) && $dataToUpdate[$field] !== $variant->{$field}) {
                    $shouldRegenerateSKU = true;
                    break;
                }
            }

            if ($shouldRegenerateSKU) {
                // Retrieve the category name from the related product
                $categoryName = DB::table('products')
                ->where('id', $product->id)
                    ->value('category_id'); // Retrieve the category_id of the product

                $categoryName = DB::table('categories')
                ->where('id', $categoryName)
                    ->value('Category'); // Retrieve category name from category table

                // Generate SKU with WEMARKA, Category Name, Variant, Color, Volume, and Variant ID
                $colorPart = strtoupper($dataToUpdate['color'] ?? $variant->color);  // Full color name
                $volumePart = strtoupper(substr($dataToUpdate['volume'] ?? $variant->volume, 0, 3)); // First 3 letters of volume
                $variantId = $variant->id;
                $skuCode = "WEMARKA-{$categoryName}-" .
                ($dataToUpdate['varient'] ?? $variant->varient) . "-{$colorPart}-{$volumePart}ML-{$variantId}";

                $dataToUpdate['Sku_code'] = $skuCode;
            }

            // If cost_price or selling_price is being updated, ensure price and selling price validation
            if (isset($dataToUpdate['cost_price']) || isset($dataToUpdate['Selling_price'])) {
                // Ensure the selling price is greater than cost_price and price (if they exist)
                $newCostPrice = $dataToUpdate['cost_price'] ?? $variant->cost_price;
                $newSellingPrice = $dataToUpdate['Selling_price'] ?? $variant->Selling_price;
                $newPrice = $dataToUpdate['price'] ?? $variant->price;

                // Check if selling_price is greater than cost_price and price
                if ($newSellingPrice <= $newCostPrice) {
                    return response()->json(['message' => 'The selling price must be greater than the cost price.'], 400);
                }

                if ($newSellingPrice <= $newPrice) {
                    return response()->json(['message' => 'The selling price must be greater than the price.'], 400);
                }
            }

            // Update the variant fields using query builder
            DB::table('varients')
            ->where('id', $id)
                ->where('product_id', $product->id)
                ->update($dataToUpdate);

            // Retrieve the updated variant
            $updatedVariant = DB::table('varients')
            ->where('id', $id)
                ->where('product_id', $product->id)
                ->first();

            // Return success response with the updated variant
            return response()->json([
                'message' => 'Variant updated successfully!',
                'variant' => $updatedVariant,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }






    public function addImage(Request $request, Product $product, $variantId)
    {
        try {
            // Find the variant and ensure it belongs to the specified product
            $variant = Varient::where('id', $variantId)
                ->where('product_id', $product->id)
                ->first();

            if (!$variant) {
                return response()->json(['message' => 'Variant not found or does not belong to the specified product'], 404);
            }

            // Validate incoming request
            $validatedData = $request->validate([
                'product_images' => 'required|array',
                'product_images.*' => 'image|max:2048', // Ensure each image is valid and less than 2MB
            ]);

            // Process each image
            $imageUrls = [];
            foreach ($validatedData['product_images'] as $image) {
                if ($image->isValid()) {
                    // Store the image
                    $imagePath = $image->store('variant_images', 'public');

                    // Add the image record to the database
                    $imageRecord = new Image();
                    $imageRecord->varient_id = $variant->id;
                    $imageRecord->image_url = asset('storage/' . $imagePath);
                    $imageRecord->save();

                    $imageUrls[] = $imageRecord->image_url;
                } else {
                    throw new \Exception("Invalid image provided.");
                }
            }

            // Return success response
            return response()->json([
                'message' => 'Images added successfully!',
                'new_image_urls' => $imageUrls,
            ], 201);
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($product, $variant)
    {
        // Find the variant scoped to the product
        $variant = Varient::where('product_id', $product)->find($variant);

        if (!$variant) {
            return response()->json([
                'message' => 'Variant not found for the specified product , check your product or varient ID'
            ], 404);
        }

        // Delete the variant (cascading deletes will handle related images)
        $variant->delete();

        // Return a response
        return response()->json(['message' => 'Variant deleted successfully'], 200);
    }

    public function updateImage(Request $request, $variantId, $imageId)
    {
        try {
            // Log the incoming request data
            Log::debug('Incoming request:', [
                'variantId' => $variantId,
                'imageId' => $imageId,
                'request_data' => $request->all()
            ]);

            // Find the variant by variantId
            $variant = Varient::find($variantId);

            if (!$variant) {
                return response()->json(['message' => 'Variant not found.'], 404);
            }

            // Find the specific image by imageId and variantId
            $imageRecord = Image::where('id', $imageId)
                ->where('varient_id', $variantId)
                ->first();

            // Log the image record details
            Log::debug('Image Record:', [
                'image_record' => $imageRecord
            ]);

            if (!$imageRecord) {
                return response()->json(['message' => 'Image not found or does not belong to the specified variant.'], 404);
            }

            // Validate the incoming image
            $validatedData = $request->validate([
                'image' => 'required|image|max:2048', // Ensure the file is valid and <= 2MB
            ]);

            // Check if the file exists in the request
            if (!$request->hasFile('product_image')) {
                return response()->json(['message' => 'No file uploaded.'], 400);
            }

            // Process the new image
            $newImage = $validatedData['product_image'];

            if ($newImage->isValid()) {
                // Log the file path and status
                Log::debug('File is valid. Processing image.', [
                    'new_image_name' => $newImage->getClientOriginalName(),
                    'new_image_mime' => $newImage->getMimeType()
                ]);

                // Delete the old image file from storage
                $oldImagePath = str_replace(asset('storage/'), '', $imageRecord->image_url);
                if (Storage::exists('public/variant_images/' . $oldImagePath)) {
                    Storage::delete('public/variant_images/' . $oldImagePath);
                    Log::debug('Old image deleted.', ['old_image_path' => $oldImagePath]);
                }

                // Store the new image
                $newImagePath = $newImage->store('variant_images', 'public');
                $newImageUrl = asset('storage/' . $newImagePath);

                // Update the image record in the database
                $imageRecord->update(['image_url' => $newImageUrl]);

                // Return success response
                return response()->json([
                    'message' => 'Image updated successfully!',
                    'updated_image_url' => $newImageUrl,
                ], 200);
            } else {
                return response()->json(['message' => 'Invalid image provided.'], 422);
            }
        } catch (\Exception $e) {
            // Handle other exceptions and log the error message
            Log::error('An error occurred while updating the image:', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'An error occurred while updating the image.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}




