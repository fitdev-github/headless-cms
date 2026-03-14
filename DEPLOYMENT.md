# HeadlessCMS — Deployment Guide
> Apache shared hosting · PHP 8.0 · MariaDB / MySQL

---

## Requirements
| Component | Version |
|-----------|---------|
| PHP       | 8.0.x   |
| MySQL / MariaDB | 5.7+ / 10.2+ |
| Apache    | 2.4+, mod_rewrite enabled |
| Extensions | pdo_mysql, mbstring, openssl, fileinfo, gd or imagick |

---

## 1. Upload files

Upload all project files to your server.
**Recommended DocumentRoot:** point directly to the `/public` subfolder.

If you cannot change DocumentRoot (some shared hosts), use the root `.htaccess` already included — it will rewrite everything through `/public`.

---

## 2. Create the database

In your hosting control panel (cPanel / DirectAdmin), create:
- A MySQL database
- A database user with full privileges on that database

---

## 3. Configure `.env`

```bash
cp .env.production.example .env
```

Edit `.env` and fill in:
```
APP_URL=https://your-domain.com
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

---

## 4. Install dependencies (on server or locally then upload vendor/)

```bash
composer install --no-dev --optimize-autoloader
```

> If the server has no Composer, run this locally and upload the `vendor/` directory.

---

## 5. Generate app key

```bash
php artisan key:generate
```

---

## 6. Run the setup wizard

Open your site in a browser: `https://your-domain.com`

The wizard will guide you through:
1. Requirements check
2. Database connection & migration
3. Admin account creation
4. Site settings

---

## 7. Post-install optimization

```bash
bash optimize.sh
```

This caches config/routes/views and links storage.

---

## 8. Storage permissions

```bash
chmod -R 775 storage bootstrap/cache
```

---

## Updating

```bash
# Pull new files, then:
php artisan migrate --force
bash optimize.sh
```

---

## File Uploads

Uploaded media is stored in `storage/app/public/`.
The `public/storage` symlink is created by `php artisan storage:link`.
Make sure `storage/app/public/` is writable by the web server.

---

## API Quick Reference

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET    | `/api/v1/{slug}` | List entries |
| GET    | `/api/v1/{slug}/{id}` | Single entry |
| POST   | `/api/v1/{slug}` | Create entry |
| PUT    | `/api/v1/{slug}/{id}` | Update entry |
| DELETE | `/api/v1/{slug}/{id}` | Delete entry |
| POST   | `/api/v1/upload` | Upload file(s) |
| GET    | `/api/v1/upload/files` | List media |
| DELETE | `/api/v1/upload/files/{id}` | Delete media |

**Authentication:** `Authorization: Bearer <token>`
Generate tokens in **Admin → Settings → API Tokens**.

---

## Response Format (Strapi v4 compatible)

```json
{
  "data": [
    {
      "id": 1,
      "attributes": {
        "title": "Hello World",
        "slug": "hello-world",
        "createdAt": "2024-01-01T00:00:00.000Z",
        "updatedAt": "2024-01-01T00:00:00.000Z",
        "publishedAt": "2024-01-01T00:00:00.000Z"
      }
    }
  ],
  "meta": {
    "pagination": {
      "page": 1,
      "pageSize": 25,
      "pageCount": 1,
      "total": 1
    }
  }
}
```

## Query Parameters

| Parameter | Example | Description |
|-----------|---------|-------------|
| `filters[field][$eq]` | `filters[title][$eq]=Hello` | Exact match |
| `filters[field][$contains]` | `filters[title][$contains]=hello` | Contains |
| `filters[field][$in]` | `filters[status][$in][0]=published` | In array |
| `sort` | `sort=createdAt:desc` | Sort field:direction |
| `pagination[page]` | `pagination[page]=2` | Page number |
| `pagination[pageSize]` | `pagination[pageSize]=10` | Items per page |
| `populate` | `populate=*` | Include relations/media |
| `publicationState` | `publicationState=preview` | Include drafts (with full-access token) |

---

## Next.js Integration Example

```js
// lib/cms.js
const CMS_URL = process.env.NEXT_PUBLIC_CMS_URL;
const CMS_TOKEN = process.env.CMS_API_TOKEN;

export async function getEntries(slug, params = {}) {
  const qs = new URLSearchParams(params).toString();
  const res = await fetch(`${CMS_URL}/api/v1/${slug}?${qs}`, {
    headers: { Authorization: `Bearer ${CMS_TOKEN}` },
    next: { revalidate: 60 }, // ISR
  });
  return res.json();
}

export async function getEntry(slug, id) {
  const res = await fetch(`${CMS_URL}/api/v1/${slug}/${id}`, {
    headers: { Authorization: `Bearer ${CMS_TOKEN}` },
  });
  return res.json();
}
```

```env
# .env.local
NEXT_PUBLIC_CMS_URL=https://your-cms-domain.com
CMS_API_TOKEN=your_api_token_here
```
