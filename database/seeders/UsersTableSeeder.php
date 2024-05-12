<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

//import the User model
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        //dummy data for inserted into database
        $users=[
            [
                'name'=>'admin',
                'email'=>'admin@gmail.com',
                'password'=> bcrypt('123456'),
                'role' => 1,
            ],
        ];

        User::insert($users);

    }
}
