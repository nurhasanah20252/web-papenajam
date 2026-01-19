# Joomla Data Migration Mapping

## Overview
This document defines the mapping between Joomla 3 export data and the new PA Penajam Laravel application schema.

## Data Export Statistics
- **Categories**: 685 records (`joomla_categories.json`)
- **Content**: 15,832 records (`joomla_content.json`)
- **Menu Items**: 3,367 records (`joomla_menu.json`)
- **Images**: 144 records (`joomla_images.json`)
- **Users**: 105 records (`joomla_users.json`)

## 1. Users Mapping

### Joomla Schema (`joomla_users.json`)
| Field | Type | Description |
|-------|------|-------------|
| id | int | Primary Key |
| name | string | Full Name |
| username | string | Login Username |
| email | string | Email Address |
| status | string | active/blocked |
| registered_at | datetime | Registration Date |

### Target Schema (`users` table)
| Joomla Field | Target Field | Transformation |
|--------------|--------------|----------------|
| id | (metadata) | Store in `joomla_migration_items` |
| name | name | Direct mapping |
| email | email | Direct mapping |
| username | username | Direct mapping |
| status | (none) | Map 'active' -> no change, 'blocked' -> maybe `deactivated_at` if exists |
| registered_at | created_at | Direct mapping |
| N/A | password | Set random, require reset via Fortify |

## 2. Categories Mapping

### Joomla Schema (`joomla_categories.json`)
| Field | Type | Description |
|-------|------|-------------|
| id | int | Primary Key |
| parent_id | int | Parent Category ID |
| title | string | Category Name |
| extension | string | e.g., 'com_content', 'com_contact' |

### Target Schema (`categories` table)
| Joomla Field | Target Field | Transformation |
|--------------|--------------|----------------|
| id | (metadata) | Store in `joomla_migration_items` |
| title | name | Direct mapping |
| title | slug | Slugify(title) |
| parent_id | parent_id | Map via `joomla_migration_items`. Map 0 or 1 to NULL |
| extension | type | 'com_content' -> 'news', 'com_contact' -> 'page', 'com_banners' -> 'news' |
| N/A | order | Use Joomla ID or sequence |

## 3. Content Mapping (Pages & News)

### Joomla Schema (`joomla_content.json`)
| Field | Type | Description |
|-------|------|-------------|
| id | int | Primary Key |
| title | string | Article Title |
| alias | string | URL Slug |
| content | string | HTML Content |
| created_at | datetime | Creation Date |
| category_id | int | Category Reference |
| status | int | 1: Published, 0: Draft, -2: Archived |

### Target Selection Logic
- If category `type` is 'page' -> `pages` table
- If category `type` is 'news' -> `news` table

### Target Schema (`pages` table)
| Joomla Field | Target Field | Transformation |
|--------------|--------------|----------------|
| title | title | Direct mapping |
| alias | slug | Direct mapping |
| content | content | Convert HTML to JSON Blocks (e.g., `[{"type":"text","data":{"content":"..."}}]`) |
| content | excerpt | Strip tags and truncate |
| status | status | 1 -> 'published', 0 -> 'draft', -2 -> 'archived' |
| created_at | published_at | Direct mapping |
| category_id | (metadata) | Use for routing to `pages` table |

### Target Schema (`news` table)
| Joomla Field | Target Field | Transformation |
|--------------|--------------|----------------|
| title | title | Direct mapping |
| alias | slug | Direct mapping |
| content | content | Convert HTML to JSON Blocks |
| content | excerpt | Strip tags and truncate |
| category_id | category_id | Map via `joomla_migration_items` |
| status | status | 1 -> 'published', 0 -> 'draft', -2 -> 'archived' |
| created_at | published_at | Direct mapping |

## 4. Menu Mapping

### Joomla Schema (`joomla_menu.json`)
| Field | Type | Description |
|-------|------|-------------|
| id | int | Primary Key |
| title | string | Label |
| menutype | string | Menu Name (e.g., 'main') |
| link | string | Internal Link |
| parent_id | int | Parent Item ID |
| level | int | Depth |
| published | int | 1: Active, 0: Disabled |

### Target Schema (`menu_items` table)
| Joomla Field | Target Field | Transformation |
|--------------|--------------|----------------|
| title | title | Direct mapping |
| parent_id | parent_id | Map via `joomla_migration_items`. 0 or 1 -> NULL |
| published | is_active | 1 -> true, 0 -> false |
| link | url_type | Parse link: if `option=com_content&view=article` -> 'page' |
| link | page_id | Extract ID from link and map to local `pages.id` |
| link | custom_url | If external or unparseable |
| menutype | menu_id | Map to `menus` table based on name |

## 5. Media Mapping

### Joomla Schema (`joomla_images.json`)
- References images in content or sliders.
- Fields: `path`, `type`, `article_id`.

### Target Strategy
- Copy files from Joomla `images/` to Laravel `storage/app/public/images/`.
- Update content blocks to reference new paths.
- Use `featured_image` column in `pages`/`news` for primary images.

## Data Transformation Rules

### 1. Status Mapping
Joomla uses integer status codes which need to be mapped to Laravel's string-based enums:

| Joomla Status | Target Status (Pages/News) |
|---------------|---------------------------|
| 1             | 'published'               |
| 0             | 'draft'                   |
| -1            | 'archived' (or 'draft')   |
| -2            | 'archived'                |

### 2. Date Transformation
Joomla dates (usually Y-m-d H:i:s) should be parsed using Carbon and stored as standard Laravel timestamps.

### 3. HTML Cleanup & Block Conversion
Joomla content is stored as raw HTML. Our Laravel application uses a block-based system (JSON):
- **Step 1**: Strip Joomla-specific tags (`{loadposition}`, `{loadmodule}`, etc.).
- **Step 2**: Clean up inline styles and attributes that conflict with the new design.
- **Step 3**: Wrap the cleaned HTML into a 'text' block: `[{"type": "text", "data": {"content": "..."}}]`.
- **Step 4**: Extract the first image found in the HTML to use as the `featured_image` if one isn't explicitly provided.

### 4. Slug Generation
If a Joomla `alias` is missing, generate a slug from the `title`. Ensure uniqueness by appending a counter if a collision occurs within the target table.

## Gap Analysis & Special Handling

| Data Type | Gap / Challenge | Proposed Solution |
|-----------|-----------------|-------------------|
| **Users** | Password incompatibility | Notify users to reset passwords or set a temporary one. |
| **Media** | Broken absolute links | Use `JoomlaDataCleaner` to rewrite `images/` paths to `storage/` and fix relative links. |
| **Shortcodes** | Joomla plugins/modules | Remove or replace with equivalent Laravel components/widgets. |
| **Menus** | Complex routing | Map `com_content` links to new `pages` or `news` routes. Use `custom` URL type for unknown patterns. |
| **Documents** | File attachments | Match files from `joomla_images.json` and `joomla_content.json` to the `documents` table. |

## Strategy: Migration Approach
We recommend using the existing **Console Command** (`php artisan joomla:migrate`) for the following reasons:
1. **Performance**: Handling 15,000+ records in a browser-based UI (Filament) might lead to timeouts.
2. **Reliability**: CLI allows for better memory management and long-running processes.
3. **Traceability**: The `joomla_migration_items` table provides a robust way to track what has been migrated and allows for targeted rollbacks.
4. **Developer-Friendly**: CLI is easier to integrate into deployment pipelines if needed.

## Migration Order
1. Users
2. Categories
3. Content (Pages & News)
4. Menus & Menu Items
5. Media/Images (can be parallelized)
