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
                'name'=>'Nguyen Tat Trung',
                'email'=>'trungptit7@gmail.com',
                'password'=> bcrypt('123456'),

            ]
        ];

        User::insert($users);

    }
}
