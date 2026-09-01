<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'category_id' => [
                'nullable',
                'string',
                'exists:categories,name',
            ],
        ]);

        $perPage = $request->get('per_page', 12); 

        $products = Product::with('category')
            ->when(
                $request->category_id,
                function ($query, $categoryName) {
                    $query->whereHas('category', function ($query) use ($categoryName) {
                        $query->where('name', $categoryName);
                    });
                }
            )
            ->latest()
            ->paginate($perPage); 

        return ProductResource::collection($products)->additional([
            'success' => true,
            'message' => 'Data produk berhasil diambil.',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        // Cukup panggil method getValidatedData() dari Request
        $product = Product::create($request->getValidatedData());
        $product->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan.',
            'data'    => new ProductResource($product),
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Detail produk berhasil diambil.',
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        // Update data produk menggunakan data dari Request
        $product->update($request->getValidatedData($product));

        $product->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui.',
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus.',
        ]);
    }
}
