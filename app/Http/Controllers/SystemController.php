<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\TovCategs;
use App\Tov2Categs;
 
class SystemController extends Controller
{

	public function ajaxGetContragentsErarhi()
	{
		$agents = DB::connection('sqlsrv_Imp_1C_temporary')->select('SELECT [Наименование], [Код], [ИНН_Строка]
			FROM [Imp_1C_temporary].[dbo].[Справочник_Контрагенты]
			WHERE [ПометкаУдаления]=0 AND [Поставщик_Булево]=1
			ORDER BY [Наименование]');

		if($agents)
		{
			$result = [];
			foreach ($agents as $value)
			{
				$result[] = [
					'label'=> $value->{'Наименование'},
					'val' => $value->{'Код'},
					'inn' => $value->{'ИНН_Строка'}];
			}
			echo json_encode($result);
		}
	}

	public function ajaxGetContragentsAvtocomplete(Request $request)
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
			WHERE 
				[ПометкаУдаления]=0 AND [Поставщик_Булево]=1 AND
				'.$str1.' OR '.$str2.' OR '.$str3);
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

		$tovs = DB::connection('sqlsrv_imported_data')->select('SELECT TOP 70 [ArtName], [ArtCode]
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
			FROM [Imported_Data].[dbo].[Store_All_Stores]
			WHERE 
				Store_Is_Active = 1 AND
				'.$str1);
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
		$shops = DB::connection('sqlsrv_imported_data')->select('SELECT
				 sm.StoreCode
  				,sr.StoreCity
  				,sr.StoreRegion
  				,sr.StoreMacroRegion
  				,sm.StoreName
			FROM [Imported_Data].[dbo].[Store_Region] as sr 
					INNER JOIN
				[Imported_Data].[dbo].[Store_All_Stores] as sm ON 
					sr.IDStore = sm.IDStore AND
					sm.Store_Is_Active = 1
			ORDER BY  sr.StoreCity DESC
  				,sr.StoreRegion DESC 
  				,sr.StoreMacroRegion DESC');

		if($shops)
		{
			$result = [];
			foreach ($shops as $value)
			{
				if(trim($value->{'StoreMacroRegion'}.$value->{'StoreRegion'}.$value->{'StoreCity'}) == '')
				{
					continue;
				}
				if(trim($value->{'StoreMacroRegion'}) == '')
					continue;	

				//id-шников у нас нету делаем хеши
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

	/**
	* Заполняем таблицу разделов в древовидной форме
	*/
	public function fillTov2CategsTable(Request $request)
	{
		$cats = DB::table('tov_categs')
				->select('lvl1', 'lvl2', 'lvl3', 'lvl4')
				->orderBy('lvl1', 'desc')
				->orderBy('lvl2', 'desc')
				->orderBy('lvl3', 'desc')
				->orderBy('lvl4', 'desc')
				->get();
		if($cats)
		{
			foreach ($cats as $key => $value)
			{
				$lvl1_right = 0;
				if($value->lvl1 != '')
				{
 					$lvl1 = trim($value->lvl1);

					$exist_lvl1 = DB::table('tov2_categs')
							->where('title', $lvl1)
							->where('level', 1)
							->get()->first();
					if(!$exist_lvl1)
					{
						$root = DB::table('tov2_categs')->get()->first();
						if(!$root)
						{
							$root = new Tov2Categs();
							$root->title = 'root';

							$root->left = 1;
							$root->right = 2;
							$root->level = 0;

							$root->save();
						}

						DB::update('UPDATE [dbo].[tov2_categs]
						   		SET [right] = [right]+2
						 		WHERE [right] >= ?',
							[
								$root->right
							]);

						$exist_lvl1 = new Tov2Categs();
						$exist_lvl1->title = $lvl1;

						$exist_lvl1->left = $root->right;
						$exist_lvl1->right = $root->right+1;
						$exist_lvl1->level = 1;

						$exist_lvl1->save();

						$lvl1_right = $exist_lvl1->right;
					}
					else
					{
						$lvl1_right = $exist_lvl1->right;
					}
				}

				if($value->lvl2 != '')
				{
 					$lvl2 = trim($value->lvl2);
					$exist_lvl2 = DB::select('SELECT c.[right] as self_right
							from [dbo].[tov2_categs] c, [dbo].[tov2_categs] c2

						 	WHERE c.[title] = ? AND
								c.[level] = 2 AND

								c.[left] > c2.[left] AND
								c.[right] < c2.[right] AND

								c2.[title] = ? AND
								c2.[level] = 1',
							[
								$lvl2, $lvl1
							]);
					if(!$exist_lvl2)
					{
						DB::update('UPDATE [dbo].[tov2_categs]
						   		SET [right] = [right]+2
						 		WHERE [right] >= ?',
							[
								$lvl1_right
							]);
						DB::update('UPDATE [dbo].[tov2_categs]
						   		SET [left] = [left]+2
						 		WHERE [left] > ?',
							[
								$lvl1_right
							]);

						$exist_lvl2 = new Tov2Categs();
						$exist_lvl2->title = $lvl2;

						$exist_lvl2->left = $lvl1_right;
						$exist_lvl2->right = $lvl1_right+1;
						$exist_lvl2->level = 2;

						$exist_lvl2->save();
						$lvl2_right = $exist_lvl2->right;
					}
					else
					{
						$lvl2_right = $exist_lvl2[0]->self_right;
					}
				}

				if($value->lvl3 != '')
				{
					$lvl3 = trim($value->lvl3);
					$exist_lvl3 = DB::select('SELECT c.[right] as self_right from [dbo].[tov2_categs] c, [dbo].[tov2_categs] c2

						 	WHERE c.[title] = ? AND
								c.[level] = 3 AND

								c.[left] > c2.[left] AND
								c.[right] < c2.[right] AND

								c2.[level] = 2 AND
								c2.[title] = ? AND

								(
									SELECT COUNT(*) from [dbo].[tov2_categs] c3 WHERE 
										c3.[level] = 1 AND
										c3.[title] = ? AND
										c3.[left] < c2.[left] AND
										c3.[right] > c2.[right]
								) > 0 ',
							[
								$lvl3, $lvl2, $lvl1
							]);

					if(!$exist_lvl3)
					{
						DB::update('UPDATE [dbo].[tov2_categs]
						   		SET [right] = [right]+2
						 		WHERE [right] >= ?',
							[
								$lvl2_right
							]);
						DB::update('UPDATE [dbo].[tov2_categs]
						   		SET [left] = [left]+2
						 		WHERE [left] > ?',
							[
								$lvl2_right
							]);

						$exist_lvl3 = new Tov2Categs();
						$exist_lvl3->title = $lvl3;

						$exist_lvl3->left = $lvl2_right;
						$exist_lvl3->right = $lvl2_right+1;
						$exist_lvl3->level = 3;

						$exist_lvl3->save();
						$lvl3_right = $exist_lvl3->right;
					}
					else
					{
						$lvl3_right = $exist_lvl3[0]->self_right;
					}
				}


				if($value->lvl4 != '')
				{
 					$lvl4 = trim($value->lvl4);

					$exist_lvl4 = DB::select('SELECT c.[right] as self_right from [dbo].[tov2_categs] c, [dbo].[tov2_categs] c2

					 	WHERE c.[title] = ? AND
								c.[level] = 4 AND

								c.[left] > c2.[left] AND
								c.[right] < c2.[right] AND

								c2.[level] = 3 AND
								c2.[title] = ? AND

								(
									SELECT COUNT(*) from [dbo].[tov2_categs] c3 WHERE 
										c3.[level] = 2 AND
										c3.[title] = ? AND
										c3.[left] < c2.[left] AND
										c3.[right] > c2.[right]
								) > 0  AND

								(
									SELECT COUNT(*) from [dbo].[tov2_categs] c3 WHERE 
										c3.[level] = 1 AND
										c3.[title] = ? AND
										c3.[left] < c2.[left] AND
										c3.[right] > c2.[right]
								) > 0 ',
							[
								$lvl4, $lvl3, $lvl2, $lvl1
							]);

					if(!$exist_lvl4)
					{
						DB::update('UPDATE [dbo].[tov2_categs]
						   		SET [right] = [right]+2
						 		WHERE [right] >= ?',
							[
								$lvl3_right
							]);
						DB::update('UPDATE [dbo].[tov2_categs]
						   		SET [left] = [left]+2
						 		WHERE [left] > ?',
							[
								$lvl3_right
							]);

						$exist_lvl4 = new Tov2Categs();
						$exist_lvl4->title = $lvl4;

						$exist_lvl4->left = $lvl3_right;
						$exist_lvl4->right = $lvl3_right+1;
						$exist_lvl4->level = 4;

						$exist_lvl4->save();
					}
				}
			}
			echo $t;
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
		// достаем все выбранные родительские разделы выбранного раздела.
		// чтобы по их названиям достать товары из таблицы [Imported_Data].[dbo].[AstHrhy]
		$parCategs = DB::select('SELECT c2.id, c2.title, c2.level from [dbo].[tov2_categs] c, [dbo].[tov2_categs] c2
						 	WHERE c.[id] = ? AND
									c.[left] >= c2.[left] AND
									c.[right] <= c2.[right] AND 
									c2.[level] != 0',
							[
								$categId
							]);
		$ctgs = $ctgs_ids = [];
		if($parCategs)
		{
			foreach ($parCategs as $value)
			{
				$ctgs[$value->level] = $value->title;
				$ctgs_ids[$value->title] = $value->id;
			}
		}
		else
		{
			return;
		}

		$result = [];
		$tovs = DB::connection('sqlsrv_imported_data')->select('
			SELECT ArtCode as c, ArtName as n, ArtArticle as art
						FROM [Imported_Data].[dbo].[AstHrhy]
			            WHERE
							[LVL1] = ? AND
							[LVL2] = ? AND
							[LVL3] = ? AND
							[LVL4] = ?', [$ctgs[1], $ctgs[2], $ctgs[3], $ctgs[4]]);
		if($tovs)
		{
			foreach ($tovs as $key => $value)
			{
				$result[$value->c] = [
					'catId' => $ctgs_ids[$ctgs[4]],
					'n' => $value->n,
					'art' => $value->art,
				];
			}
		}
		echo json_encode($result);
	}

	public function ajaxGetTovIdsForCategs(Request $request)
	{
		$cat_ids = json_decode($request->get('data'));
		if(empty($cat_ids))
			return;

		$tmp = [];
		foreach ($cat_ids as $key => $value) {
			$tmp[] = "'".$value."'";
		}

		// достаем все родительские разделы выбранного раздела.
		// чтобы по их названиям достать id-шники товаров из таблицы [Imported_Data].[dbo].[AstHrhy]
		$parCategs = DB::select('SELECT c2.id, c2.title, c2.level from [dbo].[tov2_categs] c, [dbo].[tov2_categs] c2
			 	WHERE c.[id] IN ('.implode(',', $tmp).') AND
						c.[left] >= c2.[left] AND
						c.[right] <= c2.[right] AND 
						c2.[level] != 0');


		// $parCategs = DB::table('tov2_categs as c')
		// 	->select('c2.id', 'c2.title', 'c2.level')
		// 	->join('tov2_categs as c2', function ($join) use ($cat_ids)
		// 		{
		//         	$join->on('c.left', '>=', 'c2.left')
		//                ->on('c.right', '<=', 'c2.right')
		//                ->where('c2.level', '>', 0)
		//                ->where('c.id', $cat_ids);

		//         })
		// 	->orderBy('c.level')
		// 	->get();

		$ctgs = [];
		$ctgs_str = [];

		if($parCategs)
		{
			$i = 0;
			foreach ($parCategs as $value)
			{
				$i++;

				// $ctgs[$value->level] = $value->title;
				$ctgs[] = '[LVL'.$value->level.'] = \''.$value->title.'\'';

				if($i%4 == 0)
				{
					$ctgs_str[] = '('.implode(' AND ', $ctgs).')';
					$ctgs = [];
				}
			}
		}
		else
		{
			return;
		}

		$result = [];
		$tovs = DB::connection('sqlsrv_imported_data')->
				select('SELECT ArtCode as c, ArtName as n
						FROM [Imported_Data].[dbo].[AstHrhy]
			            WHERE '.implode(' OR ', $ctgs_str));
		if($tovs)
		{
			foreach ($tovs as $key => $value)
			{
				$result[$value->c] = $value->n;
			}
		}
		echo json_encode($result);
	}

	public function ajaxGetTovsCategsErarhi(Request $request)
	{
		$result = [];
		$tovs = DB::table('tov2_categs')
				->where('level', '>', 0)
				->orderBy('left')
				->get();
		if($tovs)
        {
			$lvl1 = $lvl2 = $lvl3 = $lvl4 = 0;
			$t = 0;
			foreach($tovs as $value)
            {
				if($value->title != '')
                {
                	if($value->level == 1)
                	{
						$lvl1 = $value->id;
                        $result[$lvl1] = ['t' => $value->title];
                	}
					elseif($value->level == 2)
					{
						$lvl2 = $value->id;
                        $result[$lvl1][$lvl2] = ['t' => $value->title];
					}
					elseif($value->level == 3)
					{
						$lvl3 = $value->id;
                        $result[$lvl1][$lvl2][$lvl3] = ['t' => $value->title];
					}
					elseif($value->level == 4)
					{
						$lvl4 = $value->id;
                        $result[$lvl1][$lvl2][$lvl3][$lvl4] = ['t' => $value->title, 'id' => $value->id];
					}
                }
            }
		}
		echo json_encode($result);
	}
}