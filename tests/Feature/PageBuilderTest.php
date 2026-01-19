<?php

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->page = Page::factory()->create([
        'title' => 'Test Page',
        'slug' => 'test-page',
        'status' => 'draft',
        'author_id' => $this->user->id,
    ]);
});

it('can view page builder for owned page', function () {
    actingAs($this->user)
        ->get("/builder/pages/{$this->page->id}")
        ->assertSuccessful()
        ->assertJson([
            'page' => [
                'id' => $this->page->id,
                'title' => 'Test Page',
            ],
        ]);
});

it('cannot view page builder without authentication', function () {
    $this->get("/builder/pages/{$this->page->id}")
        ->assertRedirect('/login');
});

it('can update page builder content', function () {
    $builderContent = [
        'blocks' => [
            [
                'id' => 'block-1',
                'type' => 'text',
                'content' => ['text' => '<p>Hello World</p>'],
                'settings' => [],
                'order' => 0,
            ],
        ],
    ];

    actingAs($this->user)
        ->put("/builder/pages/{$this->page->id}", $builderContent)
        ->assertSuccessful()
        ->assertJson([
            'message' => 'Page saved successfully',
        ]);

    expect($this->page->fresh()->version)->toBe(2);
});

it('increments version when saving page builder content', function () {
    $initialVersion = $this->page->version;

    actingAs($this->user)
        ->put("/builder/pages/{$this->page->id}", [
            'blocks' => [],
        ]);

    expect($this->page->fresh()->version)->toBe($initialVersion + 1);
});

it('sets last_edited_by when saving page builder content', function () {
    actingAs($this->user)
        ->put("/builder/pages/{$this->page->id}", [
            'blocks' => [],
        ]);

    expect($this->page->fresh()->last_edited_by)->toBe($this->user->id);
});

it('enables builder mode when saving page builder content', function () {
    expect($this->page->is_builder_enabled)->toBeFalse();

    actingAs($this->user)
        ->put("/builder/pages/{$this->page->id}", [
            'blocks' => [],
        ]);

    expect($this->page->fresh()->is_builder_enabled)->toBeTrue();
});

it('can duplicate a page block', function () {
    $block = \App\Models\PageBlock::factory()->create([
        'page_id' => $this->page->id,
        'type' => 'text',
        'content' => ['text' => 'Original block'],
        'order' => 0,
    ]);

    actingAs($this->user)
        ->post("/builder/pages/{$this->page->id}/blocks/duplicate", [
            'block_id' => $block->id,
        ])
        ->assertSuccessful()
        ->assertJson([
            'message' => 'Block duplicated successfully',
        ]);

    expect($this->page->blocks()->count())->toBe(2);
});

it('cannot duplicate block from different page', function () {
    $otherPage = Page::factory()->create();
    $block = \App\Models\PageBlock::factory()->create([
        'page_id' => $otherPage->id,
        'type' => 'text',
        'content' => ['text' => 'Other block'],
        'order' => 0,
    ]);

    actingAs($this->user)
        ->post("/builder/pages/{$this->page->id}/blocks/duplicate", [
            'block_id' => $block->id,
        ])
        ->assertStatus(403);
});

it('can delete a page block', function () {
    $block = \App\Models\PageBlock::factory()->create([
        'page_id' => $this->page->id,
        'type' => 'text',
        'content' => ['text' => 'Block to delete'],
        'order' => 0,
    ]);

    actingAs($this->user)
        ->delete("/builder/pages/{$this->page->id}/blocks/delete", [
            'block_id' => $block->id,
        ])
        ->assertSuccessful()
        ->assertJson([
            'message' => 'Block deleted successfully',
        ]);

    expect($this->page->blocks()->count())->toBe(0);
});

it('can preview page builder content', function () {
    actingAs($this->user)
        ->post("/builder/pages/{$this->page->id}/preview", [
            'builder_content' => [
                'blocks' => [
                    [
                        'type' => 'text',
                        'content' => ['text' => '<p>Preview content</p>'],
                    ],
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonStructure([
            'html',
        ]);
});

it('can get available block types', function () {
    actingAs($this->user)
        ->get('/builder/block-types')
        ->assertSuccessful()
        ->assertJsonStructure([
            'block_types' => [
                '*' => [
                    'type',
                    'label',
                    'icon',
                    'category',
                    'description',
                ],
            ],
        ]);
});

it('validates builder content on update', function () {
    actingAs($this->user)
        ->put("/builder/pages/{$this->page->id}", [
            'invalid_data' => 'missing blocks',
        ])
        ->assertStatus(422);
});

it('stores builder content as json', function () {
    $builderContent = [
        'blocks' => [
            [
                'id' => 'block-1',
                'type' => 'heading',
                'content' => ['text' => 'Welcome', 'level' => 1],
                'settings' => [],
                'order' => 0,
            ],
            [
                'id' => 'block-2',
                'type' => 'text',
                'content' => ['text' => '<p>Some content</p>'],
                'settings' => [],
                'order' => 1,
            ],
        ],
    ];

    actingAs($this->user)
        ->put("/builder/pages/{$this->page->id}", $builderContent)
        ->assertSuccessful();

    assertDatabaseHas('pages', [
        'id' => $this->page->id,
        'is_builder_enabled' => true,
    ]);

    expect($this->page->fresh()->builder_content)->toBe($builderContent);
});

it('syncs blocks when saving page builder content', function () {
    actingAs($this->user)
        ->put("/builder/pages/{$this->page->id}", [
            'builder_content' => ['blocks' => []],
            'blocks' => [
                [
                    'type' => 'text',
                    'content' => ['text' => 'New block'],
                    'settings' => [],
                    'order' => 0,
                ],
            ],
        ])
        ->assertSuccessful();

    expect($this->page->blocks()->count())->toBe(1);
    expect($this->page->blocks()->first()->type)->toBe('text');
});
