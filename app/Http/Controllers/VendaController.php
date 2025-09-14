<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\VendaProduto;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class VendaController extends Controller
{
  protected $validationRules = [
    'cliente' => 'required|string|max:255',
    'total' => 'required|numeric|min:0',
    'status' => 'required|string|in:pendente,concluida,cancelada',
    'produtos' => 'required|array|min:1',
    'produtos.*.produto_id' => 'required|exists:produtos,id',
    'produtos.*.quantidade' => 'required|integer|min:1',
    'produtos.*.preco_unitario' => 'required|numeric|min:0',
  ];

  protected $relationships = ['vendaProdutos.produto'];

  public function __construct()
  {
    parent::__construct(new Venda());
  }

  public function store(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), $this->validationRules);

    if ($validator->fails()) {
      return $this->errorResponse($validator->errors(), 422);
    }

    try {
      $venda = DB::transaction(function () use ($request) {
        $lucroTotal = 0;

        // Calcula o lucro total com base nos produtos
        foreach ($request->produtos as $produto) {
          $produtoModel = Produto::findOrFail($produto['produto_id']);
          if ($produtoModel->estoque < $produto['quantidade']) {
            throw new \Exception("Estoque insuficiente para o produto ID {$produto['produto_id']}");
          }
          $custoMedio = $produtoModel->custo_medio ?? 0;
          $lucroTotal += ($produto['preco_unitario'] - $custoMedio) * $produto['quantidade'];
        }

        $venda = $this->model::create([
          'cliente' => $request->cliente,
          'total' => $request->total,
          'lucro' => round($lucroTotal, 2),
          'status' => $request->status,
        ]);

        foreach ($request->produtos as $produto) {
          VendaProduto::create([
            'venda_id' => $venda->id,
            'produto_id' => $produto['produto_id'],
            'quantidade' => $produto['quantidade'],
            'preco_unitario' => $produto['preco_unitario'],
          ]);

          // Atualiza o estoque
          $produtoModel = Produto::findOrFail($produto['produto_id']);
          $produtoModel->decrement('estoque', $produto['quantidade']);
        }

        return $venda;
      });

      $venda->load($this->relationships);
      return $this->successResponse($venda, 'Recurso criado com sucesso', 201);
    } catch (\Exception $e) {
      return $this->errorResponse(['error' => $e->getMessage()], 422);
    } catch (QueryException $e) {
      return $this->handleDatabaseException($e);
    }
  }

  public function update(Request $request, $id): JsonResponse
  {
    $venda = $this->model::findOrFail($id);

    $rules = $this->getUpdateRules();
    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return $this->errorResponse($validator->errors(), 422);
    }

    try {
      $venda = DB::transaction(function () use ($venda, $request) {
        // Reverter o estoque dos produtos antigos
        foreach ($venda->vendaProdutos as $vendaProduto) {
          $produtoModel = Produto::findOrFail($vendaProduto->produto_id);
          $produtoModel->increment('estoque', $vendaProduto->quantidade);
        }

        // Excluir os produtos antigos
        $venda->vendaProdutos()->delete();

        $lucroTotal = 0;

        // Calcula o lucro total e verifica estoque para os novos produtos
        if ($request->has('produtos')) {
          foreach ($request->produtos as $produto) {
            $produtoModel = Produto::findOrFail($produto['produto_id']);
            if ($produtoModel->estoque < $produto['quantidade']) {
              throw new \Exception("Estoque insuficiente para o produto ID {$produto['produto_id']}");
            }
            $custoMedio = $produtoModel->custo_medio ?? 0;
            $lucroTotal += ($produto['preco_unitario'] - $custoMedio) * $produto['quantidade'];
          }
        }

        $venda->update([
          'cliente' => $request->input('cliente', $venda->cliente),
          'total' => $request->input('total', $venda->total),
          'lucro' => $request->has('produtos') ? round($lucroTotal, 2) : $venda->lucro,
          'status' => $request->input('status', $venda->status),
        ]);

        if ($request->has('produtos')) {
          foreach ($request->produtos as $produto) {
            VendaProduto::create([
              'venda_id' => $venda->id,
              'produto_id' => $produto['produto_id'],
              'quantidade' => $produto['quantidade'],
              'preco_unitario' => $produto['preco_unitario'],
            ]);

            $produtoModel = Produto::findOrFail($produto['produto_id']);
            $produtoModel->decrement('estoque', $produto['quantidade']);
          }
        }

        return $venda;
      });

      $venda->load($this->relationships);
      return $this->successResponse($venda, 'Recurso atualizado com sucesso');
    } catch (\Exception $e) {
      return $this->errorResponse(['error' => $e->getMessage()], 422);
    } catch (QueryException $e) {
      return $this->handleDatabaseException($e);
    }
  }

  public function destroy($id): JsonResponse
  {
    $venda = $this->model::findOrFail($id);

    try {
      DB::transaction(function () use ($venda) {
        foreach ($venda->vendaProdutos as $vendaProduto) {
          $produtoModel = Produto::findOrFail($vendaProduto->produto_id);
          $produtoModel->increment('estoque', $vendaProduto->quantidade);
        }

        $venda->delete();
      });

      return $this->successResponse(null, 'Venda excluída com sucesso e estoque atualizado', 204);
    } catch (QueryException $e) {
      return $this->handleDatabaseException($e);
    }
  }

  public function restore($id): JsonResponse
  {
    $venda = $this->model::withTrashed()->findOrFail($id);

    try {
      $venda = DB::transaction(function () use ($venda) {
        $venda->restore();

        foreach ($venda->vendaProdutos()->withTrashed()->get() as $vendaProduto) {
          $produtoModel = Produto::findOrFail($vendaProduto->produto_id);
          if ($produtoModel->estoque < $vendaProduto->quantidade) {
            throw new \Exception("Estoque insuficiente para restaurar o produto ID {$vendaProduto->produto_id}");
          }

          $produtoModel->decrement('estoque', $vendaProduto->quantidade);
          $vendaProduto->restore();
        }

        return $venda;
      });

      $venda->load($this->relationships);
      return $this->successResponse($venda, 'Venda restaurada com sucesso e estoque atualizado');
    } catch (\Exception $e) {
      return $this->errorResponse(['error' => $e->getMessage()], 422);
    } catch (QueryException $e) {
      return $this->handleDatabaseException($e);
    }
  }
}
