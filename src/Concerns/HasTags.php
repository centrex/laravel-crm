<?php

declare(strict_types = 1);

namespace Centrex\Crm\Concerns;

use Centrex\Crm\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

trait HasTags
{
    public function tags(): MorphToMany
    {
        $taggableTable = config('crm.table_prefix', 'crm_') . 'taggables';

        return $this->morphToMany(Tag::class, 'taggable', $taggableTable);
    }

    public function attachTag(string|Tag $tag): void
    {
        $tag = $tag instanceof Tag ? $tag : Tag::query()->firstOrCreate(
            ['slug' => Str::slug($tag)],
            ['name' => $tag, 'slug' => Str::slug($tag)],
        );

        $this->tags()->syncWithoutDetaching([$tag->id]);
    }

    public function detachTag(string|Tag $tag): void
    {
        if (!$tag instanceof Tag) {
            $tag = Tag::query()->where('slug', Str::slug($tag))->first();

            if ($tag === null) {
                return;
            }
        }

        $this->tags()->detach($tag->id);
    }

    public function syncTags(array $tags): void
    {
        $ids = collect($tags)->map(function (string|Tag $tag): int {
            if ($tag instanceof Tag) {
                return $tag->id;
            }

            return Tag::query()->firstOrCreate(
                ['slug' => Str::slug($tag)],
                ['name' => $tag, 'slug' => Str::slug($tag)],
            )->id;
        })->all();

        $this->tags()->sync($ids);
    }
}
