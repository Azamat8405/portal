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
		Route::post('/processes/add',					'ProcessController@add');
		Route::post('/processes/prepareDataFromFile',	'ProcessController@prepareDataFromFile');

		Route::get('/sys/getContragentsForAvtocomplete', 	'SystemController@ajaxGetContragentsAvtocomplete');
		Route::get('/sys/getContragentsErarhi',				'SystemController@ajaxGetContragentsErarhi');
		Route::get('/sys/getContragents',				 	'SystemController@ajaxGetContragents');

		Route::get('/sys/getTovarForAvtoComplete', 			'SystemController@ajaxGetTovarForAvtoComplete');
		Route::get('/sys/getTovsCategsErarhi', 				'SystemController@ajaxGetTovsCategsErarhi');
		Route::get('/sys/getTovsForCateg/{categId}', 		'SystemController@ajaxGetTovsForCateg');
		Route::get('/sys/getTovIdsForCategs', 				'SystemController@ajaxGetTovIdsForCategs');
		Route::get('/sys/getSubCategs/{categId}',			'SystemController@ajaxGetSubCategs');
		Route::get('/sys/getSubRegions/{regionId}',			'SystemController@ajaxGetSubRegions');
		Route::get('/sys/getShopsForRegion/{regionId}',		'SystemController@ajaxGetShopsForRegion');
		Route::get('/sys/getBrendsForCategs/{categId}',		'SystemController@ajaxGetBrendsForCategs');
		Route::get('/sys/getBrendsForAvtocomplete',			'SystemController@ajaxGetBrendsForAvtocomplete');
		Route::get('/sys/getTovsToFillTable',				'SystemController@ajaxGetTovsToFillTable');

		Route::get('/sys/getShops', 			'SystemController@ajaxGetShops');
		Route::get('/sys/getShopsErarhi', 		'SystemController@ajaxGetShopsErarhi');
		Route::get('/sys/fillTovCategs', 		'SystemController@fillTovCategsTable');
		// Route::get('/sys/fillTov2Categs', 		'SystemController@fillTov2CategsTable');
		Route::get('/sys/fillRegionsTable', 	'SystemController@fillRegionsTable');
	
		Route::get('/test', 'TestController@index');
	});

	Route::get('/ucenka/list',					'UcenkaController@list')->name('ucenka.list');
	Route::get('/ucenka/add',					'UcenkaController@add')->name('ucenka.add');
	Route::post('/ucenka/addSubmit',			'UcenkaController@addSubmit')->name('ucenka.addSubmit');

	Route::get('/tovs/ajaxGetTovForAvtocomplete', 	'TovController@ajaxGetTovForAvtocomplete');

});
Auth::routes();