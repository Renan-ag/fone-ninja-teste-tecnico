<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fornecedor extends Model
{
    use HasFactory, SoftDeletes;

    // Define a tabela associada ao modelo
    protected $table = 'fornecedores';

    // Define os campos que podem ser preenchidos em massa
    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'descricao',
        'endereco',
        'ativo',
    ];

    // Define os campos que devem ser tratados como tipos específicos
    protected $casts = [
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}