<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /**
     * Display the specified page.
     */
    public function show(string $slug): Response
    {
        $page = Page::where('slug', $slug)
            ->published()
            ->firstOrFail();

        $page->incrementViews();

        return Inertia::render('pages/[slug]', [
            'page' => $page,
        ]);
    }
}
