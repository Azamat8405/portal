<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {

debug_output('5555555777');

        })->everyMinute();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}


function debug_output($debug_var, $mode = 'w+', $file = '')
{
    if(empty($file))
    {
        $file = /*$_SERVER['DOCUMENT_ROOT'].*/'C:\WebDev\xampp\htdocs\portal.ru\portal\public\debug_output.txt';
    }

    $fp = @fopen($file, $mode);
    if ($fp)
    {
        // получаем значение переменной в виде строки
        if($mode == 'a+')
        {
            @fwrite($fp, "\r\n");
        }
        @fwrite($fp, print_r($debug_var, true));
        @fclose($fp);
    }
}