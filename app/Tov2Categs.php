<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tov2Categs extends Model
{
    protected $fillable = [
        'title', 'left', 'right', 'level'
    ];

	public function getDateFormat()
    {
        return 'Y-m-d\TH:i:s';
    }
}
