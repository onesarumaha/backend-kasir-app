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

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Tenant::query();

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        return TenantResource::collection($query->latest()->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TenantResquest $request)
    {
        $data = $request->validated();
        
        $data['slug'] = Str::slug($request->name) . '-' . Str::random(5);
        $data['is_active'] = true;

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('tenants', 'public');
        }

        $tenant = Tenant::create($data);

        return (new TenantResource($tenant))->additional(['message' => 'Tenant baru berhasil ditambahkan']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tenant = Tenant::findOrFail($id);

        return new TenantResource($tenant);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTenantResquest $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);
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

        return (new TenantResource($tenant))->additional(['message' => 'Data tenant berhasil diperbarui']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->delete();

        return response()->json(['message' => 'Tenant berhasil dihapus']);
    }

    public function toggleStatus(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->is_active = !$tenant->is_active;
        $tenant->save();

        return (new TenantResource($tenant))->additional(['message' => 'Status tenant berhasil diubah']);
    }
}
