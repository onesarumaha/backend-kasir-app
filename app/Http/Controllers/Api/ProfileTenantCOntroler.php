<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantResquest;
use App\Http\Requests\UpdateTenantResquest;
use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileTenantCOntroler extends Controller
{

    public function show()
    {
        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            return response()->json(['message' => 'User tidak terhubung ke tenant manapun'], 404);
        }

        return new TenantResource($tenant);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTenantResquest $request)
    {
        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            return response()->json(['message' => 'User tidak terhubung ke tenant manapun'], 404);
        }

        $data = $request->validated();

        if ($tenant->name !== $request->name) {
            $data['slug'] = Str::slug($request->name) . '-' . Str::random(5);
        }

        if ($request->hasFile('logo')) {
            if ($tenant->logo && Storage::disk('public')->exists($tenant->logo)) {
                Storage::disk('public')->delete($tenant->logo);
            }
            $data['logo'] = $request->file('logo')->store('tenants', 'public');
        }

        $tenant->update($data);

        return (new TenantResource($tenant))
            ->additional(['message' => 'Profil toko berhasil diperbarui']);
    }

  
}
