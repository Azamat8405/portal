<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    /**
     * Get the phone record associated with the user.
     */
    public function actionType()
    {
		return $this->belongsTo('App\ActionType');
    }
}