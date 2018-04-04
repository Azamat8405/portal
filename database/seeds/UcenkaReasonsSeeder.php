<?php

use Illuminate\Database\Seeder;

class UcenkaReasonsSeeder extends Seeder
{
	private $data = [
		[
			'title' => 'Брак'
		],
		[
			'title' => 'Предпросрок',
		]
	];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->data as $value)
    	{
			DB::table('ucenka_reasons')->insert($value);
    	}
    }
}
