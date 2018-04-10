@extends('layouts.app')

@section('content')

<form class="addProcessForm" action="{{ route('ucenka.addSubmit') }}" onSubmit="return checkValues();" method="post" enctype="multipart/form-data">
		@csrf
		<div class="content-panel">
			<div class="content-panel-block">
				<h2>Добавление заявок на уценку</h2>

				<div class="form-fields-row">
					<div class="form-field-cell">
						<div class="form-field-input">
						    <div>
								<label>Магазин</label>
						    </div>
					    	<div>
					    		<select name="shop" id="shop" class="select_shop">
					            	<option value="0"> Не выбрано </option>

									@if($shops)
										@foreach($shops as $sh)

											@if(old('shop') == $sh->id)
												<option value="{{$sh->id}}" selected="selected">{{$sh->title}}</option>
											@else
												<option value="{{$sh->id}}">{{$sh->title}}</option>
											@endif

										@endforeach
									@endif
					            </select>
						    </div>
						</div>
					</div>
				</div>
				<div class="content-panel-inputs">
					<input type="submit" value="Подать заявку">

					<input type="button" onclick="delRows();" value="Удалить строки">
					<input type="button" onclick="addNewRow();" value="Добавить строку">
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
			<div class="table_data_block">
				<div id="parentTableHeader">
					<div id="offset"></div>
					<div id="tableHeader"></div>
				</div>
				<table id="tableTovs" class="table_data">
					<thead>
						<tr>
						    <th width="20">
								<input type="checkbox" id="delAll">
						    </th>
						    <th>Код номенклатуры</th>
						    <th>Наименование товара</th>
						    <th>Срок годности</th>
						    <th>Причина</th>
						    <th>Остаток</th>
						</tr>
					</thead>
					</tbody>
						@if(old('kodNomenkatur'))
							@foreach(old('kodNomenkatur') as $k => $v)
								<tr class="row_number" data-row-number="{{$k}}">
									<td>
										<input type="checkbox" class="deleteRow">
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.kodNomenkatur'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.kodNomenkatur')}}</div>
										@endif
										<input type="text" autocomplete="off" value="{{old('kodNomenkatur.'.$k)}}" name="kodNomenkatur[]"
											class="kodNomenkatur">
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.tovName'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.tovName')}}</div>
										@endif
										<input type="text" autocomplete="off" value="{{old('tovName.'.$k)}}" name="tovName[]" class="tovName">
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.srok_godnosti'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.srok_godnosti')}}</div>
										@endif
						            	<input id="srok_godnosti" type="text" class="date" autocomplete="off" name="srok_godnosti[]" value="{{ old('srok_godnosti.'.$k) }}">
									</td>

									<td>
										<select name="reasons[]" class="select">
											<option value="0"> Не выбрано </option>
											@foreach ($reasons as $reason)
												@if(old('reasons') == $reason->id)
													<option value="{{$reason->id}}" selected="selected">{{$reason->title}}</option>
												@else
													<option value="{{$reason->id}}">{{$reason->title}}</option>
												@endif

											@endforeach
										</select>
									</td>
									<td>
						            	<input id="ostatok" type="text" autocomplete="off" name="ostatok[]" value="{{ old('ostatok.'.$k) }}">
									</td>
								</tr>
							@endforeach
						@else
							<tr class="row_number" data-row-number="0">
								<td>
									<input type="checkbox" class="deleteRow">
								</td>
								<td>
									<input type="text" autocomplete="off" value="" name="kodNomenkatur[]" class="kodNomenkatur">
								</td>
								<td>
									<input type="text" autocomplete="off" value="" name="tovName[]" class="tovName">
								</td>
								<td>
					            	<input type="text" autocomplete="off" value="" name="srok_godnosti[]" class="date" id="srok_godnosti">
								</td>
								<td>
									<select name="reasons[]" class="select">
										<option value="0"> Не выбрано </option>
										@foreach ($reasons as $reason)
											<option value="{{$reason->id}}">{{$reason->title}}</option>
										@endforeach
									</select>

								</td>
								<td>
					            	<input type="text" autocomplete="off" value="" name="ostatok[]" id="ostatok">
								</td>
							</tr>
						@endif
					</tbody>
				</table>
			</div>
		</div>
	</form>
@endsection

@section('addition_js')
	<script src="{{ asset('js/select2.full.min.js') }}"></script>
	<script src="{{ asset('js/select2.full.min.ru.js') }}"></script>
	<script src="{{ asset('js/tableFixHeader.js') }}"></script>
	<script src="{{ asset('js/add_ucenka_form.js') }}"></script>
@endsection

@section('addition_css')
	<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
	<link href="{{ asset('css/select2.change.css') }}" rel="stylesheet">
@endsection