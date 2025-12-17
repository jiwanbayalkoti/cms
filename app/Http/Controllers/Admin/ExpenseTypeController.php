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
        return view('admin.expense_types.create');
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:expense_types,name',
        ]);
        ExpenseType::create($data);
        return redirect()->route('admin.expense-types.index')->with('success', 'Expense type added.');
    }
    public function edit(ExpenseType $expenseType)
    {
        return view('admin.expense_types.edit', compact('expenseType'));
    }
    public function update(Request $request, ExpenseType $expenseType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:expense_types,name,' . $expenseType->id,
        ]);
        $expenseType->update($data);
        return redirect()->route('admin.expense-types.index')->with('success', 'Expense type updated.');
    }
    public function destroy(ExpenseType $expenseType)
    {
        $expenseType->delete();
        return redirect()->route('admin.expense-types.index')->with('success', 'Expense type deleted.');
    }
}

