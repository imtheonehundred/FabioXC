<?php

namespace App\Domain\Bouquet;

use App\Domain\Bouquet\Models\Bouquet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BouquetService
{
    public function __construct(
        private BouquetRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function getStreams(): Collection
    {
        return $this->repository->getStreamsForBouquet();
    }

    public function getMovies(): Collection
    {
        return $this->repository->getMoviesForBouquet();
    }

    public function getSeries(): Collection
    {
        return $this->repository->getSeriesForBouquet();
    }

    public function create(array $data): Bouquet
    {
        return $this->repository->create($data);
    }

    public function update(Bouquet $bouquet, array $data): bool
    {
        return $this->repository->update($bouquet, $data);
    }

    public function delete(Bouquet $bouquet): bool
    {
        return $this->repository->delete($bouquet);
    }
}
