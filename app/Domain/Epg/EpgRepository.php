<?php

namespace App\Domain\Epg;

use App\Domain\Epg\Models\Epg;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EpgRepository
{
    public function __construct(
        private Epg $model
    ) {}

    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->query();
        if (!empty($filters['search'])) {
            $query->where('epg_name', 'like', '%' . $filters['search'] . '%');
        }
        return $query->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Epg
    {
        return $this->model->create($data);
    }

    public function update(Epg $epg, array $data): bool
    {
        return $epg->update($data);
    }

    public function delete(Epg $epg): bool
    {
        return $epg->delete();
    }
}
