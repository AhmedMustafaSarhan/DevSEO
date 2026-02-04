# DevSEO API Reference

## Base URL
```
https://api.devseo.com/api
```

## Authentication
- Public endpoints: No authentication required
- Admin endpoints: Bearer token via `Authorization: Bearer <token>` header

## Content Negotiation
- All responses are JSON
- Request locale via: `Accept-Language: en` or `Accept-Language: ar`
- Default: `en`

---

## Blog Endpoints

### List Blog Posts
```
GET /blog
```

**Query Parameters**:
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `locale` | string | `en` | `en` or `ar` |
| `region` | string | `GLOBAL` | `EG`, `US`, `GLOBAL` |
| `per_page` | integer | `10` | 1-100 |
| `page` | integer | `1` | Page number |

**Example**:
```bash
curl "https://api.devseo.com/api/blog?locale=en&region=EG&per_page=10"
```

**Response** (200 OK):
```json
{
  "data": [
    {
      "id": "uuid",
      "slug": "post-slug",
      "title": "Post Title",
      "description": "Post excerpt",
      "content": "Full content...",
      "featured_image_url": "https://...",
      "og_image": "https://...",
      "author": {
        "id": "uuid",
        "name": "Author Name",
        "email": "author@example.com",
        "region": "EG"
      },
      "categories": [
        {
          "id": "uuid",
          "slug": "category-slug",
          "name": "Category Name",
          "post_count": 12
        }
      ],
      "tags": [
        {
          "id": "uuid",
          "name": "Tag Name",
          "slug": "tag-slug"
        }
      ],
      "seo": {
        "meta_title": "SEO Title",
        "meta_description": "SEO Description",
        "canonical_url": "https://devseo.com/blog/post-slug",
        "schema_json": { "@context": "https://schema.org", "@type": "BlogPosting", ... },
        "seo_score": 92
      },
      "metrics": {
        "view_count": 324,
        "reading_time_minutes": 8
      },
      "status": {
        "is_published": true,
        "published_at": "2024-02-04T10:00:00Z",
        "created_at": "2024-02-01T14:30:00Z",
        "updated_at": "2024-02-04T10:00:00Z"
      },
      "regions": ["GLOBAL", "EG"]
    }
  ],
  "meta": {
    "locale": "en",
    "region": "EG",
    "total": 45,
    "per_page": 10
  },
  "links": {
    "first": "https://api.devseo.com/api/blog?page=1",
    "last": "https://api.devseo.com/api/blog?page=5",
    "prev": null,
    "next": "https://api.devseo.com/api/blog?page=2"
  }
}
```

---

### Get Single Blog Post
```
GET /blog/{slug}
```

**Query Parameters**:
| Parameter | Type | Default |
|-----------|------|---------|
| `locale` | string | `en` |

**Example**:
```bash
curl "https://api.devseo.com/api/blog/core-web-vitals-for-devs?locale=en"
```

**Response** (200 OK):
```json
{
  "data": { /* Same structure as list */ }
}
```

**Response** (404 Not Found):
```json
{
  "message": "Blog post not found.",
  "slug": "nonexistent-slug"
}
```

---

### Filter Posts by Category
```
GET /blog/category/{categorySlug}
```

**Query Parameters**:
| Parameter | Type | Default |
|-----------|------|---------|
| `locale` | string | `en` |
| `per_page` | integer | `10` |
| `page` | integer | `1` |

**Example**:
```bash
curl "https://api.devseo.com/api/blog/category/performance?locale=en"
```

**Response** (200 OK):
```json
{
  "data": [ /* Post objects */ ],
  "meta": {
    "locale": "en",
    "category": "performance"
  }
}
```

---

### Search Blog Posts
```
GET /blog/search
```

**Query Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `q` | string | Yes | Search query (min 2 chars) |
| `locale` | string | No | `en` or `ar` (default: `en`) |

**Example**:
```bash
curl "https://api.devseo.com/api/blog/search?q=performance&locale=en"
```

**Response** (200 OK):
```json
{
  "data": [ /* Matching posts */ ],
  "meta": {
    "locale": "en",
    "query": "performance",
    "results": 5
  }
}
```

**Response** (422 Unprocessable Entity):
```json
{
  "message": "Search query must be at least 2 characters."
}
```

---

### Get Recent Posts
```
GET /blog/recent
```

**Query Parameters**:
| Parameter | Type | Default |
|-----------|------|---------|
| `locale` | string | `en` |
| `limit` | integer | `5` |

**Example**:
```bash
curl "https://api.devseo.com/api/blog/recent?locale=en&limit=10"
```

**Response** (200 OK):
```json
{
  "data": [ /* Latest posts up to limit */ ],
  "meta": {
    "locale": "en",
    "limit": 10
  }
}
```

---

### Get SEO Metadata
```
GET /blog/{slug}/seo
```

**Example**:
```bash
curl "https://api.devseo.com/api/blog/core-web-vitals-for-devs/seo"
```

**Response** (200 OK):
```json
{
  "slug": "core-web-vitals-for-devs",
  "meta_title": "Core Web Vitals for Developers - DevSEO",
  "meta_description": "Learn how to optimize Core Web Vitals for better SEO rankings...",
  "og_image": "https://devseo.com/images/core-web-vitals-og.jpg",
  "canonical_url": "https://devseo.com/blog/core-web-vitals-for-devs",
  "schema_json": {
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    "headline": "Core Web Vitals for Developers",
    "description": "...",
    "datePublished": "2024-02-04T10:00:00Z",
    "author": {
      "@type": "Person",
      "name": "Ahmed Talaat"
    }
  }
}
```

