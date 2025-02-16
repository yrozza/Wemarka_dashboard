<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */






    public function index()
    {
        try {
            // Authorization
            Gate::authorize('viewAny', Package::class);

            // Cache key for paginated results
            $cacheKey = "packages_page_" . request('page', 1);

            // Caching packages for 10 minutes
            $packages = Cache::remember($cacheKey, now()->addMinutes(10), function () {
                return Package::paginate(10);
            });

            // Load relations like in searchByName()
            $packages->load(['varients.product']);

            return PackageResource::collection($packages);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving packages.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }





    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'required|string|unique:packages,name',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'varients' => 'array', // Ensure varients is an array
                'varients.*' => 'exists:varients,id' // Ensure each varient exists
            ]);

            // Authorization
            Gate::authorize('create', Package::class);

            // Create the package
            $package = Package::create([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'] ?? null,
                'price' => $validatedData['price']
            ]);

            // Return success response with the created package
            return response()->json([
                'message' => 'Package created successfully.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the package.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(Package $package)
    {
        try {
            Gate::authorize('view', $package);

            $cachedPackage = Cache::remember("package_{$package->id}", 3600, function () use ($package) {
                return new PackageResource($package->load('varients.product'));
            });

            return $cachedPackage;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving the package.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }






    public function searchByName($name)
    {
        try {
            Gate::authorize('viewAny', Package::class);

            $name = trim($name);
            $cacheKey = "package_search_" . md5($name . request('page', 1));

            $packages = Cache::remember($cacheKey, 600, function () use ($name) {
                return Package::where('name', 'LIKE', "%{$name}%")->paginate(10);
            });

            if ($packages->isEmpty()) {
                return response()->json(['message' => 'No packages found.'], 404);
            }

            // Load relationships dynamically like in `show`
            $packages->load('varients.product');

            return PackageResource::collection($packages);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while searching for packages.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }








    /**
     * Update the specified resource in storage.
     */
    public function updateBasicInfo(Request $request, $id)
    {
        // Validate the basic fields
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'nullable|numeric|min:0',
        ]);

        try {
            // Find the package to update, or fail if it doesn't exist
            $package = Package::findOrFail($id);

            Gate::authorize('update', $package);

            // Update only the fields that were provided
            $package->update(array_filter($validated, fn($value) => !is_null($value)));

            return response()->json([
                'message' => 'Package basic information updated successfully!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the package.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function replaceVariant(Request $request, $packageId, $variantId)
    {
        try {
            // Find the package to update, or fail if it doesn't exist
            $package = Package::findOrFail($packageId);

            // Check if the package has the variant attached
            $currentVariant = $package->varients()->find($variantId);

            if (!$currentVariant) {
                // If the variant is not found in the package, return a not found response
                return response()->json([
                    'message' => 'Variant not found in this package.',
                ], 404);
            }

            // Check if the user is authorized to update the package
            Gate::authorize('update', $package);

            // Validate the request data to ensure a new variant is provided
            $validated = $request->validate([
                'variant_id' => 'required|exists:varients,id', // Ensure the new variant exists
            ]);

            // Replace the current variant with the new one in the pivot table
            $newVariantId = $validated['variant_id'];

            // Update the pivot table (Assuming your relationship uses a pivot table, update accordingly)
            $package->varients()->where('varient_id', $variantId)->update(['varient_id' => $newVariantId]);

            return response()->json([
                'message' => 'Variant replaced successfully!',
                'data' => new PackageResource($package->load('varients.product')), // Load the variants with their products
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while replacing the variant.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteVariants(Request $request, $packageId)
    {
        try {
            // Find the package, or fail if it doesn't exist
            $package = Package::findOrFail($packageId);

            // Validate the request to ensure an array of package_product IDs is provided
            $validated = $request->validate([
                'package_product_ids' => 'required|array|min:1',
                'package_product_ids.*' => 'exists:package_product,id', // Ensure each ID exists in the pivot table
            ]);

            $packageProductIds = $validated['package_product_ids'];

            // Get package_product IDs that actually belong to this package
            $existingIds = DB::table('package_product')
            ->where('package_id', $packageId)
                ->whereIn('id', $packageProductIds)
                ->pluck('id')
                ->toArray();

            // Find package_product IDs that were requested but do NOT belong to this package
            $invalidIds = array_diff($packageProductIds, $existingIds);

            if (!empty($invalidIds)) {
                return response()->json([
                    'message' => 'Some package_product IDs do not belong to this package.',
                    'invalid_ids' => $invalidIds,
                ], 400);
            }

            // Authorize the action
            Gate::authorize('delete', $package);

            // Delete the valid package_product records from the pivot table
            DB::table('package_product')->whereIn('id', $existingIds)->delete();

            return response()->json([
                'message' => 'Variants removed successfully!',
                'data' => new PackageResource($package->load('varients.product')), // Load updated variants
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while removing the variants.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }












    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $package = Package::findOrFail($id);

        Gate::authorize('delete', $package);

        $package->delete();

        return response()->json(['message' => 'Package deleted successfully!'], 200);
    }

}
