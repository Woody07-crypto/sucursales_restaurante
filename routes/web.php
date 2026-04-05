<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/docs/api', 'docs.api');

Route::get('/openapi.yaml', function () {
    $path = public_path('openapi.yaml');
    abort_unless(is_readable($path), 404);

    return response()->file($path, [
        'Content-Type' => 'application/yaml',
    ]);
});
