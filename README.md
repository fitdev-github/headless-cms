# HeadlessCMS

> A self-hosted, Strapi-inspired Headless CMS built with **Laravel 9** and **PHP 8.0**.
> Designed for shared hosting (DirectAdmin / cPanel) — no Redis, no queue worker required.

---

## ✨ Features

| Feature | Description |
|---|---|
| 🗂 **Content-Type Builder** | Create Collection and Single types with 16 field types |
| 🧩 **Components & Dynamic Zone** | Reusable field groups and polymorphic content blocks |
| 🌐 **Internationalization (i18n)** | Per-locale entry variants with locale-aware API queries |
| 🔑 **End-User Auth** | JWT-based authentication for frontend users with role-based permissions |
| ⚡ **Webhooks** | HTTP callbacks on content events (create, publish, delete, etc.) |
| 🖼 **Media Library** | File uploads with folder organization, alt/caption, and CDN-ready URLs |
| 🔐 **API Tokens** | Bearer tokens with full-access, read-only, or custom ability scopes |
| 📡 **REST API** | Strapi v4-compatible JSON responses with filtering, sorting, pagination, and population |

---

## 🛠 Tech Stack

- **Backend:** Laravel 9, PHP 8.0
- **Database:** MySQL 5.7+ / MariaDB 10.2+
- **Frontend (Admin):** Blade, Alpine.js (CDN), Tailwind CSS (CDN), Tiptap (Rich Text editor)
- **Auth:** Laravel Sessions (Admin) + SHA-256 Bearer Tokens + HS256 JWT (End-User)
- **Storage:** Laravel Storage disk (local/public)
- **HTTP Client:** Guzzle (for Webhook delivery)

---

## ⚙️ Requirements

| Requirement | Minimum |
|---|---|
| PHP | 8.0+ |
| MySQL / MariaDB | 5.7+ / 10.2+ |
| Composer | 2.x |
| PHP Extensions | `pdo_mysql`, `mbstring`, `openssl`, `json`, `gd` or `imagick`, `fileinfo` |
| Web Server | Apache (with `mod_rewrite`) or Nginx |
| Disk | ~50 MB for application files |

---

## 🚀 Quick Start

### 1. Clone and Install

```bash
git clone <repository-url> headless-cms
cd headless-cms
composer install
```

### 2. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials:

```env
APP_URL=https://your-domain.com

DB_HOST=127.0.0.1
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Database Setup

```bash
php artisan migrate
php artisan db:seed --class=AdminSeeder
php artisan storage:link
```

### 4. Run Development Server

```bash
php artisan serve
# Visit http://127.0.0.1:8000
```

> **Tip:** Use the built-in **Setup Wizard** at `/setup` for a guided installation experience.

### 5. First Login

| Field | Value |
|---|---|
| URL | `http://127.0.0.1:8000/admin` |
| Email | `admin@yourcompany.com` |
| Password | `Admin@1234!` |

> ⚠️ **Change the default password immediately** after first login.

---

## 📋 Admin Panel Guide

### Dashboard `/admin`

Overview statistics:
- Total Content Types, Entries, Media files, API Tokens
- List of 5 most recently updated entries with creator name

---

### Content-Type Builder `/admin/content-type-builder`

Create and manage your content structure.

**Content Type Kinds:**

| Kind | Description |
|---|---|
| **Collection** | Multiple entries (e.g. Blog Posts, Products, Articles) |
| **Single** | One entry only (e.g. Homepage settings, About page) |

**Type Settings:**
- Display Name, API Slug (singular / plural), Description, Icon (emoji)
- ☑ **Enable Draft/Publish system** — allows entries to be in Draft or Published state
- ☑ **Enable Internationalization (i18n)** — enables per-locale entry variants

