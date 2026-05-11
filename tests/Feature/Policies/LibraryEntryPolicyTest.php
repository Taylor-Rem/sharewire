<?php

declare(strict_types=1);

use App\Models\PlaylistSong;
use App\Models\User;
use App\Policies\LibraryEntryPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->policy = new LibraryEntryPolicy;
    $this->owner = User::factory()->create();
    $this->stranger = User::factory()->create();
    $this->entry = PlaylistSong::factory()->create(['user_id' => $this->owner->id]);
});

it('lets the owner view their entry', function (): void {
    expect($this->policy->view($this->owner, $this->entry))->toBeTrue();
});

it('forbids strangers from viewing an entry', function (): void {
    expect($this->policy->view($this->stranger, $this->entry))->toBeFalse();
});

it('lets the owner delete their entry', function (): void {
    expect($this->policy->delete($this->owner, $this->entry))->toBeTrue();
});

it('forbids strangers from deleting an entry', function (): void {
    expect($this->policy->delete($this->stranger, $this->entry))->toBeFalse();
});
