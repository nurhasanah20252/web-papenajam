<?php

use App\Enums\PPIDStatus;
use App\Models\PpidRequest;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('displays ppid information page', function () {
    get('/ppid')
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            expect($page->component())->toBe('ppid/index');
        });
});

it('displays ppid form when authenticated', function () {
    $this->actingAs($this->user)
        ->get('/ppid/form')
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            expect($page->component())->toBe('ppid/form');
        });
});

it('redirects unauthenticated user from ppid form to login', function () {
    get('/ppid/form')
        ->assertRedirect('/login');
});

it('can create ppid request', function () {
    Notification::fake();

    $requestData = [
        'applicant_name' => 'John Doe',
        'nik' => '1234567890123456',
        'address' => 'Jl. Test No. 123',
        'phone' => '081234567890',
        'email' => 'john@example.com',
        'request_type' => 'informasi_publik',
        'subject' => 'Test Request Subject',
        'description' => 'This is a test description with more than fifty characters to meet the minimum requirement.',
        'priority' => 'normal',
    ];

    $this->actingAs($this->user)
        ->withoutMiddleware(VerifyCsrfToken::class)
        ->post('/ppid', $requestData)
        ->assertRedirect();

    assertDatabaseHas('ppid_requests', [
        'email' => 'john@example.com',
        'subject' => 'Test Request Subject',
        'request_type' => 'informasi_publik',
    ]);
});

it('validates required fields when creating ppid request', function () {
    $this->actingAs($this->user)
        ->withoutMiddleware(VerifyCsrfToken::class)
        ->post('/ppid', [])
        ->assertSessionHasErrors([
            'applicant_name',
            'email',
            'request_type',
            'subject',
            'description',
            'priority',
        ]);
});

it('validates minimum description length', function () {
    $this->actingAs($this->user)
        ->withoutMiddleware(VerifyCsrfToken::class)
        ->post('/ppid', [
            'applicant_name' => 'John Doe',
            'email' => 'john@example.com',
            'request_type' => 'informasi_publik',
            'subject' => 'Test',
            'description' => 'Short',
            'priority' => 'normal',
        ])
        ->assertSessionHasErrors(['description']);
});

it('displays tracking page with valid request number', function () {
    $ppidRequest = PpidRequest::factory()->create([
        'request_number' => 'PPID/2025/01/0001',
    ]);

    get('/ppid/tracking?number=PPID/2025/01/0001')
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            expect($page->component())->toBe('ppid/tracking');
        });
});

it('shows error when tracking non-existent request', function () {
    get('/ppid/tracking?number=INVALID/NUMBER')
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            expect($page->component())->toBe('ppid/tracking');
            expect($page->props)->toHaveKey('error');
        });
});

it('displays user requests when authenticated', function () {
    PpidRequest::factory()->count(3)->create([
        'email' => $this->user->email,
    ]);

    $this->actingAs($this->user)
        ->get('/ppid/my-requests')
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            expect($page->component())->toBe('ppid/my-requests');
        });
});

it('redirects unauthenticated user from my requests to login', function () {
    get('/ppid/my-requests')
        ->assertRedirect('/login');
});

it('displays single request details when authenticated', function () {
    $ppidRequest = PpidRequest::factory()->create([
        'email' => $this->user->email,
    ]);

    $this->actingAs($this->user)
        ->get("/ppid/{$ppidRequest->id}")
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            expect($page->component())->toBe('ppid/show');
        });
});

it('prevents accessing other users requests', function () {
    $otherUser = User::factory()->create();
    $ppidRequest = PpidRequest::factory()->create([
        'email' => $otherUser->email,
    ]);

    $this->actingAs($this->user)
        ->get("/ppid/{$ppidRequest->id}")
        ->assertStatus(403);
});

it('generates unique request numbers', function () {
    $request1 = PpidRequest::factory()->create();
    $request2 = PpidRequest::factory()->create();

    expect($request1->request_number)->not->toBe($request2->request_number);
});

it('marks request as responded correctly', function () {
    $admin = User::factory()->create();
    $ppidRequest = PpidRequest::factory()->create([
        'status' => PPIDStatus::Submitted,
    ]);

    $ppidRequest->markAsResponded($admin, 'Test response message');

    expect($ppidRequest->status)->toBe(PPIDStatus::Completed)
        ->and($ppidRequest->response)->toBe('Test response message')
        ->and($ppidRequest->processed_by)->toBe($admin->id)
        ->and($ppidRequest->responded_at)->not->toBeNull();
});

it('calculates days pending correctly', function () {
    $ppidRequest = PpidRequest::factory()->create([
        'status' => PPIDStatus::Submitted,
        'created_at' => now()->subDays(5),
    ]);

    expect($ppidRequest->getDaysPending())->toBe(5);
});

it('returns null for days pending when completed', function () {
    $ppidRequest = PpidRequest::factory()->create([
        'status' => PPIDStatus::Completed,
    ]);

    expect($ppidRequest->getDaysPending())->toBeNull();
});

it('scopes pending requests correctly', function () {
    PpidRequest::factory()->create(['status' => PPIDStatus::Submitted]);
    PpidRequest::factory()->create(['status' => PPIDStatus::Reviewed]);
    PpidRequest::factory()->create(['status' => PPIDStatus::Completed]);

    $pendingRequests = PpidRequest::pending()->get();

    expect($pendingRequests)->toHaveCount(2);
});

it('scopes high priority requests correctly', function () {
    PpidRequest::factory()->create(['priority' => 'normal']);
    PpidRequest::factory()->create(['priority' => 'high']);

    $highPriorityRequests = PpidRequest::highPriority()->get();

    expect($highPriorityRequests)->toHaveCount(1);
});
