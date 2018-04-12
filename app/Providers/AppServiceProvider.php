<?php

namespace App\Providers;

use Validator;
use Illuminate\Support\ServiceProvider;
use App\Shop;

class AppServiceProvider extends ServiceProvider
{
    private $cache_shops = [];

    private function fill_cache_shops()
    {
        $tmp = Shop::orderBy('title')->get();
        foreach ($tmp as $key => $value)
        {
            $value->title = Shop::prepareShopName($value->title);
            if(!isset($this->cache_shops[$value->code]))
            {
                $this->cache_shops[$value->code] = ['code' => $value->code, 'title' => $value->title, 'id' => $value->id];
            }
        }
    }


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        Validator::extend('shops_by_name', function($attribute, $value, $parameters){

            // Проверяем список магазинов,
            // Кешируем список магазинов ($this->cache_shops), чтоб каждый раз за ними не ходить.
            // if(empty($this->cache_shops))
            //     $this->fill_cache_shops();

            // if($value != '')
            // {
            //     $shops = explode(';', $value);

            //     $tmp = [];
            //     foreach ($shops as $val)
            //     {
            //         $exist = false;
            //         foreach ($this->cache_shops as $v)
            //         {
            //             if(in_array(Shop::prepareShopName($val), $v))
            //             {
            //                 $tmp[] = $v['id'];
            //                 $exist = true;
            //                 break;
            //             }
            //         }

            //         if(!$exist)
            //         {
            //             $this->validate_errors[$source][$row_num]['shops'] = 'Указанный магазин не найден "'.$value.'"';
            //         }
            //     }
            // }

            return false;
        });

        // $parameters[0] - состоит из двух частей.
        // 1 - знак сравнения ">", "<", ">=", "<="
        // 2 - время в timestamp с которым нужно сравнить текущее время
        //  пример 'date2' => 'date2:>='.mktime(0,0,0,1,1,2019)
        Validator::extend('date2', function($attribute, $value, $parameters){

            $value2 = $parameters[0] ?? false;

            $value = str_replace([',', '.'], ['-', '-'], $value);
            $valid = (bool) preg_match("/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $value);
            if($valid)
            {
                $value = explode('-', $value);
                if(count($value) != 3)
                {
                    return false;
                }

                if($value[0] > 31 || $value[1] > 12)
                {
                    return false;
                }
                $value = implode('-', $value);

                // если передан параметр
                if($value2 != '')
                {
                    $value2 = trim($value2);
                    $tmp = str_split($value2);

                    $znak = '';
                    foreach ($tmp as $k => $simbol)
                    {
                        //если знак не число значит это знак сравнения
                        if((bool)preg_match("/[^0-9]/", $simbol))
                        {
                            $znak .= $simbol;
                            unset($tmp[$k]);
                        }
                        else//если число значит пошел timestamp прерываем цикл
                        {
                            break;
                        }
                    }
                    $value2 = implode('', $tmp);

                    $value = strtotime($value);
                    $result = eval("return ($value $znak $value2);");
                    if(!$result)
                    {
                        return false;
                    }
                }
                return true;
            }
            return false;
        });


        Validator::extend('procent', function($attribute, $value, $parameters){

            if($parameters[0] != '')
            {
                $parameters[0] = unserialize($parameters[0]);
            }

            $min = $parameters[0]['min'] ?? 0;
            $max = $parameters[0]['max'] ?? 100;

            //убираем занк процента и пробелы. они допустимы в значении
            $value = preg_replace('/[\% ]/', '', $value);

            if(trim($value) == '')
            {
                return false;
            }
            //меняе запятые на точки. запятые допустиы в значении
            $value = str_replace(',', '.', $value);

            $valid = !(bool) preg_match("/[^\.0-9]+/", $value);
            if($valid)
            {
                if(floatval($value) <= $max && floatval($value) >= $min)
                {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
