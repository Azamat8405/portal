<?php

use Illuminate\Database\Seeder;

class ActionMarksTableSeeder extends Seeder
{

	private $data = [
		['Хит'],
		['Новинка'],
		['Суперцена'],
	];

    /**
     * Run the database seeds.
     *
     * @return void
     */
	public function run()
	{
		DB::table('action_marks')->truncate();

    	foreach ($this->data as $value)
    	{
			DB::table('action_marks')->insert([
	            'title' => $value[0]
	        ]);
    	}
    }
}