**Field Management:** Click **Add another field** → select a type → configure options.
See [Field Types Reference](#-field-types-reference) for the complete list of 16 field types.

---

### Components `/admin/content-type-builder/components`

Components are reusable groups of fields that can be embedded in any content type.

**Naming convention:** `namespace.componentName`

Examples: `shared.seo`, `blocks.hero`, `sections.testimonial`

**Usage in content types:**
- **Component field** — embed a specific component (optionally repeatable)
- **Dynamic Zone field** — embed an array of heterogeneous component types

---

### Content Manager `/admin/content-manager/{slug}`

Create and manage entries for each content type.

**Entry List:**
- Filter by **Status** (Draft / Published) — only for types with Draft/Publish enabled
- Filter by **Locale** (e.g. EN / TH tabs) — only for localized types
- Pagination: 20 entries per page

**Entry Form:**
- Dynamic field rendering based on content type definition
- **Sidebar panels:**
  - **Publication** — Save as Draft / Publish / Delete
  - **Locale** (if localized) — current locale badge, translate to missing locales, links to existing translations
  - **API** — quick reference URL for this entry

---

### Media Library `/admin/media-library`

**Upload:**
- Max file size: **20 MB** per file
- Files auto-organized into `uploads/YYYY/MM/` folders
- Drag-and-drop or file picker

**Features:**
- Search by filename or alt text
- Filter by folder and file type
- Edit metadata: **alt text**, **caption**
- Inline **Media Picker modal** for use in entry forms

---

### Settings — Global `/admin/settings/global`

| Setting | Description |
|---|---|
| `site_name` | Displayed in the admin panel title |
| `app_url` | Base URL used for generating media file URLs in API responses |
| `cors_origins` | Comma-separated allowed origins. Use `*` to allow all. |
| `timezone` | Server timezone for date/time display |

---

### Settings — Internationalization

Located in the lower section of **Settings → Global**.

- **Add Locale** — Enter an ISO 639-1 code: `en`, `th`, `fr`, `de`, `ja`, `zh-tw`, etc.
- **Set Default** — The fallback locale when no `?locale=` parameter is sent in API requests
- **Remove Locale** — Cannot remove the currently set default locale

---

### Settings — API Tokens `/admin/settings/api-tokens`

Create Bearer tokens to authenticate API requests from your frontend application.

**Token Types:**

| Type | Permissions |
|---|---|
| `full-access` | All read/write actions on all content types and media |
| `read-only` | `find` and `findOne` on all content types + media listing |
| `custom` | Choose specific actions per content type |

**Available actions:** `find`, `findOne`, `create`, `update`, `delete`
**Upload actions:** `upload.find`, `upload.findOne`, `upload.upload`, `upload.delete`

**Optional:** Set an expiration duration in days.

> ⚠️ The token value is shown **only once** at creation. Store it securely (e.g. `.env` file).

---

### Settings — Users & Permissions `/admin/settings/users-permissions`

Manage frontend (API) user accounts and role-based access control.

**Roles:**
- **Public** — Unauthenticated requests. Configure which endpoints are publicly accessible.
- **Authenticated** — Logged-in API users. Grant additional permissions.

**Permission Matrix:** For each role, toggle `find`, `findOne`, `create`, `update`, `delete` per content type.

**API Users Tab:** List all registered users. Block or unblock accounts.

---

### Settings — Webhooks `/admin/settings/webhooks`

Send HTTP POST notifications to external URLs when CMS events occur.

**Available events:**

| Event | When it fires |
|---|---|
| `entry.create` | A new entry is created |
| `entry.update` | Entry content is updated |
| `entry.publish` | Entry is published |
| `entry.unpublish` | Entry is reverted to draft |
| `entry.delete` | Entry is deleted |
| `media.upload` | A file is uploaded |
| `media.delete` | A file is deleted |

**Custom Headers:** Add arbitrary HTTP headers (e.g. `Authorization: Bearer secret`).

**Delivery Logs:** View last 10 delivery attempts — HTTP status code, response body, success/failure.

---

### Settings — Admin Users `/admin/settings/users`

Manage admin panel accounts.

**Roles:** `superadmin`, `editor`

---

## 📡 API Reference

### Base URL

```
https://your-domain.com/api
```

### Authentication

**API Token** (for server-to-server or static site generation):
```http
Authorization: Bearer <api-token>
```

**JWT** (for logged-in frontend users):
```http
Authorization: Bearer <jwt-token>
```

---

### Auth Endpoints

#### Login
```http
POST /api/auth/local
Content-Type: application/json

{
  "identifier": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "jwt": "eyJhbGciOiJIUzI1NiIs...",
  "user": {
    "id": 1,
    "username": "johndoe",
    "email": "user@example.com",
    "confirmed": true,
    "blocked": false,
    "role": { "id": 2, "name": "Authenticated" }
  }
}
```

#### Register
```http
POST /api/auth/local/register
Content-Type: application/json

{
  "username": "johndoe",
  "email": "user@example.com",
  "password": "password123"
}
```

> Requires the **Public** role to have the appropriate permission enabled.

#### Get Current User
```http
GET /api/users/me
Authorization: Bearer <jwt>
```

#### Update Profile
```http
PUT /api/users/me
Authorization: Bearer <jwt>
Content-Type: application/json

{
  "username": "new_username"
}
```

---

### Content Endpoints

Use the **plural API slug** of the content type (e.g. `posts`, `products`, `articles`).

#### List Entries
```http
GET /api/v1/{slug}
Authorization: Bearer <token>
```

#### Get Single Entry
```http
GET /api/v1/{slug}/{id}
Authorization: Bearer <token>
```

#### Create Entry
```http
POST /api/v1/{slug}
Authorization: Bearer <token>
Content-Type: application/json

{
  "data": {
    "title": "Hello World",
    "content": "<p>My content</p>",
    "publishedAt": "now"
  }
}
```

> Include `"publishedAt": "now"` in `data` to publish immediately. Omit it to save as draft.

#### Update Entry
```http
PUT /api/v1/{slug}/{id}
Authorization: Bearer <token>
Content-Type: application/json

{
  "data": {
    "title": "Updated Title"
  }
}
```

#### Delete Entry
```http
DELETE /api/v1/{slug}/{id}
Authorization: Bearer <token>
```

---

### Media Endpoints

#### Upload File
```http
POST /api/v1/upload
Authorization: Bearer <token>
Content-Type: multipart/form-data

files=@/path/to/file.jpg
path=blog-images
```

#### List Files
```http
GET /api/v1/upload/files
Authorization: Bearer <token>
```

#### Get Single File
```http
GET /api/v1/upload/files/{id}
Authorization: Bearer <token>
```

#### Delete File
```http
DELETE /api/v1/upload/files/{id}
Authorization: Bearer <token>
```

---

### Query Parameters

#### Filtering

```
GET /api/v1/posts?filters[title][$eq]=Hello
GET /api/v1/posts?filters[title][$contains]=world
GET /api/v1/posts?filters[views][$gte]=100
GET /api/v1/posts?filters[status][$in][0]=published&filters[status][$in][1]=draft
```

**Supported operators:**

| Operator | Description |
|---|---|
| `$eq` | Equal to |
| `$ne` | Not equal to |
| `$lt` | Less than |
| `$lte` | Less than or equal |
| `$gt` | Greater than |
| `$gte` | Greater than or equal |
| `$contains` | Contains substring (case-insensitive) |
| `$in` | Value in array |
| `$null` | Is null (`true`/`false`) |

#### Sorting

```
GET /api/v1/posts?sort=createdAt:desc
GET /api/v1/posts?sort=title:asc
```

#### Pagination

```
GET /api/v1/posts?pagination[page]=2&pagination[pageSize]=10
```

Default: `page=1`, `pageSize=25`. Maximum `pageSize`: 100.

#### Population (Relations & Media)

```
GET /api/v1/posts?populate=*
GET /api/v1/posts?populate=thumbnail
GET /api/v1/posts?populate=author,tags
```

Without `populate`, relation and media fields return `{"data": null}`.

#### Publication State

```
GET /api/v1/posts?publicationState=live     (default — published only)
GET /api/v1/posts?publicationState=preview  (include drafts — requires full-access token)
```

#### Locale (i18n)

```
GET /api/v1/posts                  (default locale)
GET /api/v1/posts?locale=th        (Thai entries only)
GET /api/v1/posts?locale=all       (all locales)
```

---

### Response Format

All responses follow the **Strapi v4** format.

**Collection (list):**
```json
{
  "data": [
    {
      "id": 1,
      "attributes": {
        "title": "My First Post",
        "locale": "en",
        "createdAt": "2026-03-15T10:00:00.000Z",
        "updatedAt": "2026-03-15T10:00:00.000Z",
        "publishedAt": "2026-03-15T10:00:00.000Z"
      }
    }
  ],
  "meta": {
    "pagination": {
      "page": 1,
      "pageSize": 25,
      "pageCount": 4,
      "total": 92
    }
  }
}
```

**Single entry:**
```json
{
  "data": {
    "id": 1,
    "attributes": {
      "title": "My First Post",
      "thumbnail": {
        "data": {
          "id": 5,
          "attributes": {
            "name": "cover.jpg",
            "url": "/storage/uploads/2026/03/cover.jpg",
            "mime": "image/jpeg",
            "size": 120.5,
            "width": 1200,
            "height": 630
          }
        }
      }
    }
  }
}
```

**Error:**
```json
{
  "data": null,
  "error": {
    "status": 404,
    "name": "entry.notFound",
    "message": "Entry not found.",
    "details": {}
  }
}
```

---

### Code Examples

#### JavaScript / TypeScript

```typescript
const API_URL = 'https://your-domain.com/api/v1';
const TOKEN   = 'your-api-token';

// List published posts with populated thumbnail
const res  = await fetch(`${API_URL}/posts?populate=thumbnail`, {
  headers: { Authorization: `Bearer ${TOKEN}` }
});
const { data, meta } = await res.json();

// Create a post (published immediately)
const post = await fetch(`${API_URL}/posts`, {
  method: 'POST',
  headers: {
    Authorization: `Bearer ${TOKEN}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    data: { title: 'Hello World', content: '<p>...</p>', publishedAt: 'now' }
  })
});
```

#### cURL

```bash
# List posts filtered by title
curl -H "Authorization: Bearer TOKEN" \
  "https://your-domain.com/api/v1/posts?filters[title][\$contains]=hello&populate=*"

