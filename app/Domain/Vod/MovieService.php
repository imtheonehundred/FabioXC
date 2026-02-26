<?php

namespace App\Domain\Vod;

use App\Domain\Vod\Models\Movie;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class MovieService
{
    public function __construct(
        private MovieRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function getCategories(): Collection
    {
        return $this->repository->getCategoriesForMovie();
    }

    public function create(array $data): Movie
    {
        $data['added'] = now();
        return $this->repository->create($data);
    }

    public function update(Movie $movie, array $data): bool
    {
        return $this->repository->update($movie, $data);
    }

    public function delete(Movie $movie): bool
    {
        return $this->repository->delete($movie);
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
