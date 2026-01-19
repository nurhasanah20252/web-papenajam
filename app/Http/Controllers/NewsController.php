<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

final class NewsController extends Controller
{
    public function __construct(
        private \App\Services\CacheService $cacheService
    ) {}

    /**
     * Display a listing of the news.
     */
    public function index(Request $request): Response
    {
        $query = News::query()
            ->with(['category', 'author'])
            ->published()
            ->latest('published_at');

        // Filter by category
        if ($request->has('category') && $request->input('category') !== 'all') {
            $categorySlug = $request->input('category');
            $category = Category::where('slug', $categorySlug)
                ->where('type', 'news')
                ->first();

            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Filter by tag
        if ($request->has('tag') && $request->filled('tag')) {
            $query->withTag($request->input('tag'));
        }

        // Search
        if ($request->has('search') && $request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('excerpt', 'like', "%{$searchTerm}%");
            });
        }

        // Get featured news for sidebar (with cache)
        $featuredNews = $this->cacheService->getFeaturedNews(5);

        // Paginate results
        $news = $query->paginate(12);

        // Get all news categories for filters (with cache)
        $categories = $this->cacheService->getCategories('news');

        return Inertia::render('news/index', [
            'news' => $news,
            'featuredNews' => $featuredNews,
            'categories' => $categories,
            'filters' => [
                'category' => $request->input('category', 'all'),
                'tag' => $request->input('tag'),
                'search' => $request->input('search'),
            ],
        ]);
    }

    /**
     * Display the specified news article.
     */
    public function show(string $slug): Response
    {
        $news = News::query()
            ->with(['category', 'author'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        // Increment view count
        $news->incrementViews();

        // Get related news (same category, excluding current)
        $relatedNews = [];
        if ($news->category) {
            $relatedNews = News::query()
                ->with(['category'])
                ->published()
                ->where('category_id', $news->category_id)
                ->where('id', '!=', $news->id)
                ->latest('published_at')
                ->limit(4)
                ->get();
        }

        // Get latest news
        $latestNews = News::query()
            ->with(['category'])
            ->published()
            ->where('id', '!=', $news->id)
            ->latest('published_at')
            ->limit(5)
            ->get();

        return Inertia::render('news/show', [
            'news' => $news,
            'relatedNews' => $relatedNews,
            'latestNews' => $latestNews,
        ]);
    }

    /**
     * Display news by category.
     */
    public function category(Request $request, string $slug): Response
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->where('type', 'news')
            ->firstOrFail();

        $request->merge(['category' => $slug]);

        return $this->index($request);
    }

    /**
     * Display news by tag.
     */
    public function tag(Request $request, string $tag): Response
    {
        $request->merge(['tag' => $tag]);

        return $this->index($request);
    }

    /**
     * Generate RSS feed for news.
     */
    public function rss(Request $request): HttpResponse
    {
        $news = News::query()
            ->with(['category', 'author'])
            ->published()
            ->latest('published_at')
            ->limit(50)
            ->get();

        $xml = $this->generateRssXml($news);

        return response($xml, 200)
            ->header('Content-Type', 'application/xml+xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Generate RSS XML from news collection.
     */
    private function generateRssXml($news): string
    {
        $siteUrl = config('app.url');
        $siteName = config('app.name', 'Pengadilan Agama Penajam');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml .= '<channel>';
        $xml .= '<title>'.$siteName.' - Berita</title>';
        $xml .= '<link>'.$siteUrl.'/news</link>';
        $xml .= '<description>Berita dan pengumuman terbaru dari '.$siteName.'</description>';
        $xml .= '<language>id-id</language>';
        $xml .= '<atom:link href="'.$siteUrl.'/news/rss" rel="self" type="application/rss+xml" />';

        foreach ($news as $item) {
            $url = $siteUrl.'/news/'.$item->slug;
            $description = $item->excerpt ?? strip_tags($item->content ?? '');
            $description = substr($description, 0, 200).'...';

            $xml .= '<item>';
            $xml .= '<title>'.htmlspecialchars($item->title).'</title>';
            $xml .= '<link>'.$url.'</link>';
            $xml .= '<description>'.htmlspecialchars($description).'</description>';
            $xml .= '<pubDate>'.$item->published_at->toRssString().'</pubDate>';
            $xml .= '<guid isPermaLink="true">'.$url.'</guid>';

            if ($item->category) {
                $xml .= '<category>'.htmlspecialchars($item->category->name).'</category>';
            }

            if ($item->author) {
                $xml .= '<author>'.htmlspecialchars($item->author->name).'</author>';
            }

            if ($item->featured_image) {
                $xml .= '<enclosure url="'.$item->featured_image.'" type="image/jpeg" />';
            }

            $xml .= '</item>';
        }

        $xml .= '</channel>';
        $xml .= '</rss>';

        return $xml;
    }
}
