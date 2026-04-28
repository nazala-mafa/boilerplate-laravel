<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Repositories\BaseRepository;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function getQuery(): Builder
    {
        $query = $this->model->query();
        return $query;
    }

    public function searchQuery(Builder $q, string $search): void
    {
        $q
            ->where('email', 'like', "%$search%")
            ->orWhere('name', 'like', "%$search%");
    }

    public function save(array $data):? User
    {
        DB::beginTransaction();
        try {
            $user = $this->model->updateOrCreate([
                'id' => @$data['id'],
            ], [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);
            DB::commit();
            return $user;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        
    }
}