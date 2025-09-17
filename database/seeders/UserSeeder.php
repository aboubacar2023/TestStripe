<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::insert([
            [
                'email' => 'aboubacar@gmail.com',
                'name' => 'M. Aboubacar BANE',
                'password' => bcrypt('password'),
            ],
            [
                'email' => 'moussa@gmail.com',
                'name' => 'M. Moussa CISSE',
                'password' => bcrypt('password1'),
            ],
            [
                'email' => 'raby@gmail.com',
                'name' => 'Mme Raby DIAGNE',
                'password' => bcrypt('password2'),
            ],
        ]);
    }
}
