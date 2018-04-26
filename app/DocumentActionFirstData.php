<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentActionFirstData extends Model
{
	public function shop()
    {
		return $this->belongsTo('App\Shop');
	}

	public function processType()
    {
		return $this->belongsTo('App\ProcessType');
	}

	public function brend()
    {
		return $this->belongsTo('App\Brend');
	}

}
