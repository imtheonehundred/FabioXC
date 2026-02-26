<?php

namespace App\Domain\User;

use App\Models\User;
use App\Domain\User\Models\MemberGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    public function __construct(
        private User $model
    ) {}

    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->with('group');
        if (!empty($filters['search'])) {
            $query->where('username', 'like', '%' . $filters['search'] . '%')
                ->orWhere('email', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['sort'])) {
            $query->orderBy($filters['sort'], $filters['direction'] ?? 'asc');
        } else {
            $query->orderByDesc('id');
        }
        return $query->paginate($perPage)->withQueryString();
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function getGroups(): Collection
    {
        return MemberGroup::orderBy('group_name')->get(['id', 'group_name']);
    }
}
