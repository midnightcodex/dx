<?php

namespace App\Core\Crud;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CrudService
{
    public function __construct(private string $modelClass)
    {
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->modelClass::query();

        if ($this->hasColumn('organization_id')) {
            $query->where('organization_id', $organizationId);
        }

        return $query->latest()->paginate($perPage);
    }

    public function all(string $organizationId)
    {
        $query = $this->modelClass::query();
        if ($this->hasColumn('organization_id')) {
            $query->where('organization_id', $organizationId);
        }
        return $query->get();
    }

    public function find(string $organizationId, string $id): Model
    {
        $query = $this->modelClass::query();
        if ($this->hasColumn('organization_id')) {
            $query->where('organization_id', $organizationId);
        }
        return $query->findOrFail($id);
    }

    public function create(string $organizationId, string $userId, array $data): Model
    {
        if ($this->hasColumn('organization_id')) {
            $data['organization_id'] = $organizationId;
        }
        if ($this->hasColumn('created_by')) {
            $data['created_by'] = $userId;
        }

        return $this->modelClass::create($data);
    }

    public function update(Model $model, string $userId, array $data): Model
    {
        if ($this->hasColumn('updated_by')) {
            $data['updated_by'] = $userId;
        }
        $model->update($data);

        return $model->refresh();
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }

    private function hasColumn(string $column): bool
    {
        if (!class_exists($this->modelClass)) {
            return false;
        }

        $model = new $this->modelClass();
        if (!($model instanceof Model)) {
            return false;
        }

        $fillable = $model->getFillable();
        return in_array($column, $fillable, true);
    }
}
