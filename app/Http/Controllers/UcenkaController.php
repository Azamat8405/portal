<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\User;
use App\Shop;
use App\UcenkaReason;
use App\UcenkaApp;
use App\UcenkaAppTov;
use DB;
use Gate;

class UcenkaController extends Controller
{
	public function list()
	{
		if (Gate::denies('ucenkaapp-read', User::find(Auth::id())))
		{
			abort(403);
		}

		$apps = UcenkaApp::paginate(20);
		return view('ucenka/list', 
			[
				'apps' => $apps,
				'reasons' => UcenkaReason::all(),
				'shops' => Shop::all()->sortBy('title')
			]);
	}

	public function ajaxJsonList()
	{
		if (Gate::denies('ucenkaapp-read', User::find(Auth::id())))
		{
			abort(403);
		}

		$perPage = 20;
		$apps = UcenkaApp::where('user_id', Auth::id())->orderBy('created_at', 'desc')->paginate($perPage);

		$responce=[];
		$responce['page'] = $apps->currentPage();
		$responce['total'] = $apps->lastPage();
		$responce['records'] = $apps->count();

		if($apps->currentPage() > 1)
		{
			$number = ($perPage * ($apps->currentPage()-1));
		}
		else
		{
			$number = 0;
		}

		foreach ($apps as $key => $value)
		{
			$tovs = '';
			if($value->app_tovs()->count() > 0)
			{
				foreach($value->app_tovs()->get() as $k_tov => $v_tov)
				{
					$tovs .= $v_tov->nomenklatury_title.';<br>';
				}
			}

			$responce['rows'][$key]['id'] = $value->id;
		    $responce['rows'][$key]['cell'] = 
		    	[
					++$number,
					$value->shop->title,
					($value->status ? $value->status->title : ''),
					$tovs,
				];
		}
		echo json_encode($responce);
	}

	public function edit($appId)
	{
		$app = UcenkaApp::find($appId);
		return view('ucenka/edit', 
 			[
 				'app' => $app
 			]);
	}

	public function ajaxJsonEdit($appId)
	{
		$perPage = 20;

		$app = UcenkaApp::find($appId);
		$app_tovs = UcenkaAppTov::where('ucenka_app_id', $appId)->paginate($perPage);

		$responce = [];
		$responce['page'] = $app_tovs->currentPage();
		$responce['total'] = $app_tovs->lastPage();
		$responce['records'] = $app_tovs->count();

		if($app_tovs->currentPage() > 1)
		{
			$number = ($perPage * ($app_tovs->currentPage()-1));
		}
		else
		{
			$number = 0;
		}

		foreach($app_tovs as $key => $value)
		{
			$responce['rows'][$key]['id'] = $value->id;
		    $responce['rows'][$key]['cell'] = 
		    	[
					++$number,
					$app->shop->title,
					$value->nomenklatury_kod,
					$value->nomenklatury_title,
					$value->srok_godnosty,
					($value->ucenka_reason ? $value->ucenka_reason->title : ''),
					$value->ostatok,
					$value->skidka,
					is_null($value->agreement_date) ? 'Отклонено' : 'Одобрено',
					'',
				];
		}
		echo json_encode($responce);
	}

