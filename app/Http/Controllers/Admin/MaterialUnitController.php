<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaterialUnit;
use Illuminate\Http\Request;

class MaterialUnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $units = MaterialUnit::orderBy('name')->paginate(15);

        return view('admin.material_units.index', compact('units'));
    }

    public function create()
    {
        return view('admin.material_units.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:material_units,name',
            'description' => 'nullable|string',
        ]);

        MaterialUnit::create($data);

        return redirect()->route('admin.material-units.index')
            ->with('success', 'Material unit created successfully.');
    }

    public function edit(MaterialUnit $material_unit)
    {
        return view('admin.material_units.edit', ['unit' => $material_unit]);
    }

    public function update(Request $request, MaterialUnit $material_unit)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:material_units,name,' . $material_unit->id,
            'description' => 'nullable|string',
        ]);

        $material_unit->update($data);

        return redirect()->route('admin.material-units.index')
            ->with('success', 'Material unit updated successfully.');
    }

    public function destroy(MaterialUnit $material_unit)
    {
        $material_unit->delete();

        return redirect()->route('admin.material-units.index')
            ->with('success', 'Material unit deleted successfully.');
    }
}


