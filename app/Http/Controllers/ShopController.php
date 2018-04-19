<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class ShopController extends Controller
{


	public function ajaxGetShops(Request $request)
	{
		if(trim($request->get('term')) == '')
			return;

		$words = explode(' ', $request->get('term'));

		$str1 = '';
		foreach ($words as $key => $value)
		{
			$str1 .= ' [title] LIKE \'%'.$value.'%\' AND ';
		}
		$str1 = mb_substr($str1, 0, -4);

		$shops = DB::select('select * FROM shops WHERE '.$str1);
		if(count($shops) > 0)
		{
			$result = [];
			foreach ($shops as $value)
			{
				$result[] = ['label'=> $value->title, 'value' => $value->title, 'val' => $value->code];
			}
			echo json_encode($result);
		}
	}
}