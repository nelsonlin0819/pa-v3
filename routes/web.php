<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Home', ['name' => 'Nelson'])->name('home');
