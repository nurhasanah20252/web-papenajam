<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    /**
     * Display a listing of public documents.
     */
    public function index(Request $request)
    {
        $query = Document::query()
            ->with(['category', 'uploader'])
            ->public()
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->byCategory($request->category);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);

        $documents = $query->paginate(12);

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'filters' => $request->only(['search', 'category', 'sort', 'order']),
        ]);
    }

    /**
     * Display the specified document.
     */
    public function show(string $slug)
    {
        $document = Document::where('slug', $slug)->firstOrFail();

        // Check access control
        if (! $document->isDownloadable()) {
            abort(403);
        }

        $document->load(['category', 'uploader', 'versions' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(5);
        }]);

        // Get related documents in same category
        $relatedDocuments = Document::query()
            ->public()
            ->where('id', '!=', $document->id)
            ->where('category_id', $document->category_id)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->limit(4)
            ->get();

        return Inertia::render('Documents/Show', [
            'document' => $document,
            'relatedDocuments' => $relatedDocuments,
        ]);
    }

    /**
     * Download the specified document.
     */
    public function download(string $slug)
    {
        $document = Document::where('slug', $slug)->firstOrFail();

        // Check access control
        if (! $document->isDownloadable()) {
            abort(403);
        }

        // Check file exists
        if (! Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        // Increment download count
        $document->incrementDownloads();

        // Return file download
        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Download a specific version of the document.
     */
    public function downloadVersion(string $slug, DocumentVersion $version)
    {
        $document = Document::where('slug', $slug)->firstOrFail();

        // Verify version belongs to document
        if ($version->document_id !== $document->id) {
            abort(404);
        }

        // Check access control
        if (! $document->isDownloadable()) {
            abort(403);
        }

        // Check file exists
        if (! Storage::disk('public')->exists($version->file_path)) {
            abort(404);
        }

        // Increment document download count
        $document->incrementDownloads();

        // Return file download
        return Storage::disk('public')->download($version->file_path, $version->file_name);
    }
}
