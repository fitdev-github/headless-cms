# HeadlessCMS

> Headless CMS แบบ self-hosted สร้างด้วย **Laravel 9** และ **PHP 8.0**
> ออกแบบมาให้ใช้งานได้บน shared hosting (DirectAdmin / cPanel) — ไม่ต้องการ Redis หรือ queue worker

---

## ✨ ฟีเจอร์หลัก

| ฟีเจอร์ | รายละเอียด |
|---|---|
| 🗂 **Content-Type Builder** | สร้าง Collection และ Single content type พร้อม field type 16 แบบ |
| 🧩 **Components & Dynamic Zone** | กลุ่ม field ที่นำกลับมาใช้ซ้ำได้ และบล็อกเนื้อหาแบบ polymorphic |
| 🌐 **Internationalization (i18n)** | Entry แยกตาม locale พร้อม API ที่รองรับการกรองตาม locale |
| 🔑 **End-User Auth** | ระบบ authentication ด้วย JWT สำหรับผู้ใช้ frontend พร้อม role-based permissions |
| ⚡ **Webhooks** | HTTP callback เมื่อเกิด event บน CMS (create, publish, delete ฯลฯ) |
| 🖼 **Media Library** | อัปโหลดไฟล์ จัดหมวดหมู่เป็น folder รองรับ alt/caption และ URL พร้อม CDN |
| 🔐 **API Tokens** | Bearer token แบบ full-access, read-only, หรือ custom scopes |
| 📡 **REST API** | Response format เข้ากันได้กับ Strapi v4 รองรับ filter, sort, pagination, populate |

---

## 🛠 เทคโนโลยีที่ใช้

- **Backend:** Laravel 9, PHP 8.0
- **ฐานข้อมูล:** MySQL 5.7+ / MariaDB 10.2+
- **Frontend (Admin):** Blade, Alpine.js (CDN), Tailwind CSS (CDN), Tiptap (Rich Text Editor)
- **Auth:** Laravel Sessions (ผู้ดูแลระบบ) + SHA-256 Bearer Tokens + HS256 JWT (ผู้ใช้ API)
- **Storage:** Laravel Storage disk (local/public)
- **HTTP Client:** Guzzle (สำหรับส่ง Webhook)

---

## ⚙️ ความต้องการของระบบ

| รายการ | เวอร์ชันขั้นต่ำ |
|---|---|
| PHP | 8.0+ |
| MySQL / MariaDB | 5.7+ / 10.2+ |
| Composer | 2.x |
| PHP Extensions | `pdo_mysql`, `mbstring`, `openssl`, `json`, `gd` หรือ `imagick`, `fileinfo` |
| Web Server | Apache (เปิด `mod_rewrite`) หรือ Nginx |
| พื้นที่ disk | ประมาณ 50 MB สำหรับไฟล์แอปพลิเคชัน |

---

## 🚀 เริ่มต้นอย่างรวดเร็ว

### 1. Clone และติดตั้ง

```bash
git clone <repository-url> headless-cms
cd headless-cms
composer install
```

### 2. ตั้งค่า Environment

```bash
cp .env.example .env
php artisan key:generate
```

แก้ไขไฟล์ `.env` ด้วยข้อมูลฐานข้อมูลของคุณ:

```env
APP_URL=https://your-domain.com

DB_HOST=127.0.0.1
DB_DATABASE=ชื่อฐานข้อมูล
DB_USERNAME=ชื่อผู้ใช้
DB_PASSWORD=รหัสผ่าน
```

### 3. ตั้งค่าฐานข้อมูล

```bash
php artisan migrate
php artisan db:seed --class=AdminSeeder
php artisan storage:link
```

### 4. รัน Development Server

```bash
php artisan serve
# เปิดเบราว์เซอร์ไปที่ http://127.0.0.1:8000
```

> **เคล็ดลับ:** ใช้ **Setup Wizard** ที่ `/setup` สำหรับการติดตั้งแบบมีคำแนะนำทีละขั้นตอน

### 5. เข้าสู่ระบบครั้งแรก

| รายการ | ค่า |
|---|---|
| URL | `http://127.0.0.1:8000/admin` |
| Email | `admin@yourcompany.com` |
| รหัสผ่าน | `Admin@1234!` |

> ⚠️ **เปลี่ยนรหัสผ่านทันที** หลังเข้าสู่ระบบครั้งแรก

---

## 📋 คู่มือ Admin Panel

