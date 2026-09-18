<?php

namespace App\Models;

use App\Enums\EstadoChofer;
use Database\Factories\ChoferFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nombre
 * @property string $apellido
 * @property string $dni
 * @property Carbon|null $fecha_nacimiento
 * @property string|null $telefono
 * @property string|null $email
 * @property string|null $domicilio
 * @property string $licencia_numero
 * @property string $licencia_categoria
 * @property Carbon $licencia_vencimiento
 * @property EstadoChofer $estado
 */
#[Fillable([
    'nombre',
    'apellido',
    'dni',
    'fecha_nacimiento',
    'telefono',
    'email',
    'domicilio',
    'licencia_numero',
    'licencia_categoria',
    'licencia_vencimiento',
    'estado',
])]
class Chofer extends Model
{
    /** @use HasFactory<ChoferFactory> */
    use HasFactory;

    protected $table = 'choferes';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'licencia_vencimiento' => 'date',
            'estado' => EstadoChofer::class,
        ];
    }

    /**
     * @return HasMany<Vehiculo, $this>
     */
    public function vehiculosAsignados(): HasMany
    {
        return $this->hasMany(Vehiculo::class);
    }

    /**
     * @return HasMany<Gasto, $this>
     */
    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class);
    }

    public function nombreCompleto(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }

    public function licenciaVencida(): bool
    {
        return $this->licencia_vencimiento->isPast();
    }

    public function licenciaPorVencer(int $dias = 30): bool
    {
        return ! $this->licenciaVencida()
            && $this->licencia_vencimiento->lte(now()->addDays($dias));
    }
}
