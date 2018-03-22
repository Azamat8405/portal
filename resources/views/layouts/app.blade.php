<!DOCTYPE html>

{{--
1) Разделы номенклатуры. Откуда берем? из таблицы [Assortment_Hierarchy]?

    - что делаем с разделами указанными через слеш? это вложенные разделы? т.е. уровень вложенности может быть  более 4 уровней?
    - есть разделы в которых на всех 4 уровнях указано одно и тоже 
    (ПОДМЕННЫЙ ФОНД  ПОДМЕННЫЙ ФОНД  ПОДМЕННЫЙ ФОНД  ПОДМЕННЫЙ ФОНД)

    -IDArt - это уникальный ИД в 1с?

2) Сами товары номенклатуры? Где берем? Таблица?

3) Бренды из 1с? Какая таблица? Плоская таблица? Без иерархии?

4) Дистрибьютеры? Где берем?

5) 'код ДиС' - что это

6) 'Артикул ШК' - Это артикал товра? ШК это что?

7) Это всегда число? В процентах?

    on_invoice
    off_invoice
    skidka_itogo

    old_zakup_price - всегда рубли?
    new_zakup_price - всегда рубли?

--}}

<html lang="{{ app()->getLocale() }}">
    <head>
        <title>{{ config('app.name', 'Портал') }}</title>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Styles -->
        <link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/jquery-ui.min.change.css') }}" rel="stylesheet">

        @guest
        @else

{{--
            <link rel="stylesheet" type="text/css" href="{{ asset('css/handsontable.full.min.css') }}">
--}}
        @endguest

        @yield('addition_css')

        <link href="{{ asset('css/panel.css') }}" rel="stylesheet">
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    </head>
    <body>
        <div class="wrapper">
            <section class="header">
                <a href="/"><h1>ИНФОПОРТАЛ</h1></a>
                <ul class="auth">
                    @guest
                        <li><a href="{{ route('login') }}">Войти</a></li>
                        <li><a href="{{ route('register') }}">Регистрация</a></li>
                    @else

                        <li>
                            <a href="">{{ Auth::user()->name }}<i></i></a>
                            <ul>
                                <li><a href="">Аккаунт</a></li>
                                <li><a href="">Настройки</a></li>

                                <li><a href="{{ route('logout') }}" onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">Выход</a>
                                </li>
                            </ul>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    @endguest
                </ul>
            </section>
            @guest
                <style>
                    section.content
                    {
                        width: 100%;
                        margin: 50px 0 0 0%;
                    }
                </style>
            @else
                <nav>
                    <div class="handrail"><div></div></div>
                    <ul>
                        <li><a href="{{ route('processes') }}">Акции</a></li>
                        <li><a href="{{ route('ucenka.list') }}">Уценка</a></li>

<!--
                            <li><a href="">Участники</a>
                            <ul>
                                <li><a href="">Иванов И.И</a></li>
                                <li><a href="">Петров П.П.</a></li>
                                <li><a href="">Сидоров С.С</a></li>
                            </ul>
                        </li>
-->

                    </ul>
                </nav>
            @endguest

            <section class="content">
                @yield('content')
            </section>
        </div>

        <script src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>
        <script src="{{ asset('js/jquery.mousewheel.min.js') }}"></script>
        <script src="{{ asset('js/jquery-ui.min.js') }}"></script>

        @guest
        @else
{{--
            <script src="{{ asset('js/handsontable.full.min.js') }}"></script>
--}}
        @endguest

        @yield('addition_js')

        <script src="{{ asset('js/scripts.js') }}"></script>
    </body>
</html>