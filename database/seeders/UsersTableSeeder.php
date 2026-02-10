<?php
namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@nexora.test'],
            ['name' => 'Admin', 'password' => Hash::make('password')]
        );

        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole && ! $user->hasRole('admin')) {
            $user->roles()->attach($adminRole->id);
        }
    }
}
