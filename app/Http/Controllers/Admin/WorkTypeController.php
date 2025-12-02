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
        return view('admin.work_types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:work_types,name',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        WorkType::create($data);

        return redirect()->route('admin.work-types.index')
            ->with('success', 'Work type created successfully.');
    }

    public function edit(WorkType $work_type)
    {
        return view('admin.work_types.edit', ['workType' => $work_type]);
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

        return redirect()->route('admin.work-types.index')
            ->with('success', 'Work type updated successfully.');
    }

    public function destroy(WorkType $work_type)
    {
        $work_type->delete();

        return redirect()->route('admin.work-types.index')
            ->with('success', 'Work type deleted successfully.');
    }
}


