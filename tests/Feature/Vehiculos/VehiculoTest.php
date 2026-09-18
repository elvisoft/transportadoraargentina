<?php

use App\Enums\EstadoVehiculo;
use App\Enums\TipoVehiculo;
use App\Models\Chofer;
use App\Models\User;
use App\Models\Vehiculo;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $this->get(route('vehiculos.index'))->assertRedirect(route('login'));
});

test('authenticated users can view the vehiculos page', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('vehiculos.index'))->assertOk();
});

test('a vehiculo can be created', function () {
    $this->actingAs(User::factory()->create());
    $chofer = Chofer::factory()->create();

    Livewire::test('pages::vehiculos.index')
        ->call('crear')
        ->set('patente', 'AB123CD')
        ->set('marca', 'Scania')
        ->set('modelo', 'R450')
        ->set('anio', 2022)
        ->set('tipo', TipoVehiculo::Camion->value)
        ->set('chofer_id', $chofer->id)
        ->set('estado', EstadoVehiculo::Activo->value)
        ->call('guardar')
        ->assertHasNoErrors();

    $vehiculo = Vehiculo::where('patente', 'AB123CD')->first();

    expect($vehiculo)->not->toBeNull();
    expect($vehiculo->chofer_id)->toBe($chofer->id);
});

test('a vehiculo can be created without an assigned chofer', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::vehiculos.index')
        ->call('crear')
        ->set('patente', 'AB123CD')
        ->set('marca', 'Scania')
        ->set('modelo', 'R450')
        ->set('tipo', TipoVehiculo::Camion->value)
        ->set('chofer_id', '')
        ->set('estado', EstadoVehiculo::Activo->value)
        ->call('guardar')
        ->assertHasNoErrors();

    $vehiculo = Vehiculo::where('patente', 'AB123CD')->first();

    expect($vehiculo)->not->toBeNull();
    expect($vehiculo->chofer_id)->toBeNull();
});

test('required fields are validated when creating a vehiculo', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::vehiculos.index')
        ->call('crear')
        ->set('estado', '')
        ->call('guardar')
        ->assertHasErrors([
            'patente' => 'required',
            'marca' => 'required',
            'modelo' => 'required',
            'tipo' => 'required',
            'estado' => 'required',
        ]);
});

test('patente must be unique', function () {
    $this->actingAs(User::factory()->create());

    Vehiculo::factory()->create(['patente' => 'AB123CD']);

    Livewire::test('pages::vehiculos.index')
        ->call('crear')
        ->set('patente', 'AB123CD')
        ->set('marca', 'Scania')
        ->set('modelo', 'R450')
        ->set('tipo', TipoVehiculo::Camion->value)
        ->set('estado', EstadoVehiculo::Activo->value)
        ->call('guardar')
        ->assertHasErrors(['patente' => 'unique']);
});

test('a vehiculo can be updated', function () {
    $this->actingAs(User::factory()->create());

    $vehiculo = Vehiculo::factory()->create(['marca' => 'Ford']);

    Livewire::test('pages::vehiculos.index')
        ->call('editar', $vehiculo->id)
        ->set('marca', 'Iveco')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($vehiculo->fresh()->marca)->toBe('Iveco');
});

test('a vehiculo can be deleted', function () {
    $this->actingAs(User::factory()->create());

    $vehiculo = Vehiculo::factory()->create();

    Livewire::test('pages::vehiculos.index')
        ->call('eliminar', $vehiculo->id);

    expect(Vehiculo::find($vehiculo->id))->toBeNull();
});
