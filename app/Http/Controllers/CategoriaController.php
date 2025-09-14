<?php

namespace App\Http\Controllers;

use App\Models\Categoria;

class CategoriaController extends Controller
{
    protected $validationRules = [
        'nome' => 'required|string|max:255',
        'descricao' => 'nullable|string',
        'ativo' => 'boolean',
    ];

    protected $relationships = [];

    public function __construct(Categoria $categoria)
    {
        parent::__construct($categoria);
    }
}