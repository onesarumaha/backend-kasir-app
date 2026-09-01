<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

        // User melakukan transaksi
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // User membuat kategori
    public function createdCategories()
    {
        return $this->hasMany(Category::class, 'created_by');
    }

    // User mengubah kategori
    public function updatedCategories()
    {
        return $this->hasMany(Category::class, 'updated_by');
    }

    // User membuat produk
    public function createdProducts()
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    // User mengubah produk
    public function updatedProducts()
    {
        return $this->hasMany(Product::class, 'updated_by');
    }

    // Pergerakan stok yang dilakukan user
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
