<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::withCount('customers')->orderBy('name')->paginate(15);

        return view('packages.index', compact('packages'));
    }

    public function create()
    {
        return view('packages.create');
    }

    public function store(Request $request)
    {
        Package::create($this->rules($request));

        return redirect()->route('packages.index')->with('success', 'Paket dibuat.');
    }

    public function edit(Package $package)
    {
        return view('packages.edit', compact('package'));
    }

    public function update(Request $request, Package $package)
    {
        $package->update($this->rules($request));

        return redirect()->route('packages.index')->with('success', 'Paket diperbarui.');
    }

    public function destroy(Package $package)
    {
        if ($package->customers()->exists()) {
            return back()->withErrors(['package' => 'Paket masih dipakai oleh pelanggan.']);
        }
        $package->delete();

        return redirect()->route('packages.index')->with('success', 'Paket dihapus.');
    }

    protected function rules(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price_idr' => ['required', 'integer', 'min:0'],
            'speed_mbps' => ['nullable', 'integer', 'min:0'],
            'mikrotik_profile' => ['nullable', 'string', 'max:64'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
