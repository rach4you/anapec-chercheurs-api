<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ── Frontend Portal (ANAPEC API Management) ──
Route::get('login', function () {
    return view('auth.login');
})->name('login');

Route::get('dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('admin/users', function () {
    return view('admin.users');
})->name('admin.users');

Route::get('admin/users/{id}', function ($id) {
    return view('admin.users.show');
})->where('id', '[0-9]+')->name('admin.users.show');
