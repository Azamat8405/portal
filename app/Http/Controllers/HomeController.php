<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //ЗАГЛУШКА пока не сделана главная страница портала
        $user = User::find(Auth::id());

        // Если пользователь КМ или директор магазина
        if($user->user_group_id == 5)
        {
            return redirect('/avtodefectura');
        }
        elseif($user->user_group_id == 4 || $user->user_group_id == 5)
        {
            return redirect('/ucenka/list');
        }
        elseif($user->user_group_id == 1)
        {
            return redirect('/processes');
        }
        return view('home');
    }
}