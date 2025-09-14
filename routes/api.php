<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\ProdutoController;

// Rotas para Fornecedores
Route::resource('fornecedores', FornecedorController::class)
  ->only(['index', 'show', 'store', 'update']);

Route::prefix('fornecedores')->group(function () {
  Route::delete('{fornecedor}', [FornecedorController::class, 'destroy']);
  Route::post('{fornecedor}/restore', [FornecedorController::class, 'restore']);
});

// Rotas para Categorias
Route::resource('categorias', CategoriaController::class)
  ->only(['index', 'show', 'store', 'update']);

Route::prefix('categorias')->group(function () {
  Route::delete('{categoria}', [CategoriaController::class, 'destroy']);
  Route::post('{categoria}/restore', [CategoriaController::class, 'restore']);
});

// Rotas para Produtos
Route::resource('produtos', ProdutoController::class)
  ->only(['index', 'show', 'store', 'update']);

Route::prefix('produtos')->group(function () {
  Route::delete('{produto}', [ProdutoController::class, 'destroy']);
  Route::post('{produto}/restore', [ProdutoController::class, 'restore']);
});

// Rotas para Compras
Route::resource('compras', CompraController::class)
  ->only(['index', 'show', 'store', 'update']);

Route::prefix('compras')->group(function () {
  Route::delete('{compra}', [CompraController::class, 'destroy']);
  Route::post('{compra}/restore', [CompraController::class, 'restore']);
});

// Rotas para Vendas
Route::resource('vendas', VendaController::class)
  ->only(['index', 'show', 'store', 'update']);

Route::prefix('vendas')->group(function () {
  Route::delete('{venda}', [VendaController::class, 'destroy']);
  Route::post('{venda}/restore', [VendaController::class, 'restore']);
});
