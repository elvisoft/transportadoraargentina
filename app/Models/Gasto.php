<?php

namespace App\Models;

use App\Enums\TipoGasto;
use Database\Factories\GastoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property TipoGasto $tipo
 * @property float $monto
 * @property Carbon $fecha
 * @property string|null $descripcion
 * @property int|null $vehiculo_id
 * @property int|null $chofer_id
 */
#[Fillable([
    'tipo',
    'monto',
    'fecha',
    'descripcion',
    'vehiculo_id',
    'chofer_id',
])]
class Gasto extends Model
{
    /** @use HasFactory<GastoFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tipo' => TipoGasto::class,
            'monto' => 'decimal:2',
            'fecha' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Vehiculo, $this>
     */
    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class);
    }

    /**
     * @return BelongsTo<Chofer, $this>
     */
    public function chofer(): BelongsTo
    {
        return $this->belongsTo(Chofer::class);
    }
}
