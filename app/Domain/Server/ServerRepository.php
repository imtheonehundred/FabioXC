<?php

namespace App\Domain\Server;

use App\Domain\Server\Models\Server;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ServerRepository
{
    public function __construct(
        private Server $model
    ) {}

    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->withCount('streams');
        if (!empty($filters['search'])) {
            $query->where('server_name', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['sort'])) {
            $query->orderBy($filters['sort'], $filters['direction'] ?? 'asc');
        } else {
            $query->orderByDesc('is_main')->orderBy('server_name');
        }
        return $query->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Server
    {
        return $this->model->create($data);
    }

    public function update(Server $server, array $data): bool
    {
        return $server->update($data);
    }

    public function delete(Server $server): bool
    {
        return $server->delete();
    }
}
