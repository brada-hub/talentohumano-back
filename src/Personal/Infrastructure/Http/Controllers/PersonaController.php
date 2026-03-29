<?php

namespace Src\Personal\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Personal\Application\Create\CreatePersonaCommand;
use Src\Personal\Application\Create\CreatePersonaHandler;
use Src\Personal\Domain\Repositories\PersonaRepositoryInterface;
use Src\Personal\Infrastructure\Http\Requests\CreatePersonaRequest;
use Src\Shared\Domain\ValueObjects\UuidVO;
use Src\Shared\Infrastructure\Http\ApiResponse;

final class PersonaController
{
    public function __construct(
        private readonly CreatePersonaHandler $createHandler,
        private readonly PersonaRepositoryInterface $repo,
    ) {}

    public function index(): JsonResponse
    {
        $page    = (int) request('page', 1);
        $perPage = (int) request('per_page', 15);
        $result  = $this->repo->findAll($page, $perPage);

        return ApiResponse::paginate($result['data'], $result['total'], $result['page'], $perPage);
    }

    public function store(CreatePersonaRequest $request): JsonResponse
    {
        $id = $this->createHandler->handle(new CreatePersonaCommand(...$request->validated()));
        return ApiResponse::created(['id' => $id]);
    }

    public function show(string $id): JsonResponse
    {
        $persona = $this->repo->findById(new UuidVO($id));
        if (!$persona) return ApiResponse::notFound('Persona no encontrada');
        return ApiResponse::success($persona->toArray());
    }
}