# Upload a file
curl -X POST \
  -H "Authorization: Bearer TOKEN" \
  -F "files=@./image.jpg" \
  -F "path=blog" \
  "https://your-domain.com/api/v1/upload"
```

---

## 📐 Field Types Reference

| Type | Description | API Output Example |
|---|---|---|
| `text` | Single-line string | `"Hello World"` |
| `textarea` | Multi-line string | `"Line 1\nLine 2"` |
| `richtext` | HTML from WYSIWYG editor (Tiptap) | `"<p>Hello <strong>World</strong></p>"` |
| `email` | Email address with format validation | `"user@example.com"` |
| `number` | Integer or float | `42` or `3.14` |
| `boolean` | True/False | `true` |
| `date` | Date only (YYYY-MM-DD) | `"2026-03-15"` |
| `datetime` | Date + time (ISO 8601) | `"2026-03-15T10:00:00"` |
| `password` | Bcrypt-hashed — **write only, never returned in API** | — |
| `enumeration` | Select from predefined list | `"published"` |
| `uid` | URL-safe slug (auto-generated from source field) | `"my-post-slug"` |
| `media` | File/image attachment | `{ "data": { "id": 1, "attributes": {...} } }` |
| `relation` | Link to entries of another content type | `{ "data": [ {"id": 2, ...} ] }` |
| `json` | Free-form JSON object | `{ "key": "value" }` |
| `component` | Embedded reusable component | `{ "__component": "shared.seo", "metaTitle": "..." }` |
| `dynamiczone` | Array of mixed component types | `[{ "__component": "blocks.hero", "heading": "..." }]` |

**Common Field Options:**

| Option | Applicable Types | Description |
|---|---|---|
| `required` | Most | Value must be provided on save |
| `private` | Most | Field hidden from API responses |
| `unique` | text, email | Value must be unique within the content type |
| `minLength` / `maxLength` | text, textarea, email | String length constraints |
| `min` / `max` | number | Numeric range constraints |
| `enum_values` | enumeration | Newline-separated list of allowed values |
| `target_field` | uid | Field name to auto-generate slug from |
| `multiple` | media | Allow multiple file selections |
| `relation` | relation | `oneToOne`, `oneToMany`, or `manyToMany` |
| `component_id` | component | ID of the component to embed |
| `repeatable` | component | Allow multiple instances in an array |
| `allowed_component_ids` | dynamiczone | Array of component IDs that can be added |
| `localizable` | all | Whether value varies per locale (default: `true`) |

---

## ⚡ Webhooks

### Payload Format

**Content events (`entry.*`):**
```json
{
  "event": "entry.publish",
  "createdAt": "2026-03-15T10:00:00.000Z",
  "model": "posts",
  "uid": "api::posts.posts",
  "entry": {
    "id": 1,
    "status": "published",
    "createdAt": "2026-03-15T09:00:00.000Z",
    "updatedAt": "2026-03-15T10:00:00.000Z"
  }
}
```

**Media events (`media.*`):**
```json
{
  "event": "media.upload",
  "createdAt": "2026-03-15T10:00:00.000Z",
  "media": {
    "id": 5,
    "filename": "cover.jpg",
    "url": "/storage/uploads/2026/03/cover.jpg",
    "mime_type": "image/jpeg",
    "size": 123456
  }
}
```

### Next.js ISR Revalidation Example

**Webhook configuration:**
- **URL:** `https://your-nextjs.com/api/revalidate`
- **Events:** `entry.publish`, `entry.unpublish`, `entry.delete`
- **Custom Header:** `x-webhook-secret: YOUR_SECRET`

