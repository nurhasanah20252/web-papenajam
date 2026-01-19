# PH2.1 Page Builder System Implementation Plan

## Overview
This document details the implementation strategy for the Page Builder system, transitioning from the current prototype to a production-ready visual editor.

## 1. Database & Schema Refinement (PH2.1.1)
- **Goal**: Support full versioning and metadata.
- **Actions**:
    - Create `page_versions` table: `id`, `page_id`, `content` (JSON), `created_by`, `created_at`.
    - Update `Page` model to handle version snapshots.
    - Ensure `PageBlock` model has `meta` field for SEO and custom attributes.

## 2. Robust Backend API (PH2.1.2)
- **Goal**: Reliable sync and server-side rendering.
- **Actions**:
    - Refactor `PageBuilderController@syncBlocks` to use a recursive approach for nested blocks.
    - Implement `App\Services\PageBuilder\BlockRenderer` to generate HTML for all block types.
    - Add `App\Http\Requests\PageBuilder\SaveRequest` for strict validation of block data.

## 3. Advanced Frontend Interface (PH2.1.3)
- **Goal**: Seamless user experience with nesting support.
- **Actions**:
    - Update `@dnd-kit` implementation to support `verticalListSortingStrategy` within nested containers.
    - Implement "Undo/Redo" logic using the history state in `PageBuilder.tsx`.
    - Create a "Template Gallery" component to allow starting from predefined layouts.

## 4. Feature-Rich Component Library (PH2.1.4)
- **Goal**: 15+ functional blocks.
- **Required Blocks**:
    - **Layout**: Section (w/ backgrounds), Columns (variable widths), Container.
    - **Basic**: Text (Tiptap), Heading, Button, Separator, Spacer.
    - **Media**: Image (w/ lightbox), Gallery (grid/masonry), Video (YouTube/Vimeo).
    - **Advanced**: HTML, Accordion, Tabs, Card Grid.
    - **Court Specific**: SIPP Schedule, News Grid, Document List.

## 5. Testing Strategy
- **Unit**: Test `BlockRenderer` output for all types.
- **Feature**: Test `update` API with complex nested JSON structures.
- **Browser**: Test drag-and-drop workflows and property editing using Pest v4.
