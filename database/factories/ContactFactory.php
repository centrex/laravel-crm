<?php

declare(strict_types = 1);

namespace Centrex\Crm\Database\Factories;

use Centrex\Crm\Models\{Company, Contact};
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'company_id' => null,
            'first_name' => $this->faker->firstName(),
            'last_name'  => $this->faker->lastName(),
            'email'      => $this->faker->unique()->safeEmail(),
            'phone'      => $this->faker->phoneNumber(),
            'job_title'  => $this->faker->jobTitle(),
            'owner_id'   => null,
            'is_primary' => false,
            'meta'       => null,
        ];
    }

    public function forCompany(Company $company): static
    {
        return $this->state(['company_id' => $company->id]);
    }

    public function primary(): static
    {
        return $this->state(['is_primary' => true]);
    }
}
