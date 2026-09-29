@extends('layouts.app')

@section('content')
    <h1>SOSlive</h1>
    <p>Az eseményeid (stream link, pozíció, chat, képek) a saját Google Drive-odban, Google Sheets fájlokban tárolódnak.
       A SOSlive csak az általa létrehozott fájlokhoz fér hozzá.</p>
    <p><a class="button" href="{{ route('auth.google') }}">Belépés Google-fiókkal</a></p>
@endsection
