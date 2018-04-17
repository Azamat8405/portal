<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
	public $timestamps = false;
    public static function prepareShopName($shop_name)
    {
		$pos = strpos($shop_name, '(');
		if($pos !== false)
		{
			$shop_name = trim(substr($shop_name, 0, strpos($shop_name, '(')));
		}
		return preg_replace('/[ \,\.\-]+/iu', ' ', trim($shop_name));
	}
}