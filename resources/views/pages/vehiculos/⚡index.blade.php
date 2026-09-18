<?php

use App\Enums\EstadoVehiculo;
use App\Enums\TipoVehiculo;
use App\Models\Chofer;
use App\Models\Vehiculo;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Vehículos')] class extends Component {
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $patente = '';

    public string $marca = '';

    public string $modelo = '';

    public $anio = null;

    public string $tipo = '';

    public $chofer_id = null;

    public ?string $rto_vencimiento = null;

    public string $seguro_compania = '';

    public ?string $seguro_vencimiento = null;

    public string $estado = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function crear(): void
    {
        $this->resetForm();
        $this->estado = EstadoVehiculo::Activo->value;
        Flux::modal('vehiculo-form')->show();
    }

    public function editar(int $vehiculoId): void
    {
        $vehiculo = Vehiculo::findOrFail($vehiculoId);

        $this->editingId = $vehiculo->id;
        $this->patente = $vehiculo->patente;
        $this->marca = $vehiculo->marca;
        $this->modelo = $vehiculo->modelo;
        $this->anio = $vehiculo->anio;
        $this->tipo = $vehiculo->tipo->value;
        $this->chofer_id = $vehiculo->chofer_id;
        $this->rto_vencimiento = $vehiculo->rto_vencimiento?->toDateString();
        $this->seguro_compania = (string) $vehiculo->seguro_compania;
        $this->seguro_vencimiento = $vehiculo->seguro_vencimiento?->toDateString();
        $this->estado = $vehiculo->estado->value;

        Flux::modal('vehiculo-form')->show();
    }

    public function guardar(): void
    {
        $validated = $this->validate([
            'patente' => ['required', 'string', 'max:10', Rule::unique('vehiculos', 'patente')->ignore($this->editingId)],
            'marca' => ['required', 'string', 'max:100'],
            'modelo' => ['required', 'string', 'max:100'],
            'anio' => ['nullable', 'integer', 'min:1970', 'max:'.(now()->year + 1)],
            'tipo' => ['required', Rule::enum(TipoVehiculo::class)],
            'chofer_id' => ['nullable', 'exists:choferes,id'],
            'rto_vencimiento' => ['nullable', 'date'],
            'seguro_compania' => ['nullable', 'string', 'max:100'],
            'seguro_vencimiento' => ['nullable', 'date'],
            'estado' => ['required', Rule::enum(EstadoVehiculo::class)],
        ]);

        $validated['anio'] = filled($validated['anio']) ? (int) $validated['anio'] : null;
        $validated['chofer_id'] = filled($validated['chofer_id']) ? (int) $validated['chofer_id'] : null;

        if ($this->editingId) {
            Vehiculo::findOrFail($this->editingId)->update($validated);
        } else {
            Vehiculo::create($validated);
        }

        Flux::modal('vehiculo-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: __('Vehículo guardado correctamente.'));
    }

    public function eliminar(int $vehiculoId): void
    {
        Vehiculo::findOrFail($vehiculoId)->delete();

        Flux::toast(variant: 'success', text: __('Vehículo eliminado.'));
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
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
        ]);
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'vehiculos' => Vehiculo::query()
                ->with('chofer')
                ->when($this->search, function ($query) {
                    $query->where(function ($query) {
                        $query->where('patente', 'like', "%{$this->search}%")
                            ->orWhere('marca', 'like', "%{$this->search}%")
                            ->orWhere('modelo', 'like', "%{$this->search}%");
                    });
                })
                ->orderBy('patente')
                ->paginate(10),
            'choferes' => Chofer::orderBy('apellido')->get(['id', 'nombre', 'apellido']),
        ];
    }
}; ?>

