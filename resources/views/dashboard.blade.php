@extends('layouts.app')

@section('title', 'Események – SOSlive')
@section('page', 'dashboard')

@section('content')
    <h1>Események</h1>
    <p id="page-status" class="muted">Betöltés…</p>
    {{-- A tulajonkénti szekciókat a JS építi az /app/me válaszából. --}}
    <div id="owners"></div>
@endsection
