<?php

namespace App\Http\Controllers;

use DB;
use Excel;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;
use PHPExcel_Writer_Excel5;
use App\Shop;
use Illuminate\Http\Request;
use App\ProcessType;

class TestController extends Controller
{
    public function index(Request $request)
    {
        $phpExcel = PHPExcel_IOFactory::createReader('Excel2007');
        $phpExcel = $phpExcel->load( public_path().'/upload/action_upload_form.xlsx' );
        $phpExcel->setActiveSheetIndex(1); // Делаем активной 2 страницу
        $sheet = $phpExcel->getActiveSheet();

        if(strtolower($sheet->getTitle()) == 'data')
        {
            $sheet->setCellValue('A1', 'Магазины');
            $sheet->setCellValue('B1', 'Контрагенты');

            $shops = Shop::all();
            $i = 1;
            foreach($shops as $key => $value)
            {
                $i++;
                $sheet->setCellValue('A'.$i, $value->title);
            }
            for($j = ++$i; $j <= 1000 ; $j++)
            {
                $sheet->setCellValue('A'.$j, '');
            }
        }

        $writer = PHPExcel_IOFactory::createWriter($phpExcel, "Excel2007");
        $writer->save(public_path().'/upload/action_upload_form.xlsx');

exit();


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
