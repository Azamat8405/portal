@extends('layouts.app')

@section('content')

<form class="addProcessForm" action="{{ route('processes.add') }}" method="post" enctype="multipart/form-data">
		@csrf
		<div class="content-panel-fon"></div>
		<div class="content-panel">
			<div class="content-panel-block">
				<h2>Список заявок на уценку</h2>

				<div class="form-fields-row">
					<div class="form-field-cell">
						<div class="form-field-input">
						    <div>
								<label>Магазин</label>
						    </div>
					    	<div>
					    		<select name="shop" id="shop">
					            	<option value="0"> --- </option>

									@if(isset($shops))
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
					<a class="button" href="{{route('ucenka.add')}}">Добавить заявку</a>				
					<input type="button" onclick="addRow();" value="Добавить строку">
				</div>
			</div>

			@if (Session::has('errors.form'))
				<div class="err_dialog_messages">
				@foreach (Session::get('errors.form') as $messages)
					@foreach ($messages as $message)
						<p>{!! $message !!}</p>
					@endforeach
				@endforeach
				</div>
			@endif
			@if (Session::has('errors.file'))
				<div class="err_dialog_messages">
				@foreach (Session::get('errors.file') as $messages)
					@foreach ($messages as $message)
						<p>{!! $message !!}</p>
					@endforeach
				@endforeach
				</div>
			@endif
			@if (Session::has('ok'))
				<div class="ok_dialog_messages">
					<p>{!! Session::get('ok') !!}</p>
				</div>
			@endif
		</div>

		<div class="table_data">
			<div id="shops_dialog"></div>
			<div id="tovs_dialog"></div>
			<div id="contragent_dialog"></div>

			<div id="tableHeader"></div>
			<table id="tableTovs">
				<thead>
					<tr>
					    <th width="20"></th>
					    <th>Товар</th>
					    <th>Магазин</th>
					    <th>Дистрибьютор</th>
					    <th>Тип акции</th>
					    <th>Размер скидки ON INVOICE (%)</th>
					    <th>Процент компенсации OFF INVOICE (%)</th>
					    <th>Итого скидка (%)</th>
					    <th>Старая закупочная скидка (руб)</th>
					    <th>Новая закупочная скидка (руб)</th>
					    <th>Дата начала скидки ON INVOICE</th>
					    <th>Дата окончания скидки ON INVOICE</th>
					    <th>Старая розничная цена (руб)</th>
					    <th>Новая розничная цена (руб)</th>
					    <th>Подписи, слоганы, расшифровки и пояснения к товарам в рекламе.</th>
					    <th>Пометки к товарам: Хит, Новинка, Суперцена, Выгода 0000 рублей...</th>
					</tr>
				</thead>
				</tbody>

