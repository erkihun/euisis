<?php

declare(strict_types=1);

use App\Models\AuditLog;
use App\Models\CardRequest;
use App\Models\CardVerification;
use App\Models\Employee;
use App\Models\EmployeeAssignment;
use App\Models\EmployeeTransfer;
use App\Models\Entitlement;
use App\Models\IdCard;
use App\Models\ServiceProvider;
use App\Models\ServiceTransaction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('unauthenticated user cannot access dashboard', function (): void {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

test('user without dashboard permission cannot access dashboard', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('super admin receives all dashboard sections', function (): void {
    $user = User::where('email', 'super.admin@demo.local')->firstOrFail();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->where('can.dashboard', true)
            ->where('can.employees', true)
            ->where('can.organizations', true)
            ->where('can.cards', true)
            ->where('can.entitlements', true)
            ->where('can.transactions', true)
            ->where('can.providers', true)
            ->where('can.transfers', true)
            ->where('can.audit', true)
            ->has('kpis')
            ->has('charts')
            ->has('workflowQueues')
            ->has('alerts')
        );
});

test('scoped hr officer receives scoped dashboard counts', function (): void {
    $user = User::where('email', 'hr.officer@demo.local')->firstOrFail();

    $response = $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->where('can.employees', true)
        ->where('can.audit', false)
        ->where('meta.scope.providerOnly', false)
        ->has('kpis')
    );

    $props = $response->viewData('page')['props'];

    expect(collect($props['kpis'])->pluck('key'))->toContain('activeEmployees');
    expect($props['recentActivity'])->toBeArray()->toHaveCount(0);
});

test('provider user does not receive hr sections', function (): void {
    $user = User::where('email', 'provider.transport@demo.local')->firstOrFail();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('meta.scope.providerOnly', true)
            ->where('can.employees', false)
            ->where('can.organizations', false)
            ->where('can.transactions', true)
            ->where('can.providers', true)
        );
});

test('dashboard props do not expose sensitive pii fields', function (): void {
    $user = User::where('email', 'super.admin@demo.local')->firstOrFail();

    $response = $this->actingAs($user)->get(route('dashboard'))->assertOk();
    $propsJson = json_encode($response->viewData('page')['props'], JSON_THROW_ON_ERROR);

    expect($propsJson)->not->toContain('0911000000')
        ->and($propsJson)->not->toContain('demo-token')
        ->and($propsJson)->not->toContain('token_hash')
        ->and($propsJson)->not->toContain('photo_path')
        ->and($propsJson)->not->toContain('signature_path');
});

test('dashboard respects date range filters and keeps bounded lists', function (): void {
    $user = User::where('email', 'super.admin@demo.local')->firstOrFail();

    $response = $this->actingAs($user)
        ->get(route('dashboard', ['date_range' => '7d']))
        ->assertOk();

    $props = $response->viewData('page')['props'];

    expect($props['filters']['dateRange'])->toBe('7d');
    expect($props['recentActivity'])->toBeArray();
    expect(count($props['recentActivity']))->toBeLessThanOrEqual(12);
    expect($props['charts']['providersTopUsage'])->toBeArray();
    expect(count($props['charts']['providersTopUsage']))->toBeLessThanOrEqual(10);
});

test('dashboard handles empty data safely', function (): void {
    AuditLog::query()->delete();
    ServiceTransaction::query()->delete();
    Entitlement::query()->delete();
    CardVerification::query()->delete();
    IdCard::query()->delete();
    CardRequest::query()->delete();
    EmployeeTransfer::query()->delete();
    EmployeeAssignment::query()->delete();
    Employee::query()->delete();
    ServiceProvider::query()->delete();

    $user = User::where('email', 'super.admin@demo.local')->firstOrFail();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('kpis')
            ->has('charts')
            ->has('workflowQueues')
            ->has('alerts')
        );
});
