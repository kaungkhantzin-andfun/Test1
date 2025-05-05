<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});


Route::get('/test', function () {
    return 'Your plain text message here';
});

require __DIR__.'/auth.php';
