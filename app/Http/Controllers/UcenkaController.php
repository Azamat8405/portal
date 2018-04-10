<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Shop;
use App\UcenkaReason;
use App\UcenkaApp;
use App\UcenkaAppTov;
use DB;

class UcenkaController extends Controller
{
	public function list()
	{
		$apps = UcenkaApp::paginate(5);

		// foreach ($apps as $key => $value)
		// {
		// 	echo $value->app_tovs()->count().'___';
		// }

//		foreach($apps as $k => $v)
//		{
// 			if($v->app_tovs()->count() > 0)
// 			{
// 				foreach($v->app_tovs()->get() as $k_tov => $v_tov)
// 				{
// 					foreach($v_tov->ucenka_reason()->get() as $k_v => $v_v)
// 					{
// print_r( $v_v->id );
// 					}
// 				}
// 			}
//		}

// exit();

		return view('ucenka/list', 
 			[
 				'apps' => $apps,
 				'reasons' => UcenkaReason::all(),
 				'shops' => Shop::all()->sortBy('title')
 			]);
	}

	public function full()
	{


		exit('1');
	}

	public function add()
	{
		return view('ucenka/add', 
 			[
 				'reasons' => UcenkaReason::all(),
 				'shops' => Shop::all()->sortBy('title')
 			]);
	}

	public function addSubmit(Request $request)
	{
		$errors = [];
		if(!$request->has('shop'))
		{
			$errors[] = 'Не указан магазин';
		}

		if(!$request->has('kodNomenkatur'))
		{
			$errors[] = 'Не указан ни один товар';
		}

		if(count($errors) > 0)
		{
			return redirect()->back()
				->with('errors', $errors)
				->withInput();
		}

		DB::beginTransaction();

			$app = new UcenkaApp();
			$app->shop_id = $request->get('shop');

			if(!$app->save())
            {
				DB::rollBack();
				$errors[] = 'Не удалось сохранить данные. Попробуйте еще раз, либо обратитесь к администратору системы.';
			}
			else
			{
				foreach ($request->get('kodNomenkatur') as $key => $value)
				{
					if(trim($value) == '')
					{
						DB::rollBack();
						$errors[] = 'Пустое значение для кода номенклатуры';
						break;
					}

					$tov = new UcenkaAppTov();
					$tov->ucenka_app_id = $app->id;
					$tov->nomenklatury_kod = $value;
		            $tov->nomenklatury_title = $request->get('tovName')[$key] ?? '';
		            $tov->srok_godnosty = $request->get('srok_godnosti')[$key] ?? 0;
		            $tov->ucenka_reason_id = $request->get('reasons')[$key] ?? 0;
		            $tov->ostatok = $request->get('ostatok')[$key] ?? 0;

		            if(!$tov->save())
		            {
						DB::rollBack();
						$errors[] = 'Не удалось сохранить данные. Попробуйте еще раз, либо обратитесь к администратору системы.';
						break;
		            }
				}
			}
		DB::commit();

		if(count($errors) > 0)
		{
			return redirect()->back()
				->with('errors', $errors)
				->withInput();;
		}

		return redirect()->back()
			->with('ok', 'Заявка успешно добавлена');

	}
}