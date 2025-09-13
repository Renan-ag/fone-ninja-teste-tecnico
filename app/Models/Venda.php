<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venda extends Model
{
    use HasFactory, SoftDeletes;

    // Define a tabela associada ao modelo
    protected $table = 'vendas';

    // Define os campos que podem ser preenchidos em massa
    protected $fillable = [
        'cliente',
        'total',
        'lucro',
        'status',
    ];

    // Define os campos que devem ser tratados como tipos específicos
    protected $casts = [
        'total' => 'decimal:2',
        'lucro' => 'decimal:2',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}