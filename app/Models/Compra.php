<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compra extends Model
{
    use HasFactory, SoftDeletes;

    // Define a tabela associada ao modelo
    protected $table = 'compras';

    // Define os campos que podem ser preenchidos em massa
    protected $fillable = [
        'fornecedor_id',
        'estornado',
        'total',
        'status',
    ];

    // Define os campos que devem ser tratados como tipos específicos
    protected $casts = [
        'estornado' => 'boolean',
        'total' => 'decimal:2',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Define o relacionamento com a tabela 'fornecedores'
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id', 'id');
    }
}