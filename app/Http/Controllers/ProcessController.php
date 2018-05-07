<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\{Storage, 
Auth,
Request,
Redirect};

use App\{Process,
ProcessType,
Step,
Shop,
ActionType,
ActionMark,
TovCategs,
ShopRegion,
Brend,
User,
Document,
DocumentActionFirstData};

use Validator;
use File;
use Excel;
use DB;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;

class ProcessController extends Controller
{
	// хешируем сюда список магазинов
	private $cache_shops = [];
	private $cache_distr = [];
	private $validate_errors = [];

	/**
	* Подгрузка списка акции для таблицы jqGrid в списке процессов
	**/
	public function ajaxList(Request $request)
	{
		$perPage = 20;
		if(Request::has('rows'))
		{
			$perPage = Request::get('rows');
		}

		$sord = 'desc';
		$sidx = 'created_at';

		if(Request::get('sidx') != '')
		{
			$sidx = Request::get('sidx');
			switch($sidx)
			{
				case 'type':
					$sidx = 'process_types.title';
					break;
				case 'author':
					$sidx = 'users.name';
					break;
				case 'status':
				case 'title':
				case 'start_date':
				case 'end_date':
				case 'created_at':
				default:
					$sidx = 'processes.'.$sidx;
					break;
			}
		}

		if(Request::get('sord') != '')
		{
			$sord = Request::get('sord');
		}

		$processes = new Process();

		if(Request::get('_search'))
		{
			//ниже в этом случае должны быть подключена таблица типов
			if(Request::get('type') != '')
			{
				$processes = $processes->where('process_types.title', 'LIKE', '%'.Request::get('type').'%');
			}
			//ниже в этом случае должны быть подключена таблица пользователей
			if(Request::get('author') != '')
			{
				$processes = $processes->where('users.name', 'LIKE', '%'.Request::get('author').'%');
			}
			if(Request::get('title') != '')
			{
				$processes = $processes->where('processes.title', 'LIKE', '%'.Request::get('title').'%');
			}
			if(Request::get('start_date') != '')
			{
				$processes = $processes->where('processes.start_date', '=', strtotime(Request::get('start_date')));
			}
			if(Request::get('end_date') != '')
			{
				$processes = $processes->where('processes.end_date', '=', strtotime(Request::get('end_date')));
			}
			if(Request::get('status') != '')
			{
				$processes = $processes->where('processes.status', 'LIKE', '%'.Request::get('status').'%');
			}

			if(Request::get('created_at') != '')
			{
				$created_time = strtotime(Request::get('created_at'));
				$created_s = date('Y-m-d', $created_time);
				$created_e = date('Y-m-d', ($created_time + 86400));

				$processes = $processes->where('processes.created_at', '>=', $created_s)
										->where('processes.created_at', '<', $created_e);
			}
		}

		//связываем таблицу типов если нужно фильтровать по типу или сортировать
		if( Request::get('_search') && Request::get('type') != '' || Request::get('sidx') == 'type')
		{
			$processes = $processes->leftJoin('process_types', function($join){
				$join->on('process_types.id', '=', 'processes.process_type_id');
    		});
		}

		//связываем таблицу пользователей если нужно фильтровать по автору или сортировать
		if( Request::get('_search') && Request::get('author') != '' || Request::get('sidx') == 'author')
		{
			$processes = $processes->leftJoin('users', function($join){
					$join->on('users.id', '=', 'processes.user_id');
	    		});
		}

		$processes = $processes->select('processes.*')->orderBy($sidx, $sord)->paginate($perPage);

		$responce=[];
		$responce['page'] = $processes->currentPage();
		$responce['total'] = $processes->lastPage();
		$responce['records'] = $processes->count();

		if($processes->currentPage() > 1)
		{
			$number = ($perPage * ($processes->currentPage()-1));
		}
		else
		{
			$number = 0;
		}

		foreach ($processes as $key => $value)
		{
			$responce['rows'][$key]['id'] = $value->id;
		    $responce['rows'][$key]['cell'] = 
		    	[
					++$number,
					$value->title,
					($value->start_date != '' ? $value->start_date : ''),
					($value->end_date != '' ? $value->end_date : ''),
					($value->processType ? $value->processType->title : ''),
					$value->status,
					($value->user ? $value->user->name : ''),
					date('d.m.Y', $value->created_at->timestamp)
				];
		}
		echo json_encode($responce);
	}

	public function ajaxGetTovListForEdit(Request $request, $procId)
	{
		//достаем все магазины и кешируем
		$tmp = Shop::orderBy('title')->get();
		foreach ($tmp as $key => $value)
		{
			$value->title = Shop::prepareShopName($value->title);
			if(!isset($this->cache_shops[$value->code]))
			{
				$this->cache_shops[$value->id] = ['code' => $value->code, 'title' => $value->title];
			}
		}
		//обязательно сортируем по коду дис, чтобы в цикле ниже быть уверенным, что записи по магазину идут подряд
		$tovs = DocumentActionFirstData::where('process_id', $procId)
			->leftJoin('shops', function($join){

				$join->on('shops.id', '=', 'document_action_first_datas.shop_id');
    		})
			->orderBy('document_action_first_datas.kod_dis')
			->orderBy('shops.title')
			->get();

		$responce = [];
		$kod_dis = [];
		$data = [];

		$mapArr = [];

		foreach ($tovs as $key => $value)
		{
			if(!isset($mapArr[$value->kod_dis]))
			{
				$mapArr[$value->kod_dis] = count($mapArr);
				$key = $mapArr[$value->kod_dis];
			}
			else
			{
				$key = $mapArr[$value->kod_dis];
			}

			if(isset($this->cache_shops[$value->shop_id]))
			{
				if(isset($responce['rows'][$key]['cell']['sh_Ttl']) && $responce['rows'][$key]['cell']['sh_Ttl'] != '')
				{
					$responce['rows'][$key]['cell']['sh_Ttl'] .= '; '.$this->cache_shops[$value->shop_id]['title'];
				}
			}
			else
			{
				//	TODO errors
			}

			// в базе информация дублируется для каждого магазина. поэтому. 
			// данные для товара берем один раз, дальше для текущего кода товара все пропускаем, крома магазинов(выше по коду)
			if(isset($kod_dis[$value->kod_dis]))
			{
				continue;
			}
			$kod_dis[$value->kod_dis] = 1;

			$responce['rows'][$key]['id'] = $value->kod_dis;
		    $responce['rows'][$key]['cell'] = 
		    	[
			   		'tovsTitles' => $value->tovsTitles,
					'sh_Ttl' => $this->cache_shops[$value->shop_id]['title'] ?? '',
			   		'kT' => $value->kod_dis,
			   		'sad' => $value->start_action_date,
			   		'ead' => $value->end_action_date,
			   		'distr_ttl' => $value->distr_ttl,
			   		'brTtl' => ($value->brend ? $value->brend->title : '' ),
			   		'articule_sk' => $value->articule_sk,
			   		't' => $value->action_types_ids,
			   		'on_inv' => $value->on_invoice,
			   		'off_inv' => $value->off_invoice,
			   		'itog' => $value->skidka_itogo,
			   		'roz_old' => $value->old_roznica_price,
			   		'roz_new' => $value->new_roznica_price,
			   		'zak_old' => $value->old_zakup_price,
			   		'zak_new' => $value->new_zakup_price,
			   		's_d_on_inv' => $value->on_invoice_start != '' ? date('d-m-Y', (integer)$value->on_invoice_start) : '',
			   		'e_d_on_inv' => $value->on_invoice_end != '' ? date('d-m-Y', (integer)$value->on_invoice_end) : '',
			   		'razmesh_price' => $value->razmesh_price,
			   		'descr' => $value->description,
			   		'marks' => $value->metka
				];
		}
		echo json_encode($responce);
	}

