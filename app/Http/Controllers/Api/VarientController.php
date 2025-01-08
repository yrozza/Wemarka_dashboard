<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VarientResource;
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
        return response()->json($product->variants);
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
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
