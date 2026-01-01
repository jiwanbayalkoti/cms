<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialNameRequest;
use App\Http\Requests\UpdateMaterialNameRequest;
use App\Models\MaterialName;
use Illuminate\Http\Request;

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
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.material-names.index');
    }

    public function store(StoreMaterialNameRequest $request)
    {
        $materialName = MaterialName::create($request->validated());

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Material name created successfully.',
                'materialName' => [
                    'id' => $materialName->id,
                    'name' => $materialName->name,
                ],
            ]);
        }

        return redirect()->route('admin.material-names.index')
            ->with('success', 'Material name created successfully.');
    }

    public function show(MaterialName $material_name)
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'materialName' => [
                    'id' => $material_name->id,
                    'name' => $material_name->name,
                    'created_at' => $material_name->created_at->format('M d, Y H:i'),
                    'updated_at' => $material_name->updated_at->format('M d, Y H:i'),
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.material-names.index');
    }

    public function edit(MaterialName $material_name)
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'materialName' => [
                    'id' => $material_name->id,
                    'name' => $material_name->name,
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.material-names.index');
    }

    public function update(UpdateMaterialNameRequest $request, MaterialName $material_name)
    {
        $material_name->update($request->validated());

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Material name updated successfully.',
                'materialName' => [
                    'id' => $material_name->id,
                    'name' => $material_name->name,
                ],
            ]);
        }

        return redirect()->route('admin.material-names.index')
            ->with('success', 'Material name updated successfully.');
    }

    public function destroy(Request $request, MaterialName $material_name)
    {
        $material_name->delete();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Material name deleted successfully.',
            ]);
        }

        return redirect()->route('admin.material-names.index')
            ->with('success', 'Material name deleted successfully.');
    }
}
