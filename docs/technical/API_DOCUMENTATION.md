# PA Penajam Website - API Documentation

## Overview

This document describes the API endpoints available in the PA Penajam website system. The application uses Laravel 12 with Inertia.js v2 for frontend communication, but also provides RESTful API endpoints for external integrations.

## Base URL

- **Development:** `http://localhost:8000`
- **Production:** `https://pa-penajam.go.id`

## Authentication

Most API endpoints require authentication. Use Laravel Sanctum tokens or session-based authentication.

### Session Authentication (Inertia/Blade)

```php
// Login via Fortify
POST /login
{
  "email": "user@example.com",
  "password": "password"
}
```

### Token Authentication (API)

```php
// Generate personal access token
use Laravel\Sanctum\PersonalAccessToken;

$token = $user->createToken('api-token')->plainTextToken;
// Returns: "token_id|token_string"

// Use in requests
Authorization: Bearer {token}
```

## Response Format

All API responses follow this structure:

### Success Response

```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### Paginated Response

```json
{
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": "...",
    "next": "..."
  }
}
```

---

## Public API Endpoints

### Pages

#### Get All Pages

```http
GET /api/v1/pages
```

**Query Parameters:**
- `status` (optional): Filter by status (`draft`, `published`)
- `search` (optional): Search in title/content
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Profil Pengadilan",
      "slug": "profil-pengadilan",
      "excerpt": "Profil singkat Pengadilan Agama Penajam...",
      "status": "published",
      "published_at": "2026-01-01T00:00:00Z",
      "created_at": "2026-01-01T00:00:00Z",
      "updated_at": "2026-01-01T00:00:00Z"
    }
  ],
  "meta": { ... }
}
```

#### Get Page by Slug

```http
GET /api/v1/pages/{slug}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "title": "Profil Pengadilan",
    "slug": "profil-pengadilan",
    "content": {
      "blocks": [
        {
          "type": "heading",
          "content": { "text": "Selamat Datang", "level": 1 }
        },
        {
          "type": "paragraph",
          "content": { "text": "Konten halaman..." }
        }
      ]
    },
    "meta_description": "Deskripsi halaman",
    "status": "published",
    "published_at": "2026-01-01T00:00:00Z"
  }
}
```

### News

#### Get All News

```http
GET /api/v1/news
```

**Query Parameters:**
- `category_id` (optional): Filter by category
- `is_featured` (optional): Filter featured news (`true`, `false`)
- `search` (optional): Search in title/content
- `page` (optional): Page number
- `per_page` (optional): Items per page

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Sosialisasi SIPP",
      "slug": "sosialisasi-sipp",
      "excerpt": "Kegiatan sosialisasi sistem informasi...",
      "content": "<p>Full content...</p>",
      "category": {
        "id": 1,
        "name": "Berita",
        "slug": "berita"
      },
      "is_featured": true,
      "views_count": 150,
      "published_at": "2026-01-15T00:00:00Z",
      "created_at": "2026-01-15T00:00:00Z"
    }
  ],
  "meta": { ... }
}
```

#### Get News by Slug

```http
GET /api/v1/news/{slug}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "title": "Sosialisasi SIPP",
    "slug": "sosialisasi-sipp",
    "content": "<p>Full article content...</p>",
    "category": { ... },
    "is_featured": true,
    "views_count": 151,
    "published_at": "2026-01-15T00:00:00Z",
    "related_news": [ ... ]
  }
}
```

### Documents

#### Get All Documents

```http
GET /api/v1/documents
```

**Query Parameters:**
- `category_id` (optional): Filter by category
- `search` (optional): Search in title/description
- `page` (optional): Page number
- `per_page` (optional): Items per page

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Surat Edaran No. 1/2026",
      "description": "Tata cara pendaftaran perkara secara elektronik",
      "file_path": "/storage/documents/se1-2026.pdf",
      "file_size": 1024000,
      "download_count": 45,
      "category": {
        "id": 1,
        "name": "Surat Edaran",
        "slug": "surat-edaran"
      },
      "is_public": true,
      "published_at": "2026-01-10T00:00:00Z"
    }
  ],
  "meta": { ... }
}
```

