<?php

use App\Enums\EstadoChofer;
use App\Models\Chofer;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Choferes')] class extends Component {
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $nombre = '';

    public string $apellido = '';

    public string $dni = '';

    public ?string $fecha_nacimiento = null;

    public string $telefono = '';

    public string $email = '';

    public string $domicilio = '';

    public string $licencia_numero = '';

    public string $licencia_categoria = '';

    public string $licencia_vencimiento = '';

    public string $estado = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function crear(): void
    {
        $this->resetForm();
        $this->estado = EstadoChofer::Activo->value;
        Flux::modal('chofer-form')->show();
    }

    public function editar(int $choferId): void
    {
        $chofer = Chofer::findOrFail($choferId);

        $this->editingId = $chofer->id;
        $this->nombre = $chofer->nombre;
        $this->apellido = $chofer->apellido;
        $this->dni = $chofer->dni;
        $this->fecha_nacimiento = $chofer->fecha_nacimiento?->toDateString();
        $this->telefono = (string) $chofer->telefono;
        $this->email = (string) $chofer->email;
        $this->domicilio = (string) $chofer->domicilio;
        $this->licencia_numero = $chofer->licencia_numero;
        $this->licencia_categoria = $chofer->licencia_categoria;
        $this->licencia_vencimiento = $chofer->licencia_vencimiento->toDateString();
        $this->estado = $chofer->estado->value;

        Flux::modal('chofer-form')->show();
    }

    public function guardar(): void
    {
        $validated = $this->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'dni' => ['required', 'string', 'max:20', Rule::unique('choferes', 'dni')->ignore($this->editingId)],
            'fecha_nacimiento' => ['nullable', 'date'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'domicilio' => ['nullable', 'string', 'max:255'],
            'licencia_numero' => ['required', 'string', 'max:20'],
            'licencia_categoria' => ['required', 'string', 'max:10'],
            'licencia_vencimiento' => ['required', 'date'],
            'estado' => ['required', Rule::enum(EstadoChofer::class)],
        ]);

        if ($this->editingId) {
            Chofer::findOrFail($this->editingId)->update($validated);
        } else {
            Chofer::create($validated);
        }

        Flux::modal('chofer-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: __('Chofer guardado correctamente.'));
    }

    public function eliminar(int $choferId): void
    {
        Chofer::findOrFail($choferId)->delete();

        Flux::toast(variant: 'success', text: __('Chofer eliminado.'));
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
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
        ]);
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'choferes' => Chofer::query()
                ->when($this->search, function ($query) {
                    $query->where(function ($query) {
                        $query->where('nombre', 'like', "%{$this->search}%")
                            ->orWhere('apellido', 'like', "%{$this->search}%")
                            ->orWhere('dni', 'like', "%{$this->search}%");
                    });
                })
                ->orderBy('apellido')
                ->paginate(10),
        ];
    }
}; ?>

<section class="w-full">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Choferes') }}</flux:heading>
            <flux:subheading>{{ __('Gestioná los choferes de la flota y el vencimiento de sus licencias.') }}</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="crear">
            {{ __('Nuevo chofer') }}
        </flux:button>
    </div>

    <flux:input class="mt-6 max-w-sm" wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Buscar por nombre, apellido o DNI...')" />

    <flux:table class="mt-4">
        <flux:table.columns>
            <flux:table.column>{{ __('Nombre') }}</flux:table.column>
            <flux:table.column>{{ __('DNI') }}</flux:table.column>
            <flux:table.column>{{ __('Licencia') }}</flux:table.column>
            <flux:table.column>{{ __('Vencimiento') }}</flux:table.column>
            <flux:table.column>{{ __('Estado') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($choferes as $chofer)
                <flux:table.row wire:key="chofer-{{ $chofer->id }}">
                    <flux:table.cell>{{ $chofer->nombreCompleto() }}</flux:table.cell>
                    <flux:table.cell>{{ $chofer->dni }}</flux:table.cell>
                    <flux:table.cell>{{ $chofer->licencia_categoria }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$chofer->licenciaVencida() ? 'red' : ($chofer->licenciaPorVencer() ? 'amber' : 'green')" size="sm">
                            {{ $chofer->licencia_vencimiento->format('d/m/Y') }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$chofer->estado->value === 'activo' ? 'green' : 'zinc'" size="sm">
                            {{ $chofer->estado->label() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex justify-end gap-2">
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editar({{ $chofer->id }})" />
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="eliminar({{ $chofer->id }})" wire:confirm="{{ __('¿Eliminar este chofer?') }}" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">
                        <flux:text class="py-6 text-center">{{ __('No hay choferes cargados todavía.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $choferes->links() }}
    </div>

    <flux:modal name="chofer-form" class="max-w-lg">
        <form wire:submit="guardar" class="space-y-6">
            <flux:heading size="lg">{{ $editingId ? __('Editar chofer') : __('Nuevo chofer') }}</flux:heading>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="nombre" :label="__('Nombre')" />
                <flux:input wire:model="apellido" :label="__('Apellido')" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="dni" :label="__('DNI')" />
                <flux:input wire:model="fecha_nacimiento" type="date" :label="__('Fecha de nacimiento')" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="telefono" :label="__('Teléfono')" />
                <flux:input wire:model="email" type="email" :label="__('Email')" />
            </div>

            <flux:input wire:model="domicilio" :label="__('Domicilio')" />

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="licencia_numero" :label="__('N° de licencia')" />
                <flux:input wire:model="licencia_categoria" :label="__('Categoría (LiNTI)')" placeholder="B1, C2, D3..." />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="licencia_vencimiento" type="date" :label="__('Vencimiento de licencia')" />
                <flux:select wire:model="estado" :label="__('Estado')">
                    @foreach (EstadoChofer::cases() as $estadoOption)
                        <flux:select.option value="{{ $estadoOption->value }}">{{ $estadoOption->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
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
