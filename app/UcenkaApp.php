<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UcenkaApp extends Model
{
	public function app_tovs()
	{
		return $this->hasMany('App\UcenkaAppTov');
	}

	public function shop()
	{
		return $this->belongsTo('App\Shop');
	}
}