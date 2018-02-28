<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Action;
use Validator;
use File;
use Excel;
use DB;

class ActionController extends Controller
{
	public function list()
	{
		$actions = Action::all();
 		return view('actions/list', ['actions' => $actions]);
	}


	public function showAddFrom()
	{
		return view('actions/add');
	}

	public function add(Request $request)
	{
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
						// $rows = Excel::load($move, 'UTF-8')->all()->first();


$csv = Excel::load($move)->store('csv');

// print_r($csv->filename);
// portal\storage\exports
// echo storage_path('exports').'\\'.$csv->filename.'csv';

// $csv = Excel::load(storage_path('exports').'\\'.$csv->filename.'.csv');

if (($handle = fopen(storage_path('exports').'\\'.$csv->filename.'.csv', "r")) !== FALSE) {

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
    {
        print_r($data);

    }
    fclose($handle);
}
//print_r($csv->toArray());


exit();

						foreach($rows as $row)
						{
							$r = $row->toArray();
							// foreach ($rows as $key => $value)
							// {
							// 	// Первые две строки заголовочные. Пропускаем их
							// 	if($key < 2)
							// 	{
							// 		continue;
							// 	}
							// 	$this->validateData($value);
							// }
						}
exit();

					}
				}
				catch(Exception $e){}
			}
		}
		//	$this->validateData($request->all());
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
     * @return void
     */
    protected function validateData(Array $data)
    {
    	$curtime = time();

    	array_walk($data, 'trim');

		//0 "проверка: format "d.m.Y". дата начала акции > дата создания процесса + дедлайн проверка: тип данных = дата" 


	//	$res = DB::connection('sqlsrv_imported_data')->select('select IDStore FROM Store_Main 
	//	LIKE "'..'%"
	//	');

	//	$res = DB::connection('sqlsrv_imported_data')->select('select TOP 10 [IDStore] FROM [Imported_Data].[dbo].[Store_Main]');
	//	print_r($res);


		// $this->validate($request, [
		//     'email' => 'required|email',
		// ]);

		$validator = Validator::make($data, [
			'0' => 'required|date1',

			// '0' => 'match:/[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}/',
		]);

		if ($validator->fails())
		{

echo 'err';

			// return redirect('actions/add')
			//            ->withErrors($validator)
			//            ->withInput();

		}
		else
		{
			echo 'ok';
		}

print_r($data);
exit();

	}
}


