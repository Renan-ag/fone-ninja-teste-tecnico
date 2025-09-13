<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompraProduto extends Model
{
    use HasFactory, SoftDeletes;

    // Define a tabela associada ao modelo
    protected $table = 'compra_produto';

    // Define os campos que podem ser preenchidos em massa
    protected $fillable = [
        'compra_id',
        'produto_id',
        'quantidade',
        'preco_unitario',
    ];

    // Define os campos que devem ser tratados como tipos específicos
    protected $casts = [
        'quantidade' => 'integer',
        'preco_unitario' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Define o relacionamento com a tabela 'compras'
    public function compra()
    {
        return $this->belongsTo(Compra::class, 'compra_id', 'id');
    }

    // Define o relacionamento com a tabela 'produtos'
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id', 'id');
    }
}