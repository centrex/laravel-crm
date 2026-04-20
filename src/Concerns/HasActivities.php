<?php

declare(strict_types = 1);

namespace Centrex\Crm\Concerns;

use Centrex\Crm\Models\Activity;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasActivities
{
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function pendingActivities(): MorphMany
    {
        return $this->activities()->whereNull('completed_at');
    }

    public function completedActivities(): MorphMany
    {
        return $this->activities()->whereNotNull('completed_at');
    }
}
