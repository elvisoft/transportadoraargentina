<?php

use App\Enums\EstadoChofer;
use App\Models\Chofer;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $this->get(route('choferes.index'))->assertRedirect(route('login'));
});

test('authenticated users can view the choferes page', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('choferes.index'))->assertOk();
});

test('a chofer can be created', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::choferes.index')
        ->call('crear')
        ->set('nombre', 'Juan')
        ->set('apellido', 'Perez')
        ->set('dni', '30123456')
        ->set('licencia_numero', '12345678')
        ->set('licencia_categoria', 'C2')
        ->set('licencia_vencimiento', now()->addYear()->toDateString())
        ->set('estado', EstadoChofer::Activo->value)
        ->call('guardar')
        ->assertHasNoErrors();

    expect(Chofer::where('dni', '30123456')->exists())->toBeTrue();
});

test('required fields are validated when creating a chofer', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::choferes.index')
        ->call('crear')
        ->call('guardar')
        ->assertHasErrors([
            'nombre' => 'required',
            'apellido' => 'required',
            'dni' => 'required',
            'licencia_numero' => 'required',
            'licencia_categoria' => 'required',
            'licencia_vencimiento' => 'required',
        ]);
});

test('dni must be unique', function () {
    $this->actingAs(User::factory()->create());

    Chofer::factory()->create(['dni' => '30123456']);

    Livewire::test('pages::choferes.index')
        ->call('crear')
        ->set('nombre', 'Juan')
        ->set('apellido', 'Perez')
        ->set('dni', '30123456')
        ->set('licencia_numero', '12345678')
        ->set('licencia_categoria', 'C2')
        ->set('licencia_vencimiento', now()->addYear()->toDateString())
        ->set('estado', EstadoChofer::Activo->value)
        ->call('guardar')
        ->assertHasErrors(['dni' => 'unique']);
});

test('a chofer can be updated', function () {
    $this->actingAs(User::factory()->create());

    $chofer = Chofer::factory()->create(['nombre' => 'Juan']);

    Livewire::test('pages::choferes.index')
        ->call('editar', $chofer->id)
        ->set('nombre', 'Carlos')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($chofer->fresh()->nombre)->toBe('Carlos');
});

test('editing a chofer does not trigger the unique rule against itself', function () {
    $this->actingAs(User::factory()->create());

    $chofer = Chofer::factory()->create(['dni' => '30123456']);

    Livewire::test('pages::choferes.index')
        ->call('editar', $chofer->id)
        ->set('dni', '30123456')
        ->call('guardar')
        ->assertHasNoErrors();
});

test('a chofer can be deleted', function () {
    $this->actingAs(User::factory()->create());

    $chofer = Chofer::factory()->create();

    Livewire::test('pages::choferes.index')
        ->call('eliminar', $chofer->id);

    expect(Chofer::find($chofer->id))->toBeNull();
});