	public function ajaxJsonEditSubmit(Request $request)
	{
		if( trim($request->get('id')) == '' ||
			trim($request->get('approve')) == '' ||
			trim($request->get('app_id')) == '')
		{
			echo 0;
			return;
		}

		$tov = UcenkaAppTov::find($request->get('id'));
		if($request->get('approve') == 1)
		{
			$tov->agreement_date = time();
		}
		else
		{
			$tov->agreement_date = null;
		}
		$tov->refusal_comment = $request->get('refusal_comment');
		$tov->user_id = Auth::id();

		if($tov->save())
		{
			// TODO вынести в сервисный слой изменение сатуса
			//	Дальше изменяем статус у самой заявки 
			$app = UcenkaApp::find($request->get('app_id'));

			$approved = 0;
			$notapproved = 0;

			foreach($app->app_tovs as $key => $value)
			{
				if(is_null($value->agreement_date))
				{
					$notapproved++;
				}
				else
				{
					$approved++;
				}
				if($approved > 0 && $notapproved > 0)
				{
					$app->ucenka_approve_status_id = 2;//одобрено частично
					if($app->save())
					{
						echo 1;
						return;
					}
					break;
				}
			}
			if($app->app_tovs->count() == $approved)
			{
				$app->ucenka_approve_status_id = 1;// одобрено полностью
			}
			elseif($app->app_tovs->count() == $notapproved)
			{
				$app->ucenka_approve_status_id = 3;// отклонено
			}

			if($app->save())
			{
				echo 1;
				return;
			}
		}
		echo 0;
	}

	public function add()
	{
		$reasonVariants = [];
		$reasons = UcenkaReason::all();
		if($reasons)
		{
			foreach ($reasons as $key => $value)
			{
				$reasonVariants[] = $value->id.':'.$value->title;
			}
		}
		return view('ucenka/add',
 			[
 				'shops' => Shop::all()->sortBy('title'),
				'reasonVariants' => implode(';', $reasonVariants)
 			]);
	}

	public function ajaxAddSubmit(Request $request)
	{
		$result = [];

		$cur_user = User::find(Auth::id());
		if(!$cur_user)
		{
			$result['errors'][] = 'Вы не авторизованны либо ваш аккаунт был удален.';
			echo json_encode($result);
			return;
		}

		if(!$cur_user->shop)
		{
			$result['errors'][] = 'В настройках вашего аккаунта не указан магазин к которому вы относитесь.';
			echo json_encode($result);
			return;
		}

		$nomenkatura = json_decode($request->get('d'));
		DB::beginTransaction();

			$app = new UcenkaApp();
			$app->shop_id = $cur_user->shop->id; //TODO магазин берем из пользователя
			$app->ucenka_approve_status_id = 0;		
			$app->user_id = Auth::id();

			if(!$app->save())
            {
				DB::rollBack();
				$result['errors'][] = 'Не удалось сохранить данные. Попробуйте еще раз, либо обратитесь к администратору системы.';
			}
			else
			{
				$reasons = [];
				foreach ($nomenkatura as $key => $value)
				{
					$key++;
					if(trim($value->kod) == '')
					{
						DB::rollBack();
						$result['errors'][$key]['kod'] = 'Пустое значение для кода номенклатуры';
						break;
					}

					$tov = new UcenkaAppTov();
					$tov->ucenka_app_id = $app->id;
					$tov->nomenklatury_kod = $value->kod;
		            $tov->nomenklatury_title = $value->name ?? '';
		            $tov->srok_godnosty = strtotime($value->srok) ?? 0;

					if(!in_array($value->reason, $reasons))
					{
						$reason = UcenkaReason::where('title', $value->reason)->get();
						if($reason->count() > 0)
						{
							$reasons[$reason[0]->id] = $value->reason;
			            	$tov->ucenka_reason_id = $reason[0]->id;
			            }
			            else
			            {
							DB::rollBack();
							$result['errors'][$key]['reason'] = 'Причина не определена';
							break;
			            }
					}
					else
					{
						$tmp = array_keys($reasons, $value->reason);
						$tov->ucenka_reason_id = $tmp[0];
					}
		            $tov->ostatok = $value->ostatok ?? 0;

		            if(!$tov->save())
		            {
						DB::rollBack();
						$result['errors'][] = 'Не удалось сохранить данные. Попробуйте еще раз, либо обратитесь к администратору системы.';
						break;
		            }
				}
			}

			if(!isset($result['errors']))
			{
				$result['success'] = 1;
			}
		DB::commit();
		echo json_encode($result);
	}
}