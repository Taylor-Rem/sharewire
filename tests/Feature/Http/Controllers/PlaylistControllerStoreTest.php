<?php

declare(strict_types=1);

use App\Models\Playlist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to the login screen', function (): void {
    $this->post(route('playlists.store'), ['name' => 'Workout'])->assertRedirect('/login');
    expect(Playlist::where('name', 'Workout')->exists())->toBeFalse();
});

it('lets a user create a new non-primary playlist and flashes a toast', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->from(route('playlists.index'))
        ->post(route('playlists.store'), ['name' => 'Workout']);

    $response->assertRedirect(route('playlists.index'));
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Created playlist "Workout".',
    ]);

    $playlist = Playlist::where('name', 'Workout')->firstOrFail();
    expect($playlist->user_id)->toBe($user->id)
        ->and($playlist->is_primary)->toBeFalse();
});

it('rejects an empty name with a validation error', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('playlists.index'))
        ->post(route('playlists.store'), ['name' => ''])
        ->assertSessionHasErrors('name');

    // The primary playlist auto-created by UserObserver is the only one.
    expect(Playlist::count())->toBe(1);
});

it('rejects a missing name with a validation error', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('playlists.index'))
        ->post(route('playlists.store'), [])
        ->assertSessionHasErrors('name');
});

it('rejects a name longer than 120 characters', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('playlists.index'))
        ->post(route('playlists.store'), ['name' => str_repeat('a', 121)])
        ->assertSessionHasErrors('name');
});
