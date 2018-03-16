<?php

use Illuminate\Database\Seeder;

class ProcessesTypesTableSeeder extends Seeder
{
	private $data = [
		[
			'title' => 'Газета',
            'dedlain' => 3024000,
            'description' => 'Акция компании Дочки-сыночки. Проходит каждые 2 недели.'
		]
	];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		DB::table('process_types')->truncate();
    	foreach ($this->data as $value)
    	{
			DB::table('process_types')->insert($value);
    	}
    }
}