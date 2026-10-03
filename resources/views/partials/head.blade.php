{{-- Közös <head> elemek a statikus (layouts/app) és az admin (layouts/admin) layouthoz. --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="referrer" content="strict-origin-when-cross-origin">
<meta name="color-scheme" content="light dark">
<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#16171b" media="(prefers-color-scheme: dark)">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="stylesheet" href="/css/app.css?v={{ filemtime(public_path('css/app.css')) }}">
