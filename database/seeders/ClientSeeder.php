<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;


use GuzzleHttp\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Client::factory(100)->create();

        $this->call(ClientSeeder::class);
        $this->call(SourceSeeder::class);
    }
}
