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

    it('validates password length correctly', function (): void {
        $reflection = new ReflectionMethod($this->command, 'validatePasswordLength');

        // 8+ characters return null
        expect($reflection->invoke($this->command, 'password123'))->toBeNull();
        expect($reflection->invoke($this->command, '12345678'))->toBeNull();
        expect($reflection->invoke($this->command, 'longpassword'))->toBeNull();

        // < 8 characters return error
        expect($reflection->invoke($this->command, '1234567'))->toBe('Password must be at least 8 characters.');
        expect($reflection->invoke($this->command, ''))->toBe('Password must be at least 8 characters.');
        expect($reflection->invoke($this->command, 'short'))->toBe('Password must be at least 8 characters.');
    });

    it('handles edge cases for password validation', function (): void {
        $reflection = new ReflectionMethod($this->command, 'validatePasswordLength');

        // Exactly 8 characters
        expect($reflection->invoke($this->command, 'abcdefgh'))->toBeNull();

        // Unicode characters
        expect($reflection->invoke($this->command, 'привет12'))->toBeNull();

        // Special characters
        expect($reflection->invoke($this->command, '!@#$%^&*'))->toBeNull();

        // Whitespace (8 spaces)
        expect($reflection->invoke($this->command, '        '))->toBeNull();
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
