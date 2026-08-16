<?php

namespace App\Services;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class SaleService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        
    }

    public function create(array $data, User $user): Sale
    {
        return DB::transaction(function () use ($data, $user) {

            /*
             * Ambil dan lock semua produk terlebih dahulu.
             *
             * lockForUpdate() mencegah dua transaksi bersamaan
             * menggunakan stok yang sama.
             */
            $products = $this->getProducts($data['items']);

            /*
             * Hitung semua item transaksi.
             */
            $calculation = $this->calculateItems(
                $data['items'],
                $products
            );

            $subtotal = $calculation['subtotal'];
            $totalItem = $calculation['total_item'];
            $items = $calculation['items'];

            /*
             * Hitung discount.
             */
            $discount = (float) ($data['discount'] ?? 0);

            if ($discount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount' => [
                        'Discount tidak boleh lebih besar dari subtotal.'
                    ],
                ]);
            }

            /*
             * Tax.
             */
            $tax = (float) ($data['tax'] ?? 0);

            /*
             * Grand total.
             */
            $grandTotal = $subtotal - $discount + $tax;

            if ($grandTotal < 0) {
                throw ValidationException::withMessages([
                    'grand_total' => [
                        'Grand total tidak boleh kurang dari 0.'
                    ],
                ]);
            }

            /*
             * Payment.
             */
            $payment = (float) $data['payment'];

            if ($payment < $grandTotal) {
                throw ValidationException::withMessages([
                    'payment' => [
                        'Pembayaran kurang dari total transaksi.'
                    ],
                ]);
            }

            /*
             * Kembalian.
             */
            $change = $payment - $grandTotal;

            /*
             * Buat nomor invoice.
             */
            $invoiceNumber = $this->generateInvoiceNumber();

            /*
             * Buat Sale.
             */
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => $user->id,
                'transaction_date' => now(),

                'total_item' => $totalItem,

                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'grand_total' => $grandTotal,

                'payment' => $payment,
                'change' => $change,

                'payment_method' => $data['payment_method'],
                'status' => 'completed',
                'note' => $data['note'] ?? null,
            ]);

            /*
             * Buat SaleItem, update stock,
             * dan buat StockMovement.
             */
            foreach ($items as $item) {
                $this->createSaleItemAndUpdateStock(
                    $sale,
                    $item,
                    $user
                );
            }

            /*
             * Load relasi agar siap digunakan oleh Resource.
             */
            $sale->load([
                'user',
                'items.product',
            ]);

            return $sale;
        });
    }

    /**
     * Mengambil produk berdasarkan item transaksi.
     *
     * Semua row product di-lock selama transaction berlangsung.
     */
    private function getProducts(array $items)
    {
        $productIds = collect($items)
            ->pluck('product_id')
            ->unique()
            ->values();

        return Product::query()
            ->whereIn('id', $productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    /**
     * Menghitung subtotal transaksi.
     */
    private function calculateItems(
        array $items,
        $products
    ): array {
        $subtotal = 0;
        $totalItem = 0;
        $calculatedItems = [];

        foreach ($items as $item) {

            $productId = (int) $item['product_id'];
            $qty = (int) $item['qty'];

            /*
             * Product harus tersedia.
             */
            $product = $products->get($productId);

            if (!$product) {
                throw ValidationException::withMessages([
                    'items' => [
                        "Produk dengan ID {$productId} tidak ditemukan."
                    ],
                ]);
            }

            /*
             * Validasi stock.
             */
            if ($product->stock < $qty) {
                throw ValidationException::withMessages([
                    'items' => [
                        "Stok {$product->name} tidak mencukupi. " .
                        "Stok tersedia: {$product->stock}."
                    ],
                ]);
            }

            /*
             * Harga selalu diambil dari database.
             * Jangan percaya harga dari mobile.
             */
            $price = (float) $product->selling_price;

            $itemSubtotal = $price * $qty;

            $subtotal += $itemSubtotal;
            $totalItem += $qty;

            $calculatedItems[] = [
                'product' => $product,
                'qty' => $qty,
                'selling_price' => $price,
                'subtotal' => $itemSubtotal,
            ];
        }

        return [
            'subtotal' => $subtotal,
            'total_item' => $totalItem,
            'items' => $calculatedItems,
        ];
    }

    /**
     * Membuat SaleItem dan StockMovement.
     */
    private function createSaleItemAndUpdateStock(
        Sale $sale,
        array $item,
        User $user
    ): void {
        $product = $item['product'];

        /*
         * Simpan stok sebelum transaksi.
         */
        $stockBefore = $product->stock;

        /*
         * Hitung stok setelah transaksi.
         */
        $stockAfter = $stockBefore - $item['qty'];

        /*
         * Buat SaleItem.
         */
        $sale->items()->create([
            'product_id' => $product->id,
            'price' => $item['selling_price'],
            'qty' => $item['qty'],
            'subtotal' => $item['subtotal'],
        ]);

        /*
         * Update stok product.
         */
        $product->update([
            'stock' => $stockAfter,
        ]);

        /*
         * Catat pergerakan stok.
         *
         * quantity negatif karena stok keluar.
         */
        StockMovement::create([
            'product_id' => $product->id,

            'type' => 'sale',

            'quantity' => -$item['qty'],

            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,

            'reference_type' => Sale::class,
            'reference_id' => $sale->id,

            'user_id' => $user->id,

            'note' => 'Penjualan ' . $sale->invoice_number,
        ]);
    }

    /**
     * Generate nomor invoice unik.
     */
    private function generateInvoiceNumber(): string
    {
        do {
            $invoiceNumber =
                'INV-' .
                now()->format('YmdHis') .
                '-' .
                strtoupper(Str::random(4));

        } while (
            Sale::where(
                'invoice_number',
                $invoiceNumber
            )->exists()
        );

        return $invoiceNumber;
    }

    public function update( Sale $sale, array $data,User $user ): Sale 
    {
        return DB::transaction(function () use ($sale, $data, $user) {

            if ($sale->status === 'completed') {
                throw ValidationException::withMessages([
                    'sale' => [
                        'Transaksi yang sudah selesai tidak dapat diubah.'
                    ],
                ]);
            }

            /*
            * Ambil item lama.
            */
            $sale->load('items');

            /*
            * Kembalikan stok transaksi lama.
            */
            foreach ($sale->items as $oldItem) {

                $product = Product::query()
                    ->lockForUpdate()
                    ->findOrFail($oldItem->product_id);

                $stockBefore = $product->stock;
                $stockAfter = $stockBefore + $oldItem->qty;

                $product->update([
                    'stock' => $stockAfter,
                ]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'sale_update',
                    'quantity' => $oldItem->qty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'user_id' => $user->id,
                    'note' => 'Pengembalian stok transaksi ' .
                        $sale->invoice_number,
                ]);
            }

            /*
            * Hapus SaleItem lama.
            */
            $sale->items()->delete();

            /*
            * Ambil produk baru.
            */
            $products = $this->getProducts($data['items']);

            /*
            * Hitung ulang transaksi.
            */
            $calculation = $this->calculateItems(
                $data['items'],
                $products
            );

            $subtotal = $calculation['subtotal'];
            $totalItem = $calculation['total_item'];
            $items = $calculation['items'];

            $discount = (float) ($data['discount'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);

            if ($discount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount' => [
                        'Discount tidak boleh lebih besar dari subtotal.'
                    ],
                ]);
            }

            $grandTotal = $subtotal - $discount + $tax;

            $payment = (float) $data['payment'];

            if ($payment < $grandTotal) {
                throw ValidationException::withMessages([
                    'payment' => [
                        'Pembayaran kurang dari total transaksi.'
                    ],
                ]);
            }

            $change = $payment - $grandTotal;

            /*
            * Update Sale.
            */
            $sale->update([
                'total_item' => $totalItem,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'payment' => $payment,
                'change' => $change,
                'payment_method' => $data['payment_method'],
                'note' => $data['note'] ?? null,
            ]);

            /*
            * Buat kembali SaleItem + kurangi stok.
            */
            foreach ($items as $item) {
                $this->createSaleItemAndUpdateStock(
                    $sale,
                    $item,
                    $user
                );
            }

            $sale->load([
                'user',
                'items.product',
            ]);

            return $sale;
        });
    }

}
