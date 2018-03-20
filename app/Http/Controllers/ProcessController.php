<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use App\Process;
use App\ProcessType;
use App\Step;
use App\Shop;
use App\ActionType;
use App\ActionMark;
use App\TovCategs;
use App\ShopRegion;
use App\Brend;
use App\Document;
use Validator;
use File;
use Excel;
use DB;

class ProcessController extends Controller
{
	// хешируем сюда список магазинов
	private $cache_shops = [];
	private $validate_errors = [];
	// $validate_errors['form'] = [];ошибки формы
	// $validate_errors['file'] = [];ошибки файла загрузки

	public function list()
	{
 		return view('processes/list', ['processes' => Process::all()]);
	}

	public function showAddFrom(Request $request)
	{
		return view('processes/add', [
			'tov_categs_lvl1' => TovCategs::where('level', 1)->get(),
			'shop_regions_lvl1' => ShopRegion::where('level', 1)->get(),
			'process_types' => ProcessType::all(),
			'action_types' => ActionType::all(),
			'action_marks' => ActionMark::all()
		]);
	}

	public function add(Request $request)
	{
		$err = false;

		// Валидация даты
		$start_date = strtotime(Request::input('start_date'));
		$proc_type = ProcessType::find(Request::input('process_type'));
		if($proc_type)
		{
			$cur_date = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
			if($proc_type->dedlain + $cur_date > $start_date)
			{
				$this->validate_errors['form'][0]['start_date'] = 'Дата начала акции должна быть больше либо ровна '.strftime('%d-%m-%Y', $proc_type->dedlain + time());
			}
		}
		else
		{
			$this->validate_errors['form'][0]['process_type'] = 'Нет Акций указанного типа';
		}

		$end_date = strtotime(Request::input('end_date'));
		if($end_date <= $start_date)
		{
			$this->validate_errors['form'][0]['end_date'] = 'Дата окончания акции должна быть больше даты начала.';
		}

		// Если в шапке есть ошибки покаызваем их пока.
		if(!empty($this->validate_errors['form']))
		{
			return redirect()->back()
				->with('errors', $this->validate_errors)
				->withInput();
		}

		$dataToInsert = [];

		// Проверка полей формы
		foreach($request::input('tovs') as $key => $value)
		{
			$dataToInsert[$key]['ArtCode'] = $value;
			// $dataToInsert[$key]['articule_sk'] = $value;

			if(isset($request::input('shops')[$key]))
			{
				$dataToInsert[$key]['shops'] = explode(',', $request::input('shops')[$key]);
			}

			$dataToInsert[$key]['type'] = $request::input('types')[$key] ?? null;
			$dataToInsert[$key]['skidka_on_invoice'] = $request::input('skidka_on_invoice')[$key] ?? null;
			$dataToInsert[$key]['kompensaciya_off_invoice'] = $request::input('kompensaciya_off_invoice')[$key] ?? null;
			$dataToInsert[$key]['skidka_itogo'] = $request::input('skidka_itogo')[$key] ?? null;
			$dataToInsert[$key]['zakup_old'] = $request::input('zakup_old')[$key] ?? null;
			$dataToInsert[$key]['zakup_new'] = $request::input('zakup_new')[$key] ?? null;
			$dataToInsert[$key]['start_date_on_invoice'] = $request::input('start_date_on_invoice')[$key] ?? null;
			$dataToInsert[$key]['end_date_on_invoice'] = $request::input('end_date_on_invoice')[$key] ?? null;
			$dataToInsert[$key]['roznica_old'] = $request::input('roznica_old')[$key] ?? null;
			$dataToInsert[$key]['roznica_new'] = $request::input('roznica_new')[$key] ?? null;
			$dataToInsert[$key]['descr'] = $request::input('descr')[$key] ?? null;
			$dataToInsert[$key]['marks'] = $request::input('marks')[$key] ?? null;

			$this->validateData($dataToInsert[$key], 'form', $start_date, $end_date);
		}

		//	Проверяем загружен ли файл Exel.
		if(Request::hasFile('file'))
		{
			$validator = Validator::make(Request::all(), ['file' => 'mimes:xlsx,xls']);
			if(!$validator->fails())
			{
				$new_path = public_path().'/upload/processes/'.Auth::id();

				// создаем имя файла. На всякий случай ограничиваем до 10 раз
				$r = 0;
				do {
					$new_name = '/'.Auth::id().'gazeta'.microtime(true).'.'.Request::file('file')->getClientOriginalExtension();
					$r++;
				}
				while (File::exists($new_path.$new_name) && $r <= 10);

				// Компируем файл на постоянное место хранения и сразу читаем файл
				try{
					if(!File::exists($new_path.$new_name))
					{
						$move = Request::file('file')->move($new_path, $new_name);

						// Сохраняем в csv потому как в xls совмещенные поля читаются криво
						$csv = Excel::load($move, 'UTF-8')->store('csv');
						$csv_path_file = storage_path('exports').'\\'.$csv['file'];

						if(($handle = fopen($csv_path_file, "r")) !== FALSE)
						{
							$i = 0;

							while (($data = fgetcsv($handle, 1200, ",")) !== FALSE)
						    {
								//Первые две строки заголовки, пропускаем
						    	$i++;
						    	if($i <= 2)
						    		continue;

								// $dataToInsert
								// $data['brend']
								// $data['ArtName']

								if(!$this->validateData($data, 'file', $start_date, $end_date))
								{
									$err = true;
								}
						    }
							fclose($handle);

							if($err)
							{
								if(Storage::exists(str_replace(base_path(), '', $new_path).$new_name))
								{
									Storage::delete(str_replace(base_path(), '', $new_path).$new_name);
								}
								//TODO удялем ли файлы xlsx по которым были ошибки? или только те которые приняла система
								if(Storage::exists(str_replace(base_path(), '', $csv_path_file)))
								{
									Storage::delete(str_replace(base_path(), '', $csv_path_file));
								}
							}
						}
					}
				}
				catch(Exception $e){}
			}
		}

//	print_r($this->validate_errors);
//	exit();

		// if($err || !empty($this->validate_errors))
		// {
		// 	return redirect()->back()
		// 		->with('errors', $this->validate_errors)
		// 		->withInput();
		// }
		// else
		// {
		// 	return redirect()->back()->with('ok', 'Добавление прошло успешно');
		// }

		// tovs
		// shops

//	distr

		// types
		// skidka_on_invoice
		// kompensaciya_off_invoice
		// skidka_itogo
		// zakup_old
		// zakup_new
		// start_date_on_invoice
		// end_date_on_invoice
		// roznica_old
		// roznica_new
		// descr
		// marks

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

				$step_title = 'Данные';

				$step = new Step();
				$step->process_id = $pr->id;
				$step->title = $step_title;
				$step->conditions = '';
				$step->from_ids = 0;
				$step->to_ids = 0;
				$step->save();

				$doc = new Document();
				$doc->step_id = $step->id;
				$doc->title = 'Документ '.$step_title;
				$doc->save();

				// TODO нужно на лету создавать эту таблицу. И удалять ее если удаляется документ(родительский документ)
				if(!\Schema::hasTable('documents_values_'.$doc->id))
				{
					\Schema::create('documents_values_'.$doc->id, function ($table) {
						$table->increments('id');

			            $table->integer('shop_id')->unsigned();
			            $table->integer('process_id')->unsigned();

			            $table->string('kod_dis')->comment('код ДиС Ном. Номер');
			            $table->string('articule_sk')->comment('Артикул ШК это артикул по базе поставщика');

						$table->integer('action_types_id')->unsigned();

			            $table->string('on_invoice');
			            $table->string('off_invoice');
			            $table->string('skidka_itogo');

			            $table->string('old_zakup_price');
			            $table->string('new_zakup_price');

			            $table->string('on_invoice_start')->comment('Дата начала предоставления скидки он инвойс');
			            $table->string('on_invoice_end')->comment('Дата окончания предоставления скидки он инвойс');

			            $table->string('old_roznica_price');
			            $table->string('new_roznica_price');

			            $table->text('description')->comment('подписи, слоганы, расшифровки и пояснения, которые Вы хотели бы видеть к своим товарам.');

			            $table->text('metka')->comment('Хит, Новинка, Суперцена, Выгода 0000  рублей...');
			            //TODO внешний ключ ???

						$table->timestamps();
			            $table->softDeletes();
					});
				}

				foreach ($dataToInsert as $key => $value)
				{
					// $dataToInsert[$key]['ArtCode'] = $value;
					// $dataToInsert[$key]['shops'] = $request::input('shops')[$key] ?? null;
					// $dataToInsert[$key]['type'] = $request::input('types')[$key] ?? null;
					// $dataToInsert[$key]['skidka_on_invoice'] = $request::input('skidka_on_invoice')[$key] ?? null;
					// $dataToInsert[$key]['kompensaciya_off_invoice'] = $request::input('kompensaciya_off_invoice')[$key] ?? null;
					// $dataToInsert[$key]['skidka_itogo'] = $request::input('skidka_itogo')[$key] ?? null;
					// $dataToInsert[$key]['zakup_old'] = $request::input('zakup_old')[$key] ?? null;
					// $dataToInsert[$key]['zakup_new'] = $request::input('zakup_new')[$key] ?? null;
					// $dataToInsert[$key]['start_date_on_invoice'] = $request::input('start_date_on_invoice')[$key] ?? null;
					// $dataToInsert[$key]['end_date_on_invoice'] = $request::input('end_date_on_invoice')[$key] ?? null;
					// $dataToInsert[$key]['roznica_old'] = $request::input('roznica_old')[$key] ?? null;
					// $dataToInsert[$key]['roznica_new'] = $request::input('roznica_new')[$key] ?? null;
					// $dataToInsert[$key]['descr'] = $request::input('descr')[$key] ?? null;
					// $dataToInsert[$key]['marks'] = $request::input('marks')[$key] ?? null;


// Array
// (
// 	[0] => Array
// 	(
// 		[ArtCode] => GL000282356
// 		[shops] => 186,185,184,183,182,181,180,179,178,177,176,175,174,173,172,171,170,169,168,166,167,165,164,163,162,161,118,160,159,158,155,157,156,154,153,152,151,150,149,148,147,145,144,146,143,141,142,140,139,138,137,136,99,112,106,111,98,129,107,113,126,132,119,105,101,103,127,108,123,122,135,104,130,124,100,115,114,121,120,116,133,109,131,125,134,102,117,128,110,96,97,95,94,93,90,91,92,89,88,87,86,85,84,83,82,81,80,79,78,77,75,74,76,73,72,71,70,69,66,67,68,65,64,63,60,59,62,61,55,58,47,49,51,52,53,54,56,48,57,50,46,45,44,42,43,41,40,39,38,37,10,36,35,34,33,32,31,30,27,29,28,25,26,22,23,24,21,20,19,18,17,16,14,15,13,7,8,12,11,9,6,5,4,3,1,2
// 		[type] => 1
// 		[skidka_on_invoice] => 12
// 		[kompensaciya_off_invoice] => 2
//         [skidka_itogo] => 83.3
//         [zakup_old] => 12
//         [zakup_new] => 2
//         [start_date_on_invoice] => 20-03-2018
//         [end_date_on_invoice] => 23-03-2018
//         [roznica_old] => 12
//         [roznica_new] => 2
//         [descr] => 12
//         [marks] => 12
//     )
// )
					foreach ($value['shops'] as $value2)
					{
						\DB::table('documents_values_'.$doc->id)->insert(
		 					[
								'shop_id' => $value2,
					            'process_id' => $pr->id, // Нужно это поле тут???
					            'kod_dis' => $value['ArtCode'],
					            'articule_sk' => $value['articule_sk'],
								'action_types_id' => $value['type'],
					            'on_invoice' => $value['skidka_on_invoice'],
					            'off_invoice' => $value['kompensaciya_off_invoice'],
					            'skidka_itogo' => $value['skidka_itogo'],
					            'old_zakup_price' => $value['zakup_old'],
					            'new_zakup_price' => $value['zakup_new'],
					            'on_invoice_start' => $value['start_date_on_invoice'],
					            'on_invoice_end' => $value['end_date_on_invoice'],
					            'old_roznica_price' => $value['roznica_old'],
					            'new_roznica_price' => $value['roznica_new'],
					            'description' => $value['descr'],
					            'metka' => $value['marks']
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


		if($err || !empty($this->validate_errors))
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

// public function login(Request $request)
// {
// 	$this->validateLogin($request);

 //        // If the class is using the ThrottlesLogins trait, we can automatically throttle
 //        // the login attempts for this application. We'll key this by the username and
 //        // the IP address of the client making these requests into this application.
 //        if ($this->hasTooManyLoginAttempts($request)) {
 //            $this->fireLockoutEvent($request);

 //            return $this->sendLockoutResponse($request);
 //        }

 //        if ($this->attemptLogin($request)) {
 //            return $this->sendLoginResponse($request);
 //        }

 //        // If the login attempt was unsuccessful we will increment the number of attempts
 //        // to login and redirect the user back to the login form. Of course, when this
 //        // user surpasses their maximum number of attempts they will get locked out.
 //        $this->incrementLoginAttempts($request);

 //        return $this->sendFailedLoginResponse($request);
 //    }

    /**
     * Валидация данных 
     *
     * @param  Array $data - массив полей формы или массив полей из файла
     * @param  $start_date - дата когда должна начаться акция
     * @param  $end_date - дата когда должна окончиться акция
     * @return void
     */
    protected function validateData(Array &$data, $source, $start_date, $end_date)
    {
		if(!isset($this->validate_errors[$source]))
		{
			$this->validate_errors[$source] = [];
		}
		$v_err_count = count($this->validate_errors[$source]);

		// Проверяем список магазинов,
		// Кешируем список магазинов ($this->cache_shops), чтоб каждый раз за ними не ходить.
		if(empty($this->cache_shops))
		{
			$tmp = Shop::all();
			foreach ($tmp as $key => $value)
			{
				$value->title = Shop::prepareShopName($value->title);
				if(!isset($this->cache_shops[$value->code]))
				{
					$this->cache_shops[$value->code] = [$value->code, $value->title, $value->id];
				}
			}
		}

		if(isset($data['shops']))
		{
			foreach ($data['shops'] as $value)
			{
				$exist = false;
				foreach ($this->cache_shops as $val)
				{
					if(in_array(Shop::prepareShopName($value), $val))
					{
						$exist = true;
						break;
					}
				}
				if(!$exist)
				{
					$this->validate_errors[$source][$v_err_count]['shops'] = 'Указанный магазин не найден "'.$value.'"';
				}
			}
		}
		else
		{
			$this->validate_errors[$source][$v_err_count]['shops'] = 'Не указаны магазины для товара';
		}

		//проверка магазинов исключений
		if(isset($data['shops_exception']) && trim($data['shops_exception']) != '')
		{
			foreach (explode(',', $data['shops_exception']) as $value)
			{
				$exist = false;
				foreach ($this->cache_shops as $val)
				{
					if(in_array(Shop::prepareShopName($value), $val))
					{
						$exist = true;
						break;
					}
				}
				if(!$exist)
				{
					$this->validate_errors[$source][$v_err_count]['shops_exception'] = 'Указанные магазины-исключения не найдены "'.$value.'"';
				}
			}
		}

		if(isset($data['ArtCode']))
		{
			$tmp = DB::connection('sqlsrv_imported_data')->select('select [ArtName], [BrandName], [ArtArticle] FROM [Imported_Data].[dbo].[Assortment] 
				WHERE ArtCode=\''.$data['ArtCode'].'\'');
			if(!$tmp)
			{
				$this->validate_errors[$source][$v_err_count]['ArtCode'] = 'Не найден товар с указанным Артикуром "'.$data['ArtCode'].'"';
			}
			else
			{
				$data['articule_sk'] = $tmp[0]->ArtArticle;

				// Если данные из файла то должен быть передан параметр $data['brend']. Нужно проверить правильность заполнения бренда
				if(isset($data['brend']))
				{
					if(trim($tmp[0]->BrandName) != trim($data['brend']))
					{
						$this->validate_errors[$source][$v_err_count]['brend'] = 'Не верно указан Бренд для товара "'.($tmp[0]->BrandName).'". Введен "'.$data['brend'].'".';
					}
				}

				// Если данные из файла то должен быть передан параметр $data['ArtName']. Нужно проверить правильность заполнения Наименования товара
				if(isset($data['ArtName']))
				{
					if(trim($tmp[0]->ArtName) != trim($data['ArtName']))
					{
						$this->validate_errors[$source][$v_err_count]['ArtName'] = 'Не верно указано Наименование товара "'.($tmp[0]->ArtName).'". Введено "'.$data['ArtName'].'".';
					}
				}
			}
		}

		// Проверка типа маркетинговой акции
		if(isset($data['type']))
		{
			if(intval($data['type']) == $data['type'])
			{
				$action_type = ActionType::find($data['type']);
			}
			else
			{
				$action_type = ActionType::where('title = ?', $data['type']);
			}

			if(!$action_type)
			{
				$this->validate_errors[$source][$v_err_count]['type'] = 'Указанный тип маркетинговой акции не найден "'.$data['type'].'"';
			}
		}
		else
		{
			$this->validate_errors[$source][$v_err_count]['type'] = 'Не указан тип акции для товара';
		}
		// Размер скидки ON INVOICE
		if(isset($data['skidka_on_invoice']))
		{
			if(!$this->validateDataProcent($data['skidka_on_invoice']))
			{
				$this->validate_errors[$source][$v_err_count]['skidka_on_invoice'] = 'Не верное значение процента. Значение должно быть от 0 - 100.';
			}
		}
		// Процент компенсации OFF INVOICE 
		if(isset($data['kompensaciya_off_invoice']))
		{
			if(!$this->validateDataProcent($data['kompensaciya_off_invoice']))
			{
				$this->validate_errors[$source][$v_err_count]['kompensaciya_off_invoice'] = 'Не верное значение процента. Значение должно быть от 0 - 100.';
			}
		}

		// Скидка ИТОГО  (%)
		if(isset($data['skidka_itogo']))
		{
			if(!$this->validateDataProcent($data['skidka_itogo']))
			{
				$this->validate_errors[$source][$v_err_count]['skidka_itogo'] = 'Не верное значение процента. Значение должно быть от 0 - 100.';
			}
		}

		//Закупочная цена
		if(trim($data['zakup_old']) == '' || !preg_match('/^[0-9\.\,\-]+$/', $data['zakup_old']))
		{
			$this->validate_errors[$source][$v_err_count]['zakup_old'] = 'Не указана старая закупочная цена или указана неверно.';
		}

		if(trim($data['zakup_new']) == '' || !preg_match('/^[0-9\.\,\-]+$/', $data['zakup_new']))
		{
			$this->validate_errors[$source][$v_err_count]['zakup_new'] = 'Не указана новая закупочная цена или указана неверно.';
		}
		if(intval($data['zakup_new']) >= intval($data['zakup_old']))
		{
			$this->validate_errors[$source][$v_err_count]['zakup_new'] = 'Новая закупочная цена должны быть меньше старой закупочной цены.';
		}

		// Розничная цена
		if(trim($data['roznica_old']) == '' || !preg_match('/^[0-9\.\,\-]+$/', $data['roznica_old']))
		{
			$this->validate_errors[$source][$v_err_count]['roznica_old'] = 'Не указана старая розничная цена или указана неверно.';
		}
		if(trim($data['roznica_new']) == '' || !preg_match('/^[0-9\.\,\-]+$/', $data['roznica_new']))
		{
			$this->validate_errors[$source][$v_err_count]['roznica_new'] = 'Не указана новая розничная цена или указана неверно.';
		}
		if(intval($data['roznica_new']) >= intval($data['roznica_old']))
		{
			$this->validate_errors[$source][$v_err_count]['roznica_new'] = 'Новая розничная цена должны быть меньше старой розничной цены.';
		}

		// если предоставляется скидка он-инвойс,
		if(trim($data['skidka_on_invoice']) != '')
		{
			//дата начала
			// не пусто
			if(trim($data['start_date_on_invoice']) == '')
			{
				$this->validate_errors[$source][$v_err_count]['start_date_on_invoice'] = 'Не указана дата начала предоставления скидки ON INVOICE.';
			}
			// тип данных = дата
			$is_date = (bool) preg_match("/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $data['start_date_on_invoice']);
			if(!$is_date)
			{
				$this->validate_errors[$source][$v_err_count]['start_date_on_invoice'] = 'Неверный формат даты начала предоставления скидки ON INVOICE.';
			}
			// дата начала предоставления скидки он-инвойс <= дата начала акции
			if(strtotime($data['start_date_on_invoice']) > $start_date)
			{
				$this->validate_errors[$source][$v_err_count]['start_date_on_invoice'] = 'Дата начала предоставления скидки ON INVOICE не должна быть больше даты акции.';
			}

			// дата окончания
			// не пусто
			if(trim($data['end_date_on_invoice']) == '')
			{
				$this->validate_errors[$source][$v_err_count]['end_date_on_invoice'] = 'Не указана дата окончания предоставления скидки ON INVOICE.';
			}
			// тип данных = дата
			$is_date = (bool) preg_match("/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $data['end_date_on_invoice']);
			if(!$is_date)
			{
				$this->validate_errors[$source][$v_err_count]['end_date_on_invoice'] = 'Неверный формат даты окончания предоставления скидки ON INVOICE.';
			}

			// дата начала предоставления скидки он-инвойс <= дата начала акции
			if(strtotime($data['start_date_on_invoice']) > strtotime($data['end_date_on_invoice']))
			{
				$this->validate_errors[$source][$v_err_count]['end_date_on_invoice'] = 'Дата начала предоставления скидки ON INVOICE не должна быть больше даты окончания скидки.';
			}
		}

		// если данные из файла, то дата старта процесса(акции) будет находитя в файла. Ее нужно проверить для каждой строки
		if(isset($data['start_date']))
		{
			if(!$this->validateDataStartProcessDate($data['start_date'], $start_date))
			{
				$this->validate_errors[$source][$v_err_count]['start_date'] = 'Дата начала акции должна быть в формате dd-mm-yyyy. Дата должна быть больше даты начала процесса.';
			}
		}
		// если данные из файла, то дата окончания процесса(акции) будет находитя в файла. Ее нужно проверить для каждой строки
		if(isset($data['end_date']))
		{
			if(!$this->validateDataEndProcessDate($data['end_date'], $end_date))
			{
				$this->validate_errors[$source][$v_err_count]['end_date'] = 'Дата окончания акции должна быть в формате dd-mm-yyyy. Дата должна быть меньше даты окончания процесса.';
			}
		}

		if (!empty($this->validate_errors[$source][$v_err_count]))
		{
			return false;
		}

		//	TODO
		//	if($data[17] <= 1 && $data[17] > 0)
		//	$data[17] = $data[17]*100;

		//	if($data[18] <= 1 && $data[18] > 0)
		//	$data[18] = $data[18]*100;

		return true;
	}

	/**
	* $start_date - из шапки из формы
	* $proc_type_dedlain - дедлайн выбранногопроцесса
	* $value - из файла НЕ из формы
	*/
	private function validateDataStartProcessDate($value, $start_date)
	{
		$valid = (bool) preg_match("/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $value);
        if($valid)
        {
			$valid = ($start_date <= strtotime($value));
        }
        return $valid;
	}

	private function validateDataEndProcessDate($value, $end_date)
	{
        $valid = (bool) preg_match("/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $value);
        if($valid)
        {
			$valid = ($end_date >= strtotime($value));
        }

        return $valid;
	}

	private function validateDataProcent($value, $parameters = [])
	{
		$valid = !(bool) preg_match("/[^\.\,0-9]+/", $value);
        if($valid && trim($value) != '')
        {
			if(floatval($value) < 100 && floatval($value) > 0)
            {
				return true;
            }
		}
		return false;
	}
}