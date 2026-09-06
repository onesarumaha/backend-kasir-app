<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SaleResource;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Sale::with(['user', 'tenant', 'items.product'])
            ->latest();

        if (isset($user->tenant_id)) {
            $query->where('tenant_id', $user->tenant_id);
        }

        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('transaction_date', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $orders = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar riwayat pesanan berhasil diambil',
            'data' => SaleResource::collection($orders),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total_items' => $orders->total(),
                'per_page' => $orders->perPage(),
            ]
        ], 200);
    }

    public function show($id)
    {
        $order = Sale::with(['user', 'tenant', 'items.product'])->find($id);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail transaksi berhasil diambil',
            'data' => new SaleResource($order)
        ], 200);
    }

    public function cancel(Request $request, $id)
    {
        $order = Sale::with('items')->find($id);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        if ($order->status === 'canceled') {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi ini sudah dibatalkan sebelumnya'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $order->status = 'canceled';
            $order->save();

            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)->increment('stock', $item->qty);
                }
            }

            DB::commit();

            // Load ulang relasi agar response resource lengkap
            $order->load(['user', 'tenant', 'items.product']);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dibatalkan dan stok telah dikembalikan',
                'data' => new SaleResource($order)
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}
