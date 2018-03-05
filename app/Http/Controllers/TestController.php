<?php

namespace App\Http\Controllers;

use DB;

use Excel;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {

        // print_r(\DateTime::createFromFormat('Y-m-d H:i:s', '2018-02-26 15:49:31'));
        // print_r(\DateTime::createFromFormat('Y-m-d H:i:s', '2018-02-26 15:49:31'));

        // ============================
        // $transport = new \Swift_SmtpTransport('ssl://smtp.yandex.ru', 465, "ssl");
        // $transport->setUsername('azamat8405@yandex.ru');
        // $transport->setPassword('hy65trewqaz');

        // $nam = 'Azam';
        // $from = 'azamat8405@yandex.ru';

        // $message = new \Swift_Message('$subject');

        // $message->setFrom(array($from => $nam));
        // $message->setTo(array('a_olisaev@detoc.ru'));
        // $message->setBody('Пирвет!');

        // $message->setContentType("text/html");

        // $mailer = new \Swift_Mailer($transport);
        // $result = $mailer->send($message);

        // ============================

        // DB::insert('insert into users_groups (title) values (?)', ['Main']);
        // DB::insert('insert into users (name, email, password, user_group_id) values (?, ?, ?, ?)', ['Azam Olis', 'a_olisaev@detoc.ru', bcrypt('tester'), 1]);
    }
}
