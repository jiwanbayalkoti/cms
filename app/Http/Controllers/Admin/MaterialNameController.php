<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialNameRequest;
use App\Http\Requests\UpdateMaterialNameRequest;
use App\Models\MaterialName;

class MaterialNameController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $materialNames = MaterialName::orderBy('name')->paginate(15);

        return view('admin.material_names.index', compact('materialNames'));
    }

    public function create()
    {
        return view('admin.material_names.create');
    }

    public function store(StoreMaterialNameRequest $request)
    {
        MaterialName::create($request->validated());

        return redirect()->route('admin.material-names.index')
            ->with('success', 'Material name created successfully.');
    }

    public function show(MaterialName $material_name)
    {
        return view('admin.material_names.show', ['materialName' => $material_name]);
    }

    public function edit(MaterialName $material_name)
    {
        return view('admin.material_names.edit', ['materialName' => $material_name]);
    }

    public function update(UpdateMaterialNameRequest $request, MaterialName $material_name)
    {
        $material_name->update($request->validated());

        return redirect()->route('admin.material-names.index')
            ->with('success', 'Material name updated successfully.');
    }

    public function destroy(MaterialName $material_name)
    {
        $material_name->delete();

        return redirect()->route('admin.material-names.index')
            ->with('success', 'Material name deleted successfully.');
    }
}
