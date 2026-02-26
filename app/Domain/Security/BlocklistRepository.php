<?php

namespace App\Domain\Security;

use App\Domain\Security\Models\BlockedIp;
use App\Domain\Security\Models\BlockedIsp;
use App\Domain\Security\Models\BlockedUa;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BlocklistRepository
{
    public function getBlockedIpsPaginated(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = BlockedIp::query();
        if (!empty($filters['search'])) {
            $query->where('ip_address', 'like', '%' . $filters['search'] . '%');
        }
        return $query->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    public function createBlockedIp(array $data): BlockedIp
    {
        return BlockedIp::create($data);
    }

    public function deleteBlockedIp(BlockedIp $ip): bool
    {
        return $ip->delete();
    }

    public function getBlockedIspsPaginated(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = BlockedIsp::query();
        if (!empty($filters['search'])) {
            $query->where('isp_name', 'like', '%' . $filters['search'] . '%');
        }
        return $query->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    public function createBlockedIsp(array $data): BlockedIsp
    {
        return BlockedIsp::create($data);
    }

    public function deleteBlockedIsp(BlockedIsp $isp): bool
    {
        return $isp->delete();
    }

    public function getBlockedUasPaginated(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = BlockedUa::query();
        if (!empty($filters['search'])) {
            $query->where('user_agent', 'like', '%' . $filters['search'] . '%');
        }
        return $query->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    public function createBlockedUa(array $data): BlockedUa
    {
        return BlockedUa::create($data);
    }

    public function deleteBlockedUa(BlockedUa $ua): bool
    {
        return $ua->delete();
    }
}