```typescript
// app/api/revalidate/route.ts (Next.js App Router)
export async function POST(req: Request) {
  const secret = req.headers.get('x-webhook-secret');
  if (secret !== process.env.WEBHOOK_SECRET) {
    return Response.json({ error: 'Unauthorized' }, { status: 401 });
  }
  const body = await req.json();
  revalidatePath(`/blog`);
  if (body.entry?.id) revalidatePath(`/blog/${body.entry.id}`);
  return Response.json({ revalidated: true });
}
```

---

## 🌐 Internationalization (i18n)

### Setup

1. **Settings → Global → Internationalization** → Add locale codes
   Examples: `en`, `th`, `fr`, `de`, `ja`, `ko`, `zh-tw`
2. Set the **default locale** (fallback when `?locale=` is not specified in API calls)

### Enable on a Content Type

1. **Content-Type Builder → [Type Name] → Type Settings**
2. Check **"Enable Internationalization (i18n)"**
3. **Save Changes**

### Creating Translations

1. Open an entry in **Content Manager**
2. In the sidebar **Locale card** → click **+ Add translation** for the target locale
3. A new entry form opens pre-filled with **non-localizable** field values (shared across locales)
4. Fill in the translated content and save

### Per-Field Localizability

By default all fields are **localizable** (each locale has its own value).
To share a value across all locales (e.g. a category relation), edit the field → uncheck **Localizable**.

