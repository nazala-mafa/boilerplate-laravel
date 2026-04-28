<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface BaseRepositoryInterface
{
    public function getQuery(): Builder;

    public function searchQuery(Builder $q, string $search): void;

    public function customWhereQuery(): array;

    public function wheresQuery(Builder $q, ?Collection $wheres = null): void;

    public function defaultOrder(Builder $q): void;

    public function paginate(
        string $withPath,
        ?string $search = null,
        ?Collection $wheres = null,
        ?Collection $orders = null,
        int $perPage = 15,
        array $columns = ['*'],
    ): LengthAwarePaginator;

    public function getSelectItems(string $column = 'nama'): array;

    public function save(array $data): ?Model;
}