### Dashboard `/admin`

แสดงสถิติรวมของระบบ:
- จำนวน Content Types, Entries, Media files, API Tokens
- รายการ entry ที่อัปเดตล่าสุด 5 รายการ พร้อมชื่อผู้สร้างและเวลา

---

### Content-Type Builder `/admin/content-type-builder`

สร้างและจัดการโครงสร้างเนื้อหา

**ประเภทของ Content Type:**

| ประเภท | รายละเอียด |
|---|---|
| **Collection** | หลาย entry (เช่น บทความ, สินค้า, ประกาศ) |
| **Single** | entry เดียว (เช่น หน้าหลัก, เกี่ยวกับเรา, ตั้งค่าเว็บไซต์) |

**การตั้งค่า Type:**
- Display Name, API Slug (singular / plural), คำอธิบาย, ไอคอน (emoji)
- ☑ **Enable Draft/Publish system** — ให้ entry มีสถานะ Draft หรือ Published ได้
- ☑ **Enable Internationalization (i18n)** — ให้ entry มีเวอร์ชันแยกตาม locale

**การเพิ่ม Field:** คลิก **Add another field** → เลือกประเภท → ตั้งค่า
ดูรายการ field type ทั้งหมดได้ที่ [ตารางอ้างอิง Field Types](#-ตารางอ้างอิง-field-types)

---

### Components `/admin/content-type-builder/components`

Component คือกลุ่มของ field ที่นำกลับมาใช้ซ้ำได้ในหลาย content type

**รูปแบบชื่อ component:** `namespace.ชื่อ`

ตัวอย่าง: `shared.seo`, `blocks.hero`, `sections.testimonial`

**การใช้งานใน content type:**
- **Component field** — ฝัง component หนึ่งตัว (เลือกได้ว่าซ้ำได้หรือไม่)
- **Dynamic Zone field** — ฝัง array ของ component หลายประเภทผสมกัน

---

### Content Manager `/admin/content-manager/{slug}`

สร้างและจัดการ entry ของ content type แต่ละประเภท

**รายการ Entry:**
- กรองตาม **สถานะ** (Draft / Published) — เฉพาะ type ที่เปิด Draft/Publish
- กรองตาม **Locale** (แท็บ EN / TH ฯลฯ) — เฉพาะ type ที่เปิด i18n
- แสดง 20 รายการต่อหน้า

**ฟอร์ม Entry:**
- แสดง field แบบ dynamic ตามการกำหนดของ content type
- **Sidebar panels:**
  - **Publication** — บันทึกเป็น Draft / Publish / ลบ
  - **Locale** (ถ้าเปิด i18n) — badge locale ปัจจุบัน, แปลไปยัง locale ที่ยังไม่มี, ลิงก์ไปยังเวอร์ชันอื่น
  - **API** — URL ของ entry นี้สำหรับเรียกใช้ API

---

### Media Library `/admin/media-library`

**การอัปโหลด:**
- ขนาดไฟล์สูงสุด: **20 MB** ต่อไฟล์
- ไฟล์จัดเก็บอัตโนมัติในโฟลเดอร์ `uploads/YYYY/MM/`
- รองรับ drag-and-drop หรือเลือกไฟล์

**ฟีเจอร์:**
- ค้นหาตาม filename หรือ alt text
- กรองตาม folder และประเภทไฟล์
- แก้ไข metadata: **alt text**, **caption**
- **Media Picker modal** สำหรับเลือกไฟล์ในฟอร์ม entry

---

### Settings — Global `/admin/settings/global`

| การตั้งค่า | รายละเอียด |
|---|---|
| `site_name` | ชื่อเว็บไซต์แสดงใน admin panel |
| `app_url` | URL หลักที่ใช้สร้าง URL ของไฟล์ media ใน API response |
| `cors_origins` | domain ที่อนุญาต คั่นด้วยจุลภาค ใช้ `*` เพื่ออนุญาตทุก origin |
| `timezone` | timezone ของ server สำหรับแสดงวันเวลา |

---

### Settings — Internationalization (i18n)

อยู่ในส่วนล่างของหน้า **Settings → Global**

- **Add Locale** — กรอก ISO 639-1 code เช่น `en`, `th`, `fr`, `de`, `ja`, `zh-tw`
- **Set Default** — locale สำรองเมื่อ API ไม่ระบุ `?locale=`
- **Remove Locale** — ไม่สามารถลบ locale ที่ตั้งเป็น default ได้

---

### Settings — API Tokens `/admin/settings/api-tokens`

สร้าง Bearer token เพื่อให้ frontend application เรียกใช้ API

**ประเภท Token:**

| ประเภท | สิทธิ์ที่ได้รับ |
|---|---|
| `full-access` | อ่าน/เขียนทุก action ในทุก content type และ media |
| `read-only` | `find` และ `findOne` เท่านั้น (ทุก content type) + ดู media |
| `custom` | เลือก action เฉพาะเจาะจงต่อ content type |

**Actions ที่เลือกได้:** `find`, `findOne`, `create`, `update`, `delete`
**Upload Actions:** `upload.find`, `upload.findOne`, `upload.upload`, `upload.delete`

**กำหนดอายุหมดอายุ:** ระบุจำนวนวัน (ไม่กำหนด = ไม่มีวันหมดอายุ)

> ⚠️ ค่า token จะแสดง **เพียงครั้งเดียว** ตอนสร้าง เก็บไว้ในที่ปลอดภัย (เช่น `.env`)

---

### Settings — Users & Permissions `/admin/settings/users-permissions`

จัดการบัญชีผู้ใช้ frontend (API users) และ role-based access control

**Roles:**
- **Public** — request ที่ไม่มี token กำหนดว่า endpoint ไหนเข้าได้โดยไม่ต้อง login
- **Authenticated** — ผู้ใช้ที่ login แล้ว สามารถให้สิทธิ์เพิ่มเติมได้

**Permission Matrix:** สำหรับแต่ละ role เปิด/ปิด `find`, `findOne`, `create`, `update`, `delete` ตาม content type

**แท็บ API Users:** ดูรายการผู้ใช้ทั้งหมด บล็อก/ปลดบล็อกบัญชี

---

### Settings — Webhooks `/admin/settings/webhooks`

ส่ง HTTP POST ไปยัง URL ภายนอกเมื่อเกิด event บน CMS

**Events ที่รองรับ:**

| Event | เมื่อไหร่ |
|---|---|
| `entry.create` | สร้าง entry ใหม่ |
| `entry.update` | แก้ไขเนื้อหา entry |
| `entry.publish` | เผยแพร่ entry |
| `entry.unpublish` | ดึง entry กลับเป็น draft |
| `entry.delete` | ลบ entry |
| `media.upload` | อัปโหลดไฟล์ |
| `media.delete` | ลบไฟล์ |

**Custom Headers:** เพิ่ม HTTP header ได้ตามต้องการ เช่น `Authorization: Bearer secret`

**Delivery Logs:** ดูประวัติการส่ง 10 ครั้งล่าสุดต่อ webhook — HTTP status, response body, สำเร็จ/ล้มเหลว

---

### Settings — Admin Users `/admin/settings/users`

จัดการบัญชีสำหรับเข้าใช้ admin panel

**Roles:** `superadmin`, `editor`

---

## 📡 API Reference

### Base URL

```
https://your-domain.com/api
```

### Authentication (การยืนยันตัวตน)

**API Token** (สำหรับ server-to-server หรือ static site generation):
```http
Authorization: Bearer <api-token>
```

**JWT** (สำหรับผู้ใช้ที่ login แล้ว):
```http
Authorization: Bearer <jwt-token>
```

---

### Auth Endpoints

#### Login (เข้าสู่ระบบ)
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

#### Register (สมัครสมาชิก)
```http
POST /api/auth/local/register
Content-Type: application/json

{
  "username": "johndoe",
  "email": "user@example.com",
  "password": "password123"
}
```

> ต้องเปิดสิทธิ์ใน role **Public** ก่อน

#### ดูข้อมูลผู้ใช้ปัจจุบัน
```http
GET /api/users/me
Authorization: Bearer <jwt>
```

#### แก้ไขโปรไฟล์
```http
PUT /api/users/me
Authorization: Bearer <jwt>
Content-Type: application/json

{
  "username": "ชื่อใหม่"
}
```

---

### Content Endpoints

ใช้ **plural API slug** ของ content type (เช่น `posts`, `products`, `articles`)

#### ดูรายการ Entry
```http
GET /api/v1/{slug}
Authorization: Bearer <token>
```

#### ดู Entry เดียว
```http
GET /api/v1/{slug}/{id}
Authorization: Bearer <token>
```

#### สร้าง Entry
```http
POST /api/v1/{slug}
Authorization: Bearer <token>
Content-Type: application/json

{
  "data": {
    "title": "สวัสดีโลก",
    "content": "<p>เนื้อหาบทความ</p>",
    "publishedAt": "now"
  }
}
```

> ใส่ `"publishedAt": "now"` ใน `data` เพื่อ publish ทันที ถ้าไม่ใส่จะบันทึกเป็น draft

#### แก้ไข Entry
```http
PUT /api/v1/{slug}/{id}
Authorization: Bearer <token>
Content-Type: application/json

{
  "data": {
    "title": "หัวข้อใหม่"
  }
}
```

#### ลบ Entry
```http
DELETE /api/v1/{slug}/{id}
Authorization: Bearer <token>
```

---

### Media Endpoints

#### อัปโหลดไฟล์
```http
POST /api/v1/upload
Authorization: Bearer <token>
Content-Type: multipart/form-data

files=@/path/to/file.jpg
path=blog-images
```

#### ดูรายการไฟล์
```http
GET /api/v1/upload/files
Authorization: Bearer <token>
```

#### ดูไฟล์เดียว
```http
GET /api/v1/upload/files/{id}
Authorization: Bearer <token>
```

#### ลบไฟล์
```http
DELETE /api/v1/upload/files/{id}
Authorization: Bearer <token>
```

---

### Query Parameters (พารามิเตอร์การค้นหา)

#### การกรอง (Filters)

```
GET /api/v1/posts?filters[title][$eq]=สวัสดี
GET /api/v1/posts?filters[title][$contains]=โลก
GET /api/v1/posts?filters[views][$gte]=100
GET /api/v1/posts?filters[status][$in][0]=published&filters[status][$in][1]=draft
```

**Operators ที่รองรับ:**

| Operator | ความหมาย |
|---|---|
| `$eq` | เท่ากับ |
| `$ne` | ไม่เท่ากับ |
| `$lt` | น้อยกว่า |
| `$lte` | น้อยกว่าหรือเท่ากับ |
| `$gt` | มากกว่า |
| `$gte` | มากกว่าหรือเท่ากับ |
| `$contains` | มีคำนี้อยู่ (case-insensitive) |
| `$in` | ค่าอยู่ใน array |
| `$null` | เป็น null (`true`/`false`) |

#### การเรียงลำดับ (Sort)

```
GET /api/v1/posts?sort=createdAt:desc
GET /api/v1/posts?sort=title:asc
```

#### Pagination (การแบ่งหน้า)

```
GET /api/v1/posts?pagination[page]=2&pagination[pageSize]=10
```

ค่าเริ่มต้น: `page=1`, `pageSize=25` สูงสุด: `pageSize=100`

#### Population (ดึงข้อมูล relation และ media)

```
GET /api/v1/posts?populate=*              (ดึงทุก relation/media)
GET /api/v1/posts?populate=thumbnail      (เฉพาะ field ที่ระบุ)
GET /api/v1/posts?populate=author,tags    (หลาย field)
```

ถ้าไม่ระบุ `populate` ค่า relation และ media จะเป็น `{"data": null}`

#### Publication State (สถานะการเผยแพร่)

```
GET /api/v1/posts?publicationState=live      (ค่าเริ่มต้น — เฉพาะ published)
GET /api/v1/posts?publicationState=preview   (รวม draft — ต้องใช้ full-access token)
```

#### Locale (i18n)

```
GET /api/v1/posts                   (locale เริ่มต้น)
GET /api/v1/posts?locale=th         (เฉพาะภาษาไทย)
GET /api/v1/posts?locale=all        (ทุก locale)
```

---

### รูปแบบ Response

Response ทั้งหมดใช้รูปแบบ **Strapi v4**

**Collection (รายการ):**
```json
{
  "data": [
    {
      "id": 1,
      "attributes": {
        "title": "บทความแรก",
        "locale": "th",
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

**Single Entry:**
```json
{
  "data": {
    "id": 1,
    "attributes": {
      "title": "บทความแรก",
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

**Error (ข้อผิดพลาด):**
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

### ตัวอย่าง Code

#### JavaScript / TypeScript

```typescript
const API_URL = 'https://your-domain.com/api/v1';
const TOKEN   = 'your-api-token';

// ดึงรายการบทความพร้อม thumbnail
const res  = await fetch(`${API_URL}/posts?populate=thumbnail`, {
  headers: { Authorization: `Bearer ${TOKEN}` }
});
const { data, meta } = await res.json();

// สร้างบทความใหม่ (publish ทันที)
const post = await fetch(`${API_URL}/posts`, {
  method: 'POST',
  headers: {
    Authorization: `Bearer ${TOKEN}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    data: { title: 'สวัสดีโลก', content: '<p>เนื้อหา</p>', publishedAt: 'now' }
  })
});
```

#### cURL

```bash
# ดึงบทความที่กรองตามหัวข้อ
curl -H "Authorization: Bearer TOKEN" \
  "https://your-domain.com/api/v1/posts?filters[title][\$contains]=สวัสดี&populate=*"

