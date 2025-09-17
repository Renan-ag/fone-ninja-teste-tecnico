<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FornecedorController extends Controller
{
  protected $validationRules = [
    'nome' => 'required|string|max:255|min:3|unique:fornecedores,nome',
    'descricao' => 'nullable|string|max:200',
    'email' => 'required|email|max:255|unique:fornecedores,email',
    'telefone' => 'required|string|max:20',
    'endereco' => 'required|string|max:500',
    'ativo' => 'boolean',
  ];

  protected $customMessages = [
    'nome.required' => 'O campo nome é obrigatório.',
    'nome.string' => 'O campo nome deve ser uma string.',
    'nome.max' => 'O campo nome não pode exceder 255 caracteres.',
    'nome.min' => 'O campo nome deve ter pelo menos 3 caracteres.',
    'nome.unique' => 'Já existe um fornecedor com este nome.',
    'descricao.string' => 'O campo descrição deve ser uma string.',
    'descricao.max' => 'O campo descrição não pode exceder 200 caracteres.',
    'email.required' => 'O campo email é obrigatório.',
    'email.email' => 'O campo email deve ser um endereço de email válido.',
    'email.max' => 'O campo email não pode exceder 255 caracteres.',
    'email.unique' => 'Já existe um fornecedor com este email.',
    'telefone.required' => 'O campo telefone é obrigatório.',
    'telefone.string' => 'O campo telefone deve ser uma string.',
    'telefone.max' => 'O campo telefone não pode exceder 20 caracteres.',
    'endereco.required' => 'O campo endereço é obrigatório.',
    'endereco.string' => 'O campo endereço deve ser uma string.',
    'endereco.max' => 'O campo endereço não pode exceder 500 caracteres.',
    'ativo.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
  ];

  protected $relationships = [];

  public function __construct(Fornecedor $fornecedor)
  {
    parent::__construct($fornecedor);
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
