<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;


class TovController extends Controller
{

	/**
	* Для уценки. Поиск только в детском питании
	*
	*/
	public function ajaxGetTovForAvtocomplete(Request $request)
	{
		if(trim($request->get('term')) == '')
			return;

		$tovs = DB::connection('sqlsrv_imported_data')->select('SELECT TOP 30 [ArtName], [ArtCode]
			FROM [Imported_Data].[dbo].[AstHrhy]
			WHERE ArtCode = ? AND
				[LVL1]=\'детское питание\'', [$request->get('term')]);
		if($tovs)
		{
			foreach ($tovs as $value)
			{
				$result[] = ['label'=> $value->ArtName, 'value' => $value->ArtName.' ('.$value->ArtCode.')', 'val' => $value->ArtCode];
			}
			echo json_encode($result);
		}
	}
}