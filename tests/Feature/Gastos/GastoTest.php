<?php

use App\Enums\TipoGasto;
use App\Models\Chofer;
use App\Models\Gasto;
use App\Models\User;
use App\Models\Vehiculo;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $this->get(route('gastos.index'))->assertRedirect(route('login'));
});

test('authenticated users can view the gastos page', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('gastos.index'))->assertOk();
});

test('a gasto can be created for a vehiculo', function () {
    $this->actingAs(User::factory()->create());
    $vehiculo = Vehiculo::factory()->create();

    Livewire::test('pages::gastos.index')
        ->call('crear')
        ->set('tipo', TipoGasto::Combustible->value)
        ->set('monto', '15000.50')
        ->set('fecha', now()->toDateString())
        ->set('vehiculo_id', $vehiculo->id)
        ->call('guardar')
        ->assertHasNoErrors();

    $gasto = Gasto::first();

    expect($gasto)->not->toBeNull();
    expect($gasto->vehiculo_id)->toBe($vehiculo->id);
    expect($gasto->chofer_id)->toBeNull();
});

test('a gasto can be created for a chofer', function () {
    $this->actingAs(User::factory()->create());
    $chofer = Chofer::factory()->create();

    Livewire::test('pages::gastos.index')
        ->call('crear')
        ->set('tipo', TipoGasto::Viatico->value)
        ->set('monto', '8000')
        ->set('fecha', now()->toDateString())
        ->set('chofer_id', $chofer->id)
        ->call('guardar')
        ->assertHasNoErrors();

    $gasto = Gasto::first();

    expect($gasto)->not->toBeNull();
    expect($gasto->chofer_id)->toBe($chofer->id);
    expect($gasto->vehiculo_id)->toBeNull();
});

test('a gasto requires at least a vehiculo or a chofer', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::gastos.index')
        ->call('crear')
        ->set('tipo', TipoGasto::Peaje->value)
        ->set('monto', '500')
        ->set('fecha', now()->toDateString())
        ->call('guardar')
        ->assertHasErrors(['vehiculo_id', 'chofer_id']);

    expect(Gasto::count())->toBe(0);
});

test('required fields are validated when creating a gasto', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::gastos.index')
        ->call('crear')
        ->set('tipo', '')
        ->set('monto', '')
        ->set('fecha', '')
        ->call('guardar')
        ->assertHasErrors([
            'tipo' => 'required',
            'monto' => 'required',
            'fecha' => 'required',
        ]);
});

test('a gasto can be updated', function () {
    $this->actingAs(User::factory()->create());

    $gasto = Gasto::factory()->create(['monto' => 1000]);

    Livewire::test('pages::gastos.index')
        ->call('editar', $gasto->id)
        ->set('monto', '2500')
        ->call('guardar')
        ->assertHasNoErrors();

    expect((float) $gasto->fresh()->monto)->toBe(2500.0);
});

test('a gasto can be deleted', function () {
    $this->actingAs(User::factory()->create());

    $gasto = Gasto::factory()->create();

    Livewire::test('pages::gastos.index')
        ->call('eliminar', $gasto->id);

    expect(Gasto::find($gasto->id))->toBeNull();
});
