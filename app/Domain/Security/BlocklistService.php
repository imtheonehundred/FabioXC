<?php

namespace App\Domain\Security;

use App\Domain\Security\Models\BlockedIp;
use App\Domain\Security\Models\BlockedIsp;
use App\Domain\Security\Models\BlockedUa;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BlocklistService
{
    public function __construct(
        private BlocklistRepository $repository
    ) {}

    public function listBlockedIps(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->getBlockedIpsPaginated($filters, $perPage);
    }

    public function blockIp(array $data): BlockedIp
    {
        return $this->repository->createBlockedIp($data);
    }

    public function unblockIp(BlockedIp $ip): bool
    {
        return $this->repository->deleteBlockedIp($ip);
    }

    public function listBlockedIsps(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->getBlockedIspsPaginated($filters, $perPage);
    }

    public function blockIsp(array $data): BlockedIsp
    {
        return $this->repository->createBlockedIsp($data);
    }

    public function unblockIsp(BlockedIsp $isp): bool
    {
        return $this->repository->deleteBlockedIsp($isp);
    }

    public function listBlockedUas(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->getBlockedUasPaginated($filters, $perPage);
    }

    public function blockUa(array $data): BlockedUa
    {
        return $this->repository->createBlockedUa($data);
    }

    public function unblockUa(BlockedUa $ua): bool
    {
        return $this->repository->deleteBlockedUa($ua);
    }
}
