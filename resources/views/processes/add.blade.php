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

		input.maskProcent, input.maskPrice, input.maskDate{
			text-align: center;
		}
		textarea
		{
			border: 1px solid #ccc;
			background: #f5f5f5;
			border-radius:4px;
		}
		ul.tree{
			margin:5px;
		}
		ul.tree ul{
			margin:0px;
			display:none;
		}
		ul.tree li{
			padding:2px;
			padding: 2px 2px 2px 20px;
			background:url(/img/folder_plus.png) no-repeat 5px 5px;
		}
		ul.tree li.active
		{
			background:url(/img/folder_minus.png) no-repeat 5px 5px;
		}
		ul.tree li.no_icon{
			background:none;
		}
		ul.tree li.active > ul{
			display:block;
		}
		.file_hint{
			position: absolute;
			width: 400px;
			height:auto;
			max-height:200px;
			overflow-x:overlay;
			z-index:80;
			background:#fff;
			padding:7px;
			text-align:left;
			border:1px solid #c7c7c7;
			line-height:14px;
		}
		#tabs{
			margin-top:335px;
		}
	</style>

	<form class="addProcessForm" action="{{ route('processes.add') }}" method="post" enctype="multipart/form-data">
		@csrf
		<div class="content-panel">
			<div class="content-panel-block">
				<h2>Добавление акции</h2>
				<div class="form-fields-row">
					<div class="form-field-cell">
						<div class="form-field-input">
						    <div>
								@if(Session::has('errors.form.0.process_type'))
									<label class="error_input">Тип акции <sup>*</sup></label>
								@else
									<label>Тип акции <sup>*</sup></label>
								@endif
						    </div>
					    	<div>
					            <select name="process_type" id="process_type" class="select_chosen">
					            	<option value="0"> Не выбрано </option>
									@if($process_types)
										@foreach($process_types as $type)
											@if(old('process_type') == $type->id)
												<option data-dedlain="{{$type->dedlain}}" value="{{$type->id}}" selected="selected">{{$type->title}}</option>
											@else
												<option data-dedlain="{{$type->dedlain}}" value="{{$type->id}}">{{$type->title}}</option>
											@endif
										@endforeach
									@endif
					            </select>
						    </div>
						</div>
					</div>
					<div class="form-field-cell">
					    <div class="form-field-input">
					        <div>
					            <label>Наименование</label>
					        </div>
					        <div>
					            <input name="process_title" type="text" value="{{ old('process_title') }}">
					        </div>
						</div>
					</div>
					<div class="form-field-cell">
					    <div class="form-field-input">
					        <div>
					            @if(Session::has('errors.form.0.start_date'))
									<label class="error_input">Дата начала акции <sup>*</sup></label>
								@else
									<label>Дата начала акции <sup>*</sup></label>
								@endif
					        </div>
					        <div>
					            <input id="start_date" type="text" autocomplete="off" name="start_date" value="{{ old('start_date') }}">
					        </div>
						</div>
					</div>
					<div class="form-field-cell">
					    <div class="form-field-input">
						    <div>
					            @if(Session::has('errors.form.0.end_date'))
									<label class="error_input">Дата окончания акции <sup>*</sup></label>
								@else
									<label>Дата окончания акции <sup>*</sup></label>
								@endif
						    </div>
					    	<div>
								<input id="end_date" type="text" autocomplete="off" name="end_date" value="{{ old('end_date') }}">
						    </div>
						</div>
					</div>
				</div>
				<div class="content-panel-inputs">
					<input type="button" onclick="addEmptyRow();" value="Добавить строку">
					<input type="button" onclick="delRows();" value="Удалить строки">
					<input type="submit" value="Сохранить акцию">
				</div>
			</div>

			<div class="content-panel-block">
				<div class="form-fields-row">
					<div class="form-field-cell">
						<div class="form-field-input">
						    <div>
								<label>Товарная категория</label>
						    </div>
					    	<div>
					            <select id="tovCategory">
					            	<option value="0"> Не выбрано </option>
									@if($tov_categs_lvl1)
										@foreach($tov_categs_lvl1 as $categ)
											<option value="{{$categ->id}}">{{$categ->title}}</option>
										@endforeach
									@endif
					            </select>
						    </div>
						</div>
					</div>
					<div class="form-field-cell">
						<div class="form-field-input">
						    <div>
								<label>Товарная группа</label>
						    </div>
					    	<div>
					            <select id="tovGroup">
					            	<option value="0"> Не выбрано </option>
					            </select>
						    </div>
						</div>
					</div>
					<div class="form-field-cell">
						<div class="form-field-input">
						    <div>
								<label>Тип изделия</label>
						    </div>
					    	<div>
					            <select id="tovTipIsdeliya">
					            	<option value="0"> Не выбрано </option>
					            </select>
						    </div>
						</div>
					</div>
					<div class="form-field-cell">
						<div class="form-field-input">
						    <div>
								<label>Вид изделия</label>
						    </div>
					    	<div>
					            <select id="tovVidIsdeliya">
					            	<option value="0"> Не выбрано </option>
					            </select>
						    </div>
						</div>
					</div>
					<div class="form-field-cell">
						<div class="form-field-input">
						    <div>
								<label>Бренд</label>
						    </div>
					    	<div>
					            <select id="tovBrendSelect" >
					            	<option value="0"> Не выбрано </option>
					            </select>
						    </div>
						</div>
					</div>
				</div>

				<div class="form-fields-row">
					<div class="form-field-cell">
						<div class="form-field-input">
						    <div>
								<label>Дивизион</label>
						    </div>
					    	<div>
					            <select id="division">
					            	<option value="0"> Все </option>
									@if($shop_regions_lvl1)
										@foreach($shop_regions_lvl1 as $region)
											<option value="{{$region->id}}">{{$region->title}}</option>
										@endforeach
									@endif
					            </select>
						    </div>
						</div>
					</div>

					<div class="form-field-cell">
						<div class="form-field-input">
						    <div>
								<label>Область</label>
						    </div>
					    	<div>
					            <select id="oblast">
					            	<option value="0"> Все </option>
					            </select>
						    </div>
						</div>
					</div>
					<div class="form-field-cell">
						<div class="form-field-input">
						    <div>
								<label>Город</label>
						    </div>
					    	<div>
					            <select id="city">
					            	<option value="0"> Все </option>
					            </select>
						    </div>
						</div>
					</div>
					<div class="form-field-cell">
						<div class="form-field-input">
						    <div>
								<label>Магазин</label>
						    </div>
					    	<div>
					            <select id="shop">
					            	<option value="0"> Все </option>
					            </select>
						    </div>
						</div>
					</div>
					<div class="form-field-cell">
						<div class="form-field-input">
						    <div>
								<label>Магазины-исключения</label>
						    </div>
					    	<div>
		   						<div class="field_input_file" style="width:180px;">
									<input type="text" value="" class="shopsTitles" style="width:180px;" id="shopsIskluchTitle"/>
									<input type="hidden" value="" class="shops" id="shopsIskluch"/>
									<div class="file" data-type="getShopsErarhi">...</div>
								</div>
						    </div>
						</div>
					</div>
				</div>
				<div class="form-fields-row">
					<div class="form-field-cell">
						<div class="content-panel-inputs">
							<input type="button" id="fillTable" value="Заполнить/добавить товары и магазины">
						</div>
					</div>
				</div>
			</div>

			@if (Session::has('errors.form'))
				<div class="error_dialog_messages">
				@foreach (Session::get('errors.form') as $messages)
					@foreach ($messages as $message)
						<p>{!! $message !!}</p>
					@endforeach
				@endforeach
				</div>
			@endif
			@if (Session::has('errors.file'))
				<div class="error_dialog_messages">
				@foreach (Session::get('errors.file') as $key => $messages)
					В строке {{$key}}, в файле, найдены ошибки:<br>
					@foreach ($messages as $message)
						<p>{!! $message !!}</p>
					@endforeach
					<br>
				@endforeach
				</div>
			@endif
			@if (Session::has('ok'))
				<div class="success_dialog_messages">
					<p>{!! Session::get('ok') !!}</p>
				</div>
			@endif
		</div>

		<div id="tabs">
            <ul>
                <li><a href="#tabs-1">В ручную</a></li>
                <li><a href="#tabs-2">Из файла</a></li>
            </ul>
			<div id="tabs-1">
 
				 <div class="table_data_block">
					<div id="shops_dialog"></div>
					<div id="tovs_dialog"></div>
					<div id="contragent_dialog"></div>

					<div id="parentTableHeader">
						<div id="offset"></div>
						<div id="tableHeader"></div>
					</div>
					<table id="tableTovs">
						<thead>
							<tr>
							    <th width="20">
							    	<input type="checkbox" id="delAll">
							    </th>
							    <th>Товар <sup>*</sup></th>
							    <th>Код товара <sup>*</sup></th>
							    <th>Магазин <sup>*</sup></th>
							    <th>Дистрибьютор</th>
							    <th>Тип акции <sup>*</sup></th>
								<th>Размер скидки ON INVOICE (%)</th>
							    <th>Процент компенсации OFF INVOICE (%)</th>
							    <th>Итого скидка (%) <sup>*</sup></th>
							    <th>Старая розничная цена (руб)</th>
							    <th>Новая розничная цена (руб)</th>
							    <th>Старая закупочная скидка (руб)</th>
							    <th>Новая закупочная скидка (руб)</th>
							    <th>Дата начала скидки ON INVOICE</th>
							    <th>Дата окончания скидки ON INVOICE</th>
							    <th>Подписи, слоганы, расшифровки и пояснения к товарам в рекламе.</th>
							    <th>Пометки к товарам: Хит, Новинка, Суперцена, Выгода 0000 рублей...</th>
							</tr>
						</thead>
						</tbody>

							@if(old('kodTov'))
								@foreach(old('kodTov') as $k => $v)
									<tr>
										<td>
											<input type="checkbox" class="deleteRow">
										</td>
										<td>
											@if(Session::has('errors.form.'.$k.'.tovsTitles'))
												<div class="error_message">{{Session::get('errors.form.'.$k.'.tovsTitles')}}</div>
											@endif

											<input type="hidden" value="{{old('tovsTitles.'.$k)}}" class="chTitle"/>
											<input type="text" value="{{old('tovsTitles.'.$k)}}" name="tovsTitles[]" class="tovsTitles"  style="width:255px;" />
											<input type="hidden" class="row_number" value="{{$k}}">
										</td>
										<td>
											@if(Session::has('errors.form.'.$k.'.kodTov'))
												<div class="error_message">{{Session::get('errors.form.'.$k.'.kodTov')}}</div>
											@endif
											<input type="hidden" value="{{old('kodTov.'.$k)}}" class="chKod"/>
											<input type="text" name="kodTov[]" value="{{old('kodTov.'.$k)}}" class="kodTov"/>
										</td>
										<td>
											@if(Session::has('errors.form.'.$k.'.shops'))
												<div class="error_message">{{Session::get('errors.form.'.$k.'.shops')}}</div>
											@endif
					   						<div class="field_input_file">

												<input type="hidden" value="{{old('shopsTitles.'.$k)}}" class="chShop"/>
												<input type="text" name="shopsTitles[]" value="{{old('shopsTitles.'.$k)}}" class="shopsTitles"/>

												<input type="hidden" name="shops[]" value="{{old('shops.'.$k)}}" class="shops"/>
												<div class="file" data-type="getShopsErarhi">...</div>
											</div>
										</td>
										<td>
											@if(Session::has('errors.form.'.$k.'.distr'))
												<div class="error_message">{{Session::get('errors.form.'.$k.'.distr')}}</div>
											@endif
					   						<div class="field_input_file">
												<input type="hidden" class="chDistr" value=""/>
												<input type="text" name="distrTitles[]" class="distrTitles" value="{{old('distrTitles.'.$k)}}" />
												<input type="hidden" name="distr[]" class="distr" value="{{old('distr.'.$k)}}"/>
												<div class="file" data-type="getContagentsErarhi">...</div>
											</div>
										</td>
										<td>
											@if(Session::has('errors.form.'.$k.'.type'))
												<div class="error_message">{{Session::get('errors.form.'.$k.'.type')}}</div>
											@endif

											<select name="types[]" class="select" style="border:1px solid red;">
												<option value="0"> Не выбрано </option>
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
												class="maskProcent skidka_itogo" name="skidka_itogo[]">
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
											<input type="text" class="end_on_invoice_date maskDate" autocomplete="off" value="{{old('end_date_on_invoice.'.$k)}}"
												name="end_date_on_invoice[]">
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
										<input type="hidden" value="" class="chTitle"/>
										<input type="text" name="tovsTitles[]" style="width:255px;" class="tovsTitles"/>
										<input type="hidden" class="row_number" value="0">
									</td>
									<td>
										<input type="hidden" value="" class="chKod"/>
										<input type="text" name="kodTov[]" value="" class="kodTov"/>
									</td>
									<td>
				   						<div class="field_input_file">
											<input type="hidden" value="" class="chShop"/>
											<input type="text" name="shopsTitles[]" value="" class="shopsTitles"/>
											<input type="hidden" name="shops[]" value="" class="shops"/>
											<div class="file" data-type="getShopsErarhi">...</div>
										</div>
									</td>
									<td>
				   						<div class="field_input_file">
											<input type="hidden" class="chDistr" value=""/>
											<input type="text" name="distrTitles[]" class="distrTitles" value=""/>
											<input type="hidden" name="distr[]" class="distr" value=""/>
											<div class="file" data-type="getContagentsErarhi">...</div>
										</div>
									</td>
									<td>
										<select name="types[]" class="select">
											<option value="0"> Не выбрано </option>
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
										<input type="text" autocomplete="off" class="maskProcent skidka_itogo" name="skidka_itogo[]">
									</td>
									<td>
										<input type="text" class="maskPrice roznica_old" autocomplete="off" name="roznica_old[]">
									</td>
									<td>
										<input type="text" class="maskPrice roznica_new" autocomplete="off" name="roznica_new[]">
									</td>
									<td>
										<input type="text" autocomplete="off" class="maskPrice" name="zakup_old[]">
									</td>
									<td>
										<input type="text" autocomplete="off" class="maskPrice" name="zakup_new[]">
									</td>
									<td>
										<input type="text" class="start_on_invoice_date maskDate" autocomplete="off" name="start_date_on_invoice[]">
									</td>
									<td>
										<input type="text" class="end_on_invoice_date maskDate" autocomplete="off" name="end_date_on_invoice[]">
									</td>
									<td>
										<textarea name="descr[]"></textarea>
									</td>
									<td>
										<textarea name="marks[]"></textarea>
									</td>
								</tr>
							@endif


						</tbody>
					</table>
				</div>
            </div>
            <div id="tabs-2">
				<div class="form-field-input">
		 			<div class="div_table">
				        <div class="left">
				            <input id="file" type="file" class="" name="file" value="{{ old('file') }}" autofocus accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">
				        </div>
				        <div class="right">
				            <label>Загрузите файл в формате xsl/xslx со <a style="text-decoration:underline;" 
				            	href="/upload/action_upload_form.xlsx">следующей структурой</a>
				            </label>
				        </div>
				    </div>
				</div>
            </div>
		</div>
	</form>
@endsection

@section('addition_js')
	<script src="{{ asset('js/jquery.mask.min.js') }}"></script>
	<script src="{{ asset('js/select2.full.min.js') }}"></script>
	<script src="{{ asset('js/select2.full.min.ru.js') }}"></script>
	<script src="{{ asset('js/tableFixHeader.js') }}"></script>
	<script src="{{ asset('js/add_action_form.js') }}"></script>
@endsection

@section('addition_css')
	<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
	<link href="{{ asset('css/select2.change.css') }}" rel="stylesheet">
@endsection