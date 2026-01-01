<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $positions = Position::withCount('staff')->latest()->paginate(10);
        return view('admin.positions.index', compact('positions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.positions.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:positions,name',
            'description' => 'nullable|string',
            'salary_range' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $position = Position::create($validated);
        $position->loadCount('staff');

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Position created successfully.',
                'position' => [
                    'id' => $position->id,
                    'name' => $position->name,
                    'description' => $position->description,
                    'salary_range' => $position->salary_range,
                    'is_active' => $position->is_active,
                    'staff_count' => $position->staff_count,
                ],
            ]);
        }

        return redirect()->route('admin.positions.index')
            ->with('success', 'Position created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position)
    {
        $position->load('staff');
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'position' => [
                    'id' => $position->id,
                    'name' => $position->name,
                    'description' => $position->description,
                    'salary_range' => $position->salary_range,
                    'is_active' => $position->is_active,
                    'staff_count' => $position->staff->count(),
                    'created_at' => $position->created_at->format('M d, Y H:i'),
                    'updated_at' => $position->updated_at->format('M d, Y H:i'),
                    'staff' => $position->staff->map(function($s) {
                        return [
                            'id' => $s->id,
                            'name' => $s->name,
                            'email' => $s->email,
                        ];
                    }),
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.positions.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position)
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'position' => [
                    'id' => $position->id,
                    'name' => $position->name,
                    'description' => $position->description,
                    'salary_range' => $position->salary_range,
                    'is_active' => $position->is_active,
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.positions.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:positions,name,' . $position->id,
            'description' => 'nullable|string',
            'salary_range' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $position->update($validated);
        $position->loadCount('staff');

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Position updated successfully.',
                'position' => [
                    'id' => $position->id,
                    'name' => $position->name,
                    'description' => $position->description,
                    'salary_range' => $position->salary_range,
                    'is_active' => $position->is_active,
                    'staff_count' => $position->staff_count,
                ],
            ]);
        }

        return redirect()->route('admin.positions.index')
            ->with('success', 'Position updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Position $position)
    {
        // Check if position has staff members
        if ($position->staff()->count() > 0) {
            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete position. There are staff members assigned to this position.',
                ], 422);
            }
            
            return redirect()->route('admin.positions.index')
                ->with('error', 'Cannot delete position. There are staff members assigned to this position.');
        }

        $position->delete();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Position deleted successfully.',
            ]);
        }

        return redirect()->route('admin.positions.index')
            ->with('success', 'Position deleted successfully.');
    }
}
