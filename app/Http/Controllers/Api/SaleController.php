<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaleRequest;
use App\Http\Resources\SaleReceiptResource;
use App\Http\Resources\SaleResource;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Services\SaleService;

class SaleController extends Controller
{
    public function __construct(
        protected SaleService $saleService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::query()
            ->with([
                'user',
                'items.product',
            ])
            ->latest('transaction_date')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Data transaksi berhasil diambil',
            'data' => SaleResource::collection($sales),
        ]);
    
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SaleRequest $request)
    {
        $sale = $this->saleService->create(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dibuat',
            'data' => new SaleResource($sale),
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $sale->load([
            'user',
            'items.product',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail transaksi berhasil diambil',
            'data' => new SaleResource($sale),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SaleRequest $request, Sale $sale)
    {
        $sale = $this->saleService->update(
            $sale,
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil diperbarui',
            'data' => new SaleResource($sale),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        if ($sale->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi yang sudah selesai tidak dapat dihapus.',
            ], 422);
        }

        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dihapus.',
        ]);
    }

    public function receipt(Sale $sale)
    {
        $sale->load([
            'user',
            'items.product',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data struk berhasil diambil.',
            'data' => new SaleReceiptResource($sale),
        ]);
    }
}
