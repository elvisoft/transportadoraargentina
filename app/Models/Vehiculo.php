<?php

namespace App\Models;

use App\Enums\EstadoVehiculo;
use App\Enums\TipoVehiculo;
use Database\Factories\VehiculoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $patente
 * @property string $marca
 * @property string $modelo
 * @property int|null $anio
 * @property TipoVehiculo $tipo
 * @property int|null $chofer_id
 * @property Carbon|null $rto_vencimiento
 * @property string|null $seguro_compania
 * @property Carbon|null $seguro_vencimiento
 * @property EstadoVehiculo $estado
 */
#[Fillable([
    'patente',
    'marca',
    'modelo',
    'anio',
    'tipo',
    'chofer_id',
    'rto_vencimiento',
    'seguro_compania',
    'seguro_vencimiento',
    'estado',
])]
class Vehiculo extends Model
{
    /** @use HasFactory<VehiculoFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'tipo' => TipoVehiculo::class,
            'rto_vencimiento' => 'date',
            'seguro_vencimiento' => 'date',
            'estado' => EstadoVehiculo::class,
        ];
    }

    /**
     * @return BelongsTo<Chofer, $this>
     */
    public function chofer(): BelongsTo
    {
        return $this->belongsTo(Chofer::class);
    }

    /**
     * @return HasMany<Gasto, $this>
     */
    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class);
    }

    public function rtoVencido(): bool
    {
        return (bool) $this->rto_vencimiento?->isPast();
    }

    public function seguroVencido(): bool
    {
        return (bool) $this->seguro_vencimiento?->isPast();
    }
}