# อัปโหลดไฟล์
curl -X POST \
  -H "Authorization: Bearer TOKEN" \
  -F "files=@./image.jpg" \
  -F "path=blog" \
  "https://your-domain.com/api/v1/upload"
```

---

## 📐 ตารางอ้างอิง Field Types

| Type | รายละเอียด | ตัวอย่าง API Output |
|---|---|---|
| `text` | ข้อความบรรทัดเดียว | `"สวัสดีโลก"` |
| `textarea` | ข้อความหลายบรรทัด | `"บรรทัด 1\nบรรทัด 2"` |
| `richtext` | HTML จาก WYSIWYG editor (Tiptap) | `"<p>สวัสดี <strong>โลก</strong></p>"` |
| `email` | ที่อยู่อีเมล พร้อม validation | `"user@example.com"` |
| `number` | ตัวเลข (integer หรือ float) | `42` หรือ `3.14` |
| `boolean` | True/False (toggle) | `true` |
| `date` | วันที่เท่านั้น (YYYY-MM-DD) | `"2026-03-15"` |
| `datetime` | วันที่และเวลา (ISO 8601) | `"2026-03-15T10:00:00"` |
| `password` | เข้ารหัสด้วย Bcrypt — **ไม่คืนค่าใน API** | — |
| `enumeration` | เลือกจากตัวเลือกที่กำหนดล่วงหน้า | `"published"` |
| `uid` | slug ที่ unique (สร้างอัตโนมัติจาก field ต้นทาง) | `"my-post-slug"` |
| `media` | ไฟล์/รูปภาพที่แนบมา | `{ "data": { "id": 1, "attributes": {...} } }` |
| `relation` | ลิงก์ไปยัง entry ของ content type อื่น | `{ "data": [ {"id": 2, ...} ] }` |
| `json` | JSON object แบบอิสระ | `{ "key": "value" }` |
| `component` | component ที่ฝังอยู่ | `{ "__component": "shared.seo", "metaTitle": "..." }` |
| `dynamiczone` | array ของ component หลายประเภท | `[{ "__component": "blocks.hero", "heading": "..." }]` |

**Field Options ที่ใช้บ่อย:**

| Option | Field Types ที่ใช้ได้ | รายละเอียด |
|---|---|---|
| `required` | ส่วนใหญ่ | ต้องกรอกค่าก่อนบันทึก |
| `private` | ส่วนใหญ่ | ซ่อนจาก API response |
| `unique` | text, email | ค่าต้อง unique ใน content type |
| `minLength` / `maxLength` | text, textarea, email | จำกัดความยาว |
| `min` / `max` | number | จำกัดช่วงตัวเลข |
| `enum_values` | enumeration | รายการค่าที่อนุญาต (คั่นด้วย newline) |
| `target_field` | uid | ชื่อ field ที่ใช้สร้าง slug อัตโนมัติ |
| `multiple` | media | อนุญาตให้เลือกไฟล์หลายไฟล์ |
| `relation` | relation | `oneToOne`, `oneToMany`, หรือ `manyToMany` |
| `component_id` | component | ID ของ component ที่ต้องการฝัง |
| `repeatable` | component | อนุญาตให้มีหลาย instance |
| `allowed_component_ids` | dynamiczone | array ของ ID component ที่อนุญาต |
| `localizable` | ทุกประเภท | ค่าแตกต่างตาม locale หรือไม่ (ค่าเริ่มต้น: `true`) |

---

## ⚡ Webhooks

### รูปแบบ Payload

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

### ตัวอย่าง: Next.js ISR Revalidation

**ตั้งค่า Webhook:**
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
  revalidatePath('/blog');
  if (body.entry?.id) revalidatePath(`/blog/${body.entry.id}`);
  return Response.json({ revalidated: true });
}
```

