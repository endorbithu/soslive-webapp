@extends('layouts.admin')

@section('title', 'Teszt belépés – SOSlive')

@section('content')
    <h1>Teszt belépés</h1>
    <p class="note">Csak fejlesztői / teszt környezetben érhető el ({{ app()->environment() }}), production alatt nincs.
        A Drive-os részekhez (eseménylista, beállítások) a böngésző ettől még Google hozzáférést kér.</p>
    <form method="post" action="{{ route('auth.dev') }}" class="card stack narrow">
        @csrf
        <label>Email <input type="email" name="email" value="{{ old('email', 'teszt@example.com') }}" required autofocus></label>
        <label>Név <span class="muted">(opcionális)</span> <input type="text" name="name" value="{{ old('name') }}"></label>
        <button type="submit">Belépés</button>
    </form>
@endsection
