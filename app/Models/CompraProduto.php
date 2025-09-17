<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompraProduto extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'compra_produto';
  protected $primaryKey = ['compra_id', 'produto_id'];
  public $incrementing = false;

  protected $fillable = [
    'compra_id',
    'produto_id',
    'quantidade',
    'preco_unitario',
  ];

  protected $casts = [
    'quantidade' => 'integer',
    'preco_unitario' => 'decimal:2',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  public function compra()
  {
    return $this->belongsTo(Compra::class, 'compra_id', 'id');
  }

  public function produto()
  {
    return $this->belongsTo(Produto::class, 'produto_id', 'id');
  }

  protected function setKeysForSaveQuery($query)
  {
    return $query->where('compra_id', $this->venda_id)
      ->where('produto_id', $this->produto_id);
  }
}