	public function list()
	{
		return view('processes/list', ['processes' => Process::all()]);
	}

	public function showAddFrom(Request $request)
	{
		$action_types = [];
		$action_types[] = '0:Не выбрано';
		$action_types_descr = [];

		$types = ActionType::orderBy('title')->get();
		foreach ($types as $type)
		{
			$action_types[] = $type->id.':'.$type->title;
			$action_types_descr[$type->id] = $type->description;
		}

		$shops_for_js = ['var shops=[];'];
		$shops = Shop::all();
		if($shops)
		{
			foreach($shops as $value)
			{
				$shops_for_js[] = 'shops["'.$value->id.'"]=[];shops["'.$value->id.'"]["t"] = "'.$value->title.'";shops["'.$value->id.'"]["c"] = "'.$value->code.'";';
			}
		}

		return view('processes/add', [
			'tov_categs_lvl1' => TovCategs::where('level', 1)->orderBy('title')->get(),
			'shop_regions_lvl1' => ShopRegion::where('level', 1)->orderBy('title')->get(),
			'process_types' => ProcessType::all(),
			'action_types' => implode(';', $action_types),
			'action_types_descr' => $action_types_descr,
			'action_marks' => ActionMark::all(),
			'user' => User::find(Auth::id()),
			'shops_for_js' => implode('', $shops_for_js),
		]);
	}

	public function add(Request $request)
	{
		$err = false;

		//	Валидация даты
		$start_date = strtotime(Request::input('start_date'));
		$proc_type = ProcessType::find(Request::input('process_type'));
		if($proc_type)
		{
			// ПОКА убираем проверку
			// $cur_date = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
			// if($proc_type->dedlain + $cur_date > $start_date)
			// {
			// 	$this->validate_errors['form'][0]['start_date'] = 'Дата начала акции должна быть больше либо равна '.strftime('%d-%m-%Y', $proc_type->dedlain + time());
			// }
		}
		else
		{
			$this->validate_errors['form'][0]['process_type'] = 'Нет Акций указанного типа';
		}

		$end_date = '';
		if($proc_type)
		{
			$end_date = strtotime(Request::input('end_date'));
			if($end_date <= $start_date)
			{
				$this->validate_errors['form'][0]['end_date'] = 'Дата окончания акции должна быть больше даты начала.';
			}
		}

		if(!$request::has('kT'))
		{
			$this->validate_errors['form'][0]['kT'] = 'Не указан товар или указан неверно.';
		}

		// Если в шапке есть ошибки покаызваем их пока.
		if(!empty($this->validate_errors['form']))
		{
			// return redirect()->back()
			// 	->with('errors', $this->validate_errors)
			// 	->withInput();
		}
		$dataToInsert = [];

		// Проверка полей формы
		foreach($request::input('kT') as $key => $value)
		{
			if(trim($value) == '' && count($request::input('kT')) == 1)
			{
				break;
			}
			$dataToInsert[$key]['kT'] = $value;
			$dataToInsert[$key]['tovsTitles'] = $request::input('tovsTitles')[$key] ?? null;

			if(isset($request::input('shops')[$key]))
			{
				$dataToInsert[$key]['shops'] = explode(';', $request::input('shops')[$key]);
			}

			$dataToInsert[$key]['distr'] = $request::input('distr')[$key] ?? null;
			$dataToInsert[$key]['t'] = $request::input('t')[$key] ?? null;
			$dataToInsert[$key]['on_inv'] = $request::input('on_inv')[$key] ?? null;
			$dataToInsert[$key]['off_inv'] = $request::input('off_inv')[$key] ?? null;
			$dataToInsert[$key]['itog'] = $request::input('itog')[$key] ?? null;
			$dataToInsert[$key]['zak_old'] = $request::input('zak_old')[$key] ?? null;
			$dataToInsert[$key]['zak_new'] = $request::input('zak_new')[$key] ?? null;
			$dataToInsert[$key]['s_d_on_inv'] = $request::input('s_d_on_inv')[$key] ?? null;
			$dataToInsert[$key]['e_d_on_inv'] = $request::input('e_d_on_inv')[$key] ?? null;
			$dataToInsert[$key]['roz_old'] = $request::input('roz_old')[$key] ?? null;
			$dataToInsert[$key]['roz_new'] = $request::input('roz_new')[$key] ?? null;
			$dataToInsert[$key]['descr'] = $request::input('descr')[$key] ?? null;
			$dataToInsert[$key]['marks'] = $request::input('marks')[$key] ?? null;

			$this->validateData($dataToInsert[$key], 'form', $start_date, $end_date, $key);
		}

		if($err || !empty($this->validate_errors['form']) || !empty($this->validate_errors['file']))
		{
			return redirect()->back()
				->with('errors', $this->validate_errors)
				->withInput();
		}

		try
		{
			DB::transaction(function () use ($start_date, $end_date, $proc_type, $dataToInsert)
			{
				$pr = new Process();
				if(trim(Request::input('process_title')) == '')
				{
					$pr->title = $proc_type->title.' '.Request::input('start_date');
				}
				else
				{
					$pr->title = Request::input('process_title');
				}

				$pr->process_type_id = Request::input('process_type');
				$pr->start_date = $start_date;
				$pr->end_date = $end_date;
				$pr->save();

				// $step_title = 'Данные';
				// $step = new Step();
				// $step->process_id = $pr->id;
				// $step->title = $step_title;
				// $step->conditions = '';
				// $step->from_ids = 0;
				// $step->to_ids = 0;
				// $step->save();

				$doc = Document::where('process_type_id', Request::input('process_type'))->get();
				if(count($doc) == 0)
				{
					$doc = new Document();
					$doc->process_type_id = Request::input('process_type');
					$doc->title = 'Документ '.$pr->title;
					$doc->save();

					//TODO перенести в миграцию
					if(!\Schema::hasTable('document_action_first_datas'))
					{
						$res = \Schema::create('document_action_first_datas', function ($table) {
							$table->increments('id');

							$table->integer('doc_id')->unsigned();
				            $table->integer('shop_id')->unsigned();
				            $table->integer('process_id')->unsigned();
				            $table->integer('process_type_id')->unsigned();

				            $table->string('kod_dis')->comment('код ДиС Ном. Номер');
				            $table->string('articule_sk')->comment('Артикул ШК это артикул по базе поставщика');

							$table->string('action_types_ids')->comment('Артикул ШК это артикул по базе поставщика');

				            $table->string('on_invoice')->nullable();
				            $table->string('off_invoice')->nullable();
				            $table->string('skidka_itogo')->nullable();

				            $table->string('old_zakup_price')->nullable();
				            $table->string('new_zakup_price')->nullable();

				            $table->string('on_invoice_start')->nullable()->comment('Дата начала предоставления скидки он инвойс');
				            $table->string('on_invoice_end')->nullable()->comment('Дата окончания предоставления скидки он инвойс');

				            $table->string('old_roznica_price')->nullable();
				            $table->string('new_roznica_price')->nullable();

				            $table->text('description')->comment('подписи, слоганы, расшифровки и пояснения, которые Вы хотели бы видеть к своим товарам.')->nullable();
				            $table->text('metka')->comment('Хит, Новинка, Суперцена, Выгода 0000  рублей...')->nullable();
				            //TODO внешний ключ ???

							$table->timestamps();
				            $table->softDeletes();
						});
					}
				}
				else
				{
					$doc = $doc[0];
				}

				foreach ($dataToInsert as $key => $value)
				{
					foreach ($value['shops'] as $value2)
					{
						\DB::table('document_action_first_datas')->insert(
		 					[
		 						'doc_id' => $doc->id,
								'shop_id' => $value2,
								'process_id' => $pr->id,
								'process_type_id' => $doc->process_type_id,
					            'kod_dis' => $value['kT'],
					            'articule_sk' => $value['articule_sk'] ?? 0,
								'action_types_ids' => $value['t'],
					            'on_invoice' => $this->parseProcenteFromExcelToInt($value['on_inv']),
					            'off_invoice' => $value['off_inv'],
					            'skidka_itogo' => $value['itog'],
					            'old_zakup_price' => $value['zak_old'],
					            'new_zakup_price' => $value['zak_new'],
					            'on_invoice_start' => $value['s_d_on_inv'],
					            'on_invoice_end' => $value['e_d_on_inv'],
					            'old_roznica_price' => $value['roz_old'],
					            'new_roznica_price' => $value['roz_new'],
					            'description' => $value['descr'],
					            'metka' => $value['marks'],
					            'created_at' => date('Y-m-d H:i:s')
		 					]
		 				);
					}
				}
			}, 2);
		}
		catch(Exception $e)
		{
			$this->validate_errors['form'][0]['error_db_save'] = 'Не удалось сохранить данные. Попробуйте еще раз либо обратитесь к администратору системы.';
		}

		if($err || !empty($this->validate_errors['form']) || !empty($this->validate_errors['file']))
		{
			return redirect()->back()
				->with('errors', $this->validate_errors)
				->withInput();
		}
		else
		{
			return redirect()->back()->with('ok', 'Добавление прошло успешно');
		}
	}

