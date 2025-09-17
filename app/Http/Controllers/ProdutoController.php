<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProdutoController extends Controller
{
  protected $validationRules = [
    'nome' => 'required|string|max:255|min:3|unique:produtos,nome',
    'descricao' => 'nullable|string',
    'categoria_id' => 'required|exists:categorias,id',
    'custo_medio' => 'required|numeric|min:0',
    'preco_venda' => 'required|numeric|min:0',
    'estoque' => 'nullable|integer|min:0',
    'ativo' => 'boolean',
  ];

  protected $customMessages = [
    'nome.required' => 'O nome é obrigatório',
    'descricao.required' => 'A descrição é obrigatória',
    'categoria_id.required' => 'A categoria é obrigatória',
    'custo_medio.required' => 'O custo médio é obrigatório',
    'preco_venda.required' => 'O preço de venda é obrigatório',
    'estoque.required' => 'O estoque é obrigatório',
    'ativo.required' => 'O status é obrigatório',
    'nome.unique' => 'O nome do produto já existe',
  ];

  protected $relationships = ['categoria'];

  public function __construct(Produto $produto)
  {
    parent::__construct($produto);
  }

  public function index(Request $request): JsonResponse
  {
    $apenasAtivo = $request->input('ativo', false);
    $pesquisa = $request->input('pesquisa', false);
    $perPage = $request->input('per_page', 12);
    $query = $this->model::query()->with($this->relationships);
    if ($apenasAtivo) {
      $query->where('ativo', 1);
    }

    if ($pesquisa) {
      $query->where('nome', 'like', "%{$pesquisa}%");
    }

    $resources = $query->paginate($perPage);

    return $this->successResponse($resources, 'Recursos listados com sucesso');
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
