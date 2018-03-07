<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\TovCategs;

class SystemController extends Controller
{

	public function ajaxGetContragentsErarhi()
	{
		$agents = DB::connection('sqlsrv_Imp_1C_temporary')->select('SELECT [Наименование], [Код]
			FROM [Imp_1C_temporary].[dbo].[Справочник_Контрагенты]');

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

	public function ajaxGetContragents(Request $request)
	{
		if(trim($request->get('term')) == '')
			return;

		$words = explode(' ', $request->get('term'));

		$str1 = $str2 = $str3 = '';
		foreach ($words as $key => $value)
		{
			$str1 .= ' [Наименование] LIKE \'%'.$value.'%\' AND ';
			$str2 .= ' [НаименованиеПолное_Строка] LIKE \'%'.$value.'%\' AND ';
			$str3 .= ' [ИНН_Строка] LIKE \'%'.$value.'%\' AND ';
		}

		$str1 = '('.mb_substr($str1, 0, -4).')';
		$str2 = '('.mb_substr($str2, 0, -4).')';
		$str3 = '('.mb_substr($str3, 0, -4).')';

		$agents = DB::connection('sqlsrv_Imp_1C_temporary')->select('SELECT TOP 20 [Наименование], [Код]
			FROM [Imp_1C_temporary].[dbo].[Справочник_Контрагенты]
			WHERE '.$str1.' OR '.$str2.' OR '.$str3);

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

	public function ajaxGetTovarForAvtoComplete(Request $request)
	{
		if(trim($request->get('term')) == '')
			return;

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

		$tovs = DB::connection('sqlsrv_imported_data')->select('SELECT TOP 100 [ArtName], [ArtCode]
			FROM [Imported_Data].[dbo].[Assortment]
			WHERE
				'.$str1.' OR '.$str2.' OR '.$str3);
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

	public function ajaxGetShops(Request $request)
	{
		if(trim($request->get('term')) == '')
			return;

		$words = explode(' ', $request->get('term'));

		$str1 = '';
		foreach ($words as $key => $value)
		{
			$str1 .= ' [StoreName] LIKE \'%'.$value.'%\' AND ';
		}
		$str1 = '('.mb_substr($str1, 0, -4).')';

		$shops = DB::connection('sqlsrv_imported_data')->select('SELECT StoreCode, StoreName
			FROM [Imported_Data].[dbo].[Store_Main]
			WHERE '.$str1);

		if($shops)
		{
			$result = [];
			foreach ($shops as $value)
			{
				$result[] = ['label'=> $value->{'StoreName'}, 'value' => $value->{'StoreName'}, 'val' => $value->{'StoreCode'}];
			}
			echo json_encode($result);
		}
	}

	public function ajaxGetShopsErarhi(Request $request)
	{
		$shops = DB::connection('sqlsrv_imported_data')->select('SELECT sm.StoreCode
  				,sr.StoreCity
  				,sr.StoreRegion
  				,sr.StoreMacroRegion
  				,sm.StoreName
			FROM [Imported_Data].[dbo].[Store_Region] as sr 
					RIGHT JOIN
				[Imported_Data].[dbo].[Store_Main] as sm ON 
					sr.IDStore = sm.IDStore');
		if($shops)
		{
			$result = [];
			foreach ($shops as $value)
			{
				$hashMacroRegion = $hashRegion = $hashCity = '';
				if(trim($value->{'StoreMacroRegion'}) != '')
				{
					$hashMacroRegion = md5($value->{'StoreMacroRegion'});
				}
				if(trim($value->{'StoreRegion'}) != '')
				{
					$hashRegion = md5($value->{'StoreRegion'});
				}
				if(trim($value->{'StoreRegion'}) != '')
				{
					$hashCity = md5($value->{'StoreCity'});
				}


				if($hashMacroRegion != '' && !isset($result[$hashMacroRegion]))
				{
					$result[$hashMacroRegion] = ['title' => $value->{'StoreMacroRegion'}];
				}
				if(isset($result[$hashMacroRegion]) AND $hashRegion != '' && !isset($result[$hashMacroRegion][$hashRegion]))
				{
					$result[$hashMacroRegion][$hashRegion] = ['title' => $value->{'StoreRegion'}];
				}
				if(isset($result[$hashMacroRegion][$hashRegion]) AND $hashCity != '' && !isset($result[$hashMacroRegion][$hashRegion][$hashCity]))
				{
					$result[$hashMacroRegion][$hashRegion][$hashCity] = ['title' => $value->{'StoreCity'}];
				}
				$result[$hashMacroRegion][$hashRegion][$hashCity][$value->{'StoreCode'}] = $value->{'StoreName'};

			}
			echo json_encode($result);
		}
	}
	public function fillTovCategsTable(Request $request)
	{
		// Используем отдельное подключение через ODBC.
		// Потому как родная(PHP) библиотке медленно работает и вылетает при выборке большого количества записей
		$conf = \Config::get('database.connections');
        $dbh = new \PDO('odbc:portal', $conf['sqlsrv']['username'], $conf['sqlsrv']['password']);

		$stmt = $dbh->prepare("SELECT [LVL1], [LVL2], [LVL3], [LVL4]
			FROM [Imported_Data].[dbo].[AstHrhy]
            ORDER BY 
            	[LVL1] desc
                ,[LVL2] desc
                ,[LVL3] desc
                ,[LVL4] desc");

		$stmt->execute();
        while ($value = $stmt->fetch())
        {
			$value['LVL1'] = trim(iconv('cp1251', 'utf-8', $value['LVL1']));
			$value['LVL2'] = trim(iconv('cp1251', 'utf-8', $value['LVL2']));
			$value['LVL3'] = trim(iconv('cp1251', 'utf-8', $value['LVL3']));
			$value['LVL4'] = trim(iconv('cp1251', 'utf-8', $value['LVL4']));

			if($value['LVL1'] != '' AND
				$value['LVL2'] != '' AND
				$value['LVL3'] != '' AND 
				$value['LVL4'])
			{
				$exist = TovCategs::whereRaw(
					'lvl1 = ? AND lvl2 = ? AND lvl3 = ? AND lvl4 = ?',
					[$value['LVL1'], $value['LVL2'], $value['LVL3'], $value['LVL4']])->first();

				if(!$exist)
				{
					$categs = new TovCategs();

					$categs->lvl1 = $value['LVL1'];
					$categs->lvl2 = $value['LVL2'];
					$categs->lvl3 = $value['LVL3'];
					$categs->lvl4 = $value['LVL4'];

					$categs->save();
				}
			}
        }
	}


	public function ajaxGetTovsForCateg(Request $request, $categId)
	{
		if(intval($categId) == 0)
			return;

        $categ = DB::table('tov_categs')->where('id', $categId)->get()->first();
        if($categ)
        {
			$tovs = DB::connection('sqlsrv_imported_data')->select('
				SELECT ArtCode as c, ArtName as n, ArtArticle as art
							FROM [Imported_Data].[dbo].[AstHrhy]
				            WHERE
								[LVL1] = ? AND
								[LVL2] = ? AND
								[LVL3] = ? AND
								[LVL4] = ?', [$categ->lvl1, $categ->lvl2, $categ->lvl3, $categ->lvl4]);
			if($tovs)
				echo json_encode($tovs);
        }
	}

	public function ajaxGetTovsCategsErarhi(Request $request)
	{
		$result = [];
        $tovs = DB::table('tov_categs')->get();
		if($tovs)
        {
			foreach($tovs as $value)
            {
                $lvl1 = $lvl2 = $lvl3 = $lvl4 = '';
                if(trim($value->lvl1) != '')
                {
                    $lvl1 = md5($value->lvl1);
                    if(!isset($result[$lvl1]))
                        $result[$lvl1] = ['t' => $value->lvl1];
                }
                if(trim($value->lvl2) != '')
                {
                    $lvl2 = md5($value->lvl2);
                    if(!isset($result[$lvl1][$lvl2]))
                        $result[$lvl1][$lvl2] = ['t' => $value->lvl2];
                }
                if(trim($value->lvl3) != '')
                {
                    $lvl3 = md5($value->lvl3);
                    if(!isset($result[$lvl1][$lvl2][$lvl3]))
                        $result[$lvl1][$lvl2][$lvl3] = ['t' => $value->lvl3];
                }
                if(trim($value->lvl4) != '')
                {
                    $lvl4 = md5($value->lvl4);
                    if(!isset($result[$lvl1][$lvl2][$lvl3][$lvl4]))
                        $result[$lvl1][$lvl2][$lvl3][$lvl4] = ['t' => $value->lvl4, 'id' => $value->id];
                }
            }
		}
		echo json_encode($result);
	}
}