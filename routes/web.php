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

		Route::get('/actions', 			'ActionController@list')->name('actions');
		Route::get('/actions/add', 		'ActionController@showAddFrom')->name('actions.add');
		Route::post('/actions/add', 	'ActionController@add');

		Route::get('/sys/getContragentsForAvtocomplete', 	'SystemController@ajaxGetContragentsAvtocomplete');
		Route::get('/sys/getContragentsErarhi',				'SystemController@ajaxGetContragentsErarhi');
		Route::get('/sys/getContragents',				 	'SystemController@ajaxGetContragents');

		Route::get('/sys/getTovarForAvtoComplete', 		'SystemController@ajaxGetTovarForAvtoComplete');
		Route::get('/sys/getTovsCategsErarhi', 			'SystemController@ajaxGetTovsCategsErarhi');
		Route::get('/sys/getTovsForCateg/{categId}', 	'SystemController@ajaxGetTovsForCateg');



		Route::get('/sys/getShops', 			'SystemController@ajaxGetShops');
		Route::get('/sys/getShopsErarhi', 		'SystemController@ajaxGetShopsErarhi');
		Route::get('/sys/fillTovCategs', 		'SystemController@fillTovCategsTable');
		Route::get('/sys/fillTov2Categs', 		'SystemController@fillTov2CategsTable');

		Route::get('/test', 'TestController@index');
	});
});
Auth::routes();