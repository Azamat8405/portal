<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UcenkaAppTov extends Model
{
	public function ucenka_reason()
	{
		return $this->belongsTo('App\UcenkaReason');
	}

	public function getSrokGodnostyAttribute($value)
	{
		if(intval($value) == $value)
		{
			return date('d.m.Y', $value);
		}
		else
			return '';
	}
}