<?php

namespace App\Repositories\Eloquent;

use App\Models\BlogPost;
use App\Repositories\Contracts\BlogPostRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;

class BlogPostRepository implements BlogPostRepositoryInterface
{
    private array $relations = [];

    public function __construct(private BlogPost $model)
    {
    }

    public function all(): Collection
    {
        return $this->getQuery()->get();
    }

    public function paginate(int $perPage = 15): Paginator
    {
        return $this->getQuery()->paginate($perPage);
    }

    public function findBySlug(string $slug): ?BlogPost
    {
        return $this->getQuery()
            ->where('slug', $slug)
            ->first();
    }

    public function findById(string $id): ?BlogPost
    {
        return $this->getQuery()
            ->where('id', $id)
            ->first();
    }

    public function create(array $data): BlogPost
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $post = $this->findById($id);
        return $post?->update($data) ?? false;
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findById($id)?->delete();
    }

    public function published(): Collection
    {
        return $this->getQuery()
            ->published()
            ->get();
    }

    public function byRegion(string $region): Collection
    {
        return $this->getQuery()
            ->byRegion($region)
            ->get();
    }

    public function withRelations(array $relations = []): self
    {
        $this->relations = $relations;
        return $this;
    }

    private function getQuery()
    {
        $query = $this->model->query();

        if (!empty($this->relations)) {
            $query = $query->with($this->relations);
        }

        return $query;
    }
}
