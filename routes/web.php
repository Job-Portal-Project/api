<?php

use Illuminate\Support\Facades\Route;

Route::get('/', action: fn () => view('welcome'))->name('login');
Route::get('/logout', action: fn () => view('logout'));
