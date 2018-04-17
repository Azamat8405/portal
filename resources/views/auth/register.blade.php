@extends('layouts.app')

@section('content')
<form class="reg_form" method="POST" action="{{ route('register') }}">
    @csrf
    <input type="hidden" value="1" name="user_group_id">
    <input type="hidden" value="admin" name="role">

    @if ($errors->has('name'))
        <div class="form-field-input">
            <div class="error_message">
                <strong>{{ $errors->first('name') }}</strong>
            </div>
        </div>
    @endif
    <div class="form-field-input">
        <div>
            <label>ФИО</label>
        </div>
        <div>
            <input id="name" type="text" class="{{ $errors->has('name') ? ' invalid' : '' }}" name="name" value="{{ old('name') }}" required autofocus>
        </div>
    </div>

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
            <input id="email" type="email" class="{{ $errors->has('email') ? ' invalid' : '' }}" name="email" value="{{ old('email') }}" required>
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
            <input id="password" type="password" class="{{ $errors->has('password') ? ' invalid' : '' }}" name="password" >
        </div>
    </div>
    <div class="form-field-input">
        <div>
            <label>Подтвердите пароль</label>
        </div>
        <div>
            <input id="password-confirm" type="password" class="{{ $errors->has('password_confirmation') ? ' invalid' : '' }}" name="password_confirmation" >
        </div>
    </div>
    <div class="form-field-submit">
        <input type="submit" value="Регистрация">
    </div>
</form>
@endsection