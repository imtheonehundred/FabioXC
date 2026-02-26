<?php

namespace App\Domain\Server;

use App\Domain\Server\Models\Server;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ServerService
{
    public function __construct(
        private ServerRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function create(array $data): Server
    {
        return $this->repository->create($data);
    }

    public function update(Server $server, array $data): bool
    {
        return $this->repository->update($server, $data);
    }

    public function delete(Server $server): bool
    {
        return $this->repository->delete($server);
    }
}
