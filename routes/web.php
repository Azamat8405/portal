<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'auth'], function () {

	Route::get('/', 'HomeController@index');

	/*Для админов*/
	Route::group(['middleware' => 'admin'], function () {

		Route::get('/processes', 						'ProcessController@list')->name('processes');
		Route::get('/processes/add', 					'ProcessController@showAddFrom')->name('processes.add');
		Route::get('/processes/edit/{id}',				'ProcessController@edit');
		Route::get('/processes/ajaxList',				'ProcessController@ajaxList');
		Route::get('/processes/ajaxGetTovList/{procId}','ProcessController@ajaxGetTovList');
		Route::post('/processes/add',					'ProcessController@add');
		Route::post('/processes/ajaxAdd',				'ProcessController@ajaxAdd');
		
		Route::post('/processes/prepareDataFromFile',	'ProcessController@prepareDataFromFile');

		Route::get('/sys/getContragentsForAvtocomplete', 	'SystemController@ajaxGetContragentsAvtocomplete');
		Route::get('/sys/getContragentsErarhi',				'SystemController@ajaxGetContragentsErarhi');
		Route::get('/sys/getContragents',				 	'SystemController@ajaxGetContragents');

		Route::get('/sys/getTovsCategsErarhi', 				'SystemController@ajaxGetTovsCategsErarhi');
		Route::get('/sys/getTovsForCateg/{categId}', 		'SystemController@ajaxGetTovsForCateg');
		Route::get('/sys/getTovIdsForCategs', 				'SystemController@ajaxGetTovIdsForCategs');
		Route::get('/sys/getSubCategs/{categId}',			'SystemController@ajaxGetSubCategs');
		Route::get('/sys/getSubRegions/{regionId}',			'SystemController@ajaxGetSubRegions');
		Route::get('/sys/getShopsForRegion/{regionId}',		'SystemController@ajaxGetShopsForRegion');
		Route::get('/sys/getTovsToFillTable',				'SystemController@ajaxGetTovsToFillTable');


		Route::get('/sys/getShopsErarhi', 		'SystemController@ajaxGetShopsErarhi');
		Route::get('/sys/fillTovCategs', 		'SystemController@fillTovCategsTable');
		Route::get('/sys/fillRegionsTable', 	'SystemController@fillRegionsTable');

		Route::get('/test', 'TestController@index');
	});

	Route::get('/ucenka/list',						'UcenkaController@list')->name('ucenka.list');
	Route::get('/ucenka/add',						'UcenkaController@add')->name('ucenka.add');

	Route::get('/ucenka/ajaxJsonList',				'UcenkaController@ajaxJsonList');
	Route::get('/ucenka/edit/{appId}',				'UcenkaController@edit');
	Route::get('/ucenka/ajaxJsonEdit/{appId}',		'UcenkaController@ajaxJsonEdit');
	Route::post('/ucenka/ajaxAddSubmit',			'UcenkaController@ajaxAddSubmit');
	Route::post('/ucenka/ajaxJsonEditSubmit',		'UcenkaController@ajaxJsonEditSubmit');

	Route::get('/tovs/ajaxGetTovForAvtocomplete', 	'TovController@ajaxGetTovForAvtocomplete');
	Route::get('/tovs/ajaxGetTovarForAvtoComplete',	'TovController@ajaxGetTovarForAvtoComplete');

	Route::get('/shop/ajaxGetShops', 				'ShopController@ajaxGetShops');

	Route::get('/brends/getBrendsForAvtocomplete',			'BrendController@ajaxGetBrendsForAvtocomplete');
	Route::get('/brends/getBrendsForCategs/{categId}',		'BrendController@ajaxGetBrendsForCategs');

	Route::get('/avtodefectura',				'AvtodefecturaController@list')->name('avtodefectura.list');
	Route::get('/avtodefectura/ajaxList',		'AvtodefecturaController@ajaxList');


});
Auth::routes();