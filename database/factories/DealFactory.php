<?php

declare(strict_types = 1);

namespace Centrex\Crm\Database\Factories;

use Centrex\Crm\Enums\DealStage;
use Centrex\Crm\Models\Deal;
use Illuminate\Database\Eloquent\Factories\Factory;

class DealFactory extends Factory
{
    protected $model = Deal::class;

    public function definition(): array
    {
        return [
            'code'                => 'DEAL-' . strtoupper($this->faker->unique()->lexify('????????')),
            'lead_id'             => null,
            'company_id'          => null,
            'contact_id'          => null,
            'name'                => $this->faker->sentence(3),
            'stage'               => DealStage::Qualified->value,
            'amount'              => $this->faker->randomFloat(2, 10000, 1000000),
            'currency'            => 'BDT',
            'probability'         => 20,
            'expected_close_date' => $this->faker->dateTimeBetween('+1 month', '+6 months'),
            'owner_id'            => null,
            'notes'               => null,
            'meta'                => null,
        ];
    }

    public function won(): static
    {
        return $this->state([
            'stage'       => DealStage::Won->value,
            'probability' => 100,
            'won_at'      => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function lost(): static
    {
        return $this->state([
            'stage'       => DealStage::Lost->value,
            'probability' => 0,
            'lost_at'     => now(),
        ]);
    }

    public function inProposal(): static
    {
        return $this->state([
            'stage'       => DealStage::Proposal->value,
            'probability' => 40,
        ]);
    }

    public function inNegotiation(): static
    {
        return $this->state([
            'stage'       => DealStage::Negotiation->value,
            'probability' => 70,
        ]);
    }
}
