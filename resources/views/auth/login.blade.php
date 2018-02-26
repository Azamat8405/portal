@extends('layouts.app')

@section('content')
<form class="auth_form" action="{{ route('login') }}" method="post">
    @csrf

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
            <input id="email" type="email" class="{{ $errors->has('email') ? ' invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
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
    <div class="form-field-input">
        <label for="remember">Запоминть меня</label>
        <input id="remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
    </div>
    <div class="form-field-submit">
        <input type="submit" value="Войти">
    </div>
    <br>
    <a class="btn btn-link" href="{{ route('password.request') }}">Забыли пароль?</a>
</form>
@endsection