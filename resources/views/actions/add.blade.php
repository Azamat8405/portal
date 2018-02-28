@extends('layouts.app')

@section('content')

    <div class="content-panel">
		<h2>Добавление акции</h2>
    </div>

	<form class="" action="{{ route('actions.add') }}" method="post" enctype="multipart/form-data">
	    @csrf

	    @if ($errors->has('file'))
	        <div class="form-field-input">
	            <div class="error_message">
	                <strong>{{ $errors->first('file') }}</strong>
	            </div>
	        </div>
	    @endif

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
</style>



<input type="hidden" name="test" value="11">


	    <div class="form-field-input">
			<div class="div_table">
		        <div class="left">
		            <input id="file" type="file" class="{{ $errors->has('file') ? ' invalid' : '' }}" name="file" value="{{ old('file') }}" autofocus accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">
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