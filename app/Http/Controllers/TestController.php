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
use App\User;
use Illuminate\Http\Request;
use App\ProcessType;



class TestController extends Controller
{
    public function index(Request $request)
    {


$str = 'Директор, Архангельск, Гайдара <dir_arh_gaid@dochkisinochki.ru>; Директор, Астрахань, Звёздная <dir_astr_zvezd@dochkisinochki.ru>; Директор, Астрахань, Минусинская <Dir_Astr_Minus@dochkisinochki.ru>; Директор, Балаково, Волжская <dir_bal_vol@dochkisinochki.ru>; Директор, Балашиха, Свердлова <dir_bal_sver@dochkisinochki.ru>; Директор, Балашиха, Советская <dir_bal_sov@dochkisinochki.ru>; Директор, Барнаул, Г.Исакова <Dir_Barn_Pop@dochkisinochki.ru>; Директор, Белгород, Богдана Хмельницкого <dir_bel_hmel@dochkisinochki.ru>; Директор, Белгород, Дубовое Щорса <dir_bel_shors@dochkisinochki.ru>; Директор, Благовещенск, 50 лет Октября <dir_blag_okt@dochkisinochki.ru>; Директор, Брянск, Объездная <Dir_Br_Ob@dochkisinochki.ru>; Директор, Владимир, Тракторная <dir_vlad_trak@dochkisinochki.ru>; Директор, Волгоград, Рабоче-Крестьянская <dir_volg_rab@dochkisinochki.ru>; Директор, Волжский, Профсоюзов <dir_vol_prof@dochkisinochki.ru>; Директор, Воронеж, Ленинский <dir_vrn_len@dochkisinochki.ru>; Директор, Воронеж, Победы <dir_vrn_pob@dochkisinochki.ru>; Директор, Дубна, Боголюбова <dir_dubn_bog@dochkisinochki.ru>; Директор, Екатеринбург, Блюхера <dir_ekat_bluh@dochkisinochki.ru>; Директор, Екатеринбург, Ясная <dir_ekat_yasn@dochkisinochki.ru>; Директор, Железнодорожный, Советская <dir_gel_sov@dochkisinochki.ru>; Директор, Жуковский, Мясищева <Dir_juk_myas@dochkisinochki.ru>; Директор, Зеленоград, Крюковская <dir_zel_kruk@dochkisinochki.ru>; Директор, Иваново, Куконковых <dir_iva_kuk@dochkisinochki.ru>; Директор, Ижевск, Широкий <dir_izh_shir@dochkisinochki.ru>; Директор, Иркутск, Либкнехта <Dir_Irk_Karlib@dochkisinochki.ru>; Директор, Иркутск, Октябрьской революции <Dir_Irk_Oktrev@dochkisinochki.ru>; Директор, Йошкар-Ола, Баумана <Dir_Josh_Bau@dochkisinochki.ru>; Директор, Казань, Ибрагимова <dir_kaz_ibr@dochkisinochki.ru>; Директор, Казань, Маршала Чуйкова <dir_kaz_chuyk@dochkisinochki.ru>; Директор, Казань, Фучика <dir_kaz_fuch@dochkisinochki.ru>; Директор, Калуга, Гагарина <Dir_Kal_Gag@dochkisinochki.ru>; Директор, Кемерово, Молодежный <Dir_Kem_Mol@dochkisinochki.ru>; Директор, Кемерово, Октябрьский <dir_kem_okt@dochkisinochki.ru>; Директор, Киров, Воровского <Dir_Kir_Voro@dochkisinochki.ru>; Директор, Клин, Советская <dir_klin_sov@dochkisinochki.ru>; Директор, Коломна, Восстания <dir_kol_vosst@dochkisinochki.ru>; Директор, Колпино, Октябрьская <dir_kolp_okt@dochkisinochki.ru>; Директор, Колпино, Трудящихся <dir_kolp_trud@dochkisinochki.ru>; Директор, Королев, Космонавтов <dir_kor_kosm@dochkisinochki.ru>; Директор, Красногорск, Ленина <dir_kras_len@dochkisinochki.ru>; Директор, Краснодар, Крылатая <dir_kras_kryl@dochkisinochki.ru>; Директор, Краснодар, Уральская <Dir_Kras_Ural@dochkisinochki.ru>; Директор, Краснокаменск, 5 микрорайон <Dir_Kr_5@dochkisinochki.ru>; Директор, Красноярск, им Газеты <dir_kras_gaz@dochkisinochki.ru>; Директор, Красноярск, Мате Залки <Dir_Kras_Zal@dochkisinochki.ru>; Директор, Курск, Карла Маркса <dir_kur_karl@dochkisinochki.ru>; Директор, Курск, Ленина <Dir_Kursk_Len@dochkisinochki.ru>; Директор, Липецк, Петра Смородина <dir_lip_psm@dochkisinochki.ru>; Директор, Люберцы, Комсомольский <dir_lub_koms@dochkisinochki.ru>; Директор, Магнитогорск, Карла Маркса <dir_mgn_karl@dochkisinochki.ru>; Директор, Москва, Авиаконструктора Микояна <dir_msk_hodyn@dochkisinochki.ru>; Директор, Москва, Алтуфьевское <dir_msk_altuf@dochkisinochki.ru>; Директор, Москва, Ангелов <dir_msk_angel@dochkisinochki.ru>; Директор, Москва, Бирюлевская <dir_msk_birul@dochkisinochki.ru>; Директор, Москва, Вавилова <dir_msk_vav@dochkisinochki.ru>; Директор, Москва, Веневская <dir_msk_ven@dochkisinochki.ru>; Директор, Москва, Вешняковская <dir_msk_vesh@dochkisinochki.ru>; Директор, Москва, Вильнюская <dir_msk_vil@dochkisinochki.ru>; Директор, Москва, Волгоградский <dir_msk_volg@dochkisinochki.ru>; Директор, Москва, Зеленый <dir_msk_zelen@dochkisinochki.ru>; Директор, Москва, Кастанаевская <dir_msk_kastan@dochkisinochki.ru>; Директор, Москва, Керченская <Dir_Msk_Kerch@dochkisinochki.ru>; Директор, Москва, Кировоградская Колумбус <dir_msk_kir13@dochkisinochki.ru>; Директор, Москва, 7-я Кожуховская <dir_msk_kozh@dochkisinochki.ru>; Директор, Москва, 7-я Кожуховская ЦК <dir_msk_kozh_ck@dochkisinochki.ru>; Директор, Москва, Ленинский <dir_msk_lenin@dochkisinochki.ru>; Директор, Москва, Лобненская <dir_msk_lobn@dochkisinochki.ru>; Директор, Москва, Люблинская <dir_msk_lub@dochkisinochki.ru>; Директор, Москва, Мячковский <Dir_Msk_Myach@dochkisinochki.ru>; Директор, Москва, Народного Ополчения <dir_msk_nopol@dochkisinochki.ru>; Директор, Москва, Новогиреевская <dir_msk_novogir@dochkisinochki.ru>; Директор, Москва, Новокуркино <dir_msk_novokur@dochkisinochki.ru>; Директор, Москва, Ореховый <dir_msk_oreh@dochkisinochki.ru>; Директор, Москва, Первомайская <dir_msk_perv@dochkisinochki.ru>; Директор, Москва, Пролетарский <Dir_Msk_Prolet@dochkisinochki.ru>; Директор, Москва, Профсоюзная <dir_msk_profs2@dochkisinochki.ru>; Директор, Москва, Раменки <dir_msk_ram@dochkisinochki.ru>; Директор, Москва, Самаркандский <dir_msk_samark@dochkisinochki.ru>; Директор, Москва, Свободы <dir_msk_svobod@dochkisinochki.ru>; Директор, Москва, Староватутинский <dir_msk_starovat@dochkisinochki.ru>; Директор, Москва, Туристская <dir_msk_tur@dochkisinochki.ru>; Директор, Москва, Уральская <dir_msk_uralsk@dochkisinochki.ru>; Директор, Москва, Щербаковская <dir_msk_sher@dochkisinochki.ru>; Директор, Москва, Юрловский <dir_msk_urlov@dochkisinochki.ru>; Директор, Мытищи, Шараповский <dir_mit_shar@dochkisinochki.ru>; Директор, Мытищи, Юбилейная <dir_mit_yub@dochkisinochki.ru>; Директор, Набережные Челны <Dir_NChel_Mir@dochkisinochki.ru>; Директор, Нижневартовск, Ленина <dir_nvar_len@dochkisinochki.ru>; Директор, Нижний Новгород, Белинского <dir_nnov_bel@dochkisinochki.ru>; Директор, Нижний Новгород, Коминтерна <dir_nnov_kom@dochkisinochki.ru>; Директор, Нижний Новгород, Родионова <dir_nnov_rod@dochkisinochki.ru>; Директор, Нижний Новгород, Советская <dir_nnov_sov@dochkisinochki.ru>; Директор, Новомосковск, Берёзовая <Dir_Novmsk_Ber@dochkisinochki.ru>; Директор, Новосибирск, К.Маркса <Dir_Nsk_Marks@dochkisinochki.ru>; Директор, Новосибирск, Красный <dir_nsk_kras@dochkisinochki.ru>; Директор, Ногинск, Интернационала <dir_nog_int@dochkisinochki.ru>; Директор, Одинцово, Чикина <dir_odin_chik@dochkisinochki.ru>; Директор, Омск, 70 лет октября <dir_omsk_70okt@dochkisinochki.ru>; Директор, Омск, Мира <Dir_Omsk_Mir@dochkisinochki.ru>; Директор, Орёл, Октябрьская <dir_orel_okt@dochkisinochki.ru>; Директор, Орехово-Зуево, Якова Флиера <dir_oreh_fli@dochkisinochki.ru>; Директор, Орск, Васнецова <dir_orsk_vas@dochkisinochki.ru>; Директор, П.-Камчатский, Лукашевского <dir_ppk_luk@dochkisinochki.ru>; Директор, Павловский Посад, Большая Покровская <dir_pavl_bpok@dochkisinochki.ru>; Директор, Пенза, Строителей <dir_pen_stroi@dochkisinochki.ru>; Директор, Пермь, Куйбышева <dir_perm_kuyb@dochkisinochki.ru>; Директор, Пермь, Светлогорская <dir_perm_svet@dochkisinochki.ru>; Директор, Пермь, Юрша <dir_perm_yur@dochkisinochki.ru>; Директор, Петрозаводск, Ленина <dir_pet_len@dochkisinochki.ru>; Директор, Подольск, Свердлова <dir_pod_sver@dochkisinochki.ru>; Директор, Реутов, Октября <Dir_Reutov_Okt@dochkisinochki.ru>; Директор, Ростов-на-Дону, Космонавтов <dir_rost_kosm@dochkisinochki.ru>; Директор, Ростов-на-Дону, Малиновского <dir_rost_malin@dochkisinochki.ru>; Директор, Ростов-на-Дону, Михаила Нагибина <dir_rost_korol@dochkisinochki.ru>; Директор, Рязань, Костычева <dir_ryaz_kost@dochkisinochki.ru>; Директор, Рязань, Московское <dir_ryaz_msk@dochkisinochki.ru>; Директор, Рязань, Новоселов <dir_ryaz_nov@dochkisinochki.ru>; Директор, Самара, Аэродромная <Dir_Sam_Aer@dochkisinochki.ru>; Директор, Самара, Кирова <dir_sam_kir@dochkisinochki.ru>; Директор, Самара, Красноармейская <dir_sam_kras@dochkisinochki.ru>; Директор, Самара, Московское <dir_sam_mos@dochkisinochki.ru>; Директор, Санкт-Петербург, Большеохтинский <dir_spb_boht@dochkisinochki.ru>; Директор, Санкт-Петербург, Брантовская <dir_spb_bran@dochkisinochki.ru>; Директор, Санкт-Петербург, Бутлерова <dir_spb_butl@dochkisinochki.ru>; Директор, Санкт-Петербург, Бухарестская <dir_spb_buh@dochkisinochki.ru>; Директор, Санкт-Петербург, Ильюшина <dir_spb_ilyush@dochkisinochki.ru>; Директор, Санкт-Петербург, Испытателей <dir_spb_isp@dochkisinochki.ru>; Директор, Санкт-Петербург, Космонавтов <dir_spb_kosm@dochkisinochki.ru>; Директор, Санкт-Петербург, Ленинский <dir_spb_len@dochkisinochki.ru>; Директор, Санкт-Петербург, Московский <dir_spb_msk@dochkisinochki.ru>; Директор, Санкт-Петербург, Одоевского <dir_spb_odoev@dochkisinochki.ru>; Директор, Санкт-Петербург, Славы <dir_spb_slav@dochkisinochki.ru>; Директор, Санкт-Петербург,Прибрежная <dir_spb_prib@dochkisinochki.ru>; Директор, Саратов, Астраханская <dir_sar_astr@dochkisinochki.ru>; Директор, Саратов, Верхняя <dir_sar_ver@dochkisinochki.ru>; Директор, Сергиев Посад, Вознесенская <Dir_Ser_Voz@dochkisinochki.ru>; Директор, Смоленск, 25 Сентября <dir_smol_sent@dochkisinochki.ru>; Директор, Смоленск, Новомосковская <dir_smol_novo@dochkisinochki.ru>; Директор, Сочи, Заря <dir_sochi_zarya@dochkisinochki.ru>; Директор, Сочи, Московская <dir_sochi_msk@dochkisinochki.ru>; Директор, Ставрополь, Тухачевского <Dir_Stav_Tuh@dochkisinochki.ru>; Директор, Старый Оскол, Ольминского <dir_osk_omu@dochkisinochki.ru>; Директор, Стерлитамак, Артема <dir_ster_art@dochkisinochki.ru>; Директор, Сургут, Профсоюзов <dir_sur_prof@dochkisinochki.ru>; Директор, Таганрог, Мира <dir_tag_mir@dochkisinochki.ru>; Директор, Тамбов, Чичерина <dir_tam_chich@dochkisinochki.ru>; Директор, Тверь, Калинина <dir_tver_kal@dochkisinochki.ru>; Директор, Тобольск, 6 микрорайон <Dir_Tob_6m@dochkisinochki.ru>; Директор, Тольятти, Южное <dir_tol_yuzh@dochkisinochki.ru>; Директор, Томск, Красноармейская <Dir_Tomsk_Kras@dochkisinochki.ru>; Директор, Томск, Мира <Dir_Tomsk_Mir@dochkisinochki.ru>; Директор, Тула, Пролетарская <Dir_Tul_Prol@dochkisinochki.ru>; Директор, Тула, Пузакова <dir_tul_puzak@dochkisinochki.ru>; Директор, Тюмень, 50 лет Октября <dir_tum_mgor@dochkisinochki.ru>; Директор, Тюмень, Герцена <dir_tum_gerz@dochkisinochki.ru>; Директор, Тюмень, Пермякова <dir_tum_perm@dochkisinochki.ru>; Директор, Улан-Удэ, Корабельная <dir_ulan_kor@dochkisinochki.ru>; Директор, Улан-Удэ, Намжилова <dir_ulan_nam@dochkisinochki.ru>; Директор, Ульяновск, Рябикова <dir_ulyan_ryab@dochkisinochki.ru>; Директор, Ульяновск, Ульяновский <dir_ulyan_ulyan@dochkisinochki.ru>; Директор, Уфа, Октябрьский <dir_ufa_okt@dochkisinochki.ru>; Директор, Хабаровск, Морозова <dir_hab_mor@dochkisinochki.ru>; Директор, Челябинск, Молдавская <Dir_Chel_Mold@dochkisinochki.ru>; Директор, Челябинск, Цвиллинга <Dir_Chel_Cvil@dochkisinochki.ru>; Директор, Чита, 6 мкрн. <Dir_Chit_Ves@dochkisinochki.ru>; Директор, Чита, Бабушкина <dir_chit_bab@dochkisinochki.ru>; Директор, Чита, Богомякова <Dir_Chit_Bog2@dochkisinochki.ru>; Директор, Чита, Красноярская <Dir_Chit_Kras@dochkisinochki.ru>; Директор, Чита, Промышленная <Dir_Chit_Prom@dochkisinochki.ru>; Директор, Электросталь, Журавлева <dir_elek_zhur@dochkisinochki.ru>; Директор, Южно-Сахалинск, Комсомольская <dir_sah_koms@dochkisinochki.ru>; Директор, Якутск, Ярославского <dir_yak_yar@dochkisinochki.ru>; Директор, Ярославль, Ленинградский <dir_yar_lenin@dochkisinochki.ru>; Директор, Ярославль, Московский <dir_yar_mosk@dochkisinochki.ru>';


$str = explode(';', $str);

foreach ($str as $key => &$value)
{
    $value = explode('<', str_replace('>', '', $value));

    if($value[1] == 'dir_arh_gaid@dochkisinochki.ru')
    {
        continue;
    }

    $u = new User();
    $u->name = $value[0];
    $u->email = $value[1];
    $u->password = bcrypt( $value[1] );

    $u->role = 'avtodefectura-read';
    $u->user_group_id = 1;
    $u->remember_token = '';

    $value[0] = str_replace(['Директор,', ',', 'ё'], ['', '', 'е'], $value[0]);

    // BookingDates::where('email', Input::get('email'))
    // ->orWhere('name', 'like', '%' . Input::get('name') . '%')->get();

    $sh = Shop::where('title', 'like', '%'.trim($value[0]).'%')->get();
    if($sh->count() == 1)
    {
        $u->shop_id = $sh[0]->id;
        $u->save();
        continue;
    }

    echo $value[0].'_';
    print_r($sh->count());
    echo '<br>';
}

// print_r($str);

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