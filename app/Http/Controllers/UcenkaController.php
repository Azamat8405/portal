<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Shop;

class UcenkaController extends Controller
{
	public function list()
	{
 		return view('ucenka/list');
	}

	public function add()
	{
		return view('ucenka/add', 
 			[
 				'shops' => Shop::all()
 			]);
	}
}