	public function ajaxAdd(Request $request)
	{
		$returnData = [];
		$dataToInsert = [];

		//	Валидация даты
		$start_date = strtotime(Request::input('start_date'));
		$proc_type = ProcessType::find(Request::input('process_type'));

		$end_date = '';
		if($proc_type)
		{
			$end_date = strtotime(Request::input('end_date'));
			if($end_date <= $start_date)
			{
				$this->validate_errors['form'][0]['end_date'] = 'Дата окончания акции должна быть больше даты начала.';
			}
		}
		else
		{
			$this->validate_errors['form'][0]['process_type'] = 'Нет Акций указанного типа';
		}

		if(!$request::has('rows'))
		{
			$this->validate_errors['form'][0]['kT'] = 'Не указано ни одного товара или указаны неверно.';
		}

		// Если в шапке есть ошибки покаызваем их пока.
		if(!empty($this->validate_errors['form']))
		{
			$returnData['errors'] = $this->validate_errors['form'] ?? [];
			echo json_encode($returnData);
			return;
		}

		$rows = json_decode(Request::input('rows'));

		if(Request::has('rowsDopData'))
		{
			$rowsDopData = [];
			foreach (Request::input('rowsDopData') as $key => $value)
			{
				$rowsDopData[$key] = json_decode($value);
			}
		}

		// Проверка полей формы
		foreach($rows as $key => &$value)
		{
			if(is_null($value))
			{
				continue;
			}
			if($value->kT == '' && $value->sh_Ttl == '')
			{
				$this->validate_errors['form'][$key]['kT'] = 'Не указан товар в строке ('.$key.'), либо строка пустая.';
				// break;
			}
			$value->distr = ($rowsDopData['distr'][$key] ?? '');
			$value->sh 	= ($rowsDopData['shops'][$key] ?? '');
			// $value->brend = ($rowsDopData['brend'][$key] ?? ''); Не нужен он тут. берем сразу из товара

			$value = (array)$value;
			if($this->validateData($value, 'form', $start_date, $end_date, $key))
			{
				$dataToInsert[$key] = $value;
			}
		}

		if(isset($this->validate_errors['form']) && count($this->validate_errors['form']) > 0)
		{
			$returnData['errors'] = $this->validate_errors['form'];
			echo json_encode($returnData);
			return;
		}

		try
		{
			DB::transaction(function() use ($start_date, $end_date, $proc_type, $dataToInsert)
			{
				//prId - если пришел значит редактируем
				if(Request::has('prId') && Request::input('prId') != '')
				{
					DocumentActionFirstData::where('process_id', Request::input('prId'))->delete();
					$pr = Process::find(Request::input('prId'));

					$pr_update = false;
					if($pr->process_type_id != Request::input('process_type'))
					{
						$pr->process_type_id = Request::input('process_type');
						$pr_update = true;
					}
					if(strtotime($pr->start_date) != $start_date)
					{
						$pr->start_date = $start_date;
						$pr_update = true;
					}
					if(strtotime($pr->end_date) != $end_date)
					{
						$pr->end_date = $end_date;
						$pr_update = true;
					}
					if($pr_update)
					{
						$pr->save();
					}
				}
				else
				{
					$pr = new Process();
					if(trim(Request::input('process_title')) == '')
					{
						$pr->title = $proc_type->title.' '.Request::input('start_date');
					}
					else
					{
						$pr->title = Request::input('process_title');
					}

					$pr->process_type_id = Request::input('process_type');
					$pr->start_date = $start_date;
					$pr->end_date = $end_date;
					$pr->user_id = Auth::id();
					$pr->save();
				}

				// $step_title = 'Данные';
				// $step = new Step();
				// $step->process_id = $pr->id;
				// $step->title = $step_title;
				// $step->conditions = '';
				// $step->from_ids = 0;
				// $step->to_ids = 0;
				// $step->save();

				// TODO возможно в дальнейшем будет несколько документов привязано к типу процееса, но пока ждем один
				$doc = Document::where('process_type_id', Request::input('process_type'))->get();
				if(count($doc) > 0)
				{
					$doc = $doc[0];
					if($doc->process_type_id != Request::input('process_type'))
					{
						$doc->process_type_id = Request::input('process_type');
						$doc->save();
					}
				}
				else
				{
					$doc = new Document();
					$doc->title = 'Документ '.$pr->title;
					$doc->process_type_id = Request::input('process_type');
					$doc->save();
				}

				//TODO перенести в миграцию
				if(!\Schema::hasTable('document_action_first_datas'))
				{
					$res = \Schema::create('document_action_first_datas', function ($table) {

						$table->increments('id');

						$table->integer('doc_id')->unsigned();
			            $table->integer('shop_id')->unsigned();
			            $table->integer('process_id')->unsigned();
			            $table->integer('process_type_id')->unsigned();

			            $table->integer('brend_id')->nullable();

						$table->string('distr_ttl')->nullable();
			            $table->string('distr')->nullable();

			            $table->string('start_action_date')->nullable()->comment('Дата начала акции');
			            $table->string('end_action_date')->nullable()->comment('Дата окончания акции');

			            $table->string('tovsTitles')->comment('Наименование товара');
			            $table->string('kod_dis')->comment('код ДиС Ном. Номер');
			            $table->string('articule_sk')->comment('Артикул ШК это артикул по базе поставщика');

						$table->string('action_types_ids')->comment('Артикул ШК это артикул по базе поставщика');

			            $table->string('on_invoice')->nullable();
			            $table->string('off_invoice')->nullable();
			            $table->string('skidka_itogo')->nullable();

			            $table->string('old_zakup_price')->nullable();
			            $table->string('new_zakup_price')->nullable();

			            $table->string('on_invoice_start')->nullable()->comment('Дата начала предоставления скидки он инвойс');
			            $table->string('on_invoice_end')->nullable()->comment('Дата окончания предоставления скидки он инвойс');

			            $table->string('old_roznica_price')->nullable();
			            $table->string('new_roznica_price')->nullable();

			            $table->string('razmesh_price')->nullable();

			            $table->text('description')->comment('подписи, слоганы, расшифровки и пояснения, которые Вы хотели бы видеть к своим товарам.')->nullable();
			            $table->text('metka')->comment('Хит, Новинка, Суперцена, Выгода 0000  рублей...')->nullable();
			            //TODO внешний ключ ???

						$table->timestamps();
			            $table->softDeletes();
					});
				}

				// проход по каждому товару
				foreach ($dataToInsert as $key => $value)
				{
					// проходим по всем указнным для данного товара магазинам.
					// вносим запись для каждого магазина. Да, да ((
					foreach ($value['sh'] as $value2)
					{
						//TODO через Eloquent
						\DB::table('document_action_first_datas')->insert(
		 					[
		 						'doc_id' => $doc->id,
								'shop_id' => $value2,
								'process_id' => $pr->id,
								'process_type_id' => $doc->process_type_id,
								'brend_id' => $value['br'],
								'distr_ttl' => $value['distr_ttl'],		// берем из товара
								'distr' => $value['distr'],				// берем из товара
								'tovsTitles' => $value['tovsTitles'],
					            'kod_dis' => $value['kT'],
					            'articule_sk' => $value['articule_sk'] ?? 0,
								'action_types_ids' => $value['t'],
					            'on_invoice' => $value['on_inv'],
					            'off_invoice' => $value['off_inv'],
					            'skidka_itogo' => $value['itog'],
					            'old_zakup_price' => $value['zak_old'],
					            'new_zakup_price' => $value['zak_new'],
					            'on_invoice_start' => $value['s_d_on_inv'],
					            'on_invoice_end' => $value['e_d_on_inv'],
					            'old_roznica_price' => $value['roz_old'],
					            'new_roznica_price' => $value['roz_new'],
								'start_action_date' => $value['sad'],
								'end_action_date' => $value['ead'],
								'razmesh_price' => $value['razmesh_price'],
					            'description' => $value['descr'],
					            'metka' => $value['marks'],
					            'created_at' => date('Y-m-d H:i:s')
		 					]
		 				);
					}
				}
			}, 2);
		}
		catch(Exception $e)
		{
			$this->validate_errors['form'][0]['error_db_save'] = 'Не удалось сохранить данные. Попробуйте еще раз либо обратитесь к администратору системы.';
		}

		if(isset($this->validate_errors['form']) && count($this->validate_errors['form']) > 0)
		{
			$returnData['errors'] = $this->validate_errors['form'] ?? [];
			echo json_encode($returnData);
			return;
		}
		else
		{
			echo json_encode(['success' => 1]);
			return;
		}
	}

