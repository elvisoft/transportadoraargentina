<?php

namespace Database\Factories;

use App\Enums\TipoGasto;
use App\Models\Chofer;
use App\Models\Gasto;
use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gasto>
 */
class GastoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipo' => fake()->randomElement(TipoGasto::cases()),
            'monto' => fake()->randomFloat(2, 1000, 500000),
            'fecha' => fake()->dateTimeBetween('-6 months', 'now'),
            'descripcion' => fake()->optional()->sentence(),
            'vehiculo_id' => Vehiculo::factory(),
            'chofer_id' => null,
        ];
    }

    /**
     * Associate the expense with a driver instead of a vehicle.
     */
    public function deChofer(): static
    {
        return $this->state(fn (array $attributes) => [
            'vehiculo_id' => null,
            'chofer_id' => Chofer::factory(),
        ]);
    }
}
