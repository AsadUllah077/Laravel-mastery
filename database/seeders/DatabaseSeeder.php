<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('local')) {
            $admin = User::firstOrNew(['email' => 'admin@academy.test']);
            $admin->fill([
                'name' => 'Administrator',
                'password' => 'Admin@123456',
            ])->forceFill([
                'is_admin' => true,
                'email_verified_at' => now(),
            ])->save();
        }

        $this->call(CurriculumSeeder::class);
    }
}
