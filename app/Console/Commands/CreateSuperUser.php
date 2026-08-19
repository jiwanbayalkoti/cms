<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateSuperUser extends Command
{
    protected $signature = 'cms:create-superuser
                            {--email= : Super admin login email}
                            {--password= : Super admin password (min 8 chars)}
                            {--name=Super Admin : Display name}
                            {--force : Update password if the user already exists}';

    protected $description = 'Create or update a platform super admin (not tied to any company)';

    public function handle(): int
    {
        $email = strtolower(trim((string) ($this->option('email') ?: env('SUPER_ADMIN_EMAIL', 'superadmin@cms.local'))));
        $name = trim((string) $this->option('name'));
        $password = (string) ($this->option('password') ?: env('SUPER_ADMIN_PASSWORD', ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid --email or SUPER_ADMIN_EMAIL is required.');

            return self::FAILURE;
        }

        if ($password === '') {
            $password = Str::password(16);
            $this->warn('No password supplied; a random password was generated.');
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        $existing = User::where('email', $email)->first();

        if ($existing && !$existing->isSuperAdmin() && !$this->option('force')) {
            $this->error("User {$email} exists but is not a super admin. Use --force to convert.");

            return self::FAILURE;
        }

        if ($existing && $existing->isSuperAdmin() && !$this->option('force') && $this->option('password') === '' && env('SUPER_ADMIN_PASSWORD') === null) {
            $this->warn("Super admin {$email} already exists. Use --force to reset the password.");
            $this->line('Email: ' . $email);

            return self::SUCCESS;
        }

        $attributes = [
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'super_admin',
            'is_admin' => true,
            'company_id' => null,
            'permission_role_id' => null,
        ];

        if ($existing) {
            $existing->update($attributes);
            $user = $existing->fresh();
            $this->info('Super admin updated.');
        } else {
            $user = User::create($attributes);
            $this->info('Super admin created.');
        }

        User::where('role', 'super_admin')
            ->where('id', '!=', $user->id)
            ->update([
                'company_id' => null,
                'permission_role_id' => null,
            ]);

        $this->newLine();
        $this->line('Login URL: ' . url('/admin/login'));
        $this->line('Email: ' . $user->email);
        $this->line('Password: ' . $password);
        $this->newLine();
        $this->comment('This account is not linked to any company. After login you will see the company list.');

        return self::SUCCESS;
    }
}
