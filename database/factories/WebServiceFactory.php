<?php

namespace Database\Factories;

use App\Models\WebService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebService>
 */
class WebServiceFactory extends Factory
{
    /**
     * The model's factory associations.
     *
     * @var list<Factory>
     */
    protected static ?array $container = [];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = strtoupper(fake()->unique()->bothify('WS_????##'));

        return [
            'code' => $code,
            'name' => fake()->sentence(4),
            'description' => fake()->sentence(8),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the Web Service is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Use one of the four known initial Web Services.
     *
     * @param  string  $code  WS_CHECK_CIN | WS_PROFILE | WS_CV | WS_BILAN
     */
    public function withCode(string $code): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => $code,
        ]);
    }
}
