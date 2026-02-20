<?php

use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email' => 'test@blogwriter.test',
        'password' => 'password',
    ]);
});

function loginAndVisitCategories(): mixed
{
    $page = visit('/login');

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/');

    $page->navigate('/admin/categories');

    return $page;
}

it('renders styled page after browser back following AJAX table refresh', function (): void {
    $page = loginAndVisitCategories();

    // Open modal and fill the category form
    $page->click('Add Category')
        ->wait(0.5)
        ->fill('input[name="name"]', 'Back Nav Test Category')
        ->wait(0.3);

    // Submit the modal form via the primary button inside the dialog
    $page->click('dialog .btn-primary')
        ->wait(1.5);

    // Verify the new category appeared in the table (AJAX refresh happened)
    $page->assertSee('Back Nav Test Category');

    // Click the eye icon to navigate to the public category page
    $page->click('@view-category')
        ->wait(1);

    // Go back (simulates browser back button)
    $page->back()
        ->wait(1);

    // Assert the full admin layout rendered — not just the AJAX partial
    $page->assertSee('Categories')
        ->assertNoJavaScriptErrors();

    // Verify the full page layout is present (not just the table partial)
    $page->assertScript(
        "document.querySelector('head title') !== null",
        true
    );

    // Verify admin sidebar navigation rendered
    $page->assertScript(
        "document.querySelector('nav') !== null || document.querySelector('.menu') !== null",
        true
    );
})->group('slow');
