<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\User;
use App\Shop;

class AvtodefecturaController extends Controller
{
	public function list()
	{
		return view('avtodefectura/list');
	}

	public function ajaxList()
	{
		$user = User::find(\Auth::id());

		$pos = strpos($user->shop->title, '(');
		$sh_title = substr($user->shop->title, 0, $pos);

		$result = DB::connection('sqlsrv_td_dev')->select('
			SELECT [LVL1],
					[LVL2],
					[BrandName],
					[Код товара],
					[Наименование],
					[Корзина],
      				[In_Matrix],
					[Комментарий ДТ],
					[Sales_Amount_Daily_Av],
					[Stock_Amount_Today],
					[Нижняя граница],
					[Верхняя граница],
					[Goods_In_Transit],
					[текущий месяц],
					[-1 месяц],
					[-2 месяц],
					[-3 месяц]
			FROM [TDDev].[dbo].[IvanAutoDefect_with_Comments]
			WHERE [Название магазина] LIKE \'%'.$sh_title.'%\'
			ORDER BY [Наименование]');

		$tmp = count($result);
		if($tmp > 0)
		{
			$responce=[];
			foreach ($result as $key => $value)
			{
				$responce['rows'][$key]['id'] = $key;
			    $responce['rows'][$key]['cell'] = [
			    		$value->LVL1,
			    		$value->LVL2,
			    		$value->BrandName,
			    		$value->{'Код товара'},
			    		$value->{'Наименование'},
			    		$value->{'Корзина'},
			    		($value->In_Matrix == 1 ? 'Да' : ''),
			    		$value->{'Комментарий ДТ'},
			    		round($value->Sales_Amount_Daily_Av, 1),
			    		$value->Stock_Amount_Today,
			    		round($value->{'Нижняя граница'}),
			    		round($value->{'Верхняя граница'}),
			    		$value->{'Goods_In_Transit'},
			    		$value->{'текущий месяц'},
			    		$value->{'-1 месяц'},
			    		$value->{'-2 месяц'},
			    		$value->{'-3 месяц'},
			    	];
			}
			echo json_encode($responce);
		}
	}
}