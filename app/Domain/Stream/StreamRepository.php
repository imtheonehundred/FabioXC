<?php

namespace App\Domain\Stream;

use App\Domain\Stream\Models\Stream;
use App\Domain\Stream\Models\StreamCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StreamRepository
{
    public function __construct(
        private Stream $model
    ) {}

    public function findById(int $id): ?Stream
    {
        return $this->model->with(['category', 'server', 'epg'])->find($id);
    }

    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->with(['category', 'server']);

        if (!empty($filters['search'])) {
            $query->where('stream_display_name', 'like', '%' . $filters['search'] . '%');
        }
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['sort'])) {
            $query->orderBy($filters['sort'], $filters['direction'] ?? 'asc');
        } else {
            $query->orderByDesc('id');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function getCategoriesForType(string $type): Collection
    {
        return StreamCategory::where('category_type', $type)->orderBy('category_name')->get(['id', 'category_name']);
    }

    public function create(array $data): Stream
    {
        $data['added'] = $data['added'] ?? now();
        return $this->model->create($data);
    }

    public function update(Stream $stream, array $data): bool
    {
        return $stream->update($data);
    }

    public function delete(Stream $stream): bool
    {
        return $stream->delete();
    }

    public function massDelete(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function massUpdate(array $ids, array $data): int
    {
        return $this->model->whereIn('id', $ids)->update($data);
    }
}
