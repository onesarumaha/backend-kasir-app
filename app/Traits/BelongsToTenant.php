<?php

namespace App\Traits;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    /**
     * Boot the trait for a model.
     */
    protected static function bootBelongsToTenant(): void
    {
        // 1. Terapkan Global Scope (Auto-filter SELECT)
        static::addGlobalScope(new TenantScope());

        // 2. Otomatis pasang tenant_id saat INSERT data baru
        static::creating(function ($model) {
            if (Auth::check()) {
                /** @var User|null $user */
                $user = Auth::user();

                if ($user && isset($user->tenant_id) && !$model->tenant_id) {
                    $model->tenant_id = $user->tenant_id;
                }
            }
        });
    }

    /**
     * Relasi ke model Tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}