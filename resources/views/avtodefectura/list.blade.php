@extends('layouts.app')

@section('content')

	<form action="" onSubmit="return checkValues();" method="post" enctype="multipart/form-data" class="form">
		@csrf
	    <div class="content-panel">
	        <div class="content-panel-block">
	            <h2>Автодефектура</h2>
	        </div>
	    </div>

	    <div class="content_body">
	        <table id="jqGrid"><tr><td></td></tr></table> 
	        <div id="jqGridPager"></div> 
		</div>
	</form>
@endsection

@section('addition_js')
	<script src="{{ asset('js/jquery.jqGrid.min.js') }}"></script>
	<script src="{{ asset('js/grid.locale-ru.js') }}"></script>
	<script src="{{ asset('js/avtodefectura.js') }}"></script>
	<script src="{{ asset('js/jquery.jqGrid.after.js') }}"></script>
@endsection

@section('addition_css')
	<link href="{{ asset('css/ui.jqgrid.css') }}" rel="stylesheet">
	<link href="{{ asset('css/ui.jqgrid.change.css') }}" rel="stylesheet">
@endsection