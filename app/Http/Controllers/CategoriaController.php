<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
  protected $validationRules = [
    'nome' => 'required|string|max:255',
    'descricao' => 'nullable|string',
    'ativo' => 'boolean',
  ];

  protected $customMessages = [
    'nome.required' => 'O nome é obrigatório',
    'nome.max' => 'O nome deve ter no máximo 255 caracteres',
  ];

  protected $relationships = [];

  public function __construct(Categoria $categoria)
  {
    parent::__construct($categoria);
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
}
