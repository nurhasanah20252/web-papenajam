<?php

use App\Models\Category;
use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;

beforeEach(function () {
    Storage::fake('public');
});

test('documents index page loads', function () {
    $response = get(route('documents.index'));

    $response->assertStatus(200);
});

test('public documents are displayed on index page', function () {
    $category = Category::factory()->create();
    Document::factory()->public()->create([
        'title' => 'Test Public Document',
        'slug' => 'test-public-document',
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    $response = get(route('documents.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Documents/Index')
        ->has('documents.data')
        ->where('documents.data.0.title', 'Test Public Document')
    );
});

test('private documents are not displayed on index page', function () {
    Document::factory()->private()->create([
        'title' => 'Test Private Document',
        'slug' => 'test-private-document',
    ]);

    $response = get(route('documents.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Documents/Index')
        ->where('documents.data', [])
    );
});

test('document show page loads for public document', function () {
    $document = Document::factory()->public()->create([
        'title' => 'Test Document',
        'slug' => 'test-document',
        'published_at' => now(),
    ]);

    $response = get(route('documents.show', $document->slug));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Documents/Show')
        ->where('document.title', 'Test Document')
    );
});

test('document show page returns 403 for private document', function () {
    $document = Document::factory()->private()->create([
        'title' => 'Private Document',
        'slug' => 'private-document',
    ]);

    $response = get(route('documents.show', $document->slug));

    $response->assertForbidden();
});

test('document can be downloaded', function () {
    Storage::disk('public')->put('documents/test.pdf', 'test content');

    $document = Document::factory()->public()->create([
        'title' => 'Test Document',
        'slug' => 'test-document',
        'file_path' => 'documents/test.pdf',
        'file_name' => 'test.pdf',
        'download_count' => 0,
        'published_at' => now(),
    ]);

    $response = get(route('documents.download', $document->slug));

    $response->assertStatus(200);
    $response->assertDownload('test.pdf');

    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'download_count' => 1,
    ]);
});

test('document version can be downloaded', function () {
    Storage::disk('public')->put('documents/test.pdf', 'test content');
    Storage::disk('public')->put('documents/versions/test-v2.pdf', 'version 2 content');

    $document = Document::factory()->public()->create([
        'title' => 'Test Document',
        'slug' => 'test-document',
        'published_at' => now(),
    ]);

    $version = DocumentVersion::factory()->create([
        'document_id' => $document->id,
        'version' => '2.0',
        'file_path' => 'documents/versions/test-v2.pdf',
        'file_name' => 'test-v2.pdf',
        'file_size' => 100,
    ]);

    $response = get(route('documents.versions.download', [$document->slug, $version->id]));

    $response->assertStatus(200);
    $response->assertDownload('test-v2.pdf');
});

test('documents can be filtered by category', function () {
    $category = Category::factory()->create();
    Document::factory()->public()->create([
        'title' => 'Category Document',
        'slug' => 'category-document',
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    $response = get(route('documents.index', ['category' => $category->id]));

    $response->assertStatus(200);
});

test('documents can be searched', function () {
    Document::factory()->public()->create([
        'title' => 'Unique Document Title',
        'slug' => 'unique-document-title',
        'published_at' => now(),
    ]);

    $response = get(route('documents.index', ['search' => 'Unique']));

    $response->assertStatus(200);
});

test('document slug is auto-generated on create', function () {
    $document = Document::factory()->create([
        'title' => 'Test Document Title',
    ]);

    expect($document->slug)->toBe('test-document-title');
});

test('document versions relationship works', function () {
    $document = Document::factory()->create();
    $version = DocumentVersion::factory()->create([
        'document_id' => $document->id,
        'version' => '1.0',
    ]);

    expect($document->versions)->toHaveCount(1);
    expect($document->versions->first()->version)->toBe('1.0');
});

test('document version can be marked as current', function () {
    $document = Document::factory()->create();

    $version1 = DocumentVersion::factory()->create([
        'document_id' => $document->id,
        'version' => '1.0',
        'is_current' => true,
    ]);

    $version2 = DocumentVersion::factory()->create([
        'document_id' => $document->id,
        'version' => '2.0',
        'is_current' => false,
    ]);

    $version2->markAsCurrent();

    $version1->refresh();
    $version2->refresh();

    expect($version1->is_current)->toBeFalse();
    expect($version2->is_current)->toBeTrue();
});

test('document download count increments', function () {
    Storage::disk('public')->put('documents/test.pdf', 'test content');

    $document = Document::factory()->public()->create([
        'slug' => 'test-document',
        'file_path' => 'documents/test.pdf',
        'file_name' => 'test.pdf',
        'download_count' => 5,
        'published_at' => now(),
    ]);

    get(route('documents.download', $document->slug));

    $document->refresh();
    expect($document->download_count)->toBe(6);
});

test('document file size is formatted correctly', function () {
    $document = Document::factory()->create([
        'file_size' => 1024 * 1024, // 1 MB
    ]);

    expect($document->getHumanFileSize())->toBe('1 MB');
});

test('document checksum validation works', function () {
    $content = 'test content';
    Storage::disk('public')->put('documents/test.pdf', $content);

    $checksum = hash('sha256', $content);

    $document = Document::factory()->create([
        'slug' => 'test-document',
        'file_path' => 'documents/test.pdf',
        'checksum' => $checksum,
    ]);

    expect($document->validateChecksum(Storage::disk('public')->path('documents/test.pdf')))->toBeTrue();
});
