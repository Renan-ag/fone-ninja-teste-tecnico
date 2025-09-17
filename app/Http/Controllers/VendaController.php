<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\VendaProduto;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class VendaController extends Controller
{
  protected $validationRules = [
    'cliente' => 'required|string|max:255',
    'total' => 'required|numeric|min:0',
    'status' => 'required|string|in:pendente,concluída,cancelada',
    'produtos' => 'required|array|min:1',
    'produtos.*.produto_id' => 'required|exists:produtos,id',
    'produtos.*.quantidade' => 'required|integer|min:1',
    'produtos.*.preco_unitario' => 'required|numeric|min:0',
    'data_venda' => 'required|date',
  ];

  protected $customMessages = [
    'cliente.required' => 'O cliente é obrigatório',
    'total.required' => 'O total é obrigatório',
    'status.required' => 'O status é obrigatório',
    'produtos.required' => 'Os produtos são obrigatórios',
    'produtos.*.produto_id.required' => 'O produto é obrigatório',
    'produtos.*.quantidade.required' => 'A quantidade é obrigatória',
    'produtos.*.preco_unitario.required' => 'O preço unitário é obrigatório',
    'data_venda.required' => 'A data da venda é obrigatória',
    'data_venda.date' => 'A data da venda deve ser uma data válida',
  ];

  protected $relationships = ['vendaProdutos.produto'];

  public function __construct()
  {
    parent::__construct(new Venda());
  }

  public function store(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), $this->validationRules, $this->customMessages);

    if ($validator->fails()) {
      return $this->errorResponse($validator->errors(), 422);
    }

    try {
      $venda = DB::transaction(function () use ($request) {
        $lucroTotal = 0;

        // Verifica estoque e calcula lucro
        foreach ($request->produtos as $index => $produto) {
          $produtoModel = Produto::findOrFail($produto['produto_id']);

          if ($produtoModel->estoque < $produto['quantidade']) {
            throw ValidationException::withMessages([
              "produtos.{$index}.quantidade" => [
                "Estoque insuficiente, disponível {$produtoModel->estoque}"
              ],
            ]);
          }

          $custoMedio = $produtoModel->custo_medio ?? 0;
          $lucroTotal += ((float) ($produto['preco_unitario'] ?? 0) - (float) $custoMedio) * (int) $produto['quantidade'];
        }

        // Cria venda        
        $venda = $this->model::create([
          'cliente' => $request->cliente,
          'total'   => $request->total,
          'lucro'   => round($lucroTotal, 2),
          'status'  => $request->status,
          'data_venda' => $request->data_venda,
        ]);

        // Cria itens da venda e decrementa estoque
        foreach ($request->produtos as $produto) {
          VendaProduto::create([
            'venda_id'       => $venda->id,
            'produto_id'     => $produto['produto_id'],
            'quantidade'     => $produto['quantidade'],
            'preco_unitario' => $produto['preco_unitario'],
          ]);

          $produtoModel = Produto::findOrFail($produto['produto_id']);
          $produtoModel->decrement('estoque', $produto['quantidade']);
        }

        return $venda;
      });

      $venda->load($this->relationships);
      return $this->successResponse($venda, 'Recurso criado com sucesso', 201);
    } catch (ValidationException $e) {
      return $this->errorResponse($e->errors(), 422);
    } catch (QueryException $e) {
      return $this->handleDatabaseException($e);
    } catch (\Exception $e) {
      return $this->errorResponse(['error' => $e->getMessage()], 422);
    }
  }

  public function update(Request $request, $id): JsonResponse
  {
    $venda = $this->model::findOrFail($id);
    $rules = $this->getUpdateRules();
    $validator = Validator::make($request->all(), $rules, $this->customMessages);

    if ($validator->fails()) {
      return $this->errorResponse($validator->errors(), 422);
    }

    try {
      $venda = DB::transaction(function () use ($venda, $request) {
        foreach ($venda->vendaProdutos as $vendaProduto) {
          $produtoModel = Produto::findOrFail($vendaProduto->produto_id);
          $produtoModel->increment('estoque', $vendaProduto->quantidade);
        }

        $venda->vendaProdutos()->withTrashed()->forceDelete();

        $lucroTotal = 0;
        $produtoIds = [];
        if ($request->has('produtos')) {
          foreach ($request->produtos as $index => $produto) {
            $produtoId = $produto['produto_id'];
            if (in_array($produtoId, $produtoIds)) {
              throw new \Exception("Produto ID {$produtoId} duplicado na solicitação");
            }

            $produtoIds[] = $produtoId;

            $produtoModel = Produto::findOrFail($produtoId);

            if ($produtoModel->estoque < $produto['quantidade']) {
              throw ValidationException::withMessages([
                "produtos.{$index}.quantidade" => [
                  "Estoque insuficiente, disponível {$produtoModel->estoque}"
                ],
              ]);
            }
            $custoMedio = $produtoModel->custo_medio ?? 0;
            $lucroTotal += ($produto['preco_unitario'] - $custoMedio) * $produto['quantidade'];
          }
        }

        // Update venda
        $venda->update([
          'cliente' => $request->input('cliente', $venda->cliente),
          'total' => $request->input('total', $venda->total),
          'lucro' => $request->has('produtos') ? round($lucroTotal, 2) : $venda->lucro,
          'status' => $request->input('status', $venda->status),
          'data_venda' => $request->input('data_venda', $venda->data_venda),
        ]);

        // Create new VendaProduto records
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
    } catch (ValidationException $e) {
      return $this->errorResponse($e->errors(), 422);
    } catch (QueryException $e) {
      return $this->handleDatabaseException($e);
    } catch (\Exception $e) {
      return $this->errorResponse(['error' => $e->getMessage()], 422);
    }
  }

  public function destroy($id): JsonResponse
  {
    $venda = $this->model::findOrFail($id);

    try {
      DB::transaction(function () use ($venda) {
        foreach ($venda->vendaProdutos as $vendaProduto) {
          $produtoModel = Produto::find($vendaProduto->produto_id);
          if ($produtoModel) {
            $produtoModel->increment('estoque', $vendaProduto->quantidade);
          }
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
