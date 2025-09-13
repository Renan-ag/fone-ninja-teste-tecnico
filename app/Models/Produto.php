<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produto extends Model
{
    use HasFactory, SoftDeletes;

    // Define a tabela associada ao modelo
    protected $table = 'produtos';

    // Define os campos que podem ser preenchidos em massa
    protected $fillable = [
        'nome',
        'descricao',
        'categoria_id',
        'custo_medio',
        'preco_venda',
        'estoque',
        'ativo',
    ];

    // Define os campos que devem ser tratados como tipos específicos
    protected $casts = [
        'ativo' => 'boolean',
        'custo_medio' => 'decimal:2',
        'preco_venda' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Define o relacionamento com a tabela 'categorias'
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id', 'id');
    }
}