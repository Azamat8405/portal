@extends('layouts.app')

@section('content')
    <div class="content-panel">
        <div class="content-panel-block">
            <h2>Список акций</h2>
            <div class="form-fields-row">
                <div class="form-field-cell">
                    <div class="form-field-input">
                        <input type="button" onclick="window.location.href='/processes/add'" name="" value="Добавить акцию">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content_body">
        <table id="jqGridList"><tr><td></td></tr></table> 
        <div id="jqGridpager"></div> 
    </div>

@endsection

@section('addition_js')
    <script src="{{ asset('js/jquery.jqGrid.min.js') }}"></script>
    <script src="{{ asset('js/grid.locale-ru.js') }}"></script>
    <script src="{{ asset('js/action_list.js') }}"></script>
    <script src="{{ asset('js/jquery.jqGrid.after.js') }}"></script>
@endsection

@section('addition_css')
    <link href="{{ asset('css/ui.jqgrid.css') }}" rel="stylesheet">
@endsection