#### Download Document

```http
GET /api/v1/documents/{id}/download
```

**Response:** File download

**Note:** This endpoint increments the `download_count`.

### Court Schedules

#### Get Court Schedules

```http
GET /api/v1/court-schedules
```

**Query Parameters:**
- `start_date` (optional): Start date (Y-m-d format)
- `end_date` (optional): End date (Y-m-d format)
- `judge_id` (optional): Filter by judge
- `court_room_id` (optional): Filter by courtroom
- `case_type_id` (optional): Filter by case type
- `status` (optional): Filter by status

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "case_number": "123/Pdt.G/2026/PA.Pnj",
      "case_title": "Perceraian Ahmad & Siti",
      "judge": {
        "id": 1,
        "name": "H. Muhammad Yunus, S.Ag., M.H."
      },
      "court_room": {
        "id": 1,
        "name": "Ruang Sidang I"
      },
      "schedule_date": "2026-01-20",
      "time": "09:00",
      "status": "scheduled",
      "notes": null
    }
  ]
}
```

#### Get Case Types

```http
GET /api/v1/case-types
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Perdata Gugatan",
      "code": "Pdt.G"
    },
    {
      "id": 2,
      "name": "Perdata Permohonan",
      "code": "Pdt.P"
    }
  ]
}
```

### Categories

#### Get Categories by Type

```http
GET /api/v1/categories/{type}
```

**Types:** `news`, `document`, `page`

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Berita",
      "slug": "berita",
      "description": "Kategori berita",
      "parent_id": null,
      "children": [ ... ]
    }
  ]
}
```

### Menus

#### Get Menu by Location

```http
GET /api/v1/menus/{location}
```

**Locations:** `header`, `footer`, `sidebar`, `mobile`

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Main Navigation",
    "location": "header",
    "items": [
      {
        "id": 1,
        "title": "Beranda",
        "url": "/",
        "type": "route",
        "children": [ ... ]
      },
      {
        "id": 2,
        "title": "Profil",
        "url": "/profil",
        "type": "page",
        "children": [
          {
            "id": 3,
            "title": "Sejarah",
            "url": "/profil/sejarah",
            "type": "page"
          }
        ]
      }
    ]
  }
}
```

### Transparency Data

#### Get Budget Transparency

```http
GET /api/v1/budget-transparency
```

**Query Parameters:**
- `year` (optional): Filter by year

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "year": 2026,
      "title": "Anggaran Pendapatan dan Belanja",
      "description": "Ringkasan APBN tahun 2026",
      "amount": 2500000000,
      "document_path": "/storage/budget/apbn-2026.pdf"
    }
  ]
}
```

#### Get Case Statistics

```http
GET /api/v1/case-statistics
```

**Query Parameters:**
- `year` (optional): Filter by year
- `month` (optional): Filter by month
- `case_type_id` (optional): Filter by case type

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "year": 2026,
      "month": 1,
      "case_type": {
        "id": 1,
        "name": "Perdata Gugatan",
        "code": "Pdt.G"
      },
      "total_cases": 45,
      "resolved_cases": 30,
      "pending_cases": 15,
      "average_duration": 14.5
    }
  ]
}
```

### PPID Portal

#### Submit PPID Request

```http
POST /api/v1/ppid-requests
```

**Request Body:**
```json
{
  "requester_name": "Ahmad Subardjo",
  "email": "ahmad@example.com",
  "phone": "08123456789",
  "request_type": "setiap_saat",
  "description": "Meminta salai putusan perkara No. 123/Pdt.G/2026"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "request_number": "PPID-2026-0001",
    "requester_name": "Ahmad Subardjo",
    "email": "ahmad@example.com",
    "request_type": "setiap_saat",
    "description": "...",
    "status": "pending",
    "created_at": "2026-01-18T10:00:00Z"
  },
  "message": "Permohonan berhasil dikirim"
}
```

#### Check PPID Request Status

```http
GET /api/v1/ppid-requests/{requestNumber}
```

**Response:**
```json
{
  "data": {
    "request_number": "PPID-2026-0001",
    "status": "processed",
    "response": "Dokumen sedang diproses",
    "responded_at": "2026-01-19T14:30:00Z"
  }
}
```

---

## Admin API Endpoints

> **Note:** All admin endpoints require authentication and appropriate role permissions.

### Authentication

#### Admin Login

```http
POST /admin/login
```

**Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Administrator",
      "email": "admin@example.com",
      "role": "admin"
    },
    "token": "1|abc123..."
  }
}
```

