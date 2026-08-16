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
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data produk berhasil diambil.',
            'data' => ProductResource::collection($products),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create([
            'category_id' => $request->category_id,
            'code' => $request->code,
            'barcode' => $request->barcode,
            'name' => $request->name,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'stock' => $request->input('stock', 0),
            'minimum_stock' => $request->input('minimum_stock', 0),
            'unit' => $request->input('unit', 'pcs'),
            'image' => $request->image,
            'status' => $request->input('status', true),
            'created_by' => $request->user()->id,
        ]);

        $product->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan.',
            'data' => new ProductResource($product),
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
        $product->update([
            'category_id' => $request->category_id,
            'code' => $request->code,
            'barcode' => $request->barcode,
            'name' => $request->name,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'minimum_stock' => $request->input(
                'minimum_stock',
                $product->minimum_stock
            ),
            'unit' => $request->input(
                'unit',
                $product->unit
            ),
            'image' => $request->image,
            'status' => $request->input(
                'status',
                $product->status
            ),
            'updated_by' => $request->user()->id,
        ]);

        $product->fresh()->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui.',
            'data' => new ProductResource($product),
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
