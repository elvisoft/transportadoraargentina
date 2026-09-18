<?php

use App\Enums\TipoGasto;
use App\Models\Chofer;
use App\Models\Gasto;
use App\Models\Vehiculo;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Gastos')] class extends Component {
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $tipo = '';

    public string $monto = '';

    public string $fecha = '';

    public string $descripcion = '';

    public $vehiculo_id = null;

    public $chofer_id = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function crear(): void
    {
        $this->resetForm();
        $this->tipo = TipoGasto::Combustible->value;
        $this->fecha = now()->toDateString();
        Flux::modal('gasto-form')->show();
    }

    public function editar(int $gastoId): void
    {
        $gasto = Gasto::findOrFail($gastoId);

        $this->editingId = $gasto->id;
        $this->tipo = $gasto->tipo->value;
        $this->monto = (string) $gasto->monto;
        $this->fecha = $gasto->fecha->toDateString();
        $this->descripcion = (string) $gasto->descripcion;
        $this->vehiculo_id = $gasto->vehiculo_id;
        $this->chofer_id = $gasto->chofer_id;

        Flux::modal('gasto-form')->show();
    }

    public function guardar(): void
    {
        $validated = $this->validate([
            'tipo' => ['required', Rule::enum(TipoGasto::class)],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'fecha' => ['required', 'date'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'vehiculo_id' => ['nullable', 'required_without:chofer_id', 'exists:vehiculos,id'],
            'chofer_id' => ['nullable', 'required_without:vehiculo_id', 'exists:choferes,id'],
        ], [], [
            'vehiculo_id' => __('vehículo'),
            'chofer_id' => __('chofer'),
        ]);

        $validated['vehiculo_id'] = filled($validated['vehiculo_id']) ? (int) $validated['vehiculo_id'] : null;
        $validated['chofer_id'] = filled($validated['chofer_id']) ? (int) $validated['chofer_id'] : null;

        if ($this->editingId) {
            Gasto::findOrFail($this->editingId)->update($validated);
        } else {
            Gasto::create($validated);
        }

        Flux::modal('gasto-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: __('Gasto guardado correctamente.'));
    }

    public function eliminar(int $gastoId): void
    {
        Gasto::findOrFail($gastoId)->delete();

        Flux::toast(variant: 'success', text: __('Gasto eliminado.'));
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'tipo',
            'monto',
            'fecha',
            'descripcion',
            'vehiculo_id',
            'chofer_id',
        ]);
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'gastos' => Gasto::query()
                ->with(['vehiculo', 'chofer'])
                ->when($this->search, function ($query) {
                    $query->where(function ($query) {
                        $query->whereHas('vehiculo', fn ($q) => $q->where('patente', 'like', "%{$this->search}%"))
                            ->orWhereHas('chofer', fn ($q) => $q->where('apellido', 'like', "%{$this->search}%"));
                    });
                })
                ->orderByDesc('fecha')
                ->paginate(10),
            'vehiculosDisponibles' => Vehiculo::orderBy('patente')->get(['id', 'patente']),
            'choferesDisponibles' => Chofer::orderBy('apellido')->get(['id', 'nombre', 'apellido']),
        ];
    }
}; ?>

<section class="w-full">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Gastos') }}</flux:heading>
            <flux:subheading>{{ __('Registrá combustible, peajes, reparaciones y viáticos asociados a vehículos o choferes.') }}</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="crear">
            {{ __('Nuevo gasto') }}
        </flux:button>
    </div>

    <flux:input class="mt-6 max-w-sm" wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Buscar por patente o apellido del chofer...')" />

    <flux:table class="mt-4">
        <flux:table.columns>
            <flux:table.column>{{ __('Fecha') }}</flux:table.column>
            <flux:table.column>{{ __('Tipo') }}</flux:table.column>
            <flux:table.column>{{ __('Monto') }}</flux:table.column>
            <flux:table.column>{{ __('Vehículo') }}</flux:table.column>
            <flux:table.column>{{ __('Chofer') }}</flux:table.column>
            <flux:table.column>{{ __('Descripción') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($gastos as $gasto)
                <flux:table.row wire:key="gasto-{{ $gasto->id }}">
                    <flux:table.cell>{{ $gasto->fecha->format('d/m/Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="zinc" size="sm">{{ $gasto->tipo->label() }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>${{ number_format((float) $gasto->monto, 2, ',', '.') }}</flux:table.cell>
                    <flux:table.cell>{{ $gasto->vehiculo?->patente ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $gasto->chofer?->nombreCompleto() ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $gasto->descripcion ? \Illuminate\Support\Str::limit($gasto->descripcion, 40) : '—' }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex justify-end gap-2">
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editar({{ $gasto->id }})" />
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="eliminar({{ $gasto->id }})" wire:confirm="{{ __('¿Eliminar este gasto?') }}" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7">
                        <flux:text class="py-6 text-center">{{ __('No hay gastos cargados todavía.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $gastos->links() }}
    </div>

    <flux:modal name="gasto-form" class="max-w-lg">
        <form wire:submit="guardar" class="space-y-6">
            <flux:heading size="lg">{{ $editingId ? __('Editar gasto') : __('Nuevo gasto') }}</flux:heading>

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="tipo" :label="__('Tipo')">
                    @foreach (TipoGasto::cases() as $tipoOption)
                        <flux:select.option value="{{ $tipoOption->value }}">{{ $tipoOption->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="monto" type="number" step="0.01" min="0" :label="__('Monto')" />
            </div>

            <flux:input wire:model="fecha" type="date" :label="__('Fecha')" />

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="vehiculo_id" :label="__('Vehículo')">
                    <flux:select.option value="">{{ __('Sin vehículo') }}</flux:select.option>
                    @foreach ($vehiculosDisponibles as $vehiculoOption)
                        <flux:select.option value="{{ $vehiculoOption->id }}">{{ $vehiculoOption->patente }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model="chofer_id" :label="__('Chofer')">
                    <flux:select.option value="">{{ __('Sin chofer') }}</flux:select.option>
                    @foreach ($choferesDisponibles as $choferOption)
                        <flux:select.option value="{{ $choferOption->id }}">{{ $choferOption->nombreCompleto() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <flux:text class="text-sm">{{ __('Seleccioná al menos un vehículo o un chofer.') }}</flux:text>

            <flux:textarea wire:model="descripcion" :label="__('Descripción')" rows="3" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">{{ __('Guardar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
