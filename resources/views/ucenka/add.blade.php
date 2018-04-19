@extends('layouts.app')

@section('content')
	<form action="" onSubmit="return checkValues();" method="post" enctype="multipart/form-data" clas="form">
		@csrf
		<div class="content-panel">
			<div class="content-panel-block">
				<h2>Добавление заявок на уценку</h2>
				<div class="content-panel-inputs">
					<input type="submit" id="save" value="Подать заявку">
					<input type="button" onclick="delJqGridRows();" value="Удалить строки">
					<input type="button" onclick="addJqGridRow();" value="Добавить строку">
				</div>
			</div>

			@if (Session::has('errors'))
				<div class="error_dialog_messages">
				@foreach (Session::get('errors') as $message)
					<p>{!! $message !!}</p>
				@endforeach
				</div>
			@endif
			@if (Session::has('ok'))
				<div class="success_dialog_messages">
					<p>{!! Session::get('ok') !!}</p>
				</div>
			@endif
		</div>

	    <div class="content_body">
	    	<script>
	    		var reasonVariants = '{{$reasonVariants}}';
	    	</script>
	        <table id="jqGridAdd"><tr><td></td></tr></table> 
	        <div id="jqGridAddPager"></div> 
		</div>
	</form>
@endsection

@section('addition_js')
	<script src="{{ asset('js/select2.full.min.js') }}"></script>
	<script src="{{ asset('js/select2.full.min.ru.js') }}"></script>
	<script src="{{ asset('js/jquery.jqGrid.min.js') }}"></script>
	<script src="{{ asset('js/grid.locale-ru.js') }}"></script>
	<script src="{{ asset('js/ucenka.js') }}"></script>
    <script src="{{ asset('js/jquery.jqGrid.after.js') }}"></script>

@endsection

@section('addition_css')
	<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
	<link href="{{ asset('css/select2.change.css') }}" rel="stylesheet">

	<link href="{{ asset('css/ui.jqgrid.css') }}" rel="stylesheet">
	<link href="{{ asset('css/ui.jqgrid.change.css') }}" rel="stylesheet">
@endsection