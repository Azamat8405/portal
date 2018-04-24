<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    public function processType()
    {
		return $this->belongsTo('App\ProcessType');
    }

	public function processTovs()
    {
		return $this->hasMany('App\DocumentActionFirstData');
    }

	public function user()
    {
		return $this->belongsTo('App\User');
	}

	public function getStartDateAttribute($value)
    {
		return date('d.m.Y', $value);
	}

	public function getEndDateAttribute($value)
    {
		return date('d.m.Y', $value);
	}


}