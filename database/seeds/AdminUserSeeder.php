<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;
class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@drphone.com'],
            [
                'name' => 'Admin Dr Phone',
                'email' => 'admin@drphone.com',
                'role' => 'admin',
                'password' => Hash::make('password123') // Change si besoin
            ]
        );
    }
}
