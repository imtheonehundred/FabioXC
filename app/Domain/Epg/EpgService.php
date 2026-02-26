<?php

namespace App\Domain\Epg;

use App\Domain\Epg\Models\Epg;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EpgService
{
    public function __construct(
        private EpgRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function create(array $data): Epg
    {
        return $this->repository->create($data);
    }

    public function update(Epg $epg, array $data): bool
    {
        return $this->repository->update($epg, $data);
    }

    public function delete(Epg $epg): bool
    {
        return $this->repository->delete($epg);
    }
}
