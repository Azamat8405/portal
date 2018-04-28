@extends('layouts.app')

@section('content')

	<form action="" onSubmit="return checkValues();" method="post" enctype="multipart/form-data" class="form">
		@csrf
		<div class="content-panel">
			<div class="content-panel-block">
				<h2>Добавление акции</h2>
				<div class="form-fields-row">
					<div class="form-field-cell">
						<div class="form-field-input">
						    <div>
								@if(Session::has('errors.form.0.process_type'))
									<label class="error_input">Тип <sup>*</sup></label>
								@else
									<label>Тип <sup>*</sup></label>
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
					            <input name="process_title" id="process_title" type="text" value="{{ old('process_title') }}">
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
					<div class="form-field-cell">
					    <div class="form-field-input">
						    <div>
								<label>Автор</label>
						    </div>
					    	<div>
								{{$user->name}}
						    </div>
						</div>
					</div>
				</div>
				<div class="content-panel-inputs">
					<input type="button" onclick="addJqGridSubmit();" value="Сохранить акцию">
					<input type="button" onclick="addJqGridRowFromPanel();" value="Добавить строку">
					<input type="button" onclick="delJqGridRows();" value="Удалить строки">
					<input type="button" onclick="showPanel(this, '#fillTablePanel');" value="Заполнить/добавить товары">
					<input type="button" onclick="showPanel(this, '#importTablePanel');" value="Импорт">
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
									<div class="file" data-type="getShopsErarhiIsk">...</div>
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
						            <label>Загрузите файл в формате xslx со <a style="text-decoration:underline;" 
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
		</div>

	    <div class="content_body">
			<div id="shops_dialog" style="display:none;"></div>
			<div id="tovs_dialog" style="display:none;"></div>
			<div id="contragent_dialog" style="display:none;"></div>
			<script>
				{!!$shops_for_js!!}
				var action_types = "{{$action_types}}";
				var action_types_descr = [];
				@foreach($action_types_descr as $key => $value)
					action_types_descr[{{$key}}] = "{{$value}}";
				@endforeach
			</script>
	        <table id="jqGridAdd"><tr><td></td></tr></table> 
	        <div id="jqGridAddPager"></div> 
		</div>
	</form>

@endsection

@section('addition_js')

	<script src="{{ asset('js/jquery.mask.min.js') }}"></script>
 	<script src="{{ asset('js/select2.full.min.js') }}"></script>
	<script src="{{ asset('js/select2.full.min.ru.js') }}"></script>

	<script src="{{ asset('js/jquery.jqGrid.min.js') }}"></script>
	<script src="{{ asset('js/grid.locale-ru.js') }}"></script>
	<script src="{{ asset('js/action_add_form.js') }}"></script>
    <script src="{{ asset('js/jquery.jqGrid.after.js') }}"></script>

@endsection

@section('addition_css')

	<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
	<link href="{{ asset('css/select2.change.css') }}" rel="stylesheet">
	<link href="{{ asset('css/ui.jqgrid.css') }}" rel="stylesheet">
	<link href="{{ asset('css/ui.jqgrid.change.css') }}" rel="stylesheet">
	<link href="{{ asset('css/processes.css') }}" rel="stylesheet">

@endsection