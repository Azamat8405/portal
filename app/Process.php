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
    	if($value > 0)
    	{
			return date('d.m.Y', $value);
    	}
		return '';
	}

	public function getEndDateAttribute($value)
    {
    	if($value > 0)
    	{
			return date('d.m.Y', $value);
    	}
    	return '';
	}
}