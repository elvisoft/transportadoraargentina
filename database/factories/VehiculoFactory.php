<?php

namespace Database\Factories;

use App\Enums\EstadoVehiculo;
use App\Enums\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehiculo>
 */
class VehiculoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patente' => strtoupper(fake()->bothify('??###??')),
            'marca' => fake()->randomElement(['Mercedes-Benz', 'Scania', 'Iveco', 'Volkswagen', 'Ford']),
            'modelo' => fake()->word(),
            'anio' => fake()->numberBetween(2005, 2026),
            'tipo' => fake()->randomElement(TipoVehiculo::cases()),
            'chofer_id' => null,
            'rto_vencimiento' => fake()->dateTimeBetween('-1 year', '+2 years'),
            'seguro_compania' => fake()->randomElement(['Sancor Seguros', 'Federación Patronal', 'La Segunda', 'Zurich']),
            'seguro_vencimiento' => fake()->dateTimeBetween('-1 year', '+1 year'),
            'estado' => EstadoVehiculo::Activo,
        ];
    }

    /**
     * Indicate that the vehicle's RTO/VTV has expired.
     */
    public function conRtoVencido(): static
    {
        return $this->state(fn (array $attributes) => [
            'rto_vencimiento' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }
}
