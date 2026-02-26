<?php

namespace App\Domain\Line;

use App\Domain\Line\Models\Line;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class LineService
{
    public function __construct(
        private LineRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function getPackages(): Collection
    {
        return $this->repository->getPackagesList();
    }

    public function getBouquets(): Collection
    {
        return $this->repository->getBouquetsList();
    }

    public function create(array $data, array $packageIds = []): Line
    {
        unset($data['package_ids']);
        $data['added'] = now();
        $data['created_by'] = $data['created_by'] ?? auth()->id();
        $line = $this->repository->create($data);
        if (!empty($packageIds)) {
            $line->packages()->attach($packageIds);
        }
        return $line;
    }

    public function update(Line $line, array $data, array $packageIds = []): bool
    {
        unset($data['package_ids']);
        $this->repository->update($line, $data);
        $line->packages()->sync($packageIds);
        return true;
    }

    public function delete(Line $line): bool
    {
        return $this->repository->delete($line);
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
