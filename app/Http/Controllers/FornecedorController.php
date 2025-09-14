<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;

class FornecedorController extends Controller
{
    protected $validationRules = [
        'nome' => 'required|string|max:255|min:3',
        'descricao' => 'nullable|string|max:200',
        'email' => 'required|email|max:255',
        'telefone' => 'required|string|max:20',
        'endereco' => 'required|string|max:500',
        'ativo' => 'boolean',
    ];

    protected $relationships = [];

    public function __construct(Fornecedor $fornecedor)
    {
        parent::__construct($fornecedor);
    }
}