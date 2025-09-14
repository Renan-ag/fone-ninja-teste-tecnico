<?php

namespace App\Http\Controllers;

use App\Models\Produto;

class ProdutoController extends Controller
{
  protected $validationRules = [
    'nome' => 'required|string|max:255|min:3',
    'descricao' => 'nullable|string',
    'categoria_id' => 'required|exists:categorias,id',
    'custo_medio' => 'required|numeric|min:0',
    'preco_venda' => 'required|numeric|min:0',
    'estoque' => 'nullable|integer|min:0',
    'ativo' => 'boolean',
  ];

  protected $relationships = ['categoria'];

  public function __construct(Produto $produto)
  {
    parent::__construct($produto);
  }
}