<section class="w-full">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Vehículos') }}</flux:heading>
            <flux:subheading>{{ __('Gestioná la flota, su RTO/VTV, seguro y chofer asignado.') }}</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="crear">
            {{ __('Nuevo vehículo') }}
        </flux:button>
    </div>

    <flux:input class="mt-6 max-w-sm" wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Buscar por patente, marca o modelo...')" />

    <flux:table class="mt-4">
        <flux:table.columns>
            <flux:table.column>{{ __('Patente') }}</flux:table.column>
            <flux:table.column>{{ __('Vehículo') }}</flux:table.column>
            <flux:table.column>{{ __('Tipo') }}</flux:table.column>
            <flux:table.column>{{ __('Chofer asignado') }}</flux:table.column>
            <flux:table.column>{{ __('RTO/VTV') }}</flux:table.column>
            <flux:table.column>{{ __('Seguro') }}</flux:table.column>
            <flux:table.column>{{ __('Estado') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($vehiculos as $vehiculo)
                <flux:table.row wire:key="vehiculo-{{ $vehiculo->id }}">
                    <flux:table.cell>{{ $vehiculo->patente }}</flux:table.cell>
                    <flux:table.cell>{{ $vehiculo->marca }} {{ $vehiculo->modelo }} @if($vehiculo->anio)({{ $vehiculo->anio }})@endif</flux:table.cell>
                    <flux:table.cell>{{ $vehiculo->tipo->label() }}</flux:table.cell>
                    <flux:table.cell>{{ $vehiculo->chofer?->nombreCompleto() ?? '—' }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($vehiculo->rto_vencimiento)
                            <flux:badge :color="$vehiculo->rtoVencido() ? 'red' : 'green'" size="sm">
                                {{ $vehiculo->rto_vencimiento->format('d/m/Y') }}
                            </flux:badge>
                        @else
                            —
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($vehiculo->seguro_vencimiento)
                            <flux:badge :color="$vehiculo->seguroVencido() ? 'red' : 'green'" size="sm">
                                {{ $vehiculo->seguro_vencimiento->format('d/m/Y') }}
                            </flux:badge>
                        @else
                            —
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$vehiculo->estado->value === 'activo' ? 'green' : ($vehiculo->estado->value === 'mantenimiento' ? 'amber' : 'zinc')" size="sm">
                            {{ $vehiculo->estado->label() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex justify-end gap-2">
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editar({{ $vehiculo->id }})" />
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="eliminar({{ $vehiculo->id }})" wire:confirm="{{ __('¿Eliminar este vehículo?') }}" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8">
                        <flux:text class="py-6 text-center">{{ __('No hay vehículos cargados todavía.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $vehiculos->links() }}
    </div>

    <flux:modal name="vehiculo-form" class="max-w-lg">
        <form wire:submit="guardar" class="space-y-6">
            <flux:heading size="lg">{{ $editingId ? __('Editar vehículo') : __('Nuevo vehículo') }}</flux:heading>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="patente" :label="__('Patente')" />
                <flux:select wire:model="tipo" :label="__('Tipo')">
                    @foreach (TipoVehiculo::cases() as $tipoOption)
                        <flux:select.option value="{{ $tipoOption->value }}">{{ $tipoOption->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="marca" :label="__('Marca')" />
                <flux:input wire:model="modelo" :label="__('Modelo')" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="anio" type="number" :label="__('Año')" />
                <flux:select wire:model="chofer_id" :label="__('Chofer asignado')">
                    <flux:select.option value="">{{ __('Sin asignar') }}</flux:select.option>
                    @foreach ($choferes as $choferOption)
                        <flux:select.option value="{{ $choferOption->id }}">{{ $choferOption->nombreCompleto() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="rto_vencimiento" type="date" :label="__('Vencimiento RTO/VTV')" />
                <flux:select wire:model="estado" :label="__('Estado')">
                    @foreach (EstadoVehiculo::cases() as $estadoOption)
                        <flux:select.option value="{{ $estadoOption->value }}">{{ $estadoOption->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="seguro_compania" :label="__('Compañía de seguro')" />
                <flux:input wire:model="seguro_vencimiento" type="date" :label="__('Vencimiento de seguro')" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">{{ __('Guardar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
