<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class SystemController extends Controller
{
	public function ajaxGetContragents(Request $request)
	{
		if(trim($request->get('term')) == '')
			return;

		$agents = DB::connection('sqlsrv_Imp_1C_temporary')->select('SELECT TOP 20 [Наименование], [Код]
			FROM [Imp_1C_temporary].[dbo].[Справочник_Контрагенты]

			WHERE
				[Наименование] LIKE \'%'.$request->get('term').'%\'
				OR
				[НаименованиеПолное_Строка] LIKE \'%'.$request->get('term').'%\' 
				OR
				[ИНН_Строка] LIKE \'%'.$request->get('term').'%\' ');
		if($agents)
		{
			$result = [];
			foreach ($agents as $value)
			{
				$result[] = ['label'=> $value->{'Наименование'}, 'value' => $value->{'Наименование'}, 'val' => $value->{'Код'}];
			}
			echo json_encode($result);
		}
    }

	public function ajaxGetTovars(Request $request)
	{
		if(trim($request->get('query')) == '')
			return;

		$tovs = DB::connection('sqlsrv_imported_data')->select('SELECT TOP 20 [ArtName], [ArtCode]
			FROM [Imported_Data].[dbo].[Assortment]
			WHERE
				[ArtName] LIKE \'%'.$request->get('query').'%\'
					OR
				[ArtFullName] LIKE \'%'.$request->get('query').'%\'
					OR
				[ArtArticle] LIKE \'%'.$request->get('query').'%\' ');
		if($tovs)
		{
			$result = [];
			foreach ($tovs as $value)
			{
				$result[] = ['label'=> $value->{'ArtName'}, 'value' => $value->{'ArtName'}, 'val' => $value->{'ArtCode'}];
			}
			echo json_encode($result);
		}
	}
}