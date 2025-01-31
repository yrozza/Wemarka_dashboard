<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VarientResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
        // Paginate the variants (you can change the perPage number as needed)
        $variants = $product->variants()->paginate(15);  // 15 items per page

        // Modify the Stock_status based on stock level
        $variants->getCollection()->transform(function ($variant) {
            if ($variant->stock <= 0) {
                $variant->Stock_status = 'out_of_stock';
            } elseif ($variant->stock < 10) {
                $variant->Stock_status = 'Almost_finished';
            } else {
                $variant->Stock_status = 'in_stock';
            }
            return $variant;
        });

        // Return the paginated result
        return response()->json($variants);
    }





    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Product $product)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'color' => 'required|string|max:100',
            'volume' => 'required|string|max:100',
            'varient' => 'required|string|max:100',
            'Pcode' => 'required|string|max:50',
            'price' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'weight' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:10',
            'product_images' => 'required|array', // Ensure it's an array of images
            'product_images.*' => 'image', // Validate each file in the array is an image
        ], [
            'stock.min' => 'The stock must be at least 10 units.'
        ]);

        // Check if the product exists
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Check if the variant already exists
        $existingVariant = Varient::where('product_id', $product->id)
            ->where('color', $request->color)
            ->where('volume', $request->volume)
            ->where('varient', $request->varient)
            ->where('weight', $request->weight)
            ->first();

        if ($existingVariant) {
            return response()->json(['message' => 'Variant with these attributes already exists'], 400);
        }

        // Create a new variant
        $variant = new Varient();
        $variant->color = $validatedData['color'];
        $variant->volume = $validatedData['volume'];
        $variant->varient = $validatedData['varient'];
        $variant->Pcode = $validatedData['Pcode'];
        $variant->weight = $validatedData['weight'];
        $variant->price = $validatedData['price'];
        $variant->stock = $validatedData['stock'];
        $variant->product_id = $product->id; // Associate the variant with the correct product
        $variant->save();

        // Step 3: Handle multiple image uploads and store them in the images table
        $imageUrls = [];
        try {
            foreach ($validatedData['product_images'] as $image) {
                if ($image->isValid()) {
                    // Store the image in the 'variant_images' folder inside the 'public' disk
                    $imagePath = $image->store('variant_images', 'public');

                    // Create a new Image record for each image
                    $imageRecord = new Image();
                    $imageRecord->varient_id = $variant->id; // Associate the image with the created variant
                    $imageRecord->image_url = asset('storage/' . $imagePath); // Save the full URL of the stored image
                    $imageRecord->save();

                    // Add the saved image URL to the response array
                    $imageUrls[] = $imageRecord->image_url;
                } else {
                    throw new \Exception("Image upload failed for one of the images.");
                }
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to store images', 'message' => $e->getMessage()], 500);
        }

        // Return success response with the variant and associated image URLs
        return response()->json([
            'message' => 'Variant added successfully!',
            'variant' => $variant,
            'image_urls' => $imageUrls,
        ], 201);
    }



    /**
     * Display the specified resource.
     */
    public function show($product, $variant)
    {
        // Find the variant scoped to the product
        $variant = Varient::where('product_id', $product)
            ->with('images')
            ->findOrFail($variant);

        // Check the stock and update Stock_status
        if ($variant->stock <= 0) {
            $variant->Stock_status = 'out_of_stock';
        } elseif ($variant->stock < 10) {
            $variant->Stock_status = 'Almost_finished';
        } else {
            $variant->Stock_status = 'in_stock';
        }

        // Return the variant as a JSON response
        return response()->json($variant, 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product, $id)
    {
        try {
            // Find the variant by its ID and ensure it belongs to the specified product
            $variant = Varient::where('id', $id)
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
                'Pcode' => 'nullable|string|max:50',
                'price' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                'weight' => 'nullable|numeric|min:0',
                'stock' => 'nullable|integer|min:10',
            ]);

            // Prepare the data to update, removing null values
            $dataToUpdate = array_filter($validatedData);

            // Check if the incoming data is different from the current data
            $noChangesDetected = true;
            foreach ($dataToUpdate as $key => $value) {
                if ($variant->{$key} !== $value) {
                    $noChangesDetected = false;
                    break;  // No need to check further if a change is found
                }
            }

            // If no changes were detected, return the response
            if ($noChangesDetected) {
                return response()->json([
                    'message' => 'No changes detected',
                ], 200);
            }

            // Update the variant fields if there are changes
            $variant->update($dataToUpdate);

            // Return success response with the updated variant
            return response()->json([
                'message' => 'Variant updated successfully!',
                'variant' => $variant,
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




