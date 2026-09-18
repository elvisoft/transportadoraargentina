<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('choferes', 'pages::choferes.index')->name('choferes.index');
    Route::livewire('vehiculos', 'pages::vehiculos.index')->name('vehiculos.index');
    Route::livewire('gastos', 'pages::gastos.index')->name('gastos.index');
});
