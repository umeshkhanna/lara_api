<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AmbassadorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory(25)->create([
        	'is_admin' => 0
        ]);
    }
}
