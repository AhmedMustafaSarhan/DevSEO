<?php

namespace App\Repositories\Contracts;

use App\Models\BlogPost;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

interface BlogPostRepositoryInterface
{
    public function all(): Collection;
    
    public function paginate(int $perPage = 15): Paginator;
    
    public function findBySlug(string $slug): ?BlogPost;
    
    public function findById(string $id): ?BlogPost;
    
    public function create(array $data): BlogPost;
    
    public function update(string $id, array $data): bool;
    
    public function delete(string $id): bool;
    
    public function published(): Collection;
    
    public function byRegion(string $region): Collection;
    
    public function withRelations(array $relations = []): self;
}
