<?php

namespace App\Http\Controllers;

use Validator;
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

exit();

//         $validator = Validator::make(
//             [
//                 'proc_field' => '100%',
//                 'date2' => '31-12-2018',
//                 'date3' => '31-12-2018',
//                 'shops' => '111',
//             ],
//             [
//                 'proc_field' => 'procent:'.serialize([
//                         'max' => 100,
//                         'min' => 0
//                     ]),
//                 'date2' => 'date2:>'.mktime(0,0,0,1,1,2019),
//                 'date3' => 'date2:>'.mktime(0,0,0,1,1,2019),
//                 'shops' => 'shops_by_name',
//             ]
//         );

//         if($validator->fails())
//         {
//             echo 'err';
//         }
//         else
//         {
//             echo 'ok';
//         }

//         print_r( $validator->messages() );

// exit();

        $phpExcel = PHPExcel_IOFactory::createReader('Excel2007');
        $phpExcel = $phpExcel->load( public_path().'/upload/action_upload_form.xlsx' );
        $phpExcel->setActiveSheetIndex(2); // Делаем активной 3 лист
        $sheet = $phpExcel->getActiveSheet();

        if(strtolower($sheet->getTitle()) == 'список магазинов')
        {
            $sheet->setCellValue('B1', 'Магазины');
            // $sheet->setCellValue('B1', 'Контрагенты');

            $shops = Shop::orderBy('title')->get();
            $i = 1;
            foreach($shops as $key => $value)
            {
                $i++;
                $sheet->setCellValue('B'.$i, $value->title);
            }
            for($j = ++$i; $j <= 1000 ; $j++)
            {
                $sheet->setCellValue('B'.$j, '');
            }
        }

        $writer = PHPExcel_IOFactory::createWriter($phpExcel, "Excel2007");
        $writer->save(public_path().'/upload/action_upload_form.xlsx');

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