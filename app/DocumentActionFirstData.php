<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentActionFirstData extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

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