### API Filtering

```bash
# Default locale entries (no param)
GET /api/v1/posts

# Specific locale
GET /api/v1/posts?locale=th

# All locales (each entry includes "locale" in attributes)
GET /api/v1/posts?locale=all
```

---

## 🚢 Deployment

### Shared Hosting — DirectAdmin / cPanel

#### 1. Upload Files

Upload the project to your server via SFTP (e.g. to `~/headless-cms/`).

#### 2. Configure Environment

```bash
cp .env.production.example .env
```

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_HOST=localhost
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_password

SESSION_DRIVER=file
CACHE_DRIVER=file
```

#### 3. Install & Setup

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=AdminSeeder --force
php artisan storage:link
```

#### 4. Optimize

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
# or simply:
bash optimize.sh
```

#### 5. Point DocumentRoot to `/public`

In **DirectAdmin** → Domains → your domain → **Document Root:**
```
/home/username/headless-cms/public
```

The `/public/.htaccess` file handles URL rewriting automatically (`mod_rewrite` must be enabled).

---

### VPS / Dedicated Server — Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/headless-cms/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff2)$ {
        expires max;
        log_not_found off;
    }
}
```

---

## 📁 Project Structure

```
headless-cms/
├── app/
│   ├── Http/
│   │   ├── Controllers/Admin/    # Admin panel controllers
│   │   ├── Controllers/Api/      # REST API controllers
│   │   ├── Controllers/Setup/    # Installation wizard
│   │   └── Middleware/           # ApiTokenAuth, cors, installed checks
│   ├── Models/                   # Eloquent models (Entry, Field, Component, ApiUser, ...)
│   └── Services/                 # Business logic (FieldRenderer, EntryService, WebhookService, ...)
├── database/
│   └── migrations/               # 17 migration files
├── resources/
│   └── views/admin/              # Blade templates for every admin section
├── routes/
│   ├── web.php                   # Admin panel routes (auth-protected)
│   └── api.php                   # REST API routes
├── storage/app/public/           # Uploaded files (linked to public/storage)
└── public/                       # Web root — index.php + .htaccess
```

---

## 🔒 Default Credentials

| Account | Email | Password |
|---|---|---|
| Admin | `admin@yourcompany.com` | `Admin@1234!` |

> ⚠️ **Change these immediately** after installation via **Settings → Users**.

---

## 📄 License

MIT License — free for personal and commercial use.
