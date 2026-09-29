@extends('layouts.app')

@section('title', 'Admin belépés – SOSlive')

@section('content')
    <h1>Admin belépés</h1>
    <form method="post" action="{{ route('admin.login') }}" class="stack narrow">
        @csrf
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
        <label>Jelszó <input type="password" name="password" required></label>
        <label class="row"><input type="checkbox" name="remember" value="1"> Emlékezz rám</label>
        <button type="submit">Belépés</button>
    </form>
@endsection