---

## Contact Endpoints

### Submit Contact Form
```
POST /contact
```

**Headers**:
```
Content-Type: application/json
Accept-Language: en
```

**Request Body**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "subject": "SEO Consultation Request",
  "message": "I would like to discuss SEO strategies for my website...",
  "region": "US"
}
```

**Example**:
```bash
curl -X POST "https://api.devseo.com/api/contact" \
  -H "Content-Type: application/json" \
  -H "Accept-Language: en" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "subject": "SEO Consultation",
    "message": "I would like to discuss SEO strategies...",
    "region": "US"
  }'
```

**Response** (201 Created):
```json
{
  "message": "Your message has been received. We will get back to you soon.",
  "submission_id": "550e8400-e29b-41d4-a716-446655440001"
}
```

**Response** (422 Unprocessable Entity):
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["Name is required"],
    "email": ["Email must be a valid email address"]
  }
}
```

**Validation Rules**:
| Field | Rule |
|-------|------|
| `name` | required, string, 3-100 chars |
| `email` | required, valid email, max 100 |
| `subject` | required, 5-200 chars |
| `message` | required, 20-5000 chars |
| `region` | required, one of: EG, US, INTL |

---

### Get Contact Submission (Admin)
```
GET /contact/{id}
Authorization: Bearer {token}
```

**Example**:
```bash
curl -H "Authorization: Bearer token123" \
  "https://api.devseo.com/api/contact/550e8400-e29b-41d4-a716-446655440001"
```

**Response** (200 OK):
```json
{
  "data": {
    "id": "uuid",
    "name": "John Doe",
    "email": "john@example.com",
    "subject": "SEO Consultation",
    "message": "Full message...",
    "region": "US",
    "status": "new",
    "ip_address": "192.168.1.1",
    "response_message": null,
    "responded_at": null,
    "created_at": "2024-02-04T10:00:00Z"
  }
}
```

---

### Update Contact Submission Status (Admin)
```
PATCH /contact/{id}/status/{status}
Authorization: Bearer {token}
```

**Status Values**: `new`, `in_progress`, `resolved`, `spam`

**Example**:
```bash
curl -X PATCH \
  -H "Authorization: Bearer token123" \
  "https://api.devseo.com/api/contact/550e8400-e29b-41d4-a716-446655440001/status/resolved"
```

**Response** (200 OK):
```json
{
  "message": "Contact submission updated.",
  "submission": {
    "id": "uuid",
    "status": "resolved",
    "responded_at": "2024-02-04T11:30:00Z"
  }
}
```

---

## System Endpoints

### Health Check
```
GET /health
```

**Example**:
```bash
curl "https://api.devseo.com/api/health"
```

**Response** (200 OK):
```json
{
  "status": "ok",
  "timestamp": "2024-02-04T10:30:45Z"
}
```

---

## Error Responses

### 400 Bad Request
```json
{
  "message": "Invalid request parameters"
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated"
}
```

### 403 Forbidden
```json
{
  "message": "Forbidden"
}
```

### 404 Not Found
```json
{
  "message": "Resource not found"
}
```

### 422 Unprocessable Entity
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field": ["Error message"]
  }
}
```

### 429 Too Many Requests
```json
{
  "message": "Too many requests. Please try again later."
}
```

### 500 Internal Server Error
```json
{
  "message": "Server error",
  "error_id": "12345"
}
```

---

## Rate Limiting

**Limit**: 60 requests per minute per IP address

**Headers**:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1707010260
```

---

## Localization

### Supported Languages
- `en` - English
- `ar` - العربية (Arabic)

### Request Locale
Use `Accept-Language` header:
```
Accept-Language: ar
```

Or query parameter:
```
?locale=ar
```

### Translatable Fields
The following fields respond based on locale:
- `title`
- `description`
- `content`
- `meta_title`
- `meta_description`
- Category `name`, `description`, `meta_title`, `meta_description`
- Tag `name`

---

## Usage Examples

### Fetch Post for Astro Build
```javascript
// astro.config.mjs
const response = await fetch(
  'https://api.devseo.com/api/blog?locale=en&per_page=1000',
  { headers: { 'Accept-Language': 'en' } }
);
const { data } = await response.json();
```

### Display Recent Posts in Homepage
```html
<!-- React/Vue/Svelte component -->
<script>
  const posts = await fetch(
    'https://api.devseo.com/api/blog/recent?limit=5&locale=en'
  ).then(r => r.json());
</script>
```

### Submit Contact Form
```javascript
// JavaScript
const response = await fetch('https://api.devseo.com/api/contact', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept-Language': 'en'
  },
  body: JSON.stringify({
    name: 'John',
    email: 'john@example.com',
    subject: 'Question',
    message: 'I have a question about...',
    region: 'US'
  })
});
```

---

## Best Practices

1. **Cache Responses**: Cache blog list (1 hour), posts (24 hours)
2. **Use Pagination**: Always specify `per_page` for large datasets
3. **Error Handling**: Implement exponential backoff for retries
4. **Locale Handling**: Set `Accept-Language` header consistently
5. **Performance**: Use SEO endpoint for metadata, full post endpoint for content
6. **Security**: Never expose API tokens in frontend code

---

**API Version**: 1.0.0
**Last Updated**: February 4, 2024
