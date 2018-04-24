<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class AvtodefecturaController extends Controller
{
	public function list()
	{
		$result = DB::connection('sqlsrv_td_dev')->select('
			SELECT [LVL1],
					[LVL2],
					[BrandName],
					[Код товара],
					[Наименование],
					[Корзина],
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
			WHERE 1=1');

		$tmp = count($result);
		if($tmp > 0)
		{
			print_r($result);
		}
	}
}