@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('password.request') }}" class="auth_form">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    @if ($errors->has('email'))
        <div class="form-field-input">
            <div class="error_message">
                <strong>{{ $errors->first('email') }}</strong>
            </div>
        </div>
    @endif
    <div class="form-field-input">
        <div>
            <label>Email</label>
        </div>
        <div>
            <input id="email" type="email" class="{{ $errors->has('email') ? ' invalid' : '' }}" name="email" value="{{ $email or old('email') }}" required autofocus>
        </div>
    </div>

    @if ($errors->has('password'))
        <div class="form-field-input">
            <div class="error_message">
                <strong>{{ $errors->first('password') }}</strong>
            </div>
        </div>
    @endif
    <div class="form-field-input">
        <div>
            <label>Пароль</label>
        </div>
        <div>
            <input id="password" type="password" class="{{ $errors->has('password') ? ' invalid' : '' }}" name="password" required>
        </div>
    </div>

    @if ($errors->has('password_confirmation'))
        <div class="form-field-input">
            <div class="error_message">
                <strong>{{ $errors->first('password_confirmation') }}</strong>
            </div>
        </div>
    @endif
    <div class="form-field-input">
        <div>
            <label>Подтвердите пароль</label>
        </div>
        <div>
            <input id="password-confirm" type="password" class="{{ $errors->has('password_confirmation') ? ' invalid' : '' }}" 
            name="password_confirmation" required>
        </div>
    </div>

    <div class="form-field-submit">
        <input type="submit" value="Сбросить пароль">
    </div>
</form>
@endsection