#### Admin Logout

```http
POST /admin/logout
Authorization: Bearer {token}
```

### Page Management

#### Create Page

```http
POST /admin/api/pages
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "title": "New Page",
  "slug": "new-page",
  "content": {
    "blocks": [ ... ]
  },
  "meta_description": "Page description",
  "status": "draft",
  "published_at": null
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "title": "New Page",
    "slug": "new-page",
    "status": "draft",
    "created_at": "2026-01-18T10:00:00Z"
  }
}
```

#### Update Page

```http
PUT /admin/api/pages/{id}
Authorization: Bearer {token}
```

#### Delete Page

```http
DELETE /admin/api/pages/{id}
Authorization: Bearer {token}
```

### Menu Management

#### Create Menu

```http
POST /admin/api/menus
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "name": "Footer Menu",
  "location": "footer",
  "max_depth": 2
}
```

#### Reorder Menu Items

```http
POST /admin/api/menus/{id}/reorder
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "items": [
    { "id": 1, "order": 1, "parent_id": null },
    { "id": 2, "order": 2, "parent_id": null },
    { "id": 3, "order": 1, "parent_id": 2 }
  ]
}
```

### News Management

#### Create News

```http
POST /admin/api/news
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "title": "Breaking News",
  "slug": "breaking-news",
  "content": "<p>News content...</p>",
  "category_id": 1,
  "is_featured": true,
  "published_at": "2026-01-18T10:00:00Z"
}
```

### Document Management

#### Upload Document

```http
POST /admin/api/documents
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Form Data:**
```
title: "Document Title"
description: "Document description"
category_id: 1
file: [binary file data]
is_public: true
```

### SIPP Integration

#### Manual Sync

```http
POST /admin/api/sipp/sync
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "status": "success",
    "records_processed": 150,
    "records_failed": 0,
    "started_at": "2026-01-18T10:00:00Z",
    "completed_at": "2026-01-18T10:05:00Z"
  }
}
```

#### Get Sync Logs

```http
GET /admin/api/sipp/sync-logs
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "sync_type": "full",
      "status": "success",
      "records_processed": 150,
      "records_failed": 0,
      "started_at": "2026-01-18T10:00:00Z",
      "completed_at": "2026-01-18T10:05:00Z"
    }
  ]
}
```

### PPID Management

#### Update PPID Request

```http
PUT /admin/api/ppid-requests/{id}
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "status": "processed",
  "response": "Dokumen sedang disiapkan"
}
```

### User Management (Super Admin Only)

#### Create User

```http
POST /admin/api/users
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "name": "New User",
  "email": "user@example.com",
  "password": "password123",
  "role": "author",
  "permissions": {
    "publish_news": true,
    "edit_pages": false
  }
}
```

#### Update User Role

```http
PUT /admin/api/users/{id}/role
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "role": "admin"
}
```

---

## Inertia.js Endpoints

### Web Routes (Inertia Pages)

These routes return Inertia responses with React components:

```http
GET /                     → Inertia::render('Home')
GET /profil               → Inertia::render('Profil')
GET /berita               → Inertia::render('News/Index')
GET /berita/{slug}        → Inertia::render('News/Show')
GET /dokumen              → Inertia::render('Documents/Index')
GET /jadwal-sidang        → Inertia::render('CourtSchedules')
GET /transparansi         → Inertia::render('Transparency')
GET /ppid                 → Inertia::render('PPID/Index')
```

### Props Structure

Each Inertia page receives props from the backend:

```php
// Example: News Index
return Inertia::render('News/Index', [
    'news' => News::latest()->paginate(12),
    'categories' => Category::where('type', 'news')->get(),
    'featured' => News::where('is_featured', true)->limit(3)->get(),
    'filters' => request()->only(['search', 'category']),
]);
```

### Wayfinder Integration

Wayfinder generates TypeScript types for all routes:

```typescript
// resources/js/routes.ts (generated)
export const show = (slug: string | { slug: string }) => ({
    url: `/news/${typeof slug === 'string' ? slug : slug.slug}`,
    method: 'get'
});

