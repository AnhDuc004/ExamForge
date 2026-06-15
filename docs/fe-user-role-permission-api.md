# FE API Guide: User, Role, Permission

Base URL:

- `GET/POST/PUT/DELETE /api/v1/...`

Auth:

- Tất cả route bên dưới đều cần `Authorization: Bearer <token>`
- Ngoài ra cần `auth:sanctum`
- Middleware hiện tại:
  - `users` route: `permission:users,manage`
  - `roles` route: `permission:tenant,settings`
  - `permissions` route: `permission:tenant,settings`

## Response Format

Tất cả API đang trả về format chung:

```json
{
  "success": true,
  "message": "Message here",
  "data": {}
}
```

Error format:

```json
{
  "success": false,
  "message": "Error message",
  "errors": null
}
```

## User API

### 1) List users

- `GET /api/v1/users`

Query:

- `tenant_id` optional, lọc theo tenant
- `page` optional, default `1`
- `per_page` optional, default `15`

Response `data`:

```json
{
  "data": [
    {
      "id": "uuid",
      "email": "user@example.com",
      "display_name": "User Name",
      "tenant_id": "uuid",
      "is_active": true,
      "roles": [
        {
          "id": "uuid",
          "name": "Tenant Admin",
          "description": "Full access..."
        }
      ],
      "created_at": "2026-06-15T00:00:00.000000Z",
      "updated_at": "2026-06-15T00:00:00.000000Z"
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

### 2) Get user detail

- `GET /api/v1/users/{id}`

Response `data`:

```json
{
  "id": "uuid",
  "email": "user@example.com",
  "display_name": "User Name",
  "tenant_id": "uuid",
  "is_active": true,
  "roles": []
}
```

### 3) Create user

- `POST /api/v1/users`

Request body:

```json
{
  "email": "user@example.com",
  "display_name": "User Name",
  "password": "password123",
  "tenant_id": "uuid",
  "is_active": true,
  "role_ids": ["uuid-role-1", "uuid-role-2"]
}
```

Rules:

- `email` required, email
- `display_name` required, string
- `password` required, min 8
- `tenant_id` required, uuid, must exist in `tenants`
- `is_active` optional boolean
- `role_ids` optional array of role UUIDs

Response `data`:

```json
{
  "id": "uuid",
  "email": "user@example.com",
  "display_name": "User Name",
  "tenant_id": "uuid",
  "is_active": true,
  "roles": [
    {
      "id": "uuid-role-1",
      "name": "Student",
      "description": "Access to assigned tests..."
    }
  ]
}
```

### 4) Update user

- `PUT /api/v1/users/{id}`

Request body:

```json
{
  "email": "new@example.com",
  "display_name": "New Name",
  "password": "newpassword123",
  "tenant_id": "uuid",
  "is_active": false,
  "role_ids": ["uuid-role-1"]
}
```

Rules:

- All fields are optional
- `password` can be `null` or omitted

Response `data`:

```json
{
  "id": "uuid",
  "email": "new@example.com",
  "display_name": "New Name",
  "tenant_id": "uuid",
  "is_active": false,
  "roles": []
}
```

### 5) Delete user

- `DELETE /api/v1/users/{id}`

Response:

```json
{
  "success": true,
  "message": "User deleted successfully",
  "data": null
}
```

## Role API

### 1) List roles

- `GET /api/v1/roles`

Query:

- `tenant_id` optional
- `page` optional, default `1`
- `per_page` optional, default `15`

Response `data`:

```json
{
  "data": [
    {
      "id": "uuid",
      "tenant_id": "uuid",
      "name": "Tenant Admin",
      "description": "Full access to all resources within the tenant.",
      "permissions": [
        {
          "id": "uuid",
          "resource": "users",
          "action": "manage"
        }
      ],
      "created_at": "2026-06-15T00:00:00.000000Z",
      "updated_at": "2026-06-15T00:00:00.000000Z"
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

### 2) Get role detail

- `GET /api/v1/roles/{id}`

Response `data`:

```json
{
  "id": "uuid",
  "tenant_id": "uuid",
  "name": "Tenant Admin",
  "description": "Full access to all resources within the tenant.",
  "permissions": []
}
```

### 3) Create role

- `POST /api/v1/roles`

Request body:

```json
{
  "tenant_id": "uuid",
  "name": "Student",
  "description": "Access to assigned tests only.",
  "permission_ids": ["uuid-permission-1", "uuid-permission-2"]
}
```

Rules:

- `tenant_id` optional nullable uuid
- `name` required string
- `description` optional nullable string
- `permission_ids` optional array of permission UUIDs

Response `data`:

```json
{
  "id": "uuid",
  "tenant_id": "uuid",
  "name": "Student",
  "description": "Access to assigned tests only.",
  "permissions": [
    {
      "id": "uuid-permission-1",
      "resource": "assignments",
      "action": "manage"
    }
  ]
}
```

### 4) Update role

- `PUT /api/v1/roles/{id}`

Request body:

```json
{
  "tenant_id": "uuid",
  "name": "Reviewer",
  "description": "Can review submissions.",
  "permission_ids": ["uuid-permission-1"]
}
```

Rules:

- All fields optional
- `permission_ids` optional array

### 5) Delete role

- `DELETE /api/v1/roles/{id}`

Response:

```json
{
  "success": true,
  "message": "Role deleted successfully",
  "data": null
}
```

## Permission API

### 1) List permissions

- `GET /api/v1/permissions`

Query:

- `page` optional, default `1`
- `per_page` optional, default `15`

Response `data`:

```json
{
  "data": [
    {
      "id": "uuid",
      "resource": "users",
      "action": "manage",
      "created_at": "2026-06-15T00:00:00.000000Z",
      "updated_at": "2026-06-15T00:00:00.000000Z"
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

### 2) Get permission detail

- `GET /api/v1/permissions/{id}`

Response `data`:

```json
{
  "id": "uuid",
  "resource": "users",
  "action": "manage",
  "created_at": "2026-06-15T00:00:00.000000Z",
  "updated_at": "2026-06-15T00:00:00.000000Z"
}
```

### 3) Create permission

- `POST /api/v1/permissions`

Request body:

```json
{
  "resource": "users",
  "action": "manage"
}
```

Rules:

- `resource` required string
- `action` required string

### 4) Update permission

- `PUT /api/v1/permissions/{id}`

Request body:

```json
{
  "resource": "users",
  "action": "manage"
}
```

Rules:

- Both fields optional

### 5) Delete permission

- `DELETE /api/v1/permissions/{id}`

Response:

```json
{
  "success": true,
  "message": "Permission deleted successfully",
  "data": null
}
```

## Quick Notes For FE

- `roles` inside `UserResource` is always an array.
- `permissions` inside `RoleResource` is always an array.
- `permission_ids` và `role_ids` nên gửi dạng array UUID, không gửi object.
- List API luôn có `pagination`.
- `show/create/update` trả về object đơn, không có `pagination`.

