<?php

declare(strict_types = 1);

namespace Centrex\Crm\Models;

use Centrex\Crm\Concerns\AddTablePrefix;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use AddTablePrefix;

    protected function getTableSuffix(): string
    {
        return 'tags';
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setConnection(config('crm.drivers.database.connection', config('database.default')));
    }

    protected $fillable = [
        'name',
        'slug',
        'color',
    ];

    public function companies(): MorphToMany
    {
        return $this->morphedByMany(Company::class, 'taggable', $this->getTaggableTable());
    }

    public function contacts(): MorphToMany
    {
        return $this->morphedByMany(Contact::class, 'taggable', $this->getTaggableTable());
    }

    public function leads(): MorphToMany
    {
        return $this->morphedByMany(Lead::class, 'taggable', $this->getTaggableTable());
    }

    public function deals(): MorphToMany
    {
        return $this->morphedByMany(Deal::class, 'taggable', $this->getTaggableTable());
    }

    private function getTaggableTable(): string
    {
        return config('crm.table_prefix', 'crm_') . 'taggables';
    }
}
