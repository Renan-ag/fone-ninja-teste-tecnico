<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use SoftDeletes;

    /**
     * Os atributos que podem ser preenchidos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
    ];

    /**
     * Os atributos que devem ser tratados como datas.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Indica se a categoria está ativa.
     *
     * @var bool
     */
    protected $attributes = [
        'ativo' => true, // Valor padrão conforme nota implícita
    ];

    /**
     * Relacionamento com o modelo Produto (muitos produtos por categoria).
     */
    public function produtos()
    {
        return $this->hasMany(Produto::class, 'categoria_id');
    }
}