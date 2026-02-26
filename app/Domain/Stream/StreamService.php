<?php

namespace App\Domain\Stream;

use App\Domain\Stream\Models\Stream;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StreamService
{
    public function __construct(
        private StreamRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function getCategoriesForLive(): Collection
    {
        return $this->repository->getCategoriesForType('live');
    }

    public function create(array $data): Stream
    {
        $data['added'] = now();
        return $this->repository->create($data);
    }

    public function update(Stream $stream, array $data): bool
    {
        return $this->repository->update($stream, $data);
    }

    public function delete(Stream $stream): bool
    {
        return $this->repository->delete($stream);
    }

    public function massAction(string $action, array $ids): int
    {
        return match ($action) {
            'delete' => $this->repository->massDelete($ids),
            'enable' => $this->repository->massUpdate($ids, ['admin_enabled' => 1]),
            'disable' => $this->repository->massUpdate($ids, ['admin_enabled' => 0]),
            default => 0,
        };
    }
}
