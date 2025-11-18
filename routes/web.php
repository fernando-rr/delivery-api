<?php

use Illuminate\Support\Facades\Route;

// Redireciona todas as rotas web para 404
// O front-end será servido aqui no futuro
Route::fallback(function () {
    abort(404, 'Rota não encontrada. Use as rotas /api para acessar a API.');
});
