
@extends('layouts.app')

@section('content')

	<style>
		.form-field-cell {
		    width:20%;
		}
	</style>

	<script>
		var prId = {{$process->id}};
	</script>

	<form class="addProcessForm" action="{{ route('processes.add') }}" method="post" enctype="multipart/form-data">
		@csrf
		<div class="content-panel">
			<div class="content-panel-block">
				<h2>Редактирование акции</h2>

				<div class="form-fields-row">
					<div class="form-field-cell">
					    <div class="form-field-input">
					        <div>
					            <label>Наименование</label>: "{{$process->title}}"
					        </div>
						</div>
					</div>
					<div class="form-field-cell">
					    <div class="form-field-input">
					        <div>
					            <label>Дата начала акции</label>: "{{$process->start_date}}"
							</div>
						</div>
					</div>
				</div>
				<div class="form-fields-row">
					<div class="form-field-cell">
						<div class="form-field-input">
						    <div>
								<label>Тип</label>: "{{$process->processType->title}}"
						    </div>
						</div>
					</div>
					<div class="form-field-cell">
					    <div class="form-field-input">
					        <div>
					            <label>Дата окончания акции</label>: "{{$process->end_date}}"
							</div>
						</div>
					</div>
				</div>

				<div class="content-panel-inputs">
					<input type="submit" value="Сохранить акцию">
					<input type="button" onclick="addEmptyRow();" value="Добавить строку">
					<input type="button" onclick="delRows();" value="Удалить строки">
					<input type="button" onclick="showPanel('#fillTablePanel');" value="Заполнить/добавить товары">
					<input type="button" onclick="showPanel('#importTablePanel');" value="Импорт">
				</div>
			</div>

			<div class="content-panel-block hideBlock" id="fillTablePanel" style="display:none;">
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
			<div class="content-panel-block hideBlock" id="importTablePanel" style="display:none;">

				<div class="form-fields-row">
					<div class="form-field-cell">
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
						<div class="content-panel-inputs">
							<input type="submit" id="fillTableFromFile" value="Загрузить">
						</div>
					</div>
				</div>
			</div>

			@if (Session::has('errors.form'))
				<div class="error_dialog_messages">
				@foreach (Session::get('errors.form') as $key => $messages)
					@php
						$key++;
					@endphp
					<br><b>В строке "{{$key}}" обнаружены следующие ошибки:</b> <br>
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
		<div class="content_body">

			<div id="shops_dialog"></div>
			<div id="tovs_dialog"></div>
			<div id="contragent_dialog"></div>

        	<table id="jqGridList"><tr><td></td></tr></table> 
        	<div id="jqGridpager"></div> 

