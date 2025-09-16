<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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

  public function update(Request $request, $id): JsonResponse
  {
    try {
      $resource = $this->model::findOrFail($id);
      $resource->update($request->except(["custo_medio", "estoque"]));
      $resource->load($this->relationships);
      return $this->successResponse($resource, 'Recurso atualizado com sucesso');
    } catch (\Exception $e) {
      return $this->handleException($e);
    }
  }
}
