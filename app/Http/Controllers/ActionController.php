<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use App\Action;
use App\Shop;
use Validator;
use File;
use Excel;
use DB;

class ActionController extends Controller
{
	private $dedlain = 3024000;

	// хешируем сюда список магазинов
	private $shops = [];
	private $validate_errors = [];

	public function list()
	{
		$actions = Action::all();
 		return view('actions/list', ['actions' => $actions]);
	}

	public function showAddFrom(Request $request)
	{
		return view('actions/add');
	}

	public function add(Request $request)
	{
		// Валидация даты
		$start_date = strtotime(Request::input('start_date'));
		if($start_date < strtotime(date('d.m.Y 0:0:0.000', time() + $this->dedlain)))
		{
			$this->validate_errors[0]['start_date'] = 'Дата начала акции должна быть больше '.strftime('%d-%m-%Y', (time() + $this->dedlain));
		}

		$end_date = strtotime(Request::input('end_date'));
		if($end_date <= $start_date)
		{
			$this->validate_errors[1]['end_date'] = 'Дата окончания акции должна быть больше даты начала.';
		}

		// Проверяем загружен ли файл Exel
		if (Request::hasFile('file'))
		{
			$validator = Validator::make(Request::all(), ['file' => 'mimes:xlsx,xls']);
			if(!$validator->fails())
			{
				$new_path = public_path().'/upload/actions/'.Auth::id();

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
							$err = false;
							while (($data = fgetcsv($handle, 1200, ",")) !== FALSE)
						    {
								//Первые две строки заголовки, пропускаем
						    	$i++;
						    	if($i <= 2)
						    		continue;

								if(!$this->validateData($data, $start_date, $end_date))
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

								return redirect()->back()
										->with('errors', $this->validate_errors)
										->withInput();
							}
							else
							{
								return redirect()->back()->with('ok', 'Добавление прошло успешно');
							}
						}
					}
				}
				catch(Exception $e){}
			}
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
    protected function validateData(Array $data, $start_date, $end_date)
    {
		array_walk($data, 'trim');

		$v_err_count = count($this->validate_errors);
		$this->validate_errors[$v_err_count] = [];

		// Проверяем список магазинов,
		if(empty($this->shops))
		{
			// Assortment
			$tmp = DB::connection('sqlsrv_imported_data')->select('SELECT sm.StoreCode
      				,sr.StoreCity
      				,sr.StoreRegion
      				,sr.StoreMacroRegion
	  				,sm.StoreName
				FROM [Imported_Data].[dbo].[Store_Region] as sr 
						RIGHT JOIN 
					[Imported_Data].[dbo].[Store_Main] as sm ON 
						sr.IDStore = sm.IDStore');

			foreach ($tmp as $key => $value)
			{
				$value->StoreName = Shop::prepareShopName($value->StoreName);
				if(!isset($this->shops[$value->StoreCode]))
				{
					$this->shops[$value->StoreCode] = (array)$value;
				}
			}
		}

		foreach (explode(',', $data[10]) as $value)
		{
			$exist = false;
			foreach ($this->shops as $val)
			{
				if(in_array(Shop::prepareShopName($value), $val))
				{
					$exist = true;
					break;
				}
			}
			if(!$exist)
			{
				$this->validate_errors[$v_err_count][10] = 'Указанный магазин не найден "'.$value.'"';
			}
		}

		foreach (explode(',', $data[11]) as $value)
		{
			$exist = false;
			foreach ($this->shops as $val)
			{
				if(in_array(Shop::prepareShopName($value), $val))
				{
					$exist = true;
					break;
				}
			}

			if(!$exist)
			{
				$this->validate_errors[$v_err_count][11] = 'Указанный магазин-исключение не найден "'.$value.'"';
			}
		}

		$tmp = DB::connection('sqlsrv_imported_data')->select('select [ArtName], [BrandName] FROM [Imported_Data].[dbo].[Assortment] 
				WHERE ArtCode=\''.$data[14].'\'');
		if(!$tmp)
		{
			$this->validate_errors[$v_err_count][14] = 'Не найден товар с указанным Артикуром "'.$data[14].'"';
		}
		else
		{
			if(trim($tmp[0]->BrandName) != trim($data[6]))
			{
				$this->validate_errors[$v_err_count][6] = 'Не верно указан Бренд для товара "'.($tmp[0]->BrandName).'". Введен "'.$data[6].'".';
			}

			if(trim($tmp[0]->ArtName) != trim($data[13]))
			{
				$this->validate_errors[$v_err_count][13] = 'Не верно указано Наименование товара "'.($tmp[0]->ArtName).'". Введено "'.$data[13].'".';
			}
		}

		// Размер скидки ON INVOICE
		if(!$this->validateDataProcent($data[17]))
		{
			$this->validate_errors[$v_err_count][17] = 'Не верное значение процента. Значение должно быть от 0 - 100.';
		}

		// Процент компенсации OFF INVOICE 
		if(!$this->validateDataProcent($data[18]))
		{
			$this->validate_errors[$v_err_count][18] = 'Не верное значение процента. Значение должно быть от 0 - 100.';
		}

		// Скидка ИТОГО  (%)
		if(!$this->validateDataProcent($data[19]))
		{
			$this->validate_errors[$v_err_count][19] = 'Не верное значение процента. Значение должно быть от 0 - 100.';
		}

		//Закупочная цена
		if(trim($data[20]) == '' || !preg_match('/^[0-9\.\,\-]+$/', $data[20]))
		{
			$this->validate_errors[$v_err_count][20] = 'Не указана старая закупочная цена или указана неверно.';
		}
		if(trim($data[21]) == '' || !preg_match('/^[0-9\.\,\-]+$/', $data[21]))
		{
			$this->validate_errors[$v_err_count][21] = 'Не указана новая закупочная цена или указана неверно.';
		}
		if(intval($data[21]) >= intval($data[20]))
		{
			$this->validate_errors[$v_err_count][21] = 'Новая закупочная цена должны быть меньше старой закупочной цены.';
		}

		// Розничная цена
		if(trim($data[24]) == '' || !preg_match('/^[0-9\.\,\-]+$/', $data[24]))
		{
			$this->validate_errors[$v_err_count][24] = 'Не указана старая розничная цена или указана неверно.';
		}
		if(trim($data[25]) == '' || !preg_match('/^[0-9\.\,\-]+$/', $data[25]))
		{
			$this->validate_errors[$v_err_count][25] = 'Не указана новая розничная цена или указана неверно.';
		}
		if(intval($data[25]) >= intval($data[24]))
		{
			$this->validate_errors[$v_err_count][25] = 'Новая розничная цена должны быть меньше старой розничной цены.';
		}

		// если предоставляется скидка он-инвойс,
		if(trim($data[17]) != '')
		{
			//дата начала
			// не пусто
			if(trim($data[22]) == '')
			{
				$this->validate_errors[$v_err_count][22] = 'Не указана дата начала предоставления скидки ON INVOICE.';
			}
			// тип данных = дата
			$is_date = (bool) preg_match("/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $data[22]);
			if(!$is_date)
			{
				$this->validate_errors[$v_err_count][22] = 'Неверный формат даты начала представления скидки ON INVOICE.';
			}
			// дата начала предоставления скидки он-инвойс <= дата начала акции
			if(strtotime($data[22]) > strtotime($data[0]))
			{
				$this->validate_errors[$v_err_count][22] = 'Дата начала предоставления скидки ON INVOICE не должна быть больше даты акции.';
			}

			//дата окончания
			// не пусто
			if(trim($data[23]) == '')
			{
				$this->validate_errors[$v_err_count][23] = 'Не указана дата окончания предоставления скидки ON INVOICE.';
			}
			// тип данных = дата
			$is_date = (bool) preg_match("/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $data[23]);
			if(!$is_date)
			{
				$this->validate_errors[$v_err_count][23] = 'Неверный формат даты окончания представления скидки ON INVOICE.';
			}

			// дата начала предоставления скидки он-инвойс <= дата начала акции
			if(strtotime($data[22]) > strtotime($data[23]))
			{
				$this->validate_errors[$v_err_count][23] = 'Дата начала предоставления скидки ON INVOICE не должна быть больше даты окончания скидки.';
			}
		}

		if(!$this->validateDataStartActionDate($data[0], [$start_date]))
		{
			$this->validate_errors[$v_err_count][0] = 'Дата начала акции должна быть в формате dd-mm-yyyy. Дата должна быть больше даты начала процесса.';
		}
		if(!$this->validateDataEndActionDate($data[1], [$end_date]))
		{
			$this->validate_errors[$v_err_count][1] = 'Дата окончания акции должна быть в формате dd-mm-yyyy. Дата должна быть меньше даты окончания процесса.';
		}

		if (!empty($this->validate_errors[$v_err_count]))
		{
			return false;
		}

// TODO
		// if($data[17] <= 1 && $data[17] > 0)
		// 	$data[17] = $data[17]*100;

		// if($data[18] <= 1 && $data[18] > 0)
		// 	$data[18] = $data[18]*100;

		return true;
	}

	private function validateDataStartActionDate($value, $parameters = [])
	{
        $valid = (bool) preg_match("/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $value);
        if($valid)
        {
            $valid = ($parameters[0] <= strtotime($value));
        }
        return $valid;
	}

	private function validateDataEndActionDate($value, $parameters = [])
	{
        $valid = (bool) preg_match("/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $value);
        if($valid)
        {

			// $start_date = strtotime(Request::input('start_date'));
			// if($start_date < strtotime(date('d.m.Y 0:0:0.000', time() + $this->dedlain)))
			// {

			// }


// echo date('d.m.Y H:i:s', strtotime($value));
// echo strtotime($value);


// echo date('d.m.Y H:i:s', $parameters[0]);
// echo $parameters[0];


// 17-04-2018
// 17.04.2018 00:00:00

// var_dump(($parameters[0] <= strtotime($value)));
// 			exit();

			$valid = ($parameters[0] >= strtotime($value));
        }
        return $valid;
	}

	private function validateDataProcent($value, $parameters = [])
	{
        if(trim($value) != '')
        {
            if(floatval($value) < 100 && floatval($value) > 0)
            {
                return true;
            }
        }
        return false;
	}
}


