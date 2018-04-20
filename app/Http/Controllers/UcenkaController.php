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
		$user = User::find(Auth::id());
		if (Gate::denies('ucenkaapp-read', $user))
		{
			abort(403);
		}

		$apps = UcenkaApp::paginate(20);
		return view('ucenka/list', 
			[
				'user' => $user,
				'apps' => $apps,
				'reasons' => UcenkaReason::all()
			]);
	}

	public function ajaxJsonList()
	{
		$user = User::find(Auth::id());
		if (Gate::denies('ucenkaapp-read', $user))
		{
			abort(403);
		}
		$perPage = 20;

		// Если пользователь категорийный менеджер, он видит все заявки на скидку
		if($user->user_group_id == 4)
		{
			$apps = UcenkaApp::orderBy('created_at', 'desc')->paginate($perPage);
		}
		else
		{
			$apps = UcenkaApp::where('user_id', Auth::id())->orderBy('created_at', 'desc')->paginate($perPage);
		}

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
					date('d.m.Y', $value->created_at->timestamp)
				];
		}
		echo json_encode($responce);
	}

	public function edit($appId)
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

		//TODO Проверка прав в сервис перенести
		$user = User::find(Auth::id());
		if (Gate::denies('ucenkaapp-edit', $user) &&
			Gate::denies('ucenkaapp-read', $user))
		{
			abort(403);
		}

		$app = UcenkaApp::find($appId);
		return view('ucenka/edit', 
 			[
 				'app' => $app,
 				'user' => $user,
				'reasonVariants' => implode(';', $reasonVariants)
 			]);
	}

	public function ajaxJsonEdit($appId)
	{
		$user = User::find(Auth::id());
		if (Gate::denies('ucenkaapp-edit', $user) && 
			Gate::denies('ucenkaapp-read', $user))
		{
			abort(403);
		}

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
			$data = [
				$value->id,
				++$number,
				$app->shop->title,
				$value->nomenklatury_kod,
				$value->nomenklatury_title,
				$value->srok_godnosty > 0 ? $value->srok_godnosty : '',
				($value->ucenka_reason ? $value->ucenka_reason->title : ''),
				$value->ostatok,
			];

			//	если есть пользователь является Категорийным менеджером, то ему выводим дополнительные поля
			if($user->user_group_id == 4)
			{
				$data = array_merge($data, [
							$value->skidka,
							is_null($value->agreement_date) ? 2 : 1, //2:'Отклонено', 1:'Одобрено'
							$value->refusal_comment]);
			}

			$responce['rows'][$key]['id'] = $value->id;
		    $responce['rows'][$key]['cell'] = $data;
		}
		echo json_encode($responce);
	}

	public function ajaxJsonEditSubmit(Request $request)
	{

		$result = [];
		$user = User::find(Auth::id());
		if (Gate::denies('ucenkaapp-edit', $user) && 
			Gate::denies('ucenkaapp-read', $user))
		{
			abort(403);
		}

		// Если пользователь КМ
		if($user->user_group_id == 4)
		{
			$agreement = [];
			$nomenkatura = json_decode($request->get('d'));

			if(trim($request->get('appId')) == '')
			{
				$result['errors'][] = 'Ошибка сохранения. Обратитесь к администратору системы.';
				echo json_encode($result);
				return;
			}

			if( count($nomenkatura) == 0)
			{
				$result['errors'][] = 'Не указано ни одного кода номенклатуры';
				echo json_encode($result);
				return;
			}

			foreach ($nomenkatura as $key => $value)
			{
				$tov = UcenkaAppTov::find($value->ID);
				if($value->approve == 1)
				{
					$tov->agreement_date = time();
					$tov->refusal_comment = '';
				}
				else
				{
					$tov->agreement_date = null;
					$tov->refusal_comment = $value->refusal_comment;
				}
			
				$tov->skidka = $value->skidka;
				$tov->user_id = Auth::id();

				if(!$tov->save())
				{
					$result['errors'][] = 'Не удалось сохранить данные.';
				}
			}

			if(!isset($result['errors']))
			{
				// TODO вынести в сервисный слой изменение сатуса
				//	Дальше изменяем статус у самой заявки 
				$app = UcenkaApp::find($request->get('appId'));

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
							$result['success'] = 1;
							echo json_encode($result);
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
					$result['success'] = 1;
					echo json_encode($result);
					return;
				}
			}
		}
		else
		{
			$kod_reason = []; // не должно быть строк с повторящимися занчениями кода и причины;
			$reasons = [];// для кеширования, чтобы каждый раз не ходить в базу.

			$tovs = UcenkaAppTov::where('ucenka_app_id', $request->get('appId'))->get();
			$nomenkatura = json_decode($request->get('d'));

			if( count($nomenkatura) == 0)
			{
				$result['errors'][] = 'Не указано ни одного кода номенклатуры';
				echo json_encode($result);
				return;
			}

			foreach ($nomenkatura as $key => $value)
			{
				if(trim($value->kod) == '')
				{
					$result['errors'][$value->ID]['kod'] = 'Пустое значение для кода номенклатуры';
					break;
				}

				$tov = null;
				foreach ($tovs as $key => $val)
				{
					if($value->ID == $val->id)
					{
						$tov = $val;
						unset($tovs[$key]);//найденный, удаляем из коллекции. Все что останеться в итоге, нужно будет удалить из таблицы.
						break;
					}
				}
				//Если не нашли пришедшую из формы номенклатуру, значит ее добавили при редактировании и мы тоже добавляем.
				if(!$tov)
				{
					$tov = new UcenkaAppTov();
					$tov->ucenka_app_id = $request->get('appId');
				}

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
						$result['errors'][$value->ID]['reason'] = 'Причина не определена';
						break;
		            }
				}
				else
				{
					$tmp = array_keys($reasons, $value->reason);
					$tov->ucenka_reason_id = $tmp[0];
				}

				if(!isset($kod_reason[$tov->nomenklatury_kod.$tov->ucenka_reason_id]))
				{
					$kod_reason[$tov->nomenklatury_kod.$tov->ucenka_reason_id] = 1;
				}
				else
				{
					$result['errors'][$value->ID]['kod'] = 'Не допустимо указывать код с одной и той же причиной. В заявке уже указан код "'.$tov->nomenklatury_kod.'" с причиной "'.$reasons[$tov->ucenka_reason_id].'".';
				}
	            $tov->ostatok = $value->ostatok ?? 0;

	            if(!$tov->save())
	            {
					$result['errors'][$value->ID]['save'] = 'Не удалось сохранить данные. Попробуйте еще раз, либо обратитесь к администратору системы.';
					break;
	            }
			}

			if(!isset($result['errors']) && $tov->save())
			{
				if($tovs->count() > 0)
				{
					foreach ($tovs as $key => $val)
					{
						$val->delete();
					}
				}
				$result['success'] = 1;
			}
		}
		echo json_encode($result);
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
				$nomenkatura = json_decode($request->get('d'));
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