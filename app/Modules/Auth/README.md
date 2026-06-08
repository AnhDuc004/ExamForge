# Auth Module - ExamForge

## Overview

The Auth module handles user authentication and authorization using **Laravel Sanctum** for token-based API authentication. It provides endpoints for user login, registration, token management, and profile access.

## Architecture

```
Route (api/v1/auth)
  ↓
Controller (AuthController)
  ↓
DTO (LoginDTO, RegisterDTO)
  ↓
Service (AuthService)
  ↓
Repository (UserRepository, TenantRepository)
  ↓
Eloquent Model (User)
```

## Features

- **User Login**: Authenticate with email and password; receive API token
- **User Registration**: Create new account; assigned to default or specified tenant
- **Token Refresh**: Revoke current token and issue new one
- **Logout**: Revoke current token
- **Revoke All Tokens**: Revoke all active tokens for user
- **Profile Endpoint**: Fetch authenticated user info

## API Endpoints

### Public Endpoints

#### 1. Login
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123",
  "device_name": "iPhone"  // optional
}

Response: 200 OK
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "1|...token...",
    "token_type": "Bearer"
  }
}
```

#### 2. Register
```http
POST /api/v1/auth/register
Content-Type: application/json

{
  "email": "newuser@example.com",
  "display_name": "John Doe",
  "password": "password123",
  "password_confirmation": "password123",
  "device_name": "iPhone"  // optional
}

Response: 201 Created
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": { ... },
    "token": "1|...token...",
    "token_type": "Bearer"
  }
}
```

### Protected Endpoints (require `auth:sanctum`)

#### 3. Get Current User Profile
```http
GET /api/v1/auth/me
Authorization: Bearer {token}

Response: 200 OK
{
  "success": true,
  "message": "User profile",
  "data": {
    "id": "uuid",
    "email": "user@example.com",
    "display_name": "John Doe",
    "tenant_id": "uuid",
    "is_active": true,
    "created_at": "2026-06-08T10:00:00Z"
  }
}
```

#### 4. Logout
```http
POST /api/v1/auth/logout
Authorization: Bearer {token}

Response: 200 OK
{
  "success": true,
  "message": "Logout successful"
}
```

#### 5. Refresh Token
```http
POST /api/v1/auth/refresh
Authorization: Bearer {token}
Content-Type: application/json

{
  "device_name": "iPhone"  // optional
}

Response: 200 OK
{
  "success": true,
  "message": "Token refreshed",
  "data": {
    "user": { ... },
    "token": "2|...new_token...",
    "token_type": "Bearer"
  }
}
```

#### 6. Revoke All Tokens
```http
POST /api/v1/auth/revoke-all
Authorization: Bearer {token}

Response: 200 OK
{
  "success": true,
  "message": "All tokens revoked"
}
```

## Request Validation

### LoginRequest
- `email`: required, valid email format
- `password`: required, minimum 8 characters
- `device_name`: optional, max 255 characters

### RegisterRequest
- `email`: required, valid email, unique in users table
- `display_name`: required, max 255 characters
- `password`: required, minimum 8 characters, must be confirmed
- `device_name`: optional, max 255 characters

## Error Handling

All auth errors return standardized JSON responses:

```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }  // optional validation errors
}
```

### Common Errors

| HTTP | Error | Cause |
|------|-------|-------|
| 422 | Validation errors | Invalid request data |
| 401 | Credentials incorrect | Wrong email/password |
| 401 | User account inactive | Account disabled |
| 409 | Email already registered | Registration with existing email |

## DTOs

### LoginDTO
- `email: string`
- `password: string`
- `device_name?: string`

### RegisterDTO
- `email: string`
- `display_name: string`
- `password: string`
- `device_name?: string`

## Services

### AuthService

Methods:
- `login(LoginDTO $dto, ?string $tenantId): array` - Authenticate user and issue token
- `register(RegisterDTO $dto, ?string $tenantId): array` - Create user and issue token
- `logout($user): void` - Revoke current token
- `revokeAllTokens($user): void` - Revoke all user tokens
- `refreshToken($user, ?string $deviceName): array` - Issue new token

## Repositories

### UserRepository

Implements: `UserRepositoryInterface`

Methods:
- `findById(string $id)` - Find user by ID
- `findByEmail(string $email)` - Find user by email
- `create(array $attributes)` - Create new user

### TenantRepository

Implements: `TenantRepositoryInterface`

Methods:
- `findBySlug(string $slug)` - Find tenant by slug
- `findDefaultTenant()` - Get default tenant for new registrations

## Models

### User Model

**Traits:**
- `HasUuid` - Auto-generates UUID on creation
- `HasPermission` - RBAC methods (hasRole, hasPermission, etc.)
- `HasApiTokens` - Sanctum token support

**Relationships:**
- `roles()` - BelongsToMany relationship with Role model

**Attributes:**
- `id: uuid` (primary key)
- `email: string`
- `display_name: string`
- `password_hash: string`
- `tenant_id: uuid` (foreign key)
- `is_active: boolean`
- `created_at: timestamp`

**Hidden Attributes:**
- `password_hash` - Excluded from API responses

## Configuration

### Sanctum Config
Located in `config/sanctum.php`:
- Token expiration: 365 days (configurable)
- Device names: Optional device identifier for tokens
- Multiple tokens per user: Supported

### Environment Variables
```env
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:8000
SANCTUM_TOKEN_PREFIX=examforge_
```

## Testing Example

### Using cURL

```bash
# Register
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "display_name": "Test User",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123",
    "device_name": "Postman"
  }'

# Get Profile (using token from login response)
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Logout
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Integration with RBAC

After authentication, user permissions are checked via:
1. `User->roles()` relationship
2. `Role->permissions()` relationship
3. `HasPermission` trait methods:
   - `hasRole(string $role): bool`
   - `hasPermission(string $resource, string $action): bool`
   - `hasAnyRole(array $roles): bool`
   - `hasAnyPermission(array $pairs): bool`

Use `permission` middleware in routes:

```php
Route::post('tests', [TestController::class, 'store'])
    ->middleware('permission:test,create');
```

## File Structure

```
app/Modules/Auth/
├── Controllers/
│   └── AuthController.php
├── DTOs/
│   ├── LoginDTO.php
│   └── RegisterDTO.php
├── Requests/
│   ├── LoginRequest.php
│   └── RegisterRequest.php
├── Resources/
│   └── UserResource.php
└── Services/
    └── AuthService.php

routes/modules/
└── auth.php
```

## Next Steps

1. Implement email verification after registration
2. Add password reset functionality
3. Add two-factor authentication (2FA)
4. Add refresh token rotation
5. Add rate limiting to auth endpoints
6. Add audit logging for auth events
