<?php

use Illuminate\Database\Seeder;

class UsersGroupsTableSeeder extends Seeder
{

	private $data = [
		['Администратор'],
	];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('users_groups')->truncate();
        foreach ($this->data as $value)
    	{
			DB::table('users_groups')->insert([
	            'title' => $value[0],
	        ]);
    	}
    }
}
