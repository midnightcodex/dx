<?php

namespace App\Modules\Compliance\Services;

use App\Core\Crud\CrudService;
use App\Modules\Compliance\Models\Document;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DocumentService
{
    private CrudService $crud;

    public function __construct()
    {
        $this->crud = new CrudService(Document::class);
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->crud->list($organizationId, $perPage);
    }

    public function find(string $organizationId, string $id): Document
    {
        return $this->crud->find($organizationId, $id);
    }

    public function create(string $organizationId, string $userId, array $data): Document
    {
        return $this->crud->create($organizationId, $userId, $data);
    }

    public function update(Document $document, string $userId, array $data): Document
    {
        return $this->crud->update($document, $userId, $data);
    }

    public function delete(Document $document): void
    {
        $this->crud->delete($document);
    }
}
