<?php

declare(strict_types = 1);

namespace Centrex\Crm\Database\Factories;

use Centrex\Crm\Enums\{LeadSource, LeadStatus};
use Centrex\Crm\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'code'        => 'LEAD-' . strtoupper($this->faker->unique()->lexify('????????')),
            'company_id'  => null,
            'contact_id'  => null,
            'title'       => $this->faker->sentence(4),
            'source'      => $this->faker->randomElement(LeadSource::cases())->value,
            'value'       => $this->faker->randomFloat(2, 5000, 500000),
            'currency'    => 'BDT',
            'status'      => LeadStatus::Open->value,
            'probability' => $this->faker->numberBetween(5, 30),
            'score'       => 0,
            'owner_id'    => null,
            'notes'       => null,
            'meta'        => null,
        ];
    }

    public function qualified(): static
    {
        return $this->state([
            'status'       => LeadStatus::Qualified->value,
            'probability'  => $this->faker->numberBetween(30, 70),
            'qualified_at' => now(),
        ]);
    }

    public function lost(): static
    {
        return $this->state([
            'status'  => LeadStatus::Lost->value,
            'lost_at' => now(),
        ]);
    }
}
