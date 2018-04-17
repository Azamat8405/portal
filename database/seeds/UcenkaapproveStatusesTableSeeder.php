<?php

use Illuminate\Database\Seeder;

class UcenkaapproveStatusesTableSeeder extends Seeder
{

	private $data = [
		[
			'title' => 'одобрено полностью',
		],
        [
            'title' => 'одобрено частично',
		],
        [
            'title' => 'отклонено',
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
			DB::table('ucenka_approve_statuses')->insert($value);
    	}
    }
}