// Usage
import { show } from '@/routes/news';
const { url } = show('my-article');
// Returns: "/news/my-article"
```

---

## Error Responses

### HTTP Status Codes

- **200 OK** - Request successful
- **201 Created** - Resource created successfully
- **400 Bad Request** - Invalid request data
- **401 Unauthorized** - Authentication required
- **403 Forbidden** - Insufficient permissions
- **404 Not Found** - Resource not found
- **422 Unprocessable Entity** - Validation failed
- **429 Too Many Requests** - Rate limit exceeded
- **500 Internal Server Error** - Server error

### Error Response Format

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": [
      "Validation error message 1",
      "Validation error message 2"
    ]
  }
}
```

### Validation Errors Example

```http
POST /api/v1/pages
{
  "title": "",  // Required field empty
  "slug": null
}
```

**Response:**
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."],
    "slug": ["The slug field is required."]
  }
}
```

---

## Rate Limiting

API requests are rate limited to prevent abuse:

- **Public endpoints:** 100 requests/minute/IP
- **Authenticated endpoints:** 1000 requests/minute/user
- **Admin endpoints:** 1000 requests/minute/user

**Rate Limit Headers:**
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1642512000
```

### Rate Limit Exceeded Response

```http
HTTP/1.1 429 Too Many Requests
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1642512000
Retry-After: 60

{
  "success": false,
  "message": "Too many requests. Please try again later."
}
```

---

## CORS Configuration

For cross-origin requests, CORS is configured in `config/cors.php`:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],

'allowed_methods' => ['*'],

'allowed_origins' => ['https://pa-penajam.go.id'],

'allowed_origins_patterns' => ['/^https:\/\/.*\.pa-penajam\.go\.id$/'],

'allowed_headers' => ['*'],

'exposed_headers' => [],

'max_age' => 0,

'supports_credentials' => true,
```

---

## Testing API Endpoints

### Using cURL

```bash
# Get all news
curl -X GET "https://pa-penajam.go.id/api/v1/news?page=1&per_page=10"

# Create page with authentication
curl -X POST "https://pa-penajam.go.id/admin/api/pages" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"New Page","slug":"new-page","status":"draft"}'
```

### Using Postman

1. Import API collection (if available)
2. Set base URL
3. Add authentication token to headers
4. Send requests

### Using JavaScript/Fetch

```javascript
// Get news
fetch('https://pa-penajam.go.id/api/v1/news')
  .then(response => response.json())
  .then(data => console.log(data));

// Create page
fetch('https://pa-penajam.go.id/admin/api/pages', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    title: 'New Page',
    slug: 'new-page',
    status: 'draft'
  })
})
  .then(response => response.json())
  .then(data => console.log(data));
```

---

## Webhooks

### Webhook Configuration

The system can send webhooks for certain events:

```php
// config/webhooks.php
'events' => [
    'page.created',
    'page.updated',
    'news.published',
    'ppid.request.created',
],
```

### Webhook Payload

```json
{
  "event": "news.published",
  "timestamp": "2026-01-18T10:00:00Z",
  "data": {
    "id": 1,
    "title": "Breaking News",
    "slug": "breaking-news",
    "url": "https://pa-penajam.go.id/berita/breaking-news"
  }
}
```

---

## Changelog

### Version 1.0.0 (2026-01-18)
- Initial API release
- Public endpoints for pages, news, documents, schedules
- Admin endpoints for content management
- PPID portal endpoints
- SIPP integration endpoints

---

**Document Version:** 1.0.0
**Last Updated:** 2026-01-18
**API Version:** 1.0.0
