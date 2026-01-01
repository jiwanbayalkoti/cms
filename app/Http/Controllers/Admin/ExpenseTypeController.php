<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseType;
use Illuminate\Http\Request;

class ExpenseTypeController extends Controller
{
    public function index()
    {
        $expenseTypes = ExpenseType::orderBy('name')->get();
        return view('admin.expense_types.index', compact('expenseTypes'));
    }
    public function create()
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.expense-types.index');
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:expense_types,name',
        ]);
        $expenseType = ExpenseType::create($data);
        
        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Expense type added.',
                'expenseType' => [
                    'id' => $expenseType->id,
                    'name' => $expenseType->name,
                ],
            ]);
        }
        
        return redirect()->route('admin.expense-types.index')->with('success', 'Expense type added.');
    }
    public function edit(ExpenseType $expenseType)
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'expenseType' => [
                    'id' => $expenseType->id,
                    'name' => $expenseType->name,
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.expense-types.index');
    }
    public function update(Request $request, ExpenseType $expenseType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:expense_types,name,' . $expenseType->id,
        ]);
        $expenseType->update($data);
        
        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Expense type updated.',
                'expenseType' => [
                    'id' => $expenseType->id,
                    'name' => $expenseType->name,
                ],
            ]);
        }
        
        return redirect()->route('admin.expense-types.index')->with('success', 'Expense type updated.');
    }
    public function destroy(Request $request, ExpenseType $expenseType)
    {
        $expenseType->delete();
        
        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Expense type deleted.',
            ]);
        }
        
        return redirect()->route('admin.expense-types.index')->with('success', 'Expense type deleted.');
    }
}

