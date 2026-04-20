<?php

declare(strict_types = 1);

namespace Centrex\Crm\Database\Factories;

use Centrex\Crm\Enums\{ActivityPriority, ActivityType};
use Centrex\Crm\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'type'         => $this->faker->randomElement(ActivityType::cases())->value,
            'priority'     => ActivityPriority::Normal->value,
            'summary'      => $this->faker->sentence(),
            'description'  => $this->faker->optional()->paragraph(),
            'due_at'       => $this->faker->dateTimeBetween('now', '+2 weeks'),
            'completed_at' => null,
            'owner_id'     => null,
            'meta'         => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(['completed_at' => now()]);
    }

    public function overdue(): static
    {
        return $this->state([
            'due_at'       => $this->faker->dateTimeBetween('-2 weeks', '-1 day'),
            'completed_at' => null,
        ]);
    }

    public function urgent(): static
    {
        return $this->state(['priority' => ActivityPriority::Urgent->value]);
    }
}
