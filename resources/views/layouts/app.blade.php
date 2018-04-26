<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <title>{{ config('app.name', 'Портал') }}</title>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/jquery-ui.min.change.css') }}" rel="stylesheet">
        <link href="{{ asset('css/panel.css') }}" rel="stylesheet">
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">

        @yield('addition_css')
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
                        @php
                        if(Gate::allows('processes-read') || Gate::allows('processes-create') || Gate::allows('admin'))
                        {
                        @endphp
                            <li><a href="{{ route('processes') }}">Акции</a></li>
                        @php
                        }
                        @endphp

                        @php
                        if(Gate::allows('ucenkaapp-read') || Gate::allows('ucenkaapp-create') || Gate::allows('admin'))
                        {
                        @endphp
                            <li><a href="{{ route('ucenka.list') }}">Уценка</a></li>
                        @php
                        }
                        @endphp

                        @php
                        if(Gate::allows('avtodefectura-read'))
                        {
                        @endphp
                            <li><a href="{{ route('avtodefectura.list') }}">Автодефектура</a></li>
                        @php
                        }
                        @endphp

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
        <script src="{{ asset('js/scripts.js') }}"></script>

        @yield('addition_js')
    </body>
</html>