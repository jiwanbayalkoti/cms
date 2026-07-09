<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Rules\StrongPassword;
use App\Rules\ValidEmailDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegisterOptions()
    {
        return view('admin.auth.register-company');
    }

    public function showCompanyForm()
    {
        return view('admin.auth.register-company');
    }

    public function registerCompany(Request $request)
    {
        $emailDomainRule = app()->environment('local') ? [] : [new ValidEmailDomain()];

        $validated = $request->validate([
            'company_name' => 'required|string|max:255|unique:companies,name',
            'company_email' => array_merge(['nullable', 'email', 'max:255'], $emailDomainRule),
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'email' => array_merge(['required', 'email', 'max:255', 'unique:users,email'], $emailDomainRule),
            'phone' => 'nullable|string|max:50',
            'password' => ['required', 'string', 'min:8', 'confirmed', new StrongPassword()],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $company = Company::create([
                'name' => $validated['company_name'],
                'email' => $validated['company_email'] ?? null,
                'phone' => $validated['company_phone'] ?? ($validated['phone'] ?? null),
                'address' => $validated['company_address'] ?? null,
                'tax_number' => $validated['tax_number'] ?? null,
            ]);
            $company->startTrial();

            return User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'company_id' => $company->id,
                'role' => 'admin',
                'is_admin' => true,
            ]);
        });

        Auth::login($user);
        $request->session()->regenerate();
        session(['active_company_id' => $user->company_id]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Company registered successfully. Welcome, ' . $user->name . '!');
    }
}
