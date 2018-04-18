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
		if(intval($value) == $value && $value > 0)
		{
			return date('d.m.Y', $value);
		}
		else
			return '';
	}
}