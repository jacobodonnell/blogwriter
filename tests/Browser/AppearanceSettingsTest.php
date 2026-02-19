<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email' => 'test@blogwriter.test',
        'password' => 'password',
    ]);
});

function loginAndVisitAppearance(): mixed
{
    $page = visit('/login');

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/');

    $page->navigate('/admin/settings/appearance');

    return $page;
}

it('loads the appearance page with default selections', function (): void {
    $page = loginAndVisitAppearance();

    $page->assertSee('Light Theme')
        ->assertSee('Dark Theme')
        ->assertSee('Font')
        ->assertSeeIn('@light-theme-label', 'lofi')
        ->assertSeeIn('@dark-theme-label', 'dracula')
        ->assertSeeIn('@font-label', 'Noto Sans');
})->group('slow');

it('opens the light theme dropdown and selects a theme', function (): void {
    $page = loginAndVisitAppearance();

    $page->click('@light-theme-toggle')
        ->wait(0.3)
        ->assertVisible('@light-theme-dropdown')
        ->click('@light-option-cupcake')
        ->wait(0.3)
        ->assertSeeIn('@light-theme-label', 'cupcake');
})->group('slow');

it('opens the dark theme dropdown and selects a theme', function (): void {
    $page = loginAndVisitAppearance();

    $page->click('@dark-theme-toggle')
        ->wait(0.3)
        ->assertVisible('@dark-theme-dropdown')
        ->click('@dark-option-synthwave')
        ->wait(0.3)
        ->assertSeeIn('@dark-theme-label', 'synthwave');
})->group('slow');

it('opens the font dropdown and selects a font', function (): void {
    $page = loginAndVisitAppearance();

    $page->click('@font-toggle')
        ->wait(0.3)
        ->assertVisible('@font-dropdown')
        ->click('@font-option-inter')
        ->wait(0.3)
        ->assertSeeIn('@font-label', 'Inter');
})->group('slow');

it('previews theme on hover and reverts on mouseleave', function (): void {
    $page = loginAndVisitAppearance();

    $page->click('@light-theme-toggle')
        ->wait(0.3);

    // Hover over cyberpunk - should preview it
    $page->hover('@light-option-cyberpunk')
        ->wait(0.3)
        ->assertScript('document.documentElement.getAttribute("data-theme")', 'cyberpunk');

    // Move away from dropdown - should revert
    $page->hover('@preview-heading')
        ->wait(0.3)
        ->assertScript('document.documentElement.getAttribute("data-theme") !== "cyberpunk"', true);
})->group('slow');

it('previews font on hover and reverts on mouseleave', function (): void {
    $page = loginAndVisitAppearance();

    $page->click('@font-toggle')
        ->wait(0.3);

    // Hover over JetBrains Mono - inline style should contain the var reference
    $page->hover('@font-option-jetbrains-mono')
        ->wait(0.3)
        ->assertScript('document.documentElement.style.getPropertyValue("--font-sans").includes("jetbrains-mono")', true);

    // Move away - should revert to noto-sans
    $page->hover('@preview-heading')
        ->wait(0.3)
        ->assertScript('document.documentElement.style.getPropertyValue("--font-sans").includes("noto-sans")', true);
})->group('slow');

it('saves selections and persists after reload', function (): void {
    $page = loginAndVisitAppearance();

    // Select cupcake light theme
    $page->click('@light-theme-toggle')
        ->wait(0.3)
        ->click('@light-option-cupcake')
        ->wait(0.3);

    // Select forest dark theme
    $page->click('@dark-theme-toggle')
        ->wait(0.3)
        ->click('@dark-option-forest')
        ->wait(0.3);

    // Select Inter font
    $page->click('@font-toggle')
        ->wait(0.3)
        ->click('@font-option-inter')
        ->wait(0.3);

    // Save
    $page->click('@save-appearance')
        ->wait(1);

    // Verify redirect back with persisted values
    $page->assertSeeIn('@light-theme-label', 'cupcake')
        ->assertSeeIn('@dark-theme-label', 'forest')
        ->assertSeeIn('@font-label', 'Inter');

    // Verify DB
    expect(Setting::get('theme_light'))->toBe('cupcake')
        ->and(Setting::get('theme_dark'))->toBe('forest')
        ->and(Setting::get('theme_font'))->toBe('inter');
})->group('slow');

it('cycles theme mode: light → dark → system', function (): void {
    $page = loginAndVisitAppearance();

    // Set to light mode first
    $page->script("localStorage.setItem('themeMode', 'light')");
    $page->navigate('/admin/settings/appearance');

    // Click cycle button - should go to dark
    $page->click('button[aria-label="Cycle theme mode"]')
        ->wait(0.3)
        ->assertScript('localStorage.getItem("themeMode")', 'dark');

    // Click again - should go to system
    $page->click('button[aria-label="Cycle theme mode"]')
        ->wait(0.3)
        ->assertScript('localStorage.getItem("themeMode")', 'system');

    // Click again - should go back to light
    $page->click('button[aria-label="Cycle theme mode"]')
        ->wait(0.3)
        ->assertScript('localStorage.getItem("themeMode")', 'light');
})->group('slow');

it('only contains light themes in the light dropdown', function (): void {
    $page = loginAndVisitAppearance();

    $page->click('@light-theme-toggle')
        ->wait(0.3);

    // Verify light themes are present
    $page->assertVisible('@light-option-lofi')
        ->assertVisible('@light-option-cupcake')
        ->assertVisible('@light-option-corporate');

    // Verify dark themes are NOT present
    $page->assertNotPresent('@light-option-dracula')
        ->assertNotPresent('@light-option-dark')
        ->assertNotPresent('@light-option-synthwave');
})->group('slow');

it('only contains dark themes in the dark dropdown', function (): void {
    $page = loginAndVisitAppearance();

    $page->click('@dark-theme-toggle')
        ->wait(0.3);

    // Verify dark themes are present
    $page->assertVisible('@dark-option-dracula')
        ->assertVisible('@dark-option-dark')
        ->assertVisible('@dark-option-synthwave');

    // Verify light themes are NOT present
    $page->assertNotPresent('@dark-option-lofi')
        ->assertNotPresent('@dark-option-cupcake')
        ->assertNotPresent('@dark-option-light');
})->group('slow');

it('admin controls stay in Instrument Sans during font preview', function (): void {
    $page = loginAndVisitAppearance();

    $page->click('@font-toggle')
        ->wait(0.3);

    // Hover over a non-default font
    $page->hover('@font-option-lora')
        ->wait(0.3);

    // The font toggle button (has .font-admin) should still use Instrument Sans
    $page->assertScript('getComputedStyle(document.querySelector("[data-test=\\"font-toggle\\"]")).fontFamily.includes("Instrument Sans")', true);
})->group('slow');
