@extends('layouts.app')

@section('content')
<style type="text/css">
	.div_table{
		display:table;
	}
	.div_table .left, .div_table .right{
		display: table-cell;
	}
	.div_table .right{
		padding: 0 0 0 10px;
	}
</style>

    <div class="content-panel">
		<h2>Добавление акции</h2>
	</div>

	@if (Session::has('errors'))
		@foreach (Session::get('errors') as $messages)			
			@foreach ($messages as $message)
				<p>{!! $message !!}</p>
			@endforeach		
		@endforeach
	@endif

	@if (Session::has('ok'))
		<p>{!! Session::get('ok') !!}</p>
	@endif

	<form class="" action="{{ route('actions.add') }}" method="post" enctype="multipart/form-data">
		@csrf

	    <div class="form-field-input">
			<div class="div_table">
		        <div class="left">
		            <input id="start_date" type="input" class="" name="start_date" value="{{ old('start_date') }}">
		        </div>
		        <div class="right">
		            <label>Дата начала акции</label>
		        </div>
		    </div>
		</div>
	    <div class="form-field-input">
			<div class="div_table">
		        <div class="left">
		            <input id="end_date" type="input" class="" name="end_date" value="{{ old('end_date') }}">
		        </div>
		        <div class="right">
		            <label>Дата окончания акции</label>
		        </div>
		    </div>
		</div>

	    <div class="form-field-input">
			<div class="div_table">
		        <div class="left">
		            <input id="file" type="file" class="" name="file" value="{{ old('file') }}" autofocus accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">
		        </div>
		        <div class="right">
		            <label>Загрузите файл в формате xsl/xslx со <a style="text-decoration:underline;" href="">следующей структурой</a></label>
		        </div>
		    </div>
		</div>

		<div class="form-field-input">
			<input type="submit" value="Добавить">
	    </div>
	</form>
@endsection

@section('addition_js')
	@

	<script src="{{ asset('js/add_action_form.js') }}"></script>
@endsection