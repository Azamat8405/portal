<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
	private $data = [
		[
			'name' => 'Azam Olis',
			'email' => 'a_olisaev@detoc.ru',
			'password' => '$2y$10$uFc4lcwAopGw3Mq7yG//FusFvOeBaUh2lisbvteNi1QLDDUzkxa.K',
			'user_group_id' => 1,
			'role' => 'admin'
		],
		[
			'name' => 'Ниров Азамат Мухамедович',
			'email' => 'a_nirov@detoc.ru',
			'password' => '$2y$10$uFc4lcwAopGw3Mq7yG//FusFvOeBaUh2lisbvteNi1QLDDUzkxa.K',
			'user_group_id' => 1,
			'role' => 'admin'
		],
		[
			'name' => 'Метелькова Ксения',
			'email' => 'k_metelkova@detoc.ru',
			'password' => '$2y$10$uFc4lcwAopGw3Mq7yG//FusFvOeBaUh2lisbvteNi1QLDDUzkxa.K',
			'user_group_id' => 1,
			'role' => 'admin'
		]
	];

	/**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		// DB::table('users')->truncate();
    	foreach ($this->data as $value)
    	{
			DB::table('users')->insert($value);
    	}
    }
}
