<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'bsn',
            'email' => 'bsn@qatrack.com',
            'password' => bcrypt('pisangkeju'),
            'role' => 'super_admin',
        ]);

        User::factory()->create([
            'name' => 'zahra',
            'email' => 'zahra@qatrack.com',
            'password' => bcrypt('12345678'),
            'role' => 'support_dev',
        ]);

        User::factory()->create([
            'name' => 'mila',
            'email' => 'mila@qatrack.com',
            'password' => bcrypt('12345678'),
            'role' => 'support_dev',
        ]);

        User::factory()->create([
            'name' => 'satrio',
            'email' => 'satrio@qatrack.com',
            'password' => bcrypt('12345678'),
            'role' => 'support_dev',
        ]);

        User::factory()->create([
            'name' => 'alvian',
            'email' => 'alvian@qatrack.com',
            'password' => bcrypt('12345678'),
            'role' => 'developer',
        ]);

        User::factory()->create([
            'name' => 'andri',
            'email' => 'andri@qatrack.com',
            'password' => bcrypt('12345678'),
            'role' => 'developer',
        ]);

        User::factory()->create([
            'name' => 'dinda',
            'email' => 'dinda@qatrack.com',
            'password' => bcrypt('12345678'),
            'role' => 'developer',
        ]);

        $this->call(NotificationSeeder::class);
    }
}
