@extends('layouts.app')

@section('content')

    <form>
        <div class="content-panel">
            <div class="content-panel__info">
                <div>Название: Акция 3+2</div>
                <div>ID: 1257</div>
            </div>
        </div>

        <div id="tabs">
            <ul>
                <li><a href="#tabs-1">Данные</a></li>
                <li><a href="#tabs-2">Прогноз</a></li>
                <li><a href="#tabs-3">Заказ</a></li>
            </ul>
            <div id="tabs-1">

                <div class="panel">
                    <input type="submit" name="" value="Сохранить">
                    <input type="submit" name="" value="Изменить">
                    <input type="submit" name="" value="удалить">
                    <input type="submit" name="" value="Создать">
                </div>

            </div>
            <div id="tabs-2">
                tab 2
            </div>
            <div id="tabs-3">
                tab 3
            </div>
        </div>
    </form>

@endsection