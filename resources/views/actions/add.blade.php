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
	.table_data{
		width:100%;
		padding: 20px 0;
		font-size:80%;
	}
	.table_data td, .table_data th{
		border:1px solid #ccc;
		border-collapse:collapse;
	    padding: 2px 10px;
		vertical-align: middle;
	}
	#shops_dialog ul li, #tovs_dialog ul li{
		margin:5px;
	}
	#shops_dialog ul ul, #tovs_dialog ul ul{
		margin:0px 0px 0px 15px;
		display:none;
	}
	div.field_input_file, .field_input_file input{
		width:255px;
	}

	.table_data input[type=number]{
	    width: 145px;
	    text-align: center;
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
				            <select name="action_type" id="action_type"  class="select">
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
				            	<div class="file" data-type='getContagentsErarhi'>...</div>
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

		<div class="table_data">
			<div id="shops_dialog"></div>
			<div id="tovs_dialog"></div>

			<table>
				<tr>
				    <th>Товар</th>
				    <th>Магазин</th>
				    <th>Дистрибьютор</th>
				    <th>Тип акции</th>
				    <th>Размер скидки ON INVOICE</th>
				    <th>Процент компенсации ON INVOICE</th>
				    <th>Итого скидка</th>
				    <th>Старая закупочная скидка</th>
				    <th>Новая закупочная скидка</th>
				    <th>Дата начала скидки ON INVOICE</th>
				    <th>Дата окончания скидки ON INVOICE</th>
				    <th>Старая розничная цена</th>
				    <th>Новая розничная цена</th>
				    <th>Описание</th>
				    <th>Пометки</th>
				    <th>Кол-во</th>
				</tr>
				<tr>
					<td>
						<div class="field_input_file">
							<input type="input" class="tovs"/>
							<input type="hidden" name="tovs[]"/>
							<div class="file" data-type="getTovsErarhi">...</div>
						</div>
						<input type="hidden" class="row_number" value="0">
					</td>
					<td>

				<!--
 						<div class="field_input_file label">Магазин </div>
						<div class="field_input_file label">Магазин </div>
						<div class="field_input_file label">Магазин с длинным назывнием</div>
 				-->

   						<div class="field_input_file">
							<input type="input" class="shops"/>
							<input type="hidden" name="shops[]"/>
							<div class="file" data-type="getShopsErarhi">...</div>
						</div>
					</td>
					<td>
						<input class="" name="distr[]">
					</td>
					<td>
						<select name="types[]" class="select">
							<option value="0"> --- </option>
							<option value="1">2+2</option>
							<option value="2">3+2</option>
						</select>
					</td>
					<td>
						<input type="number" class="" name="skidka_on_invoice[]">
					</td>
					<td>
						<input type="number" class="" name="kompensaciya_on_invoice[]">
					</td>
					<td>
						<input type="number" class="" name="skidka_itogo[]">
					</td>
					<td>
						<input type="number" class="" name="zakup_old[]">
					</td>
					<td>
						<input type="number" class="" name="zakup_new[]">
					</td>
					<td>
						<input class="start_on_invoice_date" name="start_date_on_invoice[]">
					</td>
					<td>
						<input class="end_on_invoice_date" id="rrr" name="end_date_on_invoice[]">
					</td>
					<td>
						<input type="number" class="" name="roznica_old[]">
					</td>
					<td>
						<input type="number" class="" name="roznica_new[]">
					</td>
					<td>
						<textarea name="descr[]"></textarea>
					</td>
					<td>
						<input class="" name="marks[]">
					</td>
					<td>
						<input type="number" class="" name="kolvo[]">
					</td>
				</tr>
				<tr>
					<td>
						<div class="field_input_file">
							<input type="input" class="tovs"/>
							<input type="hidden" name="tovs[]"/>
							<div class="file">...</div>
						</div>
						<input type="hidden" class="row_number" value="1">
					</td>
					<td>
						<div class="field_input_file label">Магазин </div>
						<div class="field_input_file label">Магазин </div>
						<div class="field_input_file label">Магазин с длинным назывнием</div>

						<div class="field_input_file">
							<input type="input" class="shops"/>
							<input type="hidden" name="shops[]"/>
							<div class="file" data-type="getShopsErarhi">...</div>
						</div>
					</td>
					<td>
						<input class="" name="distr[]">
					</td>
					<td>
						<select name="types[]" class="select">
							<option value="0"> --- </option>
							<option value="1">2+2</option>
							<option value="2">3+2</option>
						</select>
					</td>
					<td>
						<input class="" name="skidka_on_invoice[]">
					</td>
					<td>
						<input class="" name="kompensaciya_on_invoice[]">
					</td>
					<td>
						<input class="" name="skidka_itogo[]">
					</td>
					<td>
						<input class="" name="zakup_old[]">
					</td>
					<td>
						<input class="" name="zakup_new[]">
					</td>
					<td>
						<input class="" name="start_date_on_invoice[]">
					</td>
					<td>
						<input class="" name="end_date_on_invoice[]">
					</td>
					<td>
						<input class="" name="roznica_old[]">
					</td>
					<td>
						<input class="" name="roznica_new[]">
					</td>
					<td>
						<input class="" name="descr[]">
					</td>
					<td>
						<input class="" name="marks[]">
					</td>
					<td>
						<input class="" name="kolvo[]">
					</td>
				</tr>
			</table>
		</div>

		<div class="form-field-input">
			<div class="div_table">
		        <div class="left">
		            <input id="file" type="file" class="" name="file" value="{{ old('file') }}" autofocus accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">
		        </div>
		        <div class="right">
		            <label>Загрузите файл в формате xsl/xslx со <a style="text-decoration:underline;" thef="">следующей структурой</a></label>
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