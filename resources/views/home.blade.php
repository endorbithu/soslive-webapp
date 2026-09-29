@extends('layouts.app')

@section('page', 'home')

@section('content')
    <h1>SOSlive</h1>
    <p>Az eseményeid (stream link, pozíció, képek) a saját Google Drive-odban tárolódnak.
       A SOSlive csak az általa létrehozott fájlokhoz fér hozzá.</p>
    <p><a id="home-cta" class="button" href="{{ route('auth.google', [], false) }}">Belépés Google-fiókkal</a></p>
@endsection