	public function edit(Request $request, $id)
	{
		$action_types = [];
		$action_types[] = '0:Не выбрано';
		$action_types_descr = [];

		$types = ActionType::orderBy('title')->get();
		foreach ($types as $type)
		{
			$action_types[] = $type->id.':'.$type->title;
			$action_types_descr[$type->id] = $type->description;
		}
		return view('processes/edit', [
			'tov_categs_lvl1' => TovCategs::where('level', 1)->orderBy('title')->get(),
			'shop_regions_lvl1' => ShopRegion::where('level', 1)->orderBy('title')->get(),
			'process_types' => ProcessType::all(),
			'action_types' => implode(';', $action_types),
			'action_types_descr' => $action_types_descr,
			'action_marks' => ActionMark::all(),
			'user' => User::find(Auth::id()),
			'process' => Process::find($id),
		]);
	}

    /**
     * Валидация данных. Так же добавляет к массиву $data дополнительные(нужные значения)
     *
     * @param  Array $data - массив полей формы или массив полей из файла
     * @param  $start_date - дата когда должна начаться акция
     * @param  $end_date - дата когда должна окончиться акция
     * @return void
     */
    protected function validateData(Array &$data, $source, $start_date, $end_date, $row_num)
    {
		if(!isset($this->validate_errors[$source]))
		{
			$this->validate_errors[$source] = [];
		}
		// если данные из файла, то дата старта процесса(акции) будет находитя в файла. Ее нужно проверить для каждой строки
		if(isset($data['sad']))
		{
			if(!$this->validateDataStartProcessDate($data['sad'], $start_date))
			{
				$this->validate_errors[$source][$row_num]['sad'] = 'Дата начала акции должна быть в формате dd-mm-yyyy. Дата должна быть больше даты начала процесса.';
			}
		}
		// если данные из файла, то дата окончания процесса(акции) будет находитя в файла. Ее нужно проверить для каждой строки
		if(isset($data['ead']))
		{
			if(!$this->validateDataEndProcessDate($data['ead'], $end_date))
			{
				$this->validate_errors[$source][$row_num]['ead'] = 'Дата окончания акции должна быть в формате dd-mm-yyyy. Дата должна быть меньше даты окончания процесса.';
			}
		}

		if($source == 'file' && 
			!isset($this->validate_errors[$source][$row_num]['ead']) &&
			strtotime($data['sad']) >= strtotime($data['ead']))
		{
			$this->validate_errors[$source][$row_num]['end_date'] = 'Дата окончания акции должна быть меньше даты начала акции. Неверно ('.$data['sad'] .' - '. $data['ead'].')';
		}

		// Проверяем список магазинов,
		// Кешируем список магазинов ($this->cache_shops), чтоб каждый раз за ними не ходить.
		if(empty($this->cache_shops))
		{
			$tmp = Shop::orderBy('title')->get();
			foreach ($tmp as $key => $value)
			{
				$value->title = Shop::prepareShopName($value->title);
				if(!isset($this->cache_shops[$value->code]))
				{
					$this->cache_shops[$value->code] = ['code' => $value->code, 'title' => $value->title, 'id' => $value->id];
				}
			}
		}

		if($source == 'form')
		{
			if(isset($data['sh']) && trim($data['sh']) != '')
			{
				$tmp = $this->searchShop($data['sh_Ttl'], $source, $row_num);
				$data['sh'] = array_keys($tmp);
			}
			elseif(isset($data['sh_Ttl']) && trim($data['sh_Ttl']) != '')
			{
				$tmp = $this->searchShop($data['sh_Ttl'], $source, $row_num);
				$data['sh'] = array_keys($tmp);
			}
			else
			{
				$this->validate_errors[$source][$row_num]['shops'] = 'Не указаны магазины для товара';
			}
		}
		elseif($source == 'file')
		{
			//	магазины исключения отбрасываем все остальные магазины добавляем
			$tmp = [];
			$tmpTitles = [];
			$found_shops = [];

			if(isset($data['sh_ex']))
			{
				$found_shops = $this->searchShop($data['sh_ex'], $source, $row_num);
			}

			$c_tmp = count($found_shops);
			// убираем магазины-исключения из списка
			foreach ($this->cache_shops as $val)
			{
				if($c_tmp > 0)
				{
					if(in_array($val['title'], $found_shops))
					{
						continue;
					}
				}
				$tmp[] = $val['id'];
			}
			$data['sh'] = $tmp;
		}

		if($source == 'form' && isset($data['distr']) && trim($data['distr']) != '')
		{
			// из формы вообще не нужен нам дистрибьютер. При добавлении будем брать дистрибьютора из товара
			// if(!isset($this->cache_distr[$data['distr']]))
			// {
			// 	$postavshik = DB::connection('sqlsrv_imported_data')->select('
			// 		SELECT TOP 2 [Наименование], [Код], [ИНН]
			// 		FROM [Imported_Data].[dbo].[Действующие_Поставщики]
			// 		WHERE [Код] = \''.$data['distr'].'\' ');

			// 	$tmp = count($postavshik);
			// 	if($tmp > 1 || $tmp == 0)
			// 	{
			// 		$data['distr_ttl'] = '';
			// 	}
			// 	else
			// 	{
			// 		// Кешируем, чтобы за этим постащиком больше в базу не ходить
			// 		$this->cache_distr[$postavshik[0]->{'Код'}] = $postavshik[0]->{'Наименование'};
			// 		$data['distr_ttl'] = $postavshik[0]->{'Наименование'};
			// 	}
			// }
		}

		// если дистрибьютер оределелился, то берем его, если нет то игнорируем. т.е. в базу вносим только проверенные данные.
		// дистрибьютер на текущий момент не обязательное поле
		if($source == 'file' && isset($data['distr_ttl']) && trim($data['distr_ttl']) != '')
		{
			if(in_array($data['distr_ttl'], $this->cache_distr))
			{
				$tmp = array_keys($this->cache_distr, $data['distr_ttl']);
				$data['distr'] = $tmp[0];
			}
			else
			{
				$postavshik = DB::connection('sqlsrv_imported_data')->select('
					SELECT TOP 2 [Наименование], [Код], [ИНН]
					FROM [Imported_Data].[dbo].[Действующие_Поставщики]
					WHERE [Наименование] LIKE \'%'.$data['distr_ttl'].'%\' ');

				if(count($postavshik) != 1)
				{
					$data['distr_ttl'] = '';
				}
				else
				{
					// Кешируем, чтобы за этим постащиком больше в базу не ходить
					$data['distr'] = $postavshik[0]->{'Код'};
					$this->cache_distr[$postavshik[0]->{'Код'}] = $postavshik[0]->{'Наименование'};
				}

			}
		}

		if(isset($data['kT']) && $data['kT'] != '')
		{
			$data['kT'] = trim($data['kT']);

			//	сначала ищем по полному соответсвуию кода.
			$tmp = DB::connection('sqlsrv_imported_data')->select('SELECT [ArtName], [BrandName], [ArtArticle]
				FROM [Imported_Data].[dbo].[Assortment] 
				WHERE ArtCode = ? ', [$data['kT']]);
			if(!$tmp)
			{
				// Если нет точного соответствия, то ищем по вхождению. т.к. может быть что код пришел без ведущих нулей.
				$searched = false;
				$tmp = DB::connection('sqlsrv_imported_data')->select('SELECT [ArtName], [BrandName], [ArtArticle], [ArtCode]
					FROM [Imported_Data].[dbo].[Assortment] 
					WHERE ArtCode LIKE \'%'.$data['kT'].'\' ');
				if($tmp)
				{
					foreach($tmp as $key => $value)
					{
						// удаляем из кода(из базы) код который пришел. если остались только нули. значит это тот самый код.мы его нашли по вхождению
						$t_ = str_replace($data['kT'], '', $value->ArtCode);
                		if(preg_match('/^[0]+$/', $t_))
						{
							$searched = true;
							$data['tTtl'] = $value->ArtName;
							// заменяем код на корректный(с ведущими нулями. если мы дошли до сюда, значит искали код без ведущих нулей)
							$data['kT'] = $value->ArtCode;

							// берем бренд из товара
							$br = Brend::where('name', $tmp[0]->BrandName)->get();
							if($br->count() > 0)
							{
								$data['brTtl'] = $value->BrandName;
								$data['br'] = $br[0]->id;
							}
							else
							{
								$data['brTtl'] = '';
								$data['br'] = '';
							}
							break;
						}
					}
					if(!$searched)
					{
						$this->validate_errors[$source][$row_num]['kT'] = 'Не найден товар с указанным кодом "'.$data['kT'].'"';
					}
				}
				else
				{
					$data['brTtl'] = '';
					$data['br'] = '';
					$this->validate_errors[$source][$row_num]['kT'] = 'Не найден товар с указанным кодом "'.$data['kT'].'"';
				}
			}
			else
			{
				$data['br'] = '';
				$data['tTtl'] = $tmp[0]->ArtName;
				$br = Brend::where('name', $tmp[0]->BrandName)->get();
				if($br->count() > 0)
				{
					$data['brTtl'] = $tmp[0]->BrandName;
					$data['br'] = $br[0]->id;
				}
			}
		}
		else
		{
			$data['brTtl'] = '';
			$data['br'] = '';
			$this->validate_errors[$source][$row_num]['kT'] = 'Не указан код товара';
		}

		// Проверка типа маркетинговой акции
		if(isset($data['t']))
		{
			if(!preg_match('/[^0-9]+/i', $data['t']))
			{
				$action_type = ActionType::find($data['t']);
				if(!$action_type)
				{
					$this->validate_errors[$source][$row_num]['t'] = 'Не найден указанный тип маркетинговой акции';
				}
			}
			else
			{
				$tmp = explode(';', $data['t']);
				$data['t'] = [];

				foreach($tmp as $val_)
				{
					$action_type = ActionType::where('title', $val_)->get();
					if(count($action_type) > 0)
					{
						$data['t'][] = $action_type[0]->id;
					}
					else
					{
						$this->validate_errors[$source][$row_num]['t'] = 'Указанный тип маркетинговой акции не найден "'.$val_.'"';
					}
				}
			}
		}
		else
		{
			$this->validate_errors[$source][$row_num]['t'] = 'Не указан тип акции для товара';
		}

		// Размер скидки ON INVOICE
		if(isset($data['on_inv']) && trim($data['on_inv']) != '')
		{
			if(!$this->validateDataProcent($data['on_inv']))
			{
				$this->validate_errors[$source][$row_num]['on_inv'] = 'Неверное значение процента в колонке скидка ON INVOICE('.$data['on_inv'].'). Значение должно быть от 0 - 100.';
			}
			elseif($source == 'file')
			{
				$data['on_inv'] = (float)str_replace([',', '%', '-'], ['.','',''], $data['on_inv']);
				if($data['on_inv'] <= 1)
				{
					$data['on_inv'] = round($data['on_inv'] * 100);
				}
				else
				{
					$data['on_inv'] = round($data['on_inv']);
				}
			}
		}

		// Процент компенсации OFF INVOICE 
		if(isset($data['off_inv']) && trim($data['off_inv']) != '')
		{
			if(!$this->validateDataProcent($data['off_inv']))
			{
				$this->validate_errors[$source][$row_num]['off_inv'] = 'Неверное значение процента в колонке компенсация OFF INVOICE('.$data['off_inv'].'). Значение должно быть от 0 - 100.';
			}
			elseif($source == 'file')
			{
				$data['off_inv'] = (float)str_replace([',', '%', '-'], ['.','',''], $data['off_inv']);
				if($data['off_inv'] <= 1)
				{
					$data['off_inv'] = round($data['off_inv'] * 100);
				}
				else
				{
					$data['off_inv'] = round($data['off_inv']);
				}
			}
		}

		// Скидка ИТОГО  (%)
		if(isset($data['itog']) && trim($data['itog']) != '')
		{
			if(!$this->validateDataProcent($data['itog']))
			{
				$this->validate_errors[$source][$row_num]['itog'] = 'Неверное значение процента в колонке скидка итого('.$data['itog'].'). Значение должно быть от 0 - 100.';
			}
			elseif($source == 'file')
			{
				$data['itog'] = (string)str_replace([',', '%', '-'], ['.','',''], $data['itog']);
				if($data['itog'] <= 1)
				{
					$data['itog'] = round($data['itog'] * 100);
				}
				else
				{
					$data['itog'] = round($data['itog']);
				}
			}
		}
		else
		{
			$this->validate_errors[$source][$row_num]['itog'] = 'Не указана скидка итого.';
		}

		//Закупочная цена
		$data['zak_old'] = preg_replace('/[ ]+/', '', $data['zak_old']);
		if($data['zak_old'] != '' && !preg_match('/^[0-9\.\,]+$/', $data['zak_old']))
		{
			$this->validate_errors[$source][$row_num]['zak_old'] = 'Старая закупочная цена указана неверно.('.$data['zak_old'].')';
		}
		elseif($source == 'file')
		{
			$data['zak_old'] = (float)$data['zak_old'];
			if(strpos($data['zak_old'], '.') === false)
			{
				$data['zak_old'] .= '.00';
			}
			else
			{
				$data['zak_old'] = round($data['zak_old'], 2);
			}
		}

		$data['zak_new'] = preg_replace('/[ ]+/', '', $data['zak_new']);
		if($data['zak_new'] != '' && !preg_match('/^[0-9\.\,]+$/', $data['zak_new']))
		{
			$this->validate_errors[$source][$row_num]['zak_new'] = 'Новая закупочная цена указана неверно.('.$data['zak_new'].')';
		}
		elseif($source == 'file')
		{
			$data['zak_new'] = (float)$data['zak_new'];
			if(strpos($data['zak_new'], '.') === false)
			{
				$data['zak_new'] .= '.00';
			}
			else
			{
				$data['zak_new'] = round($data['zak_new'], 2);
			}
		}

		if(trim($data['zak_new']) != '' && trim($data['zak_old']) != '' && intval($data['zak_new']) > intval($data['zak_old']) && intval($data['zak_old']) > 0)
		{
			$this->validate_errors[$source][$row_num]['zak_new'] = 'Новая закупочная цена должны быть меньше старой закупочной цены.
			(Новая:'.$data['zak_new'].' Старая:'.$data['zak_old'].')';
		}
		// если предоставляется скидка он-инвойс,
		if(trim($data['on_inv']) != '')
		{
			//дата начала
			// не пусто
			// тип данных = дата
			$is_date = (bool) preg_match("/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $data['s_d_on_inv']);
			if(trim($data['s_d_on_inv']) == '')
			{
				$this->validate_errors[$source][$row_num]['s_d_on_inv'] = 'Не указана дата начала предоставления скидки ON INVOICE.';
			}
			elseif(!$is_date)
			{
				$this->validate_errors[$source][$row_num]['s_d_on_inv'] = 'Неверный формат даты начала предоставления скидки ON INVOICE.('.$data['s_d_on_inv'].')';
			}
			// дата начала предоставления скидки он-инвойс <= дата начала акции
			elseif(strtotime($data['s_d_on_inv']) > $start_date)
			{
				$this->validate_errors[$source][$row_num]['s_d_on_inv'] = 'Дата начала предоставления скидки ON INVOICE не должна быть больше даты акции.';
			}
			else
			{
				if($source == 'form')
				{
					$data['s_d_on_inv'] = strtotime($data['s_d_on_inv']);
				}
			}

			// дата окончания
			// тип данных = дата
			$is_date = (bool) preg_match("/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $data['e_d_on_inv']);
			// не пусто
			if(trim($data['e_d_on_inv']) == '')
			{
				$this->validate_errors[$source][$row_num]['e_d_on_inv'] = 'Не указана дата окончания предоставления скидки ON INVOICE.';
			}
			elseif(!$is_date)
			{
				$this->validate_errors[$source][$row_num]['e_d_on_inv'] = 'Неверный формат даты окончания предоставления скидки ON INVOICE.';
			}
			// дата начала предоставления скидки он-инвойс <= дата начала акции
			elseif($data['s_d_on_inv'] > strtotime($data['e_d_on_inv']))
			{
				$this->validate_errors[$source][$row_num]['e_d_on_inv'] = 'Дата начала предоставления скидки ON INVOICE не должна быть больше даты окончания скидки.';
			}
			else
			{
				// если из формы пришли данные, то дальше мы их вносим в базу. а значит нам нужне timastamp
				if($source == 'form')
				{
					$data['e_d_on_inv'] = strtotime($data['e_d_on_inv']);
				}
				// если же из файла, то мы возвращаем данные обратно в браузер, а соответсвенно не меняем в timestamp. оставляем в формате dd-mm-yyyy
			}
		}
		// Розничная цена
		$data['roz_old'] = preg_replace('/[ ]+/', '', $data['roz_old']);
		if($data['roz_old'] != '' && !preg_match('/^[0-9\.\,]+$/', $data['roz_old']))
		{
			$this->validate_errors[$source][$row_num]['roz_old'] = 'Старая розничная цена указана неверно.('.$data['roz_old'].')';
		}
		elseif($source == 'file')
		{
			$data['roz_old'] = (float)$data['roz_old'];
			if(strpos($data['roz_old'], '.') === false)
			{
				$data['roz_old'] .= '.00';
			}
			else
			{
				$data['roz_old'] = round($data['roz_old'], 2);
			}
		}

		$data['roz_new'] = preg_replace('/[ ]+/', '', $data['roz_new']);
		if($data['roz_new'] != '' && !preg_match('/^[0-9\.\,]+$/', $data['roz_new']))
		{
			$this->validate_errors[$source][$row_num]['roz_new'] = 'Новая розничная цена указана неверно.('.$data['roz_new'].')';
		}
		elseif($source == 'file')
		{
			$data['roz_new'] = (float)$data['roz_new'];
			if(strpos($data['roz_new'], '.') === false)
			{
				$data['roz_new'] .= '.00';
			}
			else
			{
				$data['roz_new'] = round($data['roz_new'], 2);
			}
		}

		if($data['roz_new'] != '' && $data['roz_old'] != '' && $data['roz_new'] > $data['roz_old'] && $data['roz_old'] > 0)
		{
			$this->validate_errors[$source][$row_num]['roz_new'] = 'Новая розничная цена должны быть меньше старой розничной цены.';
		}

// echo $row_num;
// print_r($this->validate_errors[$source][$row_num]);

		if (isset($this->validate_errors[$source][$row_num]))
		{
			return false;
		}
		return true;
	}

	public function prepareDataFromFile(Request $request)
	{
		$returnData = [];
		$returnData['errors'] = [];
		$returnData['data'] = [];

		if(Request::hasFile('file'))
		{
			$start_date = Request::input('start_date');
			if(trim($start_date) == '')
			{
				//TODO ошибка
			}
			$end_date = Request::input('end_date');

			$start_date = strtotime( $start_date );
			$end_date = strtotime( $end_date );

			$validator = Validator::make(Request::all(), ['file' => 'mimes:xlsx,xls']);
			if(!$validator->fails())
			{
				$dataToInsert = [];
				$new_path = public_path().'/upload/processes/'.Auth::id();

				// создаем имя файла. На всякий случай ограничиваем до 10 раз
				$r = 0;
				do {
					$new_name = '/'.Auth::id().'gazeta'.microtime(true).'.'.Request::file('file')->getClientOriginalExtension();
					$r++;
				}
				while (File::exists($new_path.$new_name) && $r <= 10);

				// Компируем файл на постоянное место хранения и сразу читаем файл
				if(!File::exists($new_path.$new_name))
				{
					$move = Request::file('file')->move($new_path, $new_name);

					$excel = PHPExcel_IOFactory::load($move); // подключить Excel-файл
					$excel->setActiveSheetIndex(0); // получить данные из указанного листа
					$sheet = $excel->getActiveSheet();

					$emptyRow = [];
					foreach ($sheet->getRowIterator() as $row_num => $row)
					{
						//	Первые две строки - заголовки, пропускаем.
						if($row_num <= 2)
							continue;

						if(trim($sheet->getCell('F'.$row_num)->getValue()) == '' AND
							trim($sheet->getCell('G'.$row_num)->getValue()) == '')
						{
							$emptyRow[] = $row_num;
							$this->validate_errors['file'][$row_num]['kT'] = 'Не указан товар в строке ('.$row_num.'), либо строка пустая.';
							continue;
						}
						// если сюда прошли, значит пошли непустые строки. забываем предыдущие пустые, по ним мы выведем ошибку
						$emptyRow = [];

						$returnData['data'][$row_num] = [];
						$cellIterator = $row->getCellIterator();
						$cellIterator->setIterateOnlyExistingCells(false);
						$dataToInsert[$row_num] = [];

						foreach($cellIterator as $key => $cell)
						{
							switch($key)
							{
								case 'A': //Дата начала акции
									// start_action_date
									$dataToInsert[$row_num]['sad'] = $this->parseDateFromExcelToInt($cell);
									break;
								case 'B': //Дата окончания акции
									$dataToInsert[$row_num]['ead'] = $this->parseDateFromExcelToInt($cell);
									break;
								case 'C': //Бренд
									$dataToInsert[$row_num]['br'] = $cell->getCalculatedValue();
									$dataToInsert[$row_num]['brTtl'] = $cell->getCalculatedValue();
									break;
								case 'D'://Магазины-исключения
									$v = $cell->getCalculatedValue();
									if(trim($v) != '')
									{
										$dataToInsert[$row_num]['sh_ex'] = explode(';', $v) ?? null;
									}
									else
									{
										$dataToInsert[$row_num]['sh_ex'] = [];
									}
									break;
								case 'E':  // Дистрибьютор(Плательщик)
									$dataToInsert[$row_num]['distr_ttl'] = $cell->getCalculatedValue();
									break;
								case 'F':	//наименование
									$dataToInsert[$row_num]['tTtl'] = $cell->getCalculatedValue();
									break;
								case 'G':	//код ДиС
									$dataToInsert[$row_num]['kT'] = $cell->getCalculatedValue();
									break;
								case 'H': // Артикул (ШК)
									$dataToInsert[$row_num]['art_sk'] = $cell->getCalculatedValue();
									break;
								case 'I': //Тип Акции (скидка, механика, подарок)
									$dataToInsert[$row_num]['t'] = $cell->getCalculatedValue();
									break;
								case 'J': // Размер скидки ON INVOICE 
									$dataToInsert[$row_num]['on_inv'] = $cell->getCalculatedValue();
									break;
								case 'K': //
									$dataToInsert[$row_num]['off_inv'] = $cell->getCalculatedValue();
									break;
								case 'L': //Итого
									$dataToInsert[$row_num]['itog'] = $cell->getCalculatedValue();
									break;
								case 'M': //Закупочная цена (руб) старая
									$dataToInsert[$row_num]['zak_old'] = $cell->getCalculatedValue();
									break;
								case 'N'://Закупочная цена (руб) новая
									$dataToInsert[$row_num]['zak_new'] = $cell->getCalculatedValue();
									break;
								case 'O':  //Период действия акционной цены ON INVOICE начало
									$dataToInsert[$row_num]['s_d_on_inv'] = $this->parseDateFromExcelToInt($cell);
									if($dataToInsert[$row_num]['s_d_on_inv'] === false)
									{
										$dataToInsert[$row_num]['s_d_on_inv'] = '';
									}
									break;
								case 'P':
									$dataToInsert[$row_num]['e_d_on_inv'] = $this->parseDateFromExcelToInt($cell);
									if($dataToInsert[$row_num]['e_d_on_inv'] === false)
									{
										$dataToInsert[$row_num]['e_d_on_inv'] = '';
									}
									break;
								case 'Q': //Розничная Цена
									$dataToInsert[$row_num]['roz_old'] = $cell->getCalculatedValue();
									break;
								case 'R':
									//TODO дробная часть куда девается
									$dataToInsert[$row_num]['roz_new'] = $cell->getCalculatedValue();
									break;
								case 'S':
									$dataToInsert[$row_num]['razmesh'] = $cell->getCalculatedValue();
									break;
								case 'T': 
									$dataToInsert[$row_num]['descr'] = $cell->getCalculatedValue();
									break;
								case 'U': 
									$dataToInsert[$row_num]['marks'] = $cell->getCalculatedValue();
									break;
							}
						}

						if(!$this->validateData($dataToInsert[$row_num], 'file', $start_date, $end_date, $row_num))
						{
							unset($dataToInsert[$row_num]);
						}
					}

					//если в массиве есть что-то, значит это последние строки в файле. по ним не выводим сообщения
					foreach($emptyRow as $v)
					{
						unset($this->validate_errors['file'][$v]);
					}
				}
				else
				{
					$returnData['errors'][0] = 'Не удалось обработать файл';
				}
			}
			else
			{
				$returnData['errors'][0] = 'Ошибка загрузки файла';
			}
		}
		else
		{
			$returnData['errors'][0] = 'Не удалось загрузить файл';
		}

		array_splice($dataToInsert, 10);

		$returnData['data'] = $dataToInsert;

		$returnData['errors'] = (($returnData['errors'] ?? []) + ($this->validate_errors['file'] ?? []));

		echo json_encode($returnData);
	}

	/**
	* $start_date - из шапки из формы
	* $proc_type_dedlain - дедлайн выбранногопроцесса
	* $value - из файла НЕ из формы
	*/
	private function validateDataStartProcessDate($value, $start_date)
	{
		$valid = (bool)preg_match("/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $value);
        if($valid)
        {
			$valid = ($start_date <= strtotime($value));
		}
		return $valid;
	}
	private function validateDataEndProcessDate($value, $end_date)
	{
		$valid = (bool)preg_match("/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $value);
		if($valid)
        {
			$valid = ($end_date >= strtotime($value));
        }
        return $valid;
	}
	private function dateFormatReplace($value)
	{
		$value = preg_replace('/[\-\/\\\,]/', '.', $value);
		$tmp = explode('.', $value);

		if(isset($tmp[2]) && mb_strlen($tmp[2]) == 2)
		{
			$tmp[2] = $tmp[2]+2000;
			$value = implode('.', $tmp);
		}
		return $value;
	}
	private function validateDataProcent($value, $parameters = [])
	{
		$value = preg_replace('/[\%\-]/', '', $value);

		$valid = !(bool) preg_match("/[^\.\,0-9]+/", $value);
        if($valid && trim($value) != '')
        {
			if(floatval($value) < 100 && floatval($value) >= 0)
            {
				return true;
            }
		}
		return false;
	}
	private function parseDateFromExcelToInt($cell)
	{
		if(trim($cell->getCalculatedValue()) == '')
		{
			return false;
		}

		$v = preg_replace('#[\.\,\\\/\- ]#u', '-', (string)$cell->getCalculatedValue());
		if(strpos($v, '-') !== false)
		{
			// Предполагаем что дата в формате dd-mm-yy
			if(strlen($v) == 8)
			{
				$tmp = explode('-', $v);
				$tmp[2] = '20'.$tmp[2];
				$v = implode('-', $tmp);
			}
			$v = strtotime($v);
		}
		else
	 	{
	 		try{
		 		if (PHPExcel_Shared_Date::isDateTime($cell))
				{
					$val = $cell->getCalculatedValue();
					if(preg_match('/[^0-9]/', $val))
					{
						return false;
					}
					$v = PHPExcel_Shared_Date::ExcelToPHP($val);
				}
	 		}
	 		catch(Exception $e)
	 		{
				return false;
	 		}
		}

		if($v > 0)
		{
			return date('d-m-Y', intval($v));
		}
		else
		{
			return false;
		}
	}

	// private function parseProcenteFromExcelToInt($proc)
	// {
	// 	$value = preg_replace('/[\%\-]/', '', $proc);
	// 	$value = preg_replace('/[\,]/', '.', $value);

 //        if(trim($value) != '')
 //        {
	// 		if(floatval($value) <= 1)
 //        	{
	// 			$value = $value * 100;
	// 		}

	// 		if(floatval($value) < 100 && floatval($value) >= 0)
 //            {
	// 			return $value;
 //            }
	// 	}
	//	return false;
	//}

	private function searchShop($shops, $source, $row_num)
	{
		if(!is_array($shops))
		{
			$shops = explode(';', $shops);
		}

		$tmp_arr = [];
		//смотрим есть ли такие магазины вообще?
		foreach ($shops as $value)
		{
			$exist = false;
			foreach ($this->cache_shops as $val)
			{
				$value = Shop::prepareShopName($value);
				if(in_array($value, $val))
				{
					$tmp_arr[$val['id']] = $value;
					$exist = true;
					break;
				}
			}
			if(!$exist)
			{
				$this->validate_errors[$source][$row_num]['shops'] = 'Указанный магазин не найден "'.$value.'"';
			}
		}

		return $tmp_arr;
	}
}