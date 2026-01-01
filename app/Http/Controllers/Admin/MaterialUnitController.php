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
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.material-units.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:material_units,name',
            'description' => 'nullable|string',
        ]);

        $unit = MaterialUnit::create($data);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Material unit created successfully.',
                'unit' => [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'description' => $unit->description,
                ],
            ]);
        }

        return redirect()->route('admin.material-units.index')
            ->with('success', 'Material unit created successfully.');
    }

    public function edit(MaterialUnit $material_unit)
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'unit' => [
                    'id' => $material_unit->id,
                    'name' => $material_unit->name,
                    'description' => $material_unit->description,
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.material-units.index');
    }

    public function update(Request $request, MaterialUnit $material_unit)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:material_units,name,' . $material_unit->id,
            'description' => 'nullable|string',
        ]);

        $material_unit->update($data);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Material unit updated successfully.',
                'unit' => [
                    'id' => $material_unit->id,
                    'name' => $material_unit->name,
                    'description' => $material_unit->description,
                ],
            ]);
        }

        return redirect()->route('admin.material-units.index')
            ->with('success', 'Material unit updated successfully.');
    }

    public function destroy(Request $request, MaterialUnit $material_unit)
    {
        $material_unit->delete();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Material unit deleted successfully.',
            ]);
        }

        return redirect()->route('admin.material-units.index')
            ->with('success', 'Material unit deleted successfully.');
    }
}


