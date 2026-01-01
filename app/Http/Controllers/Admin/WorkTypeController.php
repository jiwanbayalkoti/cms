<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkType;
use Illuminate\Http\Request;

class WorkTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $workTypes = WorkType::orderBy('name')->paginate(15);

        return view('admin.work_types.index', compact('workTypes'));
    }

    public function create()
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.work-types.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:work_types,name',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $workType = WorkType::create($data);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Work type created successfully.',
                'workType' => [
                    'id' => $workType->id,
                    'name' => $workType->name,
                    'description' => $workType->description,
                    'is_active' => $workType->is_active,
                ],
            ]);
        }

        return redirect()->route('admin.work-types.index')
            ->with('success', 'Work type created successfully.');
    }

    public function edit(WorkType $work_type)
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'workType' => [
                    'id' => $work_type->id,
                    'name' => $work_type->name,
                    'description' => $work_type->description,
                    'is_active' => $work_type->is_active,
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.work-types.index');
    }

    public function update(Request $request, WorkType $work_type)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:work_types,name,' . $work_type->id,
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $work_type->update($data);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Work type updated successfully.',
                'workType' => [
                    'id' => $work_type->id,
                    'name' => $work_type->name,
                    'description' => $work_type->description,
                    'is_active' => $work_type->is_active,
                ],
            ]);
        }

        return redirect()->route('admin.work-types.index')
            ->with('success', 'Work type updated successfully.');
    }

    public function destroy(Request $request, WorkType $work_type)
    {
        $work_type->delete();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Work type deleted successfully.',
            ]);
        }

        return redirect()->route('admin.work-types.index')
            ->with('success', 'Work type deleted successfully.');
    }
}


