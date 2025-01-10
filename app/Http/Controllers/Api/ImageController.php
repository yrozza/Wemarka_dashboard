<?php

namespace App\Http\Controllers\Api;

use App\Models\Varient;
use App\Http\Resources\ImageResource;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($product, $variant)
    {
        $variant = Varient::where('id', $variant)->where('product_id', $product)->firstOrFail();
        $images = $variant->images;

        return ImageResource::collection($images);
    }


    /**
     * Store a newly created resource in storage.
     */


    /**
     * Display the specified resource.
     */
    public function show($product, $variant, $id)
    {
        // Check if the variant belongs to the product
        $variant = Varient::where('id', $variant)
            ->where('product_id', $product)
            ->first();

        // If the variant is not found, return a 404 response
        if (!$variant) {
            return response()->json([
                'message' => 'Variant not found for the specified product , please check your product id and variant id'
            ], 404);
        }

        // Find the image by its ID and ensure it belongs to the variant
        $image = $variant->images()->find($id);

        // If the image is not found, return a 404 response
        if (!$image) {
            return response()->json([
                'message' => 'Image not found for the specified variant.'
            ], 404);
        }

        // Return the image as a resource
        return new ImageResource($image);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $product, $variant, $id)
    {
        try {
            // Validate incoming request
            $validated = $request->validate([
                'image_url' => 'required|string|max:255', // Validate the new URL
            ]);

            // Find the variant to ensure it belongs to the specified product
            $variant = Varient::where('id', $variant)
                ->where('product_id', $product)
                ->first();

            // If the variant is not found, return a 404 response
            if (!$variant) {
                return response()->json([
                    'message' => 'Variant not found for the specified product.'
                ], 404);
            }

            // Find the image by its ID and ensure it belongs to the variant
            $image = $variant->images()->find($id);

            // If the image is not found, return a 404 response
            if (!$image) {
                return response()->json([
                    'message' => 'Image not found for the specified variant.'
                ], 404);
            }

            // Check if the new image_url is the same as the current one
            if ($image->image_url === $validated['image_url']) {
                return response()->json([
                    'message' => 'No change has happened. The provided URL is the same as the current one.',
                    'image' => new ImageResource($image),
                ], 200);
            }

            // Update the image_url field
            $image->image_url = $validated['image_url'];
            $image->save();

            // Return success message and the updated image resource
            return response()->json([
                'message' => 'Image updated successfully.',
                'image' => new ImageResource($image),
            ], 200);
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Catch all other exceptions and return a generic error message
            return response()->json([
                'message' => 'An error occurred while updating the image URL.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    





    /**
     * Remove the specified resource from storage.
     */
    public function destroy($product, $variant, $id)
    {
        // Find the variant to ensure it belongs to the specified product
        $variant = Varient::where('id', $variant)
            ->where('product_id', $product)
            ->first();

        // If the variant is not found, return a 404 response
        if (!$variant) {
            return response()->json([
                'message' => 'Variant not found for the specified product , check your product or varient ID'
            ], 404);
        }

        // Find the image by its ID and ensure it belongs to the variant
        $image = $variant->images()->find($id);

        // If the image is not found, return a 404 response
        if (!$image) {
            return response()->json([
                'message' => 'Image not found for the specified variant.'
            ], 404);
        }

        // Delete the image from storage
        $imagePath = storage_path('app/public/variant_images/' . $image->image_url);
        if (file_exists($imagePath)) {
            unlink($imagePath); // Remove the file from storage
        }

        // Delete the image record from the database
        $image->delete();

        // Return a success response
        return response()->json([
            'message' => 'Image deleted successfully.'
        ], 200);
    }

}
