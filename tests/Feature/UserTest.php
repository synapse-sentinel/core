<?php

declare(strict_types=1);

use App\Models\User;

describe('User', function () {
    it('can be created with factory', function () {
        $user = User::factory()->create();

        expect($user)->toBeInstanceOf(User::class);
        expect($user->name)->toBeString();
        expect($user->email)->toBeString();
    });

    it('hides password and remember_token in serialization', function () {
        $user = User::factory()->create();
        $array = $user->toArray();

        expect($array)->not->toHaveKey('password');
        expect($array)->not->toHaveKey('remember_token');
    });

    it('casts email_verified_at to datetime', function () {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        expect($user->email_verified_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    it('hashes password automatically', function () {
        $user = User::factory()->create([
            'password' => 'plain-text-password',
        ]);

        expect($user->password)->not->toBe('plain-text-password');
        expect(password_verify('plain-text-password', $user->password))->toBeTrue();
    });
});
