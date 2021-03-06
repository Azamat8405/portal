<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\TovCategs;
use App\BrendsCategsLinks;
use App\ShopRegion;
use App\Shop;
use App\Brend;

// TODO поиск по вхождению сделать нормально, с проверкой данных на инъекцию
class SystemController extends Controller
{
	public function ajaxGetContragentsErarhi()
	{
		$agents = DB::connection('sqlsrv_imported_data')->select('
			SELECT [Наименование], [Код], [ИНН]
			FROM [Imported_Data].[dbo].[Действующие_Поставщики]
			ORDER BY [Наименование]');

		if($agents)
		{
			$result = [];
			foreach ($agents as $value)
			{
				$result[] = [
					'label'=> $value->{'Наименование'}.' ('.$value->{'Код'}.')',
					'val' => $value->{'Код'}];
			}
			echo json_encode($result);
		}
	}
	public function ajaxGetContragentsAvtocomplete(Request $request)
	{
		if(trim($request->get('term')) == '')
			return;
		$words = explode(' ', $request->get('term'));

		$str1 = $str2 = '';
		foreach ($words as $key => $value)
		{
			$str1 .= ' [Наименование] LIKE \'%'.$value.'%\' AND ';
			$str2 .= ' [ИНН] LIKE \'%'.$value.'%\' AND ';
		}
		$str1 = '('.mb_substr($str1, 0, -4).')';
		$str2 = '('.mb_substr($str2, 0, -4).')';

		$agents = DB::connection('sqlsrv_imported_data')->select('
			SELECT TOP 50 [Наименование], [Код]
			FROM [Imported_Data].[dbo].[Действующие_Поставщики]
			WHERE '.$str1.' OR '.$str2.'
			ORDER BY [Наименование]');

		if($agents)
		{
			$result = [];
			foreach ($agents as $value)
			{
				$result[] = [
					'label'=> $value->{'Наименование'}.' ('.$value->{'Код'}.')',
					'value' => $value->{'Наименование'},//.' ('.$value->{'Код'}.')',
					'val' => $value->{'Код'}
				];
			}
			echo json_encode($result);
		}
    }

	public function ajaxGetShopsForRegion(Request $request, $cityId)
	{
		if(intval($cityId) == 0)
			return;

		$shops = Shop::where('region_id', $cityId)->get();
		if($shops)
		{
			echo json_encode($shops);
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
					sm.Store_Is_Active = 1 AND
					(sm.StoreActStartDate != \'1753-01-01 00:00:00.000\') AND
					(sm.StoreActEndDate = \'1753-01-01 00:00:00.000\') AND
					sm.StoreName NOT LIKE \'%(закрыт)%\'
				ORDER BY sr.StoreMacroRegion ASC
					,sr.StoreRegion ASC
					,sr.StoreCity ASC
					,sm.StoreName ASC');
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

	public function fillRegionsTable(Request $request)
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
					sm.Store_Is_Active = 1 AND
					(sm.StoreActStartDate != \'1753-01-01 00:00:00.000\') AND
					(sm.StoreActEndDate = \'1753-01-01 00:00:00.000\') AND
					sm.StoreName NOT LIKE \'%(закрыт)%\'
				ORDER BY sr.StoreCity DESC
					,sr.StoreRegion DESC 
					,sr.StoreMacroRegion DESC');

		if($shops)
		{
			foreach ($shops as $key => $value)
			{
				if($value->StoreMacroRegion == '')
				{
					continue;
				}

				$lvl1_right = 0;
				if($value->StoreMacroRegion != '')
				{
 					$lvl1 = trim($value->StoreMacroRegion);
					$exist_lvl1 = DB::table('shop_regions')
							->where('title', $lvl1)
							->where('level', 1)
							->get()->first();
					if(!$exist_lvl1)
					{
						$root = DB::table('shop_regions')->get()->first();
						if(!$root)
						{
							$root = new ShopRegion();
							$root->title = 'root';

							$root->left = 1;
							$root->right = 2;
							$root->level = 0;

							$root->save();
						}

						DB::update('UPDATE [dbo].[shop_regions]
						   		SET [right] = [right]+2
						 		WHERE [right] >= ?',
							[
								$root->right
							]);

						$exist_lvl1 = new ShopRegion();
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

				if($value->StoreRegion != '')
				{
 					$lvl2 = trim($value->StoreRegion);
					$exist_lvl2 = DB::select('SELECT c.[right] as self_right
							from [dbo].[shop_regions] c, [dbo].[shop_regions] c2

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
						DB::update('UPDATE [dbo].[shop_regions]
						   		SET [right] = [right]+2
						 		WHERE [right] >= ?',
							[
								$lvl1_right
							]);
						DB::update('UPDATE [dbo].[shop_regions]
						   		SET [left] = [left]+2
						 		WHERE [left] > ?',
							[
								$lvl1_right
							]);

						$exist_lvl2 = new ShopRegion();
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

				if($value->StoreCity != '')
				{
					$lvl3 = trim($value->StoreCity);
					$exist_lvl3 = DB::select('SELECT c.[right] as self_right, c.[id] from [dbo].[shop_regions] c, [dbo].[shop_regions] c2

						 	WHERE c.[title] = ? AND
								c.[level] = 3 AND

								c.[left] > c2.[left] AND
								c.[right] < c2.[right] AND

								c2.[level] = 2 AND
								c2.[title] = ? AND

								(
									SELECT COUNT(*) from [dbo].[shop_regions] c3 WHERE 
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
						DB::update('UPDATE [dbo].[shop_regions]
						   		SET [right] = [right]+2
						 		WHERE [right] >= ?',
							[
								$lvl2_right
							]);
						DB::update('UPDATE [dbo].[shop_regions]
						   		SET [left] = [left]+2
						 		WHERE [left] > ?',
							[
								$lvl2_right
							]);

						$exist_lvl3 = new ShopRegion();
						$exist_lvl3->title = $lvl3;

						$exist_lvl3->left = $lvl2_right;
						$exist_lvl3->right = $lvl2_right+1;
						$exist_lvl3->level = 3;

						$exist_lvl3->save();
						$lvl3_right = $exist_lvl3->right;
						$lvl3_id = $exist_lvl3->id;
					}
					else
					{
						$lvl3_right = $exist_lvl3[0]->self_right;
						$lvl3_id = $exist_lvl3[0]->id;
					}
				}

				if($value->StoreName != '' && $lvl3_id > 0)
				{
					$sh = new Shop();
					$sh->title = $value->StoreName;
					$sh->code = $value->StoreCode;
					$sh->region_id = $lvl3_id;
					$sh->save();
				}
			}
		}
	}

	public function fillTovCategsTable(Request $request)
	{
		$tovs = DB::connection('sqlsrv_imported_data')->select('
			SELECT [LVL1], [LVL2], [LVL3], [LVL4], [BrandCode], [BrandName]
			FROM [Imported_Data].[dbo].[AstHrhy]
            WHERE
				[LVL1] = \'ИГРУШКИ\'
					OR
				[LVL1] = \'КАНЦТОВАРЫ, КНИГИ, ДИСКИ\'
					OR
				[LVL1] = \'СОПУТСТВУЮЩИЕ ТОВАРЫ\'
					OR
				[LVL1] = \'КРУПНОГАБАРИТНЫЙ ТОВАР\'
					OR
				[LVL1] = \'ОБУВЬ\'
					OR
				[LVL1] = \'ДЕТСКОЕ ПИТАНИЕ\'
					OR
				[LVL1] = \'КОСМЕТИКА/ГИГИЕНА\'
					OR
				[LVL1] = \'ПОДГУЗНИКИ\'
					OR
				[LVL1] = \'ТОВАРЫ ДЛЯ КОРМЛЕНИЯ\'
					OR
				[LVL1] = \'ТЕКСТИЛЬ, ТРИКОТАЖ\'
			ORDER BY 
				LVL1, LVL2, LVL3, LVL4');

			foreach($tovs as $value)
			{
				$lvl1_right = 0;
				if($value->LVL1 != '')
				{
 					$lvl1 = trim($value->LVL1);

					// if(trim($value->LVL2) == '' || trim($value->LVL3) == '' || trim($value->LVL4) == '')
					// {
					// 	continue;
					// }

					$exist_lvl1 = DB::table('tov_categs')
							->where('title', $lvl1)
							->where('level', 1)
							->get()->first();
					if(!$exist_lvl1)
					{
						$root = DB::table('tov_categs')->get()->first();
						if(!$root)
						{
							$root = new TovCategs();
							$root->title = 'root';

							$root->left = 1;
							$root->right = 2;
							$root->level = 0;

							$root->save();
						}

						DB::update('UPDATE [dbo].[tov_categs]
						   		SET [right] = [right]+2
						 		WHERE [right] >= ?',
							[
								$root->right
							]);

						$exist_lvl1 = new TovCategs();
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

				if($value->LVL2 != '')
				{
 					$lvl2 = trim($value->LVL2);
					$exist_lvl2 = DB::select('SELECT c.[right] as self_right
							from [dbo].[tov_categs] c, [dbo].[tov_categs] c2

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
						DB::update('UPDATE [dbo].[tov_categs]
						   		SET [right] = [right]+2
						 		WHERE [right] >= ?',
							[
								$lvl1_right
							]);
						DB::update('UPDATE [dbo].[tov_categs]
						   		SET [left] = [left]+2
						 		WHERE [left] > ?',
							[
								$lvl1_right
							]);

						$exist_lvl2 = new TovCategs();
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

				if($value->LVL3 != '')
				{
					$lvl3 = trim($value->LVL3);
					$exist_lvl3 = DB::select('SELECT c.[right] as self_right from [dbo].[tov_categs] c, [dbo].[tov_categs] c2

						 	WHERE c.[title] = ? AND
								c.[level] = 3 AND

								c.[left] > c2.[left] AND
								c.[right] < c2.[right] AND

								c2.[level] = 2 AND
								c2.[title] = ? AND

								(
									SELECT COUNT(*) from [dbo].[tov_categs] c3 WHERE 
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
						DB::update('UPDATE [dbo].[tov_categs]
						   		SET [right] = [right]+2
						 		WHERE [right] >= ?',
							[
								$lvl2_right
							]);
						DB::update('UPDATE [dbo].[tov_categs]
						   		SET [left] = [left]+2
						 		WHERE [left] > ?',
							[
								$lvl2_right
							]);

						$exist_lvl3 = new TovCategs();
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

				if($value->LVL4 != '')
				{
					$lvl4 = trim($value->LVL4);

					$exist_lvl4 = DB::select('SELECT c.[id], c.[right] as self_right FROM [dbo].[tov_categs] c, [dbo].[tov_categs] c2
					 	WHERE c.[title] = ? AND
								c.[level] = 4 AND

								c.[left] > c2.[left] AND
								c.[right] < c2.[right] AND

								c2.[level] = 3 AND
								c2.[title] = ? AND

								(
									SELECT COUNT(*) from [dbo].[tov_categs] c3 WHERE 
										c3.[level] = 2 AND
										c3.[title] = ? AND
										c3.[left] < c2.[left] AND
										c3.[right] > c2.[right]
								) > 0  AND

								(
									SELECT COUNT(*) from [dbo].[tov_categs] c3 WHERE 
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
						DB::update('UPDATE [dbo].[tov_categs]
						   		SET [right] = [right]+2
						 		WHERE [right] >= ?',
							[
								$lvl3_right
							]);
						DB::update('UPDATE [dbo].[tov_categs]
						   		SET [left] = [left]+2
						 		WHERE [left] > ?',
							[
								$lvl3_right
							]);

						$exist_lvl4 = new TovCategs();
						$exist_lvl4->title = $lvl4;

						$exist_lvl4->left = $lvl3_right;
						$exist_lvl4->right = $lvl3_right+1;
						$exist_lvl4->level = 4;

						$exist_lvl4->save();

						$exist_lvl4_id = $exist_lvl4->id;
					}
					else
					{
						$exist_lvl4_id = $exist_lvl4[0]->id;
					}
				}

				if($value->BrandCode != '')
				{
					$br = DB::table('brends')
						->where('name', $value->BrandName)
						->get()->first();
					if(!$br)
					{
						DB::transaction(function() use ($value, $exist_lvl4_id) {

							$br = new Brend();
							$br->code = (!is_null($value->BrandCode) ? $value->BrandCode : 0);
							$br->name = $value->BrandName;
							$br->save();

							$bcl = new BrendsCategsLinks();
							$bcl->brend_id = $br->id;
							$bcl->categ_id = $exist_lvl4_id;
							$bcl->save();
						});
					}
					else
					{
						$br2 = DB::table('brends_categs_links')
							->where('brend_id', $br->id)
							->where('categ_id', $exist_lvl4_id)
							->get()->first();
						if(!$br2)
						{
							$brl = new BrendsCategsLinks();
							$brl->brend_id = $br->id;
							$brl->categ_id = $exist_lvl4_id;
							$brl->save();
						}
					}
				}
			}
	}

	public function ajaxGetTovsToFillTable(Request $request)
	{
		if(trim($request->get('tovCategory')) == '' || trim($request->get('division')) == '')
		{
			echo 0;
			return;
		}
		$result = [];
		$region = $shop = 0;

		if($request->get('shop') > 0)
		{
			$shop = $request->get('shop');
		}
		elseif($request->get('city') > 0)
		{
			$region = $request->get('city');
		}
		elseif($request->get('oblast') > 0)
		{
			$region = $request->get('oblast');
		}
		elseif($request->get('division') > 0)
		{
			$region = $request->get('division');
		}

		if($shop > 0)
		{
			$shop = Shop::select('id', 'title', 'code')->where('id', $shop)->get();
			if($shop)
			{
				$result['shop'] = $shop;
			}
		}
		else
		{
			if($region == 0)
			{
				$regs = DB::table('shop_regions as s')
			        ->select('s.id')
			        ->where('s.level', 3)
			        ->get();
			}
			else
			{
				$regs = DB::table('shop_regions as s')
					->join('shop_regions as s2', function($join) use ($region)
			        {
						$join->on('s.left', '<=' , "s2.left")
							->on('s.right', '>=' , 's2.right')
							->where('s.id', '=', $region)
			            	->where('s2.level', '=' , 3);
					})
			        ->select('s2.id')
			        ->get();
			}

			$region_ids = [];
			if($regs)
			{
				foreach ($regs as $v)
				{
					$region_ids[] = $v->id;
				}
			}
			$shops = Shop::select('id', 'title', 'code')
				->whereIN('region_id', $region_ids)
				->where(function($query) use ($request) {

					$shopsIskluch = $request->get('shopsIskluch');
					if(trim($shopsIskluch) != '')
					{
						$shopsIskluch = explode(',', $shopsIskluch);
						foreach($shopsIskluch as $val)
						{
							$query->where('code', '!=', $val);
						}
					}
				})
				->orderBy('title')
				->get();
			if($shops)
			{
				$result['shop'] = $shops;
			}
		}

		if($request->get('tovVidIsdeliya') > 0)
		{
			$getSubCategsFor = 0;
			$cats_level_4 = [$request->get('tovVidIsdeliya')];
		}
		elseif($request->get('tovTipIsdeliya') > 0)
		{
			$getSubCategsFor = $request->get('tovTipIsdeliya');
		}
		elseif($request->get('tovGroup') > 0)
		{
			$getSubCategsFor = $request->get('tovGroup');
		}
		else
		{
			$getSubCategsFor = $request->get('tovCategory');
		}

		$str_brend = '';
		if($request->get('tovBrend') > 0)
		{
			$tovBrend = DB::select('SELECT [code], [name] from [Portal].[dbo].[brends]
			 	WHERE [id] = ? ',
				[ $request->get('tovBrend') ]);
			if($tovBrend)
			{
				$str_brend = ' AND [BrandCode] = \''.trim($tovBrend[0]->code).'\' AND [BrandName] = \''.trim($tovBrend[0]->name).'\'';
			}
		}

		if($getSubCategsFor > 0)
		{
			//доставем все подразделы 4 уровня
			$subCategs = DB::select('SELECT c2.id from [dbo].[tov_categs] c, [dbo].[tov_categs] c2
			 	WHERE c.[id] = ? AND
					c.[left] < c2.[left] AND
					c.[right] > c2.[right] AND 
					c2.[level] = 4',
				[ $getSubCategsFor ]);

			if($subCategs)
			{
				foreach ($subCategs as $key => $value)
				{
					$cats_level_4[] = $value->id;
				}
			}
		}

		//достаем все разделы 4 увроня с их родителями
		$parCategs = DB::select('SELECT c.title as title4, c2.title as title3, c3.title as title2, c4.title as title1
			FROM [dbo].[tov_categs] c, [dbo].[tov_categs] c2, [dbo].[tov_categs] c3, [dbo].[tov_categs] c4
		 	WHERE c.[id] IN ( \''.implode('\',\'', $cats_level_4).'\' ) AND

					c.[left] > c2.[left] AND
					c.[right] < c2.[right]  AND 

					c2.[left] > c3.[left] AND
					c2.[right] < c3.[right] AND 

					c3.[left] > c4.[left] AND
					c3.[right] < c4.[right] AND 

					c4.[level] = 1 AND
					c3.[level] = 2 AND
					c2.[level] = 3 AND
					c.[level] = 4');
		if($parCategs)
		{
			$str = '';
			foreach ($parCategs as $key => $value)
			{
				$str .= '([LVL1] = \''.$value->title1.'\' AND
						[LVL2] = \''.$value->title2.'\' AND
						[LVL3] = \''.$value->title3.'\' AND
						[LVL4] = \''.$value->title4.'\') OR ';
			}
			$str = substr($str, 0, -4);

			$to = 20000;
			$from = $request->get('page') > 0 ? $request->get('page') * $to : 0;

			$tovs = DB::connection('sqlsrv_imported_data')->select('
				SELECT ArtCode as c, ArtName as n, ArtArticle as a
				FROM [Imported_Data].[dbo].[AstHrhy]
	            WHERE '.$str.$str_brend.'
				ORDER BY ArtName
				OFFSET '.$from.' ROWS
				FETCH NEXT '.$to.' ROWS ONLY');

			if($tovs)
			{
				$page = $request->get('page');
				$need = (count($tovs) < $to ? 0 : ++$page);

				$result['items'] = $tovs;
				$result['need'] = $need;

				echo json_encode($result);
			}
			else
			{
				echo 0;
			}
		}
	}

	public function ajaxGetTovsForCateg(Request $request, $categId)
	{
		if(intval($categId) == 0)
			return;
		// достаем все выбранные родительские разделы выбранного раздела.
		// чтобы по их названиям достать товары из таблицы [Imported_Data].[dbo].[AstHrhy]
		$parCategs = DB::select('SELECT c2.id, c2.title, c2.level from [dbo].[tov_categs] c, [dbo].[tov_categs] c2
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

	public function ajaxGetSubRegions(Request $request, $regionId)
	{
		if(intval($regionId) == 0)
			return;

		$sub_regions = DB::select('SELECT c2.id, c2.title, c2.level 
			FROM [dbo].[shop_regions] c, [dbo].[shop_regions] c2

		 	WHERE c.[id] = ? AND
					c.[left] < c2.[left] AND
					c.[right] > c2.[right] AND 
					c2.[level] = c.[level]+1
			ORDER BY c2.[title] DESC', [ $regionId ]);

		if($sub_regions)
			echo json_encode($sub_regions);
	}

	public function ajaxGetSubCategs(Request $request, $categId)
	{
		if(intval($categId) == 0)
			return;

		$sub_categs = DB::select('SELECT c2.id, c2.title, c2.level 
			FROM [dbo].[tov_categs] c, [dbo].[tov_categs] c2
		 	WHERE c.[id] = ? AND
					c.[left] < c2.[left] AND
					c.[right] > c2.[right] AND 
					c2.[level] = c.[level]+1
			ORDER BY c2.[title] DESC', [ $categId ]);

		if($sub_categs)
			echo json_encode($sub_categs);
	}

	public function ajaxGetTovIdsForCategs(Request $request)
	{
		$cat_ids = json_decode($request->get('data'));
		if(empty($cat_ids))
			return;

		$tmp = [];
		foreach ($cat_ids as $key => $value)
		{
			$tmp[] = "'".$value."'";
		}

		// достаем все родительские разделы выбранного раздела.
		// чтобы по их названиям достать id-шники товаров из таблицы [Imported_Data].[dbo].[AstHrhy]
		$parCategs = DB::select('SELECT c2.id, c2.title, c2.level
				FROM [dbo].[tov_categs] c, [dbo].[tov_categs] c2
			 	WHERE c.[id] IN ('.implode(',', $tmp).') AND
						c.[left] >= c2.[left] AND
						c.[right] <= c2.[right] AND 
						c2.[level] != 0');
		$ctgs = [];
		$ctgs_str = [];
		if($parCategs)
		{
			$i = 0;
			foreach ($parCategs as $value)
			{
				$i++;
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
		$tovs = DB::table('tov_categs')
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