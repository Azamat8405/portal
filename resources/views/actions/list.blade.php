@extends('layouts.app')

@section('content')
	<ul>
		@foreach ($actions as $action)

			<li>{{$action->id}} - {{$action->actionType->title}}</li>

		@endforeach
	</ul>
@endsection