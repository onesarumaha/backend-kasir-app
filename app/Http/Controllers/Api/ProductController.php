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
            'category_id' => ['nullable', 'string'],
            'search'      => ['nullable', 'string'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = $request->get('per_page', 12);

        $products = Product::with('category')
            ->when(auth()->check() && auth()->user()->tenant_id, function ($query) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            })
            ->when($request->filled('category_id') && $request->category_id !== 'ALL', function ($query) use ($request) {
                $cat = $request->category_id;
                $query->where(function ($q) use ($cat) {
                    if (is_numeric($cat)) {
                        $q->where('category_id', $cat);
                    } else {
                        $q->whereHas('category', function ($qName) use ($cat) {
                            $qName->where('name', $cat);
                        });
                    }
                });
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
                });
            })
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
