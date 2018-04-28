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

	public function ajaxGetTovarForAvtoComplete(Request $request)
	{
		if(trim($request->get('term')) == '')
			return;

		if($request->get('kod'))
		{
			$tovs = DB::connection('sqlsrv_imported_data')->select('SELECT TOP 30 [ArtName], [ArtCode], [BrandName]
				FROM [Imported_Data].[dbo].[Assortment]
				WHERE [ArtCode] LIKE \'%'.$request->get('term').'%\'');
		}
		else
		{
			$words = explode(' ', $request->get('term'));

			$str1 = $str2 = $str3 = '';
			foreach ($words as $key => $value)
			{
				$str1 .= ' [ArtName] LIKE \'%'.$value.'%\' AND ';
				$str2 .= ' [ArtFullName] LIKE \'%'.$value.'%\' AND ';
				$str3 .= ' [ArtArticle] LIKE \'%'.$value.'%\' AND ';
			}

			$str1 = '('.mb_substr($str1, 0, -4).')';
			$str2 = '('.mb_substr($str2, 0, -4).')';
			$str3 = '('.mb_substr($str3, 0, -4).')';

			$tovs = DB::connection('sqlsrv_imported_data')->select('SELECT TOP 30 [ArtName], [ArtCode], [BrandName]
				FROM [Imported_Data].[dbo].[Assortment]
				WHERE
					'.$str1.' OR '.$str2.' OR '.$str3);
		}

		if($tovs)
		{
			$result = [];
			foreach ($tovs as $value)
			{
				if($request->get('kod'))
				{
					$result[] = ['label'=> $value->ArtCode.' '.$value->ArtName, 
							'value' => $value->ArtCode,
							'val' => $value->ArtName,
							'brend' => $value->BrandName,
						];
				}
				else
				{
					$result[] = ['label'=> $value->ArtName,
							'val' => $value->ArtCode,
							'brend' => $value->BrandName,
						];
				}

			}
			echo json_encode($result);
		}
	}
}