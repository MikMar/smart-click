<?php

use Illuminate\Database\Seeder;
use Faker\Factory;

class UserTableFaker extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create('en_ZA');

        DB::table('users')->truncate();

        for ($i = 1; $i <= 1000000; $i++) {

            $data = [];
            $data[$i]['name'] = $faker->lastName . ' ' . $faker->firstName;
            $data[$i]['email'] = $faker->unique()->email;
            $data[$i]['password'] = bcrypt('userpassword');

            DB::table('users')->insert($data[$i]);

            echo $i . ' ';
        }
    }
}
