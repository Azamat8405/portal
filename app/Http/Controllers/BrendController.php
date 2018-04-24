<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class BrendController extends Controller
{
	public function ajaxGetBrendsForCategs(Request $request, $categId)
	{
		if(intval($categId) == 0)
			return;

		//достаем подразделы указанного раздела($categId)
		$subcategs = DB::table('tov_categs as c')
			->join('tov_categs as c2', function($join) use ($categId)
	        {
				$join->on('c.left', '<=' , "c2.left")
					->on('c.right', '>=' , 'c2.right')
					->where('c.id', '=', $categId);
			})
            ->select('c2.id')
            ->get();

		$ids = [];
		if($subcategs)
		{
			foreach ($subcategs as $key => $value)
			{
				$ids[] = $value->id;
			}
		}
		else
		{
			$ids[] = $categId;
		}
		$brends = DB::table('brends as br')
			->join('brends_categs_links as l', 'br.id', '=', 'l.brend_id')
			->whereIn('l.categ_id', $ids)
            ->select('br.name as title', 'br.id')
            ->get();
        if($brends)
        {
			echo json_encode($brends);
        }
	}

	public function ajaxGetBrendsForAvtocomplete(Request $request)
	{
		if(trim($request->get('term')) == '')
			return;

		$words = explode(' ', $request->get('term'));
		$where = [];
		foreach ($words as $key => $value)
		{
			$where[] = ['name', 'like', '%'.$value.'%'];
		}

		$brends = DB::table('brends as br')
			->where($where)
            ->select('br.name as title', 'br.id')
            ->get();

        if($brends)
        {
			$result = [];
			foreach ($brends as $value)
			{
				$result[] = ['label'=> $value->title, 'value' => $value->title, 'val' => $value->id];
			}
			echo json_encode($result);
        }
	}
}