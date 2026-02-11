<?php

use App\Enums\Status;

describe('Status enum values', function (): void {
    it('has correct enum values', function (): void {
        $cases = Status::cases();
        $values = array_map(fn (\App\Enums\Status $case) => $case->value, $cases);

        expect($values)->toContain('draft');
        expect($values)->toContain('published');
        expect($values)->toContain('hidden');
        expect($values)->toHaveCount(3);
    });

    it('isPublic returns true only for Published', function (): void {
        expect(Status::Draft->isPublic())->toBeFalse();
        expect(Status::Published->isPublic())->toBeTrue();
        expect(Status::Hidden->isPublic())->toBeFalse();
    });

    it('isPrivate returns true for Draft and Hidden', function (): void {
        expect(Status::Draft->isPrivate())->toBeTrue();
        expect(Status::Published->isPrivate())->toBeFalse();
        expect(Status::Hidden->isPrivate())->toBeTrue();
    });

    it('can be created from string value', function (): void {
        expect(Status::from('draft'))->toBe(Status::Draft);
        expect(Status::from('published'))->toBe(Status::Published);
        expect(Status::from('hidden'))->toBe(Status::Hidden);
    });

    it('throws exception for invalid value', function (): void {
        expect(fn () => Status::from('invalid'))->toThrow(\ValueError::class);
    });

    it('has tryFrom method for safe conversion', function (): void {
        expect(Status::tryFrom('draft'))->toBe(Status::Draft);
        expect(Status::tryFrom('published'))->toBe(Status::Published);
        expect(Status::tryFrom('hidden'))->toBe(Status::Hidden);
        expect(Status::tryFrom('invalid'))->toBeNull();
    });
});

describe('Status enum helper methods', function (): void {
    it('has label method for display', function (): void {
        expect(Status::Draft->label())->toBe('Draft');
        expect(Status::Published->label())->toBe('Published');
        expect(Status::Hidden->label())->toBe('Hidden');
    });

    it('has icon method for UI', function (): void {
        expect(Status::Draft->icon())->toBe('ph-pencil');
        expect(Status::Published->icon())->toBe('ph-check');
        expect(Status::Hidden->icon())->toBe('ph-eye-slash');
    });

    it('has color method for UI theming', function (): void {
        expect(Status::Draft->color())->toBe('warning');
        expect(Status::Published->color())->toBe('success');
        expect(Status::Hidden->color())->toBe('neutral');
    });
});

describe('Status enum collection methods', function (): void {
    it('can return all public statuses', function (): void {
        $public = Status::publicStatuses();

        expect($public)->toHaveCount(1);
        expect($public)->toContain(Status::Published);
    });

    it('can return all private statuses', function (): void {
        $private = Status::privateStatuses();

        expect($private)->toHaveCount(2);
        expect($private)->toContain(Status::Draft);
        expect($private)->toContain(Status::Hidden);
    });

    it('can return options array for select inputs', function (): void {
        $options = Status::options();

        expect($options)->toBe([
            'draft' => 'Draft',
            'published' => 'Published',
            'hidden' => 'Hidden',
        ]);
    });
});
