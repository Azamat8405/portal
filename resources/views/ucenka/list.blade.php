@extends('layouts.app')

@section('content')

	<form class="addProcessForm" action="{{ route('processes.add') }}" method="post" enctype="multipart/form-data">
		@csrf
		<div class="content-panel-fon"></div>
		<div class="content-panel">
			<div class="content-panel-block">
				<h2>Список заявок на уценку</h2>

<!-- 				<div class="form-fields-row">
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
 -->				<div class="content-panel-inputs">
					<a class="button" href="{{route('ucenka.add')}}">Добавить заявку</a>
					<input type="submit" onclick="addRow();" value="Фильтровать">
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
				@foreach (Session::get('errors.file') as $messages)
					@foreach ($messages as $message)
						<p>{!! $message !!}</p>
					@endforeach
				@endforeach
				</div>
			@endif
			@if (Session::has('ok'))
				<div class="success_dialog_messages">
					<p>{!! Session::get('ok') !!}</p>
				</div>
			@endif
		</div>
	</form>



	<div class="content_body">
		<div class="table_data_block">
			<div id="shops_dialog"></div>
			<div id="tovs_dialog"></div>
			<div id="contragent_dialog"></div>

			<div id="tableHeader"></div>
			<table id="tableTovs">
				<thead>
					<tr>
						<th>№</th>
					    <th>Магазин</th>
					    <!-- <th>Код номенклатуры</th> -->
					    <th>Наименование товара</th>
<!-- 					    <th>Срок годности</th>
					    <th>Причина</th>
					    <th>Остаток</th>
 -->					</tr>
				</thead>
				</tbody>

				@foreach($apps as $k => $v)
					<tr>
						<td align='center'>
							{{ $v->id }}
						</td>
						<td>
							<a href="{{ route('ucenka.full', ['appId' => $v->id]) }}">{{ $v->shop->title }}</a>
						</td>
						<td>

					@if($v->app_tovs()->count() > 0)
						@foreach($v->app_tovs()->get() as $k_tov => $v_tov)

							<a href="{{ route('ucenka.full', ['appId' => $v->id]) }}">{{ $v_tov->nomenklatury_title }}</a><br>

{{--

							{{ $v_tov->nomenklatury_kod }}
							{{ $v_tov->srok_godnosty }}
							@if($v_tov->ucenka_reason)
								{{ $v_tov->ucenka_reason->title }}
							@endif
							{{ $v_tov->ostatok }}




								<td>
		    						[user_id]
								</td>
								<td>
									{{ $v_tov->agreement_date }}
								</td>
								<td>
									{{ $v_tov->skidka }}
								</td>
--}}
						@endforeach
					@endif
						</td>
					</tr>
				@endforeach
				</tbody>
			</table>

			<div class="pagination_links">
				{{ $apps->links() }}
			</div>

<style>
.pagination_links{
position: relative;
overflow:hidden;
padding:30px 0;
}
.pagination
{
list-style:none;
margin:0;
padding:0;
position: relative;
left:50%;
float:left;
font:14px Arial, Helvetica, sans-serif;
}
.pagination .page-item{
position:relative;
left:-50%;
float:left;
margin:0 7px;
height:40px
}
.pagination .page-item a, 
.pagination .page-item span{
	display:block;
	text-decoration:none;
    padding:0px 5px;
    line-height:20px;
	border:1px solid #000;
}
.pagination .page-item span
{
	border:1px solid #a09999;
}

</style>

		</div>
	</div>
@endsection