---

## 🌐 Internationalization (i18n)

### การตั้งค่าเบื้องต้น

1. ไปที่ **Settings → Global → Internationalization**
2. เพิ่ม locale code เช่น `th`, `en`, `fr`, `de`, `ja`, `zh-tw`
3. ตั้ง **default locale** (ใช้เป็น fallback เมื่อไม่ระบุ `?locale=`)

### เปิดใช้งานบน Content Type

1. ไปที่ **Content-Type Builder → [ชื่อ type] → Type Settings**
2. เช็ค **"Enable Internationalization (i18n)"**
3. คลิก **Save Changes**

### การสร้างคำแปล

1. เปิด entry ที่ต้องการใน **Content Manager**
2. ที่ sidebar **Locale card** → คลิก **+ Add translation** ของ locale เป้าหมาย
3. ฟอร์มใหม่จะเปิดขึ้น โดยกรอกค่า field ที่ไม่ localizable ไว้ให้ล่วงหน้า
4. กรอกเนื้อหาที่แปลแล้ว → บันทึก

### Per-Field Localizability

ค่าเริ่มต้น: field ทุก field เป็น **localizable** (แต่ละ locale มีค่าเป็นของตัวเอง)
หากต้องการให้ค่าเดียวกันในทุก locale (เช่น relation ไปยัง category):
แก้ไข field → ยกเลิกเช็ค **Localizable**

