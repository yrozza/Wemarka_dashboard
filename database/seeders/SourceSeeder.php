<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Source;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = \App\Models\Client::all();

        foreach($clients as $client){
        Source::factory(rand(1,3))->create(['client_id' => $client->id, 
        ]);
        }
    }
}
