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
use App\DocumentActionFirstData;

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
	private $validate_errors = [];

	/**
	* Подгрузка списка для таблицы jqGrid
	**/
	public function ajaxJsonList(Request $request)
	{
		$perPage = 20;
		$processes = Process::paginate($perPage);

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
					++$number, $value->title, $value->start_date, $value->end_date, $value->processType->title
				];
		}
		echo json_encode($responce);
	}

	public function ajaxGetTovList(Request $request, $procId)
	{
		$tovs = Process::find($procId)->processTovs;
		foreach ($tovs as $key => $value)
		{
			$responce['rows'][$key]['id'] = $value->id;
		    $responce['rows'][$key]['cell'] = 
		    	[
					1, $value->id, $value->id, $value->id, $value->id
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
		return view('processes/add', [
			'tov_categs_lvl1' => TovCategs::where('level', 1)->orderBy('title')->get(),
			'shop_regions_lvl1' => ShopRegion::where('level', 1)->orderBy('title')->get(),
			'process_types' => ProcessType::all(),
			'action_types' => ActionType::all(),
			'action_marks' => ActionMark::all()
		]);
	}

	public function edit(Request $request, $id)
	{
		return view('processes/edit', ['process' => Process::find($id),
			'tov_categs_lvl1' => TovCategs::where('level', 1)->orderBy('title')->get(),
			'shop_regions_lvl1' => ShopRegion::where('level', 1)->orderBy('title')->get(),
			'process_types' => ProcessType::all(),
			'action_types' => ActionType::all(),
			'action_marks' => ActionMark::all()
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

		if(!$request::has('kodTov'))
		{
			$this->validate_errors['form'][0]['kodTov'] = 'Не указан товар или указан не верно.';
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
		foreach($request::input('kodTov') as $key => $value)
		{
			if(trim($value) == '' && count($request::input('kodTov')) == 1)
			{
				break;
			}
			$dataToInsert[$key]['kodTov'] = $value;
			$dataToInsert[$key]['tovsTitles'] = $request::input('tovsTitles')[$key] ?? null;

			if(isset($request::input('shops')[$key]))
			{
				$dataToInsert[$key]['shops'] = explode(';', $request::input('shops')[$key]);
			}

			$dataToInsert[$key]['distr'] = $request::input('distr')[$key] ?? null;
			$dataToInsert[$key]['type'] = $request::input('type')[$key] ?? null;
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
					            'kod_dis' => $value['kodTov'],
					            'articule_sk' => $value['articule_sk'] ?? 0,
								'action_types_ids' => $value['type'],
					            'on_invoice' => $this->parseProcenteFromExcelToInt($value['skidka_on_invoice']),
					            'off_invoice' => $value['kompensaciya_off_invoice'],
					            'skidka_itogo' => $value['skidka_itogo'],
					            'old_zakup_price' => $value['zakup_old'],
					            'new_zakup_price' => $value['zakup_new'],
					            'on_invoice_start' => $value['start_date_on_invoice'],
					            'on_invoice_end' => $value['end_date_on_invoice'],
					            'old_roznica_price' => $value['roznica_old'],
					            'new_roznica_price' => $value['roznica_new'],
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

    /**
     * Валидация данных 
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
		if(isset($data['start_date']))
		{
			if(!$this->validateDataStartProcessDate($data['start_date'], $start_date))
			{
				$this->validate_errors[$source][$row_num]['start_date'] = 'Дата начала акции должна быть в формате dd-mm-yyyy. Дата должна быть больше даты начала процесса.';
			}
		}
		// если данные из файла, то дата окончания процесса(акции) будет находитя в файла. Ее нужно проверить для каждой строки
		if(isset($data['end_date']))
		{
			if(!$this->validateDataEndProcessDate($data['end_date'], $end_date))
			{
				$this->validate_errors[$source][$row_num]['end_date'] = 'Дата окончания акции должна быть в формате dd-mm-yyyy. Дата должна быть меньше даты окончания процесса.';
			}
		}

		if($source == 'file' && 
			!isset($this->validate_errors[$source][$row_num]['end_date']) &&
			strtotime($data['start_date']) >= strtotime($data['end_date']))
		{
			$this->validate_errors[$source][$row_num]['end_date'] = 'Дата окончания акции должна быть меньше даты начала акции. Неверно ('.$data['start_date'] .' - '. $data['end_date'].')';
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
			if(isset($data['shops']))
			{
				$tmp = [];
				foreach ($data['shops'] as $value)
				{
					$exist = false;
					foreach ($this->cache_shops as $val)
					{
						if(in_array(Shop::prepareShopName($value), $val))
						{
							$tmp[] = $val['id'];
							$exist = true;
							break;
						}
					}

					if(!$exist)
					{
						$this->validate_errors[$source][$row_num]['shops'] = 'Указанный магазин не найден "'.$value.'"';
					}
				}
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
			$tmp_arr = [];

			if(isset($data['shops_exception']))
			{
				//смотрим есть ли такие магазины вообще?
				foreach ($data['shops_exception'] as $value)
				{
					$exist = false;
					foreach ($this->cache_shops as $val)
					{
						$value = Shop::prepareShopName($value);
						if(in_array($value, $val))
						{
							$tmp_arr[] = $value;
							$exist = true;
							break;
						}
					}
					if(!$exist)
					{
						$this->validate_errors[$source][$row_num]['shops'] = 'Указанный магазин-исключение не найден "'.$value.'"';
					}
				}
			}

			$c_tmp = count($tmp_arr);
			// убираем магазины-исключения из списка
			foreach ($this->cache_shops as $val)
			{
				if($c_tmp > 0)
				{
					if(in_array($val['title'], $tmp_arr))
					{
						continue;
					}
				}
				$tmp[] = $val['code'];
				$tmpTitles[] = $val['title'];
			}
			$data['shops'] = $tmp;
			$data['shopsTitles'] = $tmpTitles;
		}

		if($source == 'form' && isset($data['distr']) && trim($data['distr']) != '')
		{
			$postavshik = DB::connection('sqlsrv_imported_data')->select('
				SELECT TOP 2 [Наименование], [Код], [ИНН]
				FROM [Imported_Data].[dbo].[Действующие_Поставщики]
				WHERE 
					(
						[Наименование] LIKE \''.$data['distr'].'\'
						OR
						[Код] LIKE \''.$data['distr'].'\'
					)');
			$tmp = count($postavshik);
			if($tmp > 1 || $tmp == 0)
			{
				$this->validate_errors[$source][$row_num]['distr'] = 'Не удалось определить поставщика(Дистрибьютора) указан "'.$data['distr'].'"';
			}
		}
		// else
		// {
		// 	$this->validate_errors[$source][$row_num]['distr'] = 'Не указан поставщик для товара';
		// }

		// если дистрибьютер оределелился, то берем его, если нет то игнорируем. т.е. в базу вносим только проверенные данные.
		// дистрибьютер на текущий момент не обязательное поле
		if(isset($data['distrTitles']) && trim($data['distrTitles']) != '')
		{
			$postavshik = DB::connection('sqlsrv_imported_data')->select('
				SELECT TOP 2 [Наименование], [Код], [ИНН]
				FROM [Imported_Data].[dbo].[Действующие_Поставщики]
				WHERE [Наименование] LIKE \'%'.$data['distrTitles'].'%\' ');

			if(count($postavshik) != 1)
			{
				$data['distrTitles'] = '';

//				$this->validate_errors[$source][$row_num]['distr'] = 'Не удалось определить поставщика(Дистрибьютора) указан "'.$data['distrTitles'].'"';
			}
			else
			{
				$data['distr'] = $postavshik[0]->{'Код'};
			}
		}

		if(isset($data['kodTov']) && $data['kodTov'] != '')
		{
			$data['kodTov'] = trim($data['kodTov']);

			$tmp = DB::connection('sqlsrv_imported_data')->select('select [ArtName], [BrandName], [ArtArticle]
				FROM [Imported_Data].[dbo].[Assortment] 
				WHERE ArtCode = ? ', [$data['kodTov']]);
			if(!$tmp)
			{
				$searched = false;
				$tmp = DB::connection('sqlsrv_imported_data')->select('SELECT [ArtName], [BrandName], [ArtArticle], [ArtCode]
					FROM [Imported_Data].[dbo].[Assortment] 
					WHERE ArtCode LIKE \'%'.$data['kodTov'].'\' ');
				if($tmp)
				{
					foreach($tmp as $key => $value)
					{
						$t_ = str_replace($data['kodTov'], '', $value->ArtCode);
                		if(preg_match('/^[0]+$/', $t_))
						{
							$searched = true;
							$data['tovsTitles'] = $value->ArtName;
						}
					}

					if(!$searched)
					{
						$this->validate_errors[$source][$row_num]['kodTov'] = 'Не найден товар с указанным кодом "'.$data['kodTov'].'"';
					}
				}
				else
				{
					$this->validate_errors[$source][$row_num]['kodTov'] = 'Не найден товар с указанным кодом "'.$data['kodTov'].'"';
				}
			}
			else
			{
				$data['tovsTitles'] = $tmp[0]->ArtName;
			}
		}
		else
		{
			$this->validate_errors[$source][$row_num]['kodTov'] = 'Не указан код товара';
		}

		if($source == 'form')
		{
			if(isset($data['tovsTitles']) && trim($data['tovsTitles']) != '')
			{
				$tmp = DB::connection('sqlsrv_imported_data')->select('select [ArtName], [BrandName], [ArtArticle], [ArtCode]
					FROM [Imported_Data].[dbo].[Assortment]
					WHERE ArtName = ? ', [$data['tovsTitles']]);
				if(!$tmp)
				{
					$this->validate_errors[$source][$row_num]['tovsTitles'] = 'Не найден товар с указанным наименованием "'.$data['tovsTitles'].'"';
				}
				else
				{
					if(!isset($data['kodTov']) || trim($data['kodTov']) == '')
					{
						$data['kodTov'] = $tmp[0]->{'ArtCode'};
					}

					// бренд не проверяем.
					// if(isset($data['brend']))
					// {
					// 	if(trim($tmp[0]->BrandName) != trim($data['brend']))
					// 	{
					// 		$this->validate_errors[$source][$row_num]['brend'] = 'Не верно указан Бренд для товара "'.($tmp[0]->BrandName).'". Введен "'.$data['brend'].'".';
					// 	}
					// }

				}
			}
			else
			{
				$this->validate_errors[$source][$row_num]['tovsTitles'] = 'Не указано наименование товара.';
			}
		}

		// Проверка типа маркетинговой акции
		if(isset($data['type']))
		{
			if(!preg_match('/[^0-9]+/i', $data['type']))
			{
				$action_type = ActionType::find($data['type']);
				if(!$action_type)
				{
					$this->validate_errors[$source][$row_num]['type'] = 'Не найден указанный тип маркетинговой акции';
				}
			}
			else
			{
				$tmp = explode(';', $data['type']);
				$data['type'] = [];

				foreach($tmp as $val_)
				{
					$action_type = ActionType::where('title', $val_)->get();
					if(count($action_type) > 0)
					{
						$data['type'][] = $action_type[0]->id;
					}
					else
					{
						$this->validate_errors[$source][$row_num]['type'] = 'Указанный тип маркетинговой акции не найден "'.$val_.'"';
					}
				}
			}
		}
		else
		{
			$this->validate_errors[$source][$row_num]['type'] = 'Не указан тип акции для товара';
		}

		// Размер скидки ON INVOICE
		if(isset($data['skidka_on_invoice']) && trim($data['skidka_on_invoice']) != '')
		{



			if(!$this->validateDataProcent($data['skidka_on_invoice']))
			{
				$this->validate_errors[$source][$row_num]['skidka_on_invoice'] = 'Не верное значение процента в колонке скидка ON INVOICE('.$data['skidka_on_invoice'].'). Значение должно быть от 0 - 100.';
			}
			elseif($source == 'file')
			{
				if($data['skidka_on_invoice'] <= 1)
				{
					$data['skidka_on_invoice'] = str_replace(',', '.', $data['skidka_on_invoice']);
					$data['skidka_on_invoice'] = (float)$data['skidka_on_invoice'] * 100;
				}
			}
		}
		// else
		// {
		// 	$this->validate_errors[$source][$row_num]['skidka_on_invoice'] = 'Не указана скидка ON INVOICE('.$data['skidka_on_invoice'].') или указана неверно.';
		// }

		// Процент компенсации OFF INVOICE 
		if(isset($data['kompensaciya_off_invoice']) && trim($data['kompensaciya_off_invoice']) != '')
		{
			if(!$this->validateDataProcent($data['kompensaciya_off_invoice']))
			{
				$this->validate_errors[$source][$row_num]['kompensaciya_off_invoice'] = 'Не верное значение процента в колонке компенсация OFF INVOICE('.$data['kompensaciya_off_invoice'].'). Значение должно быть от 0 - 100.';
			}
			elseif($source == 'file')
			{
				$data['kompensaciya_off_invoice'] = str_replace('-', '', $data['kompensaciya_off_invoice']);
				if($data['kompensaciya_off_invoice'] <= 1)
				{
					$data['kompensaciya_off_invoice'] = str_replace(',', '.', $data['kompensaciya_off_invoice']);
					$data['kompensaciya_off_invoice'] = (float)$data['kompensaciya_off_invoice'] * 100;
				}
			}
		}

		// Скидка ИТОГО  (%)
		if(isset($data['skidka_itogo']) && trim($data['skidka_itogo']) != '')
		{
			if(!$this->validateDataProcent($data['skidka_itogo']))
			{
				$this->validate_errors[$source][$row_num]['skidka_itogo'] = 'Не верное значение процента в колонке скидка итого('.$data['skidka_itogo'].'). Значение должно быть от 0 - 100.';
			}
			elseif($source == 'file')
			{
				if($data['skidka_itogo'] <= 1)
				{
					$data['skidka_itogo'] = str_replace(',', '.', $data['skidka_itogo']);
					$data['skidka_itogo'] = (float)$data['skidka_itogo'] * 100;
				}
			}
		}
		else
		{
			$this->validate_errors[$source][$row_num]['skidka_itogo'] = 'Не указана скидка итого.';
		}

		//Закупочная цена
		$data['zakup_old'] = preg_replace('/[ ]+/', '', $data['zakup_old']);
		if($data['zakup_old'] != '' && !preg_match('/^[0-9\.\,]+$/', $data['zakup_old']))
		{
			$this->validate_errors[$source][$row_num]['zakup_old'] = 'Старая закупочная цена указана неверно.('.$data['zakup_old'].')';
		}
		elseif($source == 'file')
		{
			$data['zakup_old'] = round($data['zakup_old'], 2);
		}

		$data['zakup_new'] = preg_replace('/[ ]+/', '', $data['zakup_new']);
		if($data['zakup_new'] != '' && !preg_match('/^[0-9\.\,]+$/', $data['zakup_new']))
		{
			$this->validate_errors[$source][$row_num]['zakup_new'] = 'Новая закупочная цена указана неверно.('.$data['zakup_new'].')';
		}
		elseif($source == 'file')
		{
			$data['zakup_new'] = round($data['zakup_new'], 2);
		}

		if(trim($data['zakup_new']) != '' && trim($data['zakup_old']) != '' && intval($data['zakup_new']) > intval($data['zakup_old']))
		{
			$this->validate_errors[$source][$row_num]['zakup_new'] = 'Новая закупочная цена должны быть меньше старой закупочной цены.
			(Новая:'.intval($data['zakup_new']).' Старая:'.intval($data['zakup_old']).')';
		}

		// если предоставляется скидка он-инвойс,
		if(trim($data['skidka_on_invoice']) != '')
		{
			//дата начала
			// не пусто
			// тип данных = дата
			$is_date = (bool) preg_match("/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $data['start_date_on_invoice']);
			if(trim($data['start_date_on_invoice']) == '')
			{
				$this->validate_errors[$source][$row_num]['start_date_on_invoice'] = 'Не указана дата начала предоставления скидки ON INVOICE.';
			}
			elseif(!$is_date)
			{
				$this->validate_errors[$source][$row_num]['start_date_on_invoice'] = 'Неверный формат даты начала предоставления скидки ON INVOICE.('.$data['start_date_on_invoice'].')';
			}
			// дата начала предоставления скидки он-инвойс <= дата начала акции
			elseif(strtotime($data['start_date_on_invoice']) > $start_date)
			{
				$this->validate_errors[$source][$row_num]['start_date_on_invoice'] = 'Дата начала предоставления скидки ON INVOICE не должна быть больше даты акции.';
			}
			else
			{
				$data['start_date_on_invoice'] = strtotime($data['start_date_on_invoice']);
			}

			// дата окончания
			// тип данных = дата
			$is_date = (bool) preg_match("/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $data['end_date_on_invoice']);
			// не пусто
			if(trim($data['end_date_on_invoice']) == '')
			{
				$this->validate_errors[$source][$row_num]['end_date_on_invoice'] = 'Не указана дата окончания предоставления скидки ON INVOICE.';
			}
			elseif(!$is_date)
			{
				$this->validate_errors[$source][$row_num]['end_date_on_invoice'] = 'Неверный формат даты окончания предоставления скидки ON INVOICE.';
			}

			// дата начала предоставления скидки он-инвойс <= дата начала акции
			elseif(strtotime($data['start_date_on_invoice']) > strtotime($data['end_date_on_invoice']))
			{
				$this->validate_errors[$source][$row_num]['end_date_on_invoice'] = 'Дата начала предоставления скидки ON INVOICE не должна быть больше даты окончания скидки.';
			}
			else
			{
				$data['start_date_on_invoice'] = strtotime($data['start_date_on_invoice']);
			}
		}

		// Розничная цена
		$data['roznica_old'] = preg_replace('/[ ]+/', '', $data['roznica_old']);
		if($data['roznica_old'] != '' && !preg_match('/^[0-9\.\,]+$/', $data['roznica_old']))
		{
			$this->validate_errors[$source][$row_num]['roznica_old'] = 'Старая розничная цена указана неверно.('.$data['roznica_old'].')';
		}
		elseif($source == 'file')
		{
			$data['roznica_old'] = round($data['roznica_old'], 2);
		}

		$data['roznica_new'] = preg_replace('/[ ]+/', '', $data['roznica_new']);
		if($data['roznica_new'] != '' && !preg_match('/^[0-9\.\,]+$/', $data['roznica_new']))
		{
			$this->validate_errors[$source][$row_num]['roznica_new'] = 'Новая розничная цена указана неверно.('.$data['roznica_new'].')';
		}
		elseif($source == 'file')
		{
			$data['roznica_new'] = round($data['roznica_new'], 2);
		}

		if($data['roznica_new'] != '' && $data['roznica_old'] != '' && intval($data['roznica_new']) > intval($data['roznica_old']))
		{
			$this->validate_errors[$source][$row_num]['roznica_new'] = 'Новая розничная цена должны быть меньше старой розничной цены.';
		}

		if (!empty($this->validate_errors[$source][$row_num]))
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
					$i = 0;

					foreach ($sheet->getRowIterator() as $row_num => $row)
					{
						$i++;
						//	Первые две строки - заголовки, пропускаем.
						if($i <= 2)
							continue;

						if(trim($sheet->getCell('F'.$row_num)->getValue()) == '' AND
							trim($sheet->getCell('G'.$row_num)->getValue()) == '')
						{
							continue;
						}

						$returnData['data'][$row_num] = [];
						$cellIterator = $row->getCellIterator();
						$cellIterator->setIterateOnlyExistingCells(false);
						$dataToInsert[$row_num] = [];

						foreach($cellIterator as $key => $cell)
						{
							switch($key)
							{
								case 'A': //Дата начала акции
									$dataToInsert[$row_num]['start_date'] = $this->parseDateFromExcelToInt($cell);
									break;
								case 'B': //Дата окончания акции
									$dataToInsert[$row_num]['end_date'] = $this->parseDateFromExcelToInt($cell);
									break;
								case 'C': //Бренд
									$dataToInsert[$row_num]['brend'] = $cell->getCalculatedValue();
									break;
								case 'D'://Магазины-исключения
									$v = $cell->getCalculatedValue();
									if(trim($v) != '')
									{
										$dataToInsert[$row_num]['shops_exception'] = explode(';', $v) ?? null;
									}
									else
									{
										$dataToInsert[$row_num]['shops_exception'] = [];
									}
									break;
								case 'E':  // Дистрибьютор(Плательщик)
									$dataToInsert[$row_num]['distrTitles'] = $cell->getCalculatedValue();
									break;
								case 'F':	//наименование
									$dataToInsert[$row_num]['tovsTitles'] = $cell->getCalculatedValue();
									break;
								case 'G':	//код ДиС
									$dataToInsert[$row_num]['kodTov'] = $cell->getCalculatedValue();
									break;
								case 'H': // Артикул (ШК)
									$dataToInsert[$row_num]['articule_sk'] = $cell->getCalculatedValue();
									break;
								case 'I': //Тип Акции (скидка, механика, подарок)
									$dataToInsert[$row_num]['type'] = $cell->getCalculatedValue();
									break;
								case 'J': // Размер скидки ON INVOICE 
									$dataToInsert[$row_num]['skidka_on_invoice'] = $cell->getCalculatedValue();
									break;
								case 'K': //
									$dataToInsert[$row_num]['kompensaciya_off_invoice'] = $cell->getCalculatedValue();
									break;
								case 'L': //Итого
									$dataToInsert[$row_num]['skidka_itogo'] = $cell->getCalculatedValue();
									break;
								case 'M': //Закупочная цена (руб)
									$dataToInsert[$row_num]['zakup_old'] = $cell->getCalculatedValue();
									break;
								case 'N':
									$dataToInsert[$row_num]['zakup_new'] = $cell->getCalculatedValue();
									break;
								case 'O':  //Период действия акционной цены ON INVOICE
									$dataToInsert[$row_num]['start_date_on_invoice'] = $this->parseDateFromExcelToInt($cell);
									break;
								case 'P':
									$dataToInsert[$row_num]['end_date_on_invoice'] = $this->parseDateFromExcelToInt($cell);
									break;
								case 'Q': //Розничная Цена
									$dataToInsert[$row_num]['roznica_old'] = $cell->getCalculatedValue();
									break;
								case 'R':
									//TODO дробная часть куда девается
									$dataToInsert[$row_num]['roznica_new'] = $cell->getCalculatedValue();
									break;
								case 'S': 
									$dataToInsert[$row_num]['descr'] = $cell->getCalculatedValue();
									break;
								case 'T': 
									$dataToInsert[$row_num]['marks'] = $cell->getCalculatedValue();
									break;
							}
						}

						if(!$this->validateData($dataToInsert[$row_num], 'file', $start_date, $end_date, $row_num))
						{
							$err = true;
						}
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

// exit();

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
					$v = PHPExcel_Shared_Date::ExcelToPHP($cell->getCalculatedValue());
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

	private function parseProcenteFromExcelToInt($proc)
	{
		$value = preg_replace('/[\%\-]/', '', $proc);
		$value = preg_replace('/[\,]/', '.', $value);

        if(trim($value) != '')
        {
			if(floatval($value) <= 1)
        	{
				$value = $value * 100;
			}

			if(floatval($value) < 100 && floatval($value) >= 0)
            {
				return $value;
            }
		}
		return false;
	}
}