{{--


					@if(old('tovs'))
						@foreach(old('tovs') as $k => $v)
							<tr>
								<td>
									<input type="checkbox" class="deleteRow">
								</td>
								<td>
									@if(Session::has('errors.form.'.$k.'.ArtCode'))
										<div class="error_message">{{Session::get('errors.form.'.$k.'.ArtCode')}}</div>
									@endif
									<div class="field_input_file">
										<input type="input" value="{{old('tovsTitles.'.$k)}}" name="tovsTitles[]" class="tovsTitles"/>
		<!-- 								<input type="hidden" name="catsTovs[]" value=""/> -->
										<input type="hidden" value="{{$v}}" name="tovs[]" value="" class="tovs"/>
		<!-- 								<div class="file" data-type="getTovsErarhi">...</div> -->
									</div>
									<input type="hidden" class="row_number" value="{{$k}}">
								</td>
								<td>
									@if(Session::has('errors.form.'.$k.'.shops'))
										<div class="error_message">{{Session::get('errors.form.'.$k.'.shops')}}</div>
									@endif
			   						<div class="field_input_file">
										<input type="input" name="shopsTitles[]" value="{{old('shopsTitles.'.$k)}}" class="shops"/>
										<input type="hidden" name="shops[]" value="{{old('shops.'.$k)}}"/>
									</div>
								</td>
								<td>
									@if(Session::has('errors.form.'.$k.'.distr'))
										<div class="error_message">{{Session::get('errors.form.'.$k.'.distr')}}</div>
									@endif
			   						<div class="field_input_file">
										<input type="input" name="distrTitles[]" class="distrTitles" value="{{old('distrTitles.'.$k)}}" />
										<input type="hidden" name="distr[]" class="distr" value="{{old('distr.'.$k)}}"/>
										<div class="file" data-type="getContagentsErarhi">...</div>
									</div>
								</td>
								<td>
									@if(Session::has('errors.form.'.$k.'.type'))
										<div class="error_message">{{Session::get('errors.form.'.$k.'.type')}}</div>
									@endif

									<select name="types[]" class="select" style="border:1px solid red;">
										<option value="0"> --- </option>
										@foreach ($action_types as $type)

											@if(old('types.'.$k) == $type->id)
												<option data-descr="{{$type->description}}" value="{{$type->id}}" selected="selected">{{$type->title}}</option>
											@else
												<option data-descr="{{$type->description}}" value="{{$type->id}}">{{$type->title}}</option>
											@endif
										@endforeach
									</select>
								</td>
								<td>
									@if(Session::has('errors.form.'.$k.'.skidka_on_invoice'))
										<div class="error_message">{{Session::get('errors.form.'.$k.'.skidka_on_invoice')}}</div>
									@endif
									<input type="text" autocomplete="off" value="{{old('skidka_on_invoice.'.$k)}}"
										class="maskProcent on_invoice" name="skidka_on_invoice[]">
								</td>
								<td>
									@if(Session::has('errors.form.'.$k.'.kompensaciya_off_invoice'))
										<div class="error_message">{{Session::get('errors.form.'.$k.'.kompensaciya_off_invoice')}}</div>
									@endif
									<input type="text" autocomplete="off" value="{{old('kompensaciya_off_invoice.'.$k)}}"
										class="maskProcent off_invoice" name="kompensaciya_off_invoice[]">
								</td>
								<td>
									@if(Session::has('errors.form.'.$k.'.skidka_itogo'))
										<div class="error_message">{{Session::get('errors.form.'.$k.'.skidka_itogo')}}</div>
									@endif
									<input type="text" autocomplete="off" value="{{old('skidka_itogo.'.$k)}}"
										disabled="disabled" class="maskProcent skidka_itogo" name="skidka_itogo[]">
								</td>
								<td>
									@if(Session::has('errors.form.'.$k.'.zakup_old'))
										<div class="error_message">{{Session::get('errors.form.'.$k.'.zakup_old')}}</div>
									@endif
									<input type="text" autocomplete="off" value="{{old('zakup_old.'.$k)}}"
										class="maskPrice" name="zakup_old[]">
								</td>
								<td>
									@if(Session::has('errors.form.'.$k.'.zakup_new'))
										<div class="error_message">{{Session::get('errors.form.'.$k.'.zakup_new')}}</div>
									@endif
									<input type="text" autocomplete="off" value="{{old('zakup_new.'.$k)}}"
										class="maskPrice" name="zakup_new[]">
								</td>
								<td>
									@if(Session::has('errors.form.'.$k.'.start_date_on_invoice'))
										<div class="error_message">{{Session::get('errors.form.'.$k.'.start_date_on_invoice')}}</div>
									@endif
									<input class="start_on_invoice_date maskDate" autocomplete="off" value="{{old('start_date_on_invoice.'.$k)}}"
										name="start_date_on_invoice[]">
								</td>
								<td>
									@if(Session::has('errors.form.'.$k.'.end_date_on_invoice'))
										<div class="error_message">{{Session::get('errors.form.'.$k.'.end_date_on_invoice')}}</div>
									@endif
									<input class="end_on_invoice_date maskDate" autocomplete="off" value="{{old('end_date_on_invoice.'.$k)}}"
										name="end_date_on_invoice[]">
								</td>
								<td>
									@if(Session::has('errors.form.'.$k.'.roznica_old'))
										<div class="error_message">{{Session::get('errors.form.'.$k.'.roznica_old')}}</div>
									@endif
									<input type="text" class="maskPrice roznica_old" autocomplete="off" value="{{old('roznica_old.'.$k)}}"
										name="roznica_old[]">
								</td>
								<td>
									@if(Session::has('errors.form.'.$k.'.roznica_new'))
										<div class="error_message">{{Session::get('errors.form.'.$k.'.roznica_new')}}</div>
									@endif
									<input type="text" class="maskPrice roznica_new" autocomplete="off" value="{{old('roznica_new.'.$k)}}"
										name="roznica_new[]">
								</td>
								<td>
									<textarea name="descr[]">{{old('descr.'.$k)}}</textarea>
								</td>
								<td>
									<textarea name="marks[]">{{old('marks.'.$k)}}</textarea>
								</td>
							</tr>

						@endforeach
					@else
						<tr>
							<td>
								<input type="checkbox" class="deleteRow">
							</td>
							<td>
								<div class="field_input_file">
									<input type="input" name="tovsTitles[]" class="tovsTitles"/>
									<input type="hidden" name="tovs[]" value="" class="tovs"/>

<!-- 								<div class="file" data-type="getTovsErarhi">...</div> -->

								</div>
								<input type="hidden" class="row_number" value="0">
							</td>
							<td>
		   						<div class="field_input_file">
									<input type="input" name="shopsTitles[]" value="" class="shops"/>
									<input type="hidden" name="shops[]" value=""/>
									<!-- <div class="file" data-type="getShopsErarhi">...</div> -->
								</div>
							</td>
							<td>
		   						<div class="field_input_file">
									<input type="input" name="distrTitles[]" class="distrTitles" value=""/>
									<input type="hidden" name="distr[]" class="distr" value=""/>
									<div class="file" data-type="getContagentsErarhi">...</div>
								</div>
							</td>
							<td>
								<select name="types[]" class="select">
									<option value="0"> --- </option>
									@foreach ($action_types as $type)
										<option data-descr="{{$type->description}}" value="{{$type->id}}">{{$type->title}}</option>
									@endforeach
								</select>
							</td>
							<td>
								<input type="text" autocomplete="off" class="maskProcent on_invoice" name="skidka_on_invoice[]">
							</td>
							<td>
								<input type="text" autocomplete="off" class="maskProcent off_invoice" name="kompensaciya_off_invoice[]">
							</td>
							<td>
								<input type="text" autocomplete="off" disabled="disabled" class="maskProcent skidka_itogo" name="skidka_itogo[]">
							</td>
							<td>
								<input type="text" autocomplete="off" class="maskPrice" name="zakup_old[]">
							</td>
							<td>
								<input type="text" autocomplete="off" class="maskPrice" name="zakup_new[]">
							</td>
							<td>
								<input class="start_on_invoice_date maskDate" autocomplete="off" name="start_date_on_invoice[]">
							</td>
							<td>
								<input class="end_on_invoice_date maskDate" autocomplete="off" name="end_date_on_invoice[]">
							</td>
							<td>
								<input type="text" class="maskPrice roznica_old" autocomplete="off" name="roznica_old[]">
							</td>
							<td>
								<input type="text" class="maskPrice roznica_new" autocomplete="off" name="roznica_new[]">
							</td>
							<td>
								<textarea name="descr[]"></textarea>
							</td>
							<td>
								<textarea name="marks[]"></textarea>
							</td>
						</tr>
					@endif

--}}

				</tbody>
			</table>
		</div>
	</form>

@endsection