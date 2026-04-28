<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Repositories\Contracts\BaseRepositoryInterface;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;
    protected ?Builder $query = null;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Return a query builder
     */
    public function getQuery(): Builder
    {
        $query = $this->query ?? $this->model->newQuery();

        return $query;
    }

    /**
     * Apply eager loads to a query builder.
     */
    public function applyWith(Builder $query, array|string|null $with): Builder
    {
        if (empty($with)) {
            return $query;
        }

        return $query->with($with);
    }

    public function searchQuery(Builder $q, string $search): void
    {
        // eg: $q->where('strain.nama', 'LIKE', "%{$search}%")->orWhere('kandang.nama', 'LIKE', "%{$search}%")
    }

    public function customWhereQuery(): array
    {
        // eg: ['nama_strain' => fn($q, $namaStrain) => $q->where('strain.nama', '=', $namaStrain)],
        return [];
    }

    /**
     * Summary of wheresQuery
     * @param Builder $q
     * @param ?Collection $wheres
     * @return void
     */
    public function wheresQuery(Builder $q, ?Collection $wheres = null): void
    {
        $customWhereQueryKeys = array_keys($this->customWhereQuery());

        $wheres->each(function($value, $column) use($q, $customWhereQueryKeys) {
            if ($value === null) return;
            if (in_array($column, $customWhereQueryKeys)) {
                $q->when($value, $this->customWhereQuery()[$column]);
            } else {
                $q->where($column, '=', $value);
            }
        });
    }

    public function defaultOrder(Builder $q): void
    {
        // order default by latest updated data
        $tableName = $this->model->getTable();
        $q->orderBy("$tableName.updated_at", 'desc');
        $q->orderBy("$tableName.id", 'desc');
    }

    /**
     * Paginate records, optionally eager loading relations.
     * avoid to use 'with relation' in pagination data
     * @param ?Collection $wheres eg: ['nama_strain' => 'Isa']
     */
    public function paginate(
        $withPath = '/user',
        ?string $search = null,
        ?Collection $wheres = null,
        ?Collection $orders = null,
        int $perPage = 15,
        array $columns = ['*'],
    ): LengthAwarePaginator 
    {
        $q = $this->getQuery();

        if ($search) {
            $this->searchQuery($q, $search);
        }

        if ($wheres) {
            $this->wheresQuery($q, $wheres);
        }

        if ($orders?->count()) {
            foreach ($orders as $column => $direction) {
                $q->orderBy($column, $direction);
            }
        } else {
            $this->defaultOrder($q);
        }

        return $q->paginate($perPage, $columns)
            ->withQueryString()
            ->onEachSide(3)
            ->withPath($withPath);
    }

    public function getSelectItems(string $column = 'nama'): array
    {
        return $this->getModel()->orderBy($column)->pluck($column, 'id')->toArray();
    }
}
