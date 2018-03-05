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

		Route::get('/actions', 'ActionController@list')->name('actions');
		Route::get('/actions/add', 'ActionController@showAddFrom')->name('actions.add');
		Route::post('/actions/add', 'ActionController@add');

		Route::get('/sys/getContragents', 'SystemController@ajaxGetContragents');
		Route::get('/sys/getTovars', 'SystemController@ajaxGetTovars');

		Route::get('/test', 'TestController@index');
	});
});
Auth::routes();