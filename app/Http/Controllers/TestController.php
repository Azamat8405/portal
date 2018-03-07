<?php

namespace App\Http\Controllers;

use DB;

use Excel;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        $result = [];
        $tovs = DB::table('tov_categs')->get();
        if($tovs)
        {
            foreach ($tovs as $value)
            {
                $lvl1 = $lvl2 = $lvl3 = $lvl4 = '';
                if(trim($value->lvl1) != '')
                {
                    $lvl1 = md5($value->lvl1);
                    if(!isset($result[$lvl1]))
                        $result[$lvl1] = ['t' => $value->lvl1];
                }
                if(trim($value->lvl2) != '')
                {
                    $lvl2 = md5($value->lvl2);
                    if(!isset($result[$lvl1][$lvl2]))
                        $result[$lvl1][$lvl2] = ['t' => $value->lvl2];
                }
                if(trim($value->lvl3) != '')
                {
                    $lvl3 = md5($value->lvl3);
                    if(!isset($result[$lvl1][$lvl2][$lvl3]))
                        $result[$lvl1][$lvl2][$lvl3] = ['t' => $value->lvl3];
                }
                if(trim($value->lvl4) != '')
                {
                    $lvl4 = md5($value->lvl4);
                    if(!isset($result[$lvl1][$lvl2][$lvl3][$lvl4]))
                        $result[$lvl1][$lvl2][$lvl3][$lvl4] = ['t' => $value->lvl4];
                }

                if(!isset($result[$lvl1][$lvl2][$lvl3][$lvl4]['tovs']))
                {
                        $result[$lvl1][$lvl2][$lvl3][$lvl4]['tovs'] = [];
                }

                // $result[$lvl1][$lvl2][$lvl3][$lvl4]['tovs'][] = [
                //         'c' => $value->{'ArtCode'},
                //         'n' => $value->{'ArtName'},
                // ];
            }
        }

print_r($result);

exit();
        echo json_encode($result);
exit();

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
