<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produto extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'produtos';
    
    protected $fillable = [
        'nome',
        'descricao',
        'categoria_id',
        'custo_medio',
        'preco_venda',
        'estoque',
        'ativo',
    ];
    
    protected $casts = [
        'ativo' => 'boolean',
        'custo_medio' => 'decimal:2',
        'preco_venda' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id', 'id');
    }

    public function compras()
    {
        return $this->hasMany(CompraProduto::class, 'produto_id', 'id');
    }
}