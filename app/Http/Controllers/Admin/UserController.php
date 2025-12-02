<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Support\CompanyContext;
use App\Rules\StrongPassword;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['admin', 'super_admin']);
    }

    public function index()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $query = User::with('company')->orderBy('name');

        if ((int) $companyId !== 1) {
            $query->where(function($q) use ($companyId) {
                $q->whereNull('company_id')->orWhere('company_id', $companyId);
            });
        }

        $users = $query->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        return view('admin.users.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'string', 'min:8', 'confirmed', new StrongPassword()],
            'company_id' => 'nullable|exists:companies,id',
            'role' => 'required|in:super_admin,admin,user',
            'is_admin' => 'nullable|boolean',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->company_id = $validated['company_id'] ?? CompanyContext::getActiveCompanyId();
        $user->role = $validated['role'];
        $user->is_admin = $request->boolean('is_admin');
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $companies = Company::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'companies'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'role' => 'required|in:super_admin,admin,user',
            'is_admin' => 'nullable|boolean',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'string', 'min:8', 'confirmed', new StrongPassword()],
        ]);

        if (array_key_exists('name', $validated)) {
            $user->name = $validated['name'];
        }
        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'];
        }
        if (!empty($validated['password'] ?? null)) {
            $user->password = Hash::make($validated['password']);
        }
        $user->company_id = $validated['company_id'] ?? $user->company_id;
        $user->role = $validated['role'];
        $user->is_admin = $request->boolean('is_admin');
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}


