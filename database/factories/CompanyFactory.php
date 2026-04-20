<?php

declare(strict_types = 1);

namespace Centrex\Crm\Database\Factories;

use Centrex\Crm\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'code'      => 'COMP-' . strtoupper($this->faker->unique()->lexify('????????')),
            'name'      => $this->faker->company(),
            'email'     => $this->faker->companyEmail(),
            'phone'     => $this->faker->phoneNumber(),
            'website'   => $this->faker->url(),
            'industry'  => $this->faker->randomElement(['Technology', 'Finance', 'Healthcare', 'Retail', 'Manufacturing', 'Education']),
            'owner_id'  => null,
            'is_active' => true,
            'meta'      => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
