<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AbonnementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'aboubacar@example.com'],
            [
                'name' => 'M. Aboubacar BANE',
                'password' => bcrypt('password')
            ]
        );
    }
}
