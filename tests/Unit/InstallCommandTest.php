<?php

use App\Console\Commands\InstallCommand;

beforeEach(function (): void {
    $this->command = new InstallCommand;
});

describe('validation methods', function (): void {
    it('validates URL format correctly', function (): void {
        $reflection = new ReflectionMethod($this->command, 'validateUrl');

        // Valid URLs return null (no error)
        expect($reflection->invoke($this->command, 'https://example.com'))->toBeNull();
        expect($reflection->invoke($this->command, 'http://localhost'))->toBeNull();
        expect($reflection->invoke($this->command, 'https://blog.test'))->toBeNull();

        // Invalid URLs return error message
        expect($reflection->invoke($this->command, 'not-a-url'))->toBe('Please enter a valid URL.');
        expect($reflection->invoke($this->command, ''))->toBe('Please enter a valid URL.');
        expect($reflection->invoke($this->command, 'just-text'))->toBe('Please enter a valid URL.');
    });

    it('validates email format correctly', function (): void {
        $reflection = new ReflectionMethod($this->command, 'validateEmail');

        // Valid emails return null
        expect($reflection->invoke($this->command, 'test@example.com'))->toBeNull();
        expect($reflection->invoke($this->command, 'user+tag@domain.co.uk'))->toBeNull();
        expect($reflection->invoke($this->command, 'name.surname@company.io'))->toBeNull();

        // Invalid emails return error message
        expect($reflection->invoke($this->command, 'invalid-email'))->toBe('Please enter a valid email address.');
        expect($reflection->invoke($this->command, ''))->toBe('Please enter a valid email address.');
        expect($reflection->invoke($this->command, '@example.com'))->toBe('Please enter a valid email address.');
        expect($reflection->invoke($this->command, 'user@'))->toBe('Please enter a valid email address.');
    });

    it('validates password requirements correctly', function (): void {
        $reflection = new ReflectionMethod($this->command, 'validatePasswordLength');

        // Valid passwords (16+ chars, letter, number, symbol) return null
        expect($reflection->invoke($this->command, 'StrongP@ssw0rd123!'))->toBeNull();
        expect($reflection->invoke($this->command, 'MyS3cur3P@ssword!'))->toBeNull();
        expect($reflection->invoke($this->command, 'C0rrect-H0rs3-B@tt3ry-42'))->toBeNull();

        // Too short (< 16 chars) returns error
        expect($reflection->invoke($this->command, 'Short1!'))->toBe('Password must be at least 16 characters.');
        expect($reflection->invoke($this->command, ''))->toBe('Password must be at least 16 characters.');
        expect($reflection->invoke($this->command, '123456789012345'))->toBe('Password must be at least 16 characters.');

        // Missing letter returns error
        expect($reflection->invoke($this->command, '123456789012345!@#'))->toBe('Password must contain at least one letter.');

        // Missing number returns error
        expect($reflection->invoke($this->command, 'abcdefghijklmnop!@#'))->toBe('Password must contain at least one number.');

        // Missing symbol returns error
        expect($reflection->invoke($this->command, 'abcdefghijklmnop123'))->toBe('Password must contain at least one symbol.');
    });

    it('handles edge cases for password validation', function (): void {
        $reflection = new ReflectionMethod($this->command, 'validatePasswordLength');

        // Exactly 16 characters with all requirements
        expect($reflection->invoke($this->command, 'abcd1234!@#$5678'))->toBeNull();

        // Special symbols work
        expect($reflection->invoke($this->command, 'Strong!Pass@word#123'))->toBeNull();
    });
});

describe('env file update', function (): void {
    it('updates existing env values', function (): void {
        $reflection = new ReflectionMethod($this->command, 'updateEnvValue');

        $content = "APP_NAME=\"Laravel\"\nAPP_URL=http://localhost";
        $updated = $reflection->invoke($this->command, $content, 'APP_NAME', 'Test Blog');

        expect($updated)->toContain('APP_NAME="Test Blog"');
        expect($updated)->toContain('APP_URL=http://localhost');
    });

    it('adds new env values', function (): void {
        $reflection = new ReflectionMethod($this->command, 'updateEnvValue');

        $content = 'APP_NAME="Laravel"';
        $updated = $reflection->invoke($this->command, $content, 'APP_URL', 'https://example.com');

        expect($updated)->toContain('APP_NAME="Laravel"');
        expect($updated)->toContain('APP_URL="https://example.com"');
    });

    it('escapes quotes in env values', function (): void {
        $reflection = new ReflectionMethod($this->command, 'updateEnvValue');

        $content = 'APP_NAME="Laravel"';
        $updated = $reflection->invoke($this->command, $content, 'APP_NAME', 'My "Blog"');

        expect($updated)->toContain('APP_NAME="My \\"Blog\\""');
    });
});
