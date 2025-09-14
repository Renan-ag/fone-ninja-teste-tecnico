<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class Controller
{
  protected $model;
  protected $validationRules = [];
  protected $relationships = [];

  public function __construct(Model $model)
  {
    $this->model = $model;
  }

  /**
   * Handle both database and model not found exceptions
   */
  protected function handleException(\Exception $e): JsonResponse
  {
    if ($e instanceof ModelNotFoundException) {
      return $this->errorResponse(
        ['not_found' => 'Recurso não encontrado'],
        404
      );
    }

    if ($e instanceof QueryException) {
      return $this->handleDatabaseException($e);
    }

    return $this->errorResponse(
      ['error' => $e->getMessage()],
      422
    );
  }

  /**
   * Handle database-specific exceptions
   */
  protected function handleDatabaseException(QueryException $e): JsonResponse
  {
    $errorCode = $e->errorInfo[1] ?? null;

    switch ($errorCode) {
      case 1062: // Duplicate entry
        return $this->errorResponse(
          ['duplicate' => 'A record with the same unique value already exists.'],
          409
        );

      case 1451: // FK constraint - delete
        return $this->errorResponse(
          ['constraint' => 'This record cannot be deleted because it is in use.'],
          409
        );

      case 1452: // FK constraint - insert/update
        return $this->errorResponse(
          ['constraint' => 'Invalid foreign key value.'],
          422
        );

      case 1048: // Column cannot be null
        return $this->errorResponse(
          ['null_violation' => 'A required field was not provided.'],
          422
        );

      case 1406: // Data too long
        return $this->errorResponse(
          ['too_long' => 'One of the provided fields exceeds the maximum allowed length.'],
          422
        );

      default:
        return $this->errorResponse(
          ['db_error' => 'An unexpected database error occurred.'],
          500
        );
    }
  }

  /**
   * Return a standardized error response
   */
  protected function errorResponse($errors, $status = 422): JsonResponse
  {
    return response()->json([
      'success' => false,
      'errors' => $errors,
      'message' => $status === 404 ? 'Recurso não encontrado' : 'Erro de validação ou solicitação inválida'
    ], $status);
  }

  /**
   * Return a standardized success response
   */
  protected function successResponse($data, $message = 'Operation successful', $status = 200): JsonResponse
  {
    $response = [
      'success' => true,
      'message' => $message
    ];

    if ($data !== null) {
      $response['data'] = $data;
    }

    return response()->json($response, $status);
  }

  /**
   * Get validation rules for update (making all fields optional)
   */
  protected function getUpdateRules(): array
  {
    $rules = [];
    foreach ($this->validationRules as $field => $rule) {
      $rules[$field] = 'sometimes|' . $rule;
    }
    return $rules;
  }

  /**
   * List all resources with pagination
   */
  public function index(Request $request): JsonResponse
  {
    $perPage = $request->input('per_page', 15);
    $query = $this->model::query()->with($this->relationships);

    $resources = $query->paginate($perPage);

    return $this->successResponse($resources, 'Recursos listados com sucesso');
  }

  /**
   * Show a specific resource
   */
  public function show($id): JsonResponse
  {
    try {
      $resource = $this->model::with($this->relationships)->findOrFail($id);
      return $this->successResponse($resource, 'Recurso recuperado com sucesso');
    } catch (\Exception $e) {
      return $this->handleException($e);
    }
  }

  /**
   * Update a specific resource
   */
  public function update(Request $request, $id): JsonResponse
  {
    try {
      $resource = $this->model::findOrFail($id);
      $resource->update($request->all());
      $resource->load($this->relationships);
      return $this->successResponse($resource, 'Recurso atualizado com sucesso');
    } catch (\Exception $e) {
      return $this->handleException($e);
    }
  }

  /**
   * Delete a resource (soft delete)
   */
  public function destroy($id): JsonResponse
  {
    try {
      $resource = $this->model::findOrFail($id);
      $resource->delete();
      return $this->successResponse(null, 'Recurso excluído com sucesso', 204);
    } catch (\Exception $e) {
      return $this->handleException($e);
    }
  }

  /**
   * Restore a soft-deleted resource
   */
  public function restore($id): JsonResponse
  {
    try {
      $resource = $this->model::withTrashed()->findOrFail($id);
      $resource->restore();
      $resource->load($this->relationships);
      return $this->successResponse($resource, 'Recurso restaurado com sucesso');
    } catch (\Exception $e) {
      return $this->handleException($e);
    }
  }
}
