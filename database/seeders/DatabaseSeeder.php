<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Material;
use App\Models\Request;
use App\Models\RequestItem;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Cria 10 usuários
        User::factory(10)->create();

        // Cria 5 categorias, cada uma com 5 materiais
        Category::factory(5)
            ->has(Material::factory()->count(5))
            ->create();

        // Cria 10 requests, cada uma com 2 request items
        Request::factory(10)
            ->has(RequestItem::factory()->count(2))
            ->create();
    }
}
