<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraProduto;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @group Compras
 * APIs para gerenciar compras
 */
class CompraController extends Controller
{
  protected $validationRules = [
    'fornecedor_id' => 'required|exists:fornecedores,id',
    'total' => 'required|numeric|min:0',
    'produtos' => 'required|array|min:1',
    'produtos.*.produto_id' => 'required|exists:produtos,id',
    'produtos.*.quantidade' => 'required|integer|min:1',
    'produtos.*.preco_unitario' => 'required|numeric|min:0',
  ];

  protected $customMessages = [
    'fornecedor_id.required' => 'O fornecedor é obrigatório',
    'total.required' => 'O total é obrigatório',
    'produtos.required' => 'Os produtos são obrigatórios',
    'produtos.*.produto_id.required' => 'O produto é obrigatório',
    'produtos.*.quantidade.required' => 'A quantidade é obrigatória',
    'produtos.*.preco_unitario.required' => 'O preço unitário é obrigatório',
  ];

  protected $relationships = ['fornecedor', 'compraProdutos.produto'];

  public function __construct()
  {
    parent::__construct(new Compra());
  }

  public function store(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), $this->validationRules);

    if ($validator->fails()) {
      return $this->errorResponse($validator->errors(), 422);
    }

    try {
      $compra = DB::transaction(function () use ($request) {
        $compra = $this->model::create([
          'fornecedor_id' => $request->fornecedor_id,
          'total' => $request->total,
        ]);

        foreach ($request->produtos as $produto) {
          CompraProduto::create([
            'compra_id' => $compra->id,
            'produto_id' => $produto['produto_id'],
            'quantidade' => $produto['quantidade'],
            'preco_unitario' => $produto['preco_unitario'],
          ]);

          // Atualiza estoque e custo médio
          $produtoModel = Produto::findOrFail($produto['produto_id']);
          $estoqueAtual = $produtoModel->estoque ?? 0;
          $custoMedioAtual = $produtoModel->custo_medio ?? 0;

          $produtoModel->increment('estoque', $produto['quantidade']);

          $novoCustoMedio = ($estoqueAtual * $custoMedioAtual + $produto['quantidade'] * $produto['preco_unitario']) /
            ($estoqueAtual + $produto['quantidade']);
          $produtoModel->update(['custo_medio' => round($novoCustoMedio, 2)]);
        }

        return $compra;
      });

      $compra->load($this->relationships);
      return $this->successResponse($compra, 'Recurso criado com sucesso', 201);
    } catch (QueryException $e) {
      return $this->handleDatabaseException($e);
    }
  }

  public function update(Request $request, $id): JsonResponse
  {
    $compra = $this->model::findOrFail($id);

    $rules = $this->getUpdateRules();
    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return $this->errorResponse($validator->errors(), 422);
    }

    try {
      $compra = DB::transaction(function () use ($compra, $request) {
        $compra->update([
          'fornecedor_id' => $request->input('fornecedor_id', $compra->fornecedor_id),
          'total' => $request->input('total', $compra->total),
        ]);

        if ($request->has('produtos')) {
          // Reverter estoque e custo médio dos produtos antigos
          foreach ($compra->compraProdutos as $compraProduto) {
            $produtoModel = Produto::findOrFail($compraProduto->produto_id);
            $estoqueAtual = $produtoModel->estoque;
            $custoMedioAtual = $produtoModel->custo_medio ?? 0;

            $produtoModel->decrement('estoque', $compraProduto->quantidade);

            if ($estoqueAtual > $compraProduto->quantidade) {
              $novoCustoMedio = (($estoqueAtual * $custoMedioAtual) - ($compraProduto->quantidade * $compraProduto->preco_unitario)) /
                ($estoqueAtual - $compraProduto->quantidade);
              $produtoModel->update(['custo_medio' => round($novoCustoMedio, 2)]);
            } else {
              $produtoModel->update(['custo_medio' => 0]);
            }
          }

          // Excluir os produtos antigos
          $compra->compraProdutos()->withTrashed()->forceDelete();;

          // Adicionar novos produtos e atualizar estoque/custo médio
          foreach ($request->produtos as $produto) {
            CompraProduto::create([
              'compra_id' => $compra->id,
              'produto_id' => $produto['produto_id'],
              'quantidade' => $produto['quantidade'],
              'preco_unitario' => $produto['preco_unitario'],
            ]);

            $produtoModel = Produto::findOrFail($produto['produto_id']);
            $estoqueAtual = $produtoModel->estoque ?? 0;
            $custoMedioAtual = $produtoModel->custo_medio ?? 0;

            $produtoModel->increment('estoque', $produto['quantidade']);

            $novoCustoMedio = ($estoqueAtual * $custoMedioAtual + $produto['quantidade'] * $produto['preco_unitario']) /
              ($estoqueAtual + $produto['quantidade']);
            $produtoModel->update(['custo_medio' => round($novoCustoMedio, 2)]);
          }
        }

        return $compra;
      });

      $compra->load($this->relationships);
      return $this->successResponse($compra, 'Recurso atualizado com sucesso');
    } catch (QueryException $e) {
      return $this->handleDatabaseException($e);
    }
  }

  public function destroy($id): JsonResponse
  {
    $compra = $this->model::findOrFail($id);

    try {
      DB::transaction(function () use ($compra) {
        foreach ($compra->compraProdutos as $compraProduto) {
          $produtoModel = Produto::findOrFail($compraProduto->produto_id);
          $estoqueAtual = $produtoModel->estoque;
          $custoMedioAtual = $produtoModel->custo_medio ?? 0;

          $produtoModel->decrement('estoque', $compraProduto->quantidade);

          if ($estoqueAtual > $compraProduto->quantidade) {
            $novoCustoMedio = (($estoqueAtual * $custoMedioAtual) - ($compraProduto->quantidade * $compraProduto->preco_unitario)) /
              ($estoqueAtual - $compraProduto->quantidade);
            $produtoModel->update(['custo_medio' => round($novoCustoMedio, 2)]);
          } else {
            $produtoModel->update(['custo_medio' => 0]);
          }
        }

        $compra->delete();
      });

      return $this->successResponse(null, 'Compra excluída com sucesso e estoque/custo médio atualizados', 204);
    } catch (QueryException $e) {
      return $this->handleDatabaseException($e);
    }
  }

  public function restore($id): JsonResponse
  {
    $compra = $this->model::withTrashed()->findOrFail($id);

    try {
      $compra = DB::transaction(function () use ($compra) {
        $compra->restore();

        foreach ($compra->compraProdutos()->withTrashed()->get() as $compraProduto) {
          $produtoModel = Produto::findOrFail($compraProduto->produto_id);
          $estoqueAtual = $produtoModel->estoque ?? 0;
          $custoMedioAtual = $produtoModel->custo_medio ?? 0;

          $produtoModel->increment('estoque', $compraProduto->quantidade);

          $novoCustoMedio = ($estoqueAtual * $custoMedioAtual + $compraProduto->quantidade * $compraProduto->preco_unitario) /
            ($estoqueAtual + $compraProduto->quantidade);
          $produtoModel->update(['custo_medio' => round($novoCustoMedio, 2)]);

          $compraProduto->restore();
        }

        return $compra;
      });

      $compra->load($this->relationships);
      return $this->successResponse($compra, 'Compra restaurada com sucesso e estoque/custo médio atualizados');
    } catch (QueryException $e) {
      return $this->handleDatabaseException($e);
    }
  }
}
