@extends('layouts.app')

@section('content')

<form method="POST" action="{{ route('password.email') }}" class="auth_form">
    @csrf

    @if (session('status'))
        <div class="success_message">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->has('email'))
        <div class="form-field-input">
            <div class="error_message">
                <strong>{{ $errors->first('email') }}</strong>
            </div>
        </div>
    @endif
    <div class="form-field-input" style="margin:15px 0;">
        <div>
            <label>Email</label>
        </div>
        <div>
            <input id="email" type="email" class="{{ $errors->has('email') ? ' invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
        </div>
    </div>


    <div class="form-field-submit">
        <input type="submit" value="Получить ссылку на сброс пароля">
    </div>
</form>

@endsection