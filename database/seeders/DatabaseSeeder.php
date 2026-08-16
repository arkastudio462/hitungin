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
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $expenseCategories = [
            ['name' => 'Makanan', 'type' => 'expense', 'icon' => '🍔', 'color' => '#f97316'],
            ['name' => 'Transportasi', 'type' => 'expense', 'icon' => '🚗', 'color' => '#06b6d4'],
            ['name' => 'Belanja', 'type' => 'expense', 'icon' => '🛒', 'color' => '#8b5cf6'],
            ['name' => 'Hiburan', 'type' => 'expense', 'icon' => '🎮', 'color' => '#ec4899'],
            ['name' => 'Tagihan', 'type' => 'expense', 'icon' => '📱', 'color' => '#dc2626'],
            ['name' => 'Kesehatan', 'type' => 'expense', 'icon' => '💊', 'color' => '#16a34a'],
            ['name' => 'Pendidikan', 'type' => 'expense', 'icon' => '📚', 'color' => '#2563eb'],
        ];

        $incomeCategories = [
            ['name' => 'Gaji', 'type' => 'income', 'icon' => '💰', 'color' => '#16a34a'],
            ['name' => 'Freelance', 'type' => 'income', 'icon' => '💼', 'color' => '#2563eb'],
            ['name' => 'Investasi', 'type' => 'income', 'icon' => '📈', 'color' => '#f59e0b'],
            ['name' => 'Bonus', 'type' => 'income', 'icon' => '🎁', 'color' => '#8b5cf6'],
        ];

        foreach (array_merge($expenseCategories, $incomeCategories) as $category) {
            $user->categories()->create($category);
        }
    }
}
