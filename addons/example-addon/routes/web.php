<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin/example')->name('addon.example.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', function () {
        return view('example-addon::index');
    })->name('index');
});