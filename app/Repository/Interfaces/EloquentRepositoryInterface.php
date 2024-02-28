<?php

namespace App\Repository\Interfaces;

use Illuminate\Database\Eloquent\Model;

/**
 * Interface EloquentRepositoryInterface
 */
interface EloquentRepositoryInterface
{
    /**
     * @param  array  $attributes
     * @return Model
     */
    public function create(array $attributes): Model;

    /**
     * @param $id
     * @return Model
     */
    public function find($id): ?Model;
}
