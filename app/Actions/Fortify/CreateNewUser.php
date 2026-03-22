<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Modules\Bookkeeping\Actions\SeedDefaultChartOfAccountsAction;
use Modules\Identity\Models\Role;
use Modules\System\Models\Tenant;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'organization_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $tenant = Tenant::create([
                'name' => $input['organization_name'],
            ]);

            app()->instance('currentTenant', $tenant);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            $ownerRole = Role::create([
                'tenant_id' => $tenant->id,
                'name' => 'Owner',
                'slug' => 'owner',
                'is_system' => true,
            ]);

            Role::create([
                'tenant_id' => $tenant->id,
                'name' => 'Accountant',
                'slug' => 'accountant',
                'is_system' => true,
            ]);

            Role::create([
                'tenant_id' => $tenant->id,
                'name' => 'Staff',
                'slug' => 'staff',
                'is_system' => true,
            ]);

            $user->roles()->attach($ownerRole->id, ['tenant_id' => $tenant->id]);

            $ledgerMap = app(SeedDefaultChartOfAccountsAction::class)->execute($tenant);

            $tenant->update(['settings' => ['ledgers' => $ledgerMap]]);

            return $user;
        });
    }
}