### การกรองตาม Locale ผ่าน API

```bash
# locale เริ่มต้น (ไม่ต้องระบุ)
GET /api/v1/posts

# เฉพาะภาษาไทย
GET /api/v1/posts?locale=th

# ทุก locale (แต่ละ entry จะมี "locale" ใน attributes)
GET /api/v1/posts?locale=all
```

---

## 🚢 การ Deploy

### Shared Hosting — DirectAdmin / cPanel

#### 1. อัปโหลดไฟล์

อัปโหลดไฟล์ทั้งหมดไปยัง server ผ่าน SFTP หรือ File Manager (เช่น ไปที่ `~/headless-cms/`)

#### 2. ตั้งค่า Environment

```bash
cp .env.production.example .env
```

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_HOST=localhost
DB_DATABASE=ชื่อ_db
DB_USERNAME=ชื่อผู้ใช้
DB_PASSWORD=รหัสผ่าน

SESSION_DRIVER=file
CACHE_DRIVER=file
```

#### 3. ติดตั้งและตั้งค่า

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=AdminSeeder --force
php artisan storage:link
```

#### 4. Optimize สำหรับ Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
# หรือรัน script รวม:
bash optimize.sh
```

#### 5. ตั้ง DocumentRoot ไปที่ `/public`

ใน **DirectAdmin** → Domains → domain ของคุณ → **Document Root:**
```
/home/username/headless-cms/public
```

ไฟล์ `/public/.htaccess` จัดการ URL rewriting โดยอัตโนมัติ (ต้องเปิด `mod_rewrite`)

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

## 📁 โครงสร้างโปรเจกต์

```
headless-cms/
├── app/
│   ├── Http/
│   │   ├── Controllers/Admin/    # Controller สำหรับ admin panel
│   │   ├── Controllers/Api/      # Controller สำหรับ REST API
│   │   ├── Controllers/Setup/    # Installation wizard
│   │   └── Middleware/           # ApiTokenAuth, cors, installed checks
│   ├── Models/                   # Eloquent models (Entry, Field, Component, ApiUser, ...)
│   └── Services/                 # Business logic (FieldRenderer, EntryService, WebhookService, ...)
├── database/
│   └── migrations/               # ไฟล์ migration 17 ไฟล์
├── resources/
│   └── views/admin/              # Blade templates สำหรับทุกส่วนของ admin
├── routes/
│   ├── web.php                   # Route ของ admin panel (ต้อง login)
│   └── api.php                   # Route ของ REST API
├── storage/app/public/           # ไฟล์ที่อัปโหลด (symlink ไปที่ public/storage)
└── public/                       # Web root — index.php + .htaccess
```

---

## 🔒 ข้อมูล Login เริ่มต้น

| บัญชี | Email | รหัสผ่าน |
|---|---|---|
| Admin | `admin@yourcompany.com` | `Admin@1234!` |

> ⚠️ **เปลี่ยนรหัสผ่านทันที** หลังติดตั้งเสร็จ ผ่าน **Settings → Users**

---

## 📄 License

MIT License — ใช้งานได้ฟรีทั้งส่วนตัวและเชิงพาณิชย์
