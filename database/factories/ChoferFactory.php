<?php

namespace Database\Factories;

use App\Enums\EstadoChofer;
use App\Models\Chofer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chofer>
 */
class ChoferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'dni' => fake()->unique()->numerify('########'),
            'fecha_nacimiento' => fake()->dateTimeBetween('-60 years', '-20 years'),
            'telefono' => fake()->numerify('11 #### ####'),
            'email' => fake()->unique()->safeEmail(),
            'domicilio' => fake()->streetAddress(),
            'licencia_numero' => fake()->numerify('########'),
            'licencia_categoria' => fake()->randomElement(['B1', 'B2', 'C1', 'C2', 'C3', 'D1', 'D2', 'D3', 'E1']),
            'licencia_vencimiento' => fake()->dateTimeBetween('-1 year', '+3 years'),
            'estado' => EstadoChofer::Activo,
        ];
    }

    /**
     * Indicate that the driver's license has expired.
     */
    public function conLicenciaVencida(): static
    {
        return $this->state(fn (array $attributes) => [
            'licencia_vencimiento' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    /**
     * Indicate that the driver is inactive.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => EstadoChofer::Inactivo,
        ]);
    }
}
