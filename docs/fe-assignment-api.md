# FE API Guide: Assignment

Base URL:

- `GET/POST/PUT/DELETE /api/v1/...`

Auth:

- Tất cả route cần `Authorization: Bearer <token>`
- Tất cả route ghi dữ liệu cần thêm `X-Tenant-ID: <tenant_uuid>` nếu tenant không resolve được
- Permission:
  - `permission:assignments,manage`

## Response Format

Success:

```json
{
  "success": true,
  "message": "Message",
  "data": {}
}
```

Error:

```json
{
  "success": false,
  "message": "Error message",
  "errors": null
}
```

## Assignment API

### 1) List assignments

- `GET /api/v1/assignments`

Query:

- `page` optional, default `1`
- `per_page` optional, default `15`
- `assignee_id` optional, lọc theo người được giao
- `assigned_by_id` optional, lọc theo người giao
- `user_id` vẫn được chấp nhận như alias của `assignee_id` để backward compatibility

Response `data`:

```json
{
  "data": [
    {
      "id": "uuid",
      "tenant_id": "uuid",
      "test_id": "uuid",
      "assignee_id": "uuid",
      "assigned_by": "uuid",
      "due_at": "2026-06-30 23:59:59",
      "access_type": "token",
      "max_attempts": 3,
      "status": "assigned",
      "created_at": "2026-06-15T00:00:00.000000Z",
      "updated_at": "2026-06-15T00:00:00.000000Z",
      "test": {
        "id": "uuid",
        "title": "Midterm English",
        "status": "published"
      },
      "assignee": {
        "id": "uuid",
        "email": "student@example.com",
        "display_name": "Student"
      },
      "assigned_by_user": {
        "id": "uuid",
        "email": "creator@example.com",
        "display_name": "Creator"
      }
    }
  ],
  "pagination": {
    "total": 1,
    "count": 1,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1,
    "from": 1,
    "to": 1
  }
}
```

### 2) Get assignment detail

- `GET /api/v1/assignments/{id}`

Response `data`:

```json
{
  "id": "uuid",
  "tenant_id": "uuid",
  "test_id": "uuid",
  "assignee_id": "uuid",
  "assigned_by": "uuid",
  "due_at": "2026-06-30 23:59:59",
  "access_type": "token",
  "max_attempts": 3,
  "status": "assigned",
  "test": {},
  "assignee": {},
  "assigned_by_user": {}
}
```

### 3) Create assignment

- `POST /api/v1/assignments`

Request body:

```json
{
  "test_id": "uuid-test",
  "assignee_id": "uuid-user",
  "due_at": "2026-06-30 23:59:59",
  "max_attempts": 3,
  "access_type": "token"
}
```

Rules:

- `test_id` required uuid, must exist in `tests`
- `assignee_id` required uuid, must exist in `users`
- `due_at` optional nullable, format `Y-m-d H:i:s`
- `max_attempts` optional integer, default `1`
- `access_type` required, one of `account`, `token`

Business rules:

- Test must be `published`
- Test and assignee must belong to the same tenant
- Assigned-by user is taken from the authenticated user
- Duplicate assignment for same assignee + test is blocked
- If `access_type = token`, backend returns `access_token` once
- If `access_type = account`, `access_token` is `null`

Response:

```json
{
  "success": true,
  "message": "Assignment created successfully",
  "data": {
    "assignment": {
      "id": "uuid",
      "tenant_id": "uuid",
      "test_id": "uuid",
      "assignee_id": "uuid",
      "assigned_by": "uuid",
      "due_at": "2026-06-30 23:59:59",
      "access_type": "token",
      "max_attempts": 3,
      "status": "assigned"
    },
    "access_token": "random32chars.uniqid"
  }
}
```

### 4) Update assignment

- `PUT /api/v1/assignments/{id}`

Request body:

```json
{
  "due_at": "2026-06-30 23:59:59",
  "max_attempts": 5,
  "status": "started",
  "access_type": "account"
}
```

Rules:

- All fields optional
- `access_type` can switch between `account` and `token`

### 5) Delete assignment

- `DELETE /api/v1/assignments/{id}`

### 6) Verify access token

- `POST /api/v1/assignments/verify-token`

Request body:

```json
{
  "access_token": "random32chars.uniqid"
}
```

Validation:

- Token must match stored SHA256 hash
- Assignment must not be `expired` or `archived`
- `access_type` must be `token`
- `due_at` must not be in the past

Response:

```json
{
  "success": true,
  "message": "Access token verified",
  "data": {
    "id": "uuid",
    "tenant_id": "uuid",
    "test_id": "uuid",
    "assignee_id": "uuid",
    "assigned_by": "uuid",
    "due_at": "2026-06-30 23:59:59",
    "access_type": "token",
    "max_attempts": 3,
    "status": "assigned"
  }
}
```

## Notes For FE

- FE should display `access_token` only once after create when `access_type = token`
- In list/detail responses, token is not returned
- Use `assignee_id` as the primary filter name; `user_id` is only backward-compatible alias
- Status lifecycle used by current code:
  - `assigned`
  - `started`
  - `completed`
  - `expired`
  - `archived`
