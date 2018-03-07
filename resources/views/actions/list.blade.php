@extends('layouts.app')

@section('content')
	<ul>
		@foreach ($actions as $action)

			<li>{{$action->id}} - {{$action->actionType->title}}</li>

		@endforeach
	</ul>

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
                    <input type="button" onclick="window.location.href='/actions/add'" name="" value="Создать">
                </div>
				<div id="table_data" class="table_data"></div>

                <!-- <table class="tbl">
                    <tr>
                        <th>
                            Название поля
                        </th>
                        <th>
                            Название поля
                        </th>
                        <th>
                            Название поля
                        </th>
                        <th>
                            Название поля
                        </th>
                        <th>
                            Название поля
                        </th>
                        <th>
                            Название поля
                        </th>
                        <th>
                            Название поля
                        </th>
                        <th>
                            sdfgdsfg
                        </th>
                        <th>
                            dfgsdfg
                        </th>
                        <th>sdfgdsfg
                            
                        </th>
                        <th>
                            dfgsdfg
                        </th>
                        <th>
                            dfsadf
                        </th>
                        <th>
                            dfsgsdfg
                        </th>
                        <th>
                            dfsgsdfg
                        </th>
                        <th>
                            dfsgsdfg
                        </th>
                        <th>
                            dfsgsdfg
                        </th>
                    </tr>
                    <tr>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input tabindex="1" class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input tabindex="2" class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input tabindex="3" class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">sdfsdf</div>
                            <input class="tbl__edit-val" type="text" name="" value="sfdsdfsdf">
                        </td>
                        <td>
                            <div class="tbl__show-val">sdfsdf</div>
                            <input class="tbl__edit-val" type="text" name="" value="sfdsdfsdf">
                        </td>

                        <td>
                            <div class="tbl__show-val">sdfsdf</div>
                            <input class="tbl__edit-val" type="text" name="" value="sfdsdfsdf">
                        </td>

                        <td>
                            <div class="tbl__show-val">sdfsdf</div>
                            <input class="tbl__edit-val" type="text" name="" value="sfdsdfsdf">
                        </td>
                        <td>
                            <div class="tbl__show-val">sdfsdf</div>
                            <input class="tbl__edit-val" type="text" name="" value="sfdsdfsdf">
                        </td>
                        <td>
                            <div class="tbl__show-val">sdfsdf</div>
                            <input class="tbl__edit-val" type="text" name="" value="sfdsdfsdf">
                        </td>
                        <td>
                            <div class="tbl__show-val">sdfsdf</div>
                            <input class="tbl__edit-val" type="text" name="" value="sfdsdfsdf">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input tabindex="1" class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input tabindex="2" class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input tabindex="3" class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">1</div>
                            <input class="tbl__edit-val" type="text" name="" value="1">
                        </td>
                        <td>
                            <div class="tbl__show-val">sdfsdf</div>
                            <input class="tbl__edit-val" type="text" name="" value="sfdsdfsdf">
                        </td>
                        <td>
                            <div class="tbl__show-val">sdfsdf</div>
                            <input class="tbl__edit-val" type="text" name="" value="sfdsdfsdf">
                        </td>

                        <td>
                            <div class="tbl__show-val">sdfsdf</div>
                            <input class="tbl__edit-val" type="text" name="" value="sfdsdfsdf">
                        </td>

                        <td>
                            <div class="tbl__show-val">sdfsdf</div>
                            <input class="tbl__edit-val" type="text" name="" value="sfdsdfsdf">
                        </td>
                        <td>
                            <div class="tbl__show-val">sdfsdf</div>
                            <input class="tbl__edit-val" type="text" name="" value="sfdsdfsdf">
                        </td>
                        <td>
                            <div class="tbl__show-val">sdfsdf</div>
                            <input class="tbl__edit-val" type="text" name="" value="sfdsdfsdf">
                        </td>
                        <td>
                            <div class="tbl__show-val">sdfsdf</div>
                            <input class="tbl__edit-val" type="text" name="" value="sfdsdfsdf">
                        </td>
                    </tr>
                </table> -->

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