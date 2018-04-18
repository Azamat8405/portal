@extends('layouts.app')

@section('content')

	<form class="addProcessForm" action="{{ route('processes.add') }}" method="post" enctype="multipart/form-data">
		@csrf
		<div class="content-panel-fon"></div>
		<div class="content-panel">
			<div class="content-panel-block">
				<h2>Список заявок на уценку</h2>

				<div class="content-panel-inputs">

					@if($user->user_group_id == 5)
						<a class="button" href="{{route('ucenka.add')}}">Добавить заявку</a>
					@endif
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
        <table id="jqGridList"><tr><td></td></tr></table>
        <div id="jqGridpager"></div>
	</div>
@endsection

@section('addition_js')
    <script src="{{ asset('js/jquery.jqGrid.min.js') }}"></script>
    <script src="{{ asset('js/grid.locale-ru.js') }}"></script>
	<script src="{{ asset('js/ucenka.js') }}"></script>
    <script src="{{ asset('js/jquery.jqGrid.after.js') }}"></script>

@endsection

@section('addition_css')
    <link href="{{ asset('css/ui.jqgrid.css') }}" rel="stylesheet">
	<link href="{{ asset('css/ui.jqgrid.change.css') }}" rel="stylesheet">
@endsection