<!--

			<div class="table_data_block">

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
						    <th>Старая закупочная цена (руб)</th>
						    <th>Новая закупочная цена (руб)</th>
						    <th>Дата начала скидки ON INVOICE</th>
						    <th>Дата окончания скидки ON INVOICE</th>
						    <th>Подписи, слоганы, расшифровки и пояснения к товарам в рекламе.</th>
						    <th>Пометки к товарам: Хит, Новинка, Суперцена, Выгода 0000 рублей...</th>
						</tr>
					</thead>
					</tbody>

						@php
							$tovList = [];
							if(old('kodTov'))
							{
								foreach(old('kodTov') as $k => $v)
								{
									$tovList[$k]['kodTov'] 					= $v;
									$tovList[$k]['tovsTitles'] 				= old('tovsTitles.'.$k);
									$tovList[$k]['shops'] 					= old('shops.'.$k);
									$tovList[$k]['shopsTitles'] 			= old('shopsTitles.'.$k);
									$tovList[$k]['distr'] 					= old('distr.'.$k);
									$tovList[$k]['distrTitles'] 			= old('distrTitles.'.$k);
									$tovList[$k]['type'] 					= old('type.'.$k);
									$tovList[$k]['skidka_on_invoice'] 		= old('skidka_on_invoice.'.$k);
									$tovList[$k]['kompensaciya_off_invoice']= old('kompensaciya_off_invoice.'.$k);
									$tovList[$k]['skidka_itogo'] 			= old('skidka_itogo.'.$k);
									$tovList[$k]['roznica_old'] 			= old('roznica_old.'.$k);
									$tovList[$k]['roznica_new'] 			= old('roznica_new.'.$k);
									$tovList[$k]['zakup_old'] 				= old('zakup_old.'.$k);
									$tovList[$k]['zakup_new'] 				= old('zakup_new.'.$k);
									$tovList[$k]['start_date_on_invoice'] 	= old('start_date_on_invoice.'.$k);
									$tovList[$k]['end_date_on_invoice'] 	= old('end_date_on_invoice.'.$k);
									$tovList[$k]['descr'] 					= old('descr.'.$k);
									$tovList[$k]['marks'] 					= old('marks.'.$k);
								}
							}
						@endphp

						@if(count($tovList) > 0)
							@foreach($tovList as $k => $v)
								<tr>
									<td>
										<input type="checkbox" class="deleteRow">
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.tovsTitles'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.tovsTitles')}}</div>
										@endif
										<input type="hidden" value="{{$v['tovsTitles']}}" class="chTitle"/>
										<input type="text" value="{{$v['tovsTitles']}}" name="tovsTitles[]" class="tovsTitles" 
											style="width:255px;" />
										<input type="hidden" class="row_number" value="{{$k}}">
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.kodTov'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.kodTov')}}</div>
										@endif

										<input type="hidden" value="{{$v['kodTov']}}" class="chKod"/>
										<input type="text" name="kodTov[]" value="{{$v['kodTov']}}" class="kodTov"/>
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.shops'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.shops')}}</div>
										@endif
				   						<div class="field_input_file">
																					
											<input type="hidden" value="{{$v['shopsTitles']}}" class="chShop"/>
											<input type="text" name="shopsTitles[]" value="{{$v['shopsTitles']}}" class="shopsTitles"/>

											<input type="hidden" name="shops[]" value="{{$v['shops']}}" class="shops"/>
											<div class="file" data-type="getShopsErarhi">...</div>
										</div>
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.distr'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.distr')}}</div>
										@endif
				   						<div class="field_input_file">
											<input type="hidden" class="chDistr" value=""/>

											<input type="text" name="distrTitles[]" class="distrTitles" value="{{$v['distrTitles']}}" />
											<input type="hidden" name="distr[]" class="distr" value="{{$v['distr']}}"/>
											<div class="file" data-type="getContagentsErarhi">...</div>
										</div>
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.type'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.type')}}</div>
										@endif

										<select name="type[]" class="select" style="border:1px solid red;">
											<option value="0"> Не выбрано </option>
											@foreach ($action_types as $type)

												@if($v['type'] == $type->id)
													<option data-descr="{{$type->description}}"
														onmouseover="showHint({{$type->id}}, '{{$type->description}}')"
														value="{{$type->id}}" 
														selected="selected">
															{{$type->title}}
													</option>
												@else
													<option 
														onmouseover="showHint({{$type->id}}, '{{$type->description}}')"
														value="{{$type->id}}">
															{{$type->title}}
													</option>
												@endif
											@endforeach
										</select>
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.skidka_on_invoice'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.skidka_on_invoice')}}</div>
										@endif
										<input type="text" autocomplete="off" value="{{$v['skidka_on_invoice']}}"
											class="maskProcent on_invoice" name="skidka_on_invoice[]">
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.kompensaciya_off_invoice'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.kompensaciya_off_invoice')}}</div>
										@endif
										<input type="text" autocomplete="off" value="{{$v['kompensaciya_off_invoice']}}"
											class="maskProcent off_invoice" name="kompensaciya_off_invoice[]">
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.skidka_itogo'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.skidka_itogo')}}</div>
										@endif
										<input type="text" autocomplete="off" value="{{$v['skidka_itogo']}}"
											class="maskProcent skidka_itogo" name="skidka_itogo[]">
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.roznica_old'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.roznica_old')}}</div>
										@endif
										<input type="text" class="maskPrice roznica_old" autocomplete="off" value="{{$v['roznica_old']}}"
											name="roznica_old[]">
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.roznica_new'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.roznica_new')}}</div>
										@endif
										<input type="text" class="maskPrice roznica_new" autocomplete="off" value="{{$v['roznica_new']}}"
											name="roznica_new[]">
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.zakup_old'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.zakup_old')}}</div>
										@endif
										<input type="text" autocomplete="off" value="{{$v['zakup_old']}}" class="maskPrice zakup_old" name="zakup_old[]">
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.zakup_new'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.zakup_new')}}</div>
										@endif
										<input type="text" autocomplete="off" value="{{$v['zakup_new']}}" class="maskPrice zakup_new" name="zakup_new[]">
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.start_date_on_invoice'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.start_date_on_invoice')}}</div>
										@endif
										<input class="start_on_invoice_date maskDate" autocomplete="off" value="{{$v['start_date_on_invoice']}}"
											name="start_date_on_invoice[]">
									</td>
									<td>
										@if(Session::has('errors.form.'.$k.'.end_date_on_invoice'))
											<div class="error_message">{{Session::get('errors.form.'.$k.'.end_date_on_invoice')}}</div>
										@endif
										<input type="text" class="end_on_invoice_date maskDate" autocomplete="off" value="{{$v['end_date_on_invoice']}}"
											name="end_date_on_invoice[]">
									</td>
									<td>
										<textarea name="descr[]" class="descr">{{$v['descr']}}</textarea>
									</td>
									<td>
										<textarea name="marks[]" class="marks">{{$v['marks']}}</textarea>
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
									<select name="type[]" class="select">
										<option value="0"> Не выбрано </option>
										@foreach ($action_types as $type)
											<option
												onmouseover="showHint({{$type->id}}, '{{$type->description}}')"
												value="{{$type->id}}">
													{{$type->title}}
											</option>
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
									<input type="text" autocomplete="off" class="maskPrice zakup_old" name="zakup_old[]">
								</td>
								<td>
									<input type="text" autocomplete="off" class="maskPrice zakup_new" name="zakup_new[]">
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
			-->

		</div>
	</form>

@endsection

@section('addition_js')
	<script src="{{ asset('js/jquery.mask.min.js') }}"></script>
	<script src="{{ asset('js/select2.full.min.js') }}"></script>
	<script src="{{ asset('js/select2.full.min.ru.js') }}"></script>

    <script src="{{ asset('js/jquery.jqGrid.min.js') }}"></script>
	<script src="{{ asset('js/grid.locale-ru.js') }}"></script>
	<script src="{{ asset('js/action_edit_form.js') }}"></script>
    <script src="{{ asset('js/jquery.jqGrid.after.js') }}"></script>
@endsection

@section('addition_css')
	<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
	<link href="{{ asset('css/select2.change.css') }}" rel="stylesheet">

    <link href="{{ asset('css/ui.jqgrid.css') }}" rel="stylesheet">
@endsection