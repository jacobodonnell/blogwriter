<?php

use App\Exceptions\SingleUserViolationException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows creating the first user', function (): void {
    $user = User::factory()->create();

    expect(User::count())->toBe(1);
    expect($user->exists)->toBeTrue();
});

it('throws SingleUserViolationException when creating a second user', function (): void {
    User::factory()->create();

    User::factory()->create();
})->throws(SingleUserViolationException::class);

it('allows creating a new user after deleting the existing one', function (): void {
    $first = User::factory()->create();
    $first->delete();

    $second = User::factory()->create();

    expect(User::count())->toBe(1);
    expect($second->exists)->toBeTrue();
});

it('has articles relationship', function (): void {
    $user = User::factory()->create();

    expect($user->articles())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

it('has photos relationship', function (): void {
    $user = User::factory()->create();

    expect($user->photos())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});
