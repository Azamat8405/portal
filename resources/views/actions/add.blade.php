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
	#contragent_dialog{
		display:none;
	}
</style>

	<form class="" action="{{ route('actions.add') }}" method="post" enctype="multipart/form-data">
		@csrf

	    <div class="content-panel">
			<h2>Добавление акции</h2>

			<div class="form-fields-row">
				<div class="form-field-cell">
				    <div class="form-field-input">
				        <div>
				            <label>Дата начала акции</label>
				        </div>
				        <div>
				            <input id="start_date" type="input" name="start_date" value="{{ old('start_date') }}">
				        </div>
					</div>
				</div>
				<div class="form-field-cell">
				    <div class="form-field-input">
				        <div>
				            <label>Наименование</label>
				        </div>
				        <div>
				            <input name="name_action" type="text" value="{{ old('name_action') }}">
				        </div>
					</div>
				</div>
				<div class="form-field-cell">

					<div class="form-field-input">
					    <div>
					            <label>Тип акции</label>
					    </div>
				    	<div>
				            <select name="action_type" id="action_type">
				            	<option value="0"> --- </option>
				            	<option value="11">Газета</option>
				            </select>
					    </div>
					</div>

				</div>
			</div>
			<div class="form-fields-row">
				<div class="form-field-cell">
				    <div class="form-field-input">
					    <div>
					            <label>Дата окончания акции</label>
					    </div>
				    	<div>
				            <input id="end_date" type="input" name="end_date" value="{{ old('end_date') }}">
					    </div>
					</div>
				</div>

				<div class="form-field-cell">
				    <div class="form-field-input">
				        <div>
				            <label>Контрагент</label>
				        </div>
				        <div>
							<div class="field_input_file">
								<input id="contragent" type="hidden" name="contragent" value="{{ old('contragent') }}">
				            	<input id="contragent_title" type="input" name="contragent_title" value="{{ old('contragent_title') }}">
								<div id="contragent_dialog">TODO</div>
				            	<div class="file">...</div>
				            </div>
				        </div>
					</div>
				</div>
				<div class="form-field-cell"></div>
			</div>
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


<input type="button" id="save" class="table_data" value="OK">



		<div id="table_data" class="table_data"></div>

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

@section('addition_css')
	@

	<script src="{{ asset('js/add_action_form.js') }}"></script>
@endsection