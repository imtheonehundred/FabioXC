<?php

namespace App\Domain\Line;

use App\Domain\Line\Models\Line;
use App\Domain\Line\Models\Package;
use App\Domain\Bouquet\Models\Bouquet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class LineRepository
{
    public function __construct(
        private Line $model
    ) {}

    public function findById(int $id): ?Line
    {
        return $this->model->with('packages')->find($id);
    }

    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->with('packages');

        if (!empty($filters['search'])) {
            $query->where('username', 'like', '%' . $filters['search'] . '%');
        }
        if (isset($filters['status'])) {
            match ($filters['status']) {
                'active' => $query->where('admin_enabled', 1)->where(fn ($q) => $q->whereNull('exp_date')->orWhere('exp_date', '>', now())),
                'expired' => $query->where('exp_date', '<', now()),
                'disabled' => $query->where('admin_enabled', 0),
                default => null,
            };
        }
        if (!empty($filters['sort'])) {
            $query->orderBy($filters['sort'], $filters['direction'] ?? 'asc');
        } else {
            $query->orderByDesc('id');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Line
    {
        $data['added'] = $data['added'] ?? now();
        return $this->model->create($data);
    }

    public function update(Line $line, array $data): bool
    {
        return $line->update($data);
    }

    public function delete(Line $line): bool
    {
        return $line->delete();
    }

    public function massDelete(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function massUpdate(array $ids, array $data): int
    {
        return $this->model->whereIn('id', $ids)->update($data);
    }

    public function getPackagesList(): Collection
    {
        return Package::orderBy('package_name')->get(['id', 'package_name', 'is_trial']);
    }

    public function getBouquetsList(): Collection
    {
        return Bouquet::orderBy('bouquet_name')->get(['id', 'bouquet_name']);
    }
}
