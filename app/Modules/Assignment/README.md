# Assignment Module - ExamForge

## Overview

The Assignment module manages the distribution of published tests to users. Assignments link users to tests with configurable due dates, attempt limits, and access tokens. Supports soft deletion for canceling assignments.

## Architecture

```
Route (api/v1/assignments)
  ↓
Controller (AssignmentController)
  ↓
DTO (CreateAssignmentDTO, UpdateAssignmentDTO)
  ↓
Service (AssignmentService)
  ↓
Repository (AssignmentRepository)
  ↓
Eloquent Model (Assignment)
```

## Features

- **Test Assignment**: Assign published tests to users
- **Due Dates**: Set optional due dates for assignments
- **Attempt Limits**: Control maximum attempts per assignment
- **Access Tokens**: SHA256-hashed tokens for secure test access
- **Status Tracking**: assigned → started → completed → expired/archived
- **Soft Delete**: Cancel assignments without data loss
- **Tenant Isolation**: Assignments scoped to tenant
- **User Isolation**: Unique user+test combinations per tenant
- **Full CRUD**: Create, read, update, delete operations
- **Access Verification**: Verify tokens and check attempt availability
- **Events**: Future integration with scoring and notifications

## API Endpoints

### Public Operations (requires `auth:sanctum`)

#### 1. List Assignments
```http
GET /api/v1/assignments?page=1&per_page=15&assignee_id={optional_user_id}
Authorization: Bearer {token}
X-Tenant-ID: {tenant_id}

Response: 200 OK
{
  "success": true,
  "message": "Assignments retrieved",
  "data": {
    "data": [
      {
        "id": "uuid",
        "tenant_id": "uuid",
        "assignee_id": "uuid",
        "assigned_by": "uuid",
        "test_id": "uuid",
        "due_at": "2026-06-30T23:59:59.000000Z",
        "access_type": "account",
        "max_attempts": 3,
        "status": "started",
        "created_at": "2026-06-09T...",
        "updated_at": "2026-06-09T..."
      }
    ],
    "pagination": {
      "total": 50,
      "count": 15,
      "per_page": 15,
      "current_page": 1,
      "last_page": 4,
      "from": 1,
      "to": 15
    }
  }
}
```

#### 2. Get Assignment by ID
```http
GET /api/v1/assignments/{id}
Authorization: Bearer {token}

Response: 200 OK
{
  "success": true,
  "message": "Assignment retrieved",
  "data": { ... }
}
```

### Privileged Operations (require `auth:sanctum` + permission)

#### 3. Create Assignment
```http
POST /api/v1/assignments
Authorization: Bearer {token}
X-Tenant-ID: {tenant_id}
Content-Type: application/json

Required Permissions: assignment:create

{
  "test_id": "uuid",
  "assignee_id": "uuid",
  "due_at": "2026-06-30 23:59:59",
  "max_attempts": 3,
  "access_type": "account"
}

Response: 201 Created
{
  "success": true,
  "message": "Assignment created successfully",
  "data": {
    "assignment": { ... },
    "access_token": "random32chars.uniqid"
  }
}
```

**Note:** The `access_token` is returned **only once** at creation. Store it securely. It will not be returned in subsequent API calls. The stored token in DB is SHA256-hashed.

You can also assign the same test to multiple students in one request:

```json
{
  "test_id": "uuid",
  "assignee_ids": ["uuid-1", "uuid-2", "uuid-3"],
  "due_at": "2026-06-30 23:59:59",
  "max_attempts": 3,
  "access_type": "account"
}
```

#### 4. Update Assignment
```http
PUT /api/v1/assignments/{id}
Authorization: Bearer {token}
Content-Type: application/json

Required Permissions: assignment:update

{
  "due_at": "2026-06-30 23:59:59",
  "max_attempts": 5,
  "status": "started"
}

Response: 200 OK
{
  "success": true,
  "message": "Assignment updated successfully",
  "data": { ... }
}
```

#### 5. Delete Assignment
```http
DELETE /api/v1/assignments/{id}
Authorization: Bearer {token}

Required Permissions: assignment:delete

Response: 200 OK
{
  "success": true,
  "message": "Assignment deleted successfully"
}
```

### Special Operations

#### 6. Verify Access Token
```http
POST /api/v1/assignments/verify-token
Authorization: Bearer {token}
Content-Type: application/json

{
  "access_token": "random32chars.uniqid"
}

Response: 200 OK
{
  "success": true,
  "message": "Access token verified",
  "data": {
    "id": "uuid",
    "tenant_id": "uuid",
    "assignee_id": "uuid",
    "test_id": "uuid",
    "status": "assigned",
    "max_attempts": 3
  }
}
```

**Validation Checks:**
- Token exists in database
- Status is not "expired" or "archived"
- Due date has not passed (if set)
- Current attempts < max attempts

**Error Responses:**
```json
{
  "success": false,
  "message": "Invalid or expired access token"
}

{
  "success": false,
  "message": "Assignment has expired"
}

{
  "success": false,
  "message": "Maximum attempts exceeded"
}
```

## Request Validation

### CreateAssignmentRequest

**Rules:**
- `test_id`: required, uuid, must exist in tests table
- `assignee_id`: required when `assignee_ids` is missing, uuid, must exist in users table
- `assignee_ids`: required when `assignee_id` is missing, array of uuid values, must contain at least one student
- `due_at`: nullable, date_format Y-m-d H:i:s, must be after now
- `max_attempts`: nullable, integer (1-100, default: 1)

**Example:**
```json
{
  "test_id": "550e8400-e29b-41d4-a716-446655440000",
  "assignee_ids": [
    "650e8400-e29b-41d4-a716-446655440001",
    "650e8400-e29b-41d4-a716-446655440002"
  ],
  "due_at": "2026-06-30 23:59:59",
  "max_attempts": 3
}
```

### UpdateAssignmentRequest

**Rules:** All fields optional

**Example:**
```json
{
  "max_attempts": 5,
  "status": "started"
}
```

## DTOs

### CreateAssignmentDTO
- `test_id: string` (UUID, must be published test)
- `assignee_id?: string` (UUID, for single student assignment)
- `assignee_ids?: array<string>` (UUID list, for bulk assignment)
- `due_at?: string` (Y-m-d H:i:s format, nullable)
- `max_attempts?: int` (default: 1, 1-100)

### UpdateAssignmentDTO
- `due_date?: string` (Y-m-d H:i:s format)
- `max_attempts?: int` (1-100)
- `status?: string` (assigned, started, completed, expired, archived)

## Services

### AssignmentService

**Methods:**

```php
create(CreateAssignmentDTO $dto, string $tenantId): array
```
Create new assignment. Validates:
- Test exists and is published
- No duplicate assignment for each assignee+test combo
Generates SHA256-hashed access token.
Returns access token unencrypted (only time it's readable).

```php
update(string $assignmentId, UpdateAssignmentDTO $dto): array
```
Update assignment with partial update support.

```php
getById(string $assignmentId): array
```
Retrieve assignment by ID.

```php
listByTenant(string $tenantId, int $page = 1, int $perPage = 15): array
```
List all assignments for tenant with pagination.

```php
listByUser(string $userId, int $page = 1, int $perPage = 15): array
```
List all assignments for user.

```php
listByUserAndTenant(string $userId, string $tenantId, int $page = 1, int $perPage = 15): array
```
List assignments for specific user in tenant.

```php
verifyAccessToken(string $accessTokenHash): array
```
Verify access token validity. Checks:
- Token exists
- Status not expired/archived
- Due date not passed
- Attempts not exceeded
Throws exception with specific message for each failure.

```php
incrementAttempts(string $assignmentId): void
```
Increment attempt count. Updates status to 'completed' when max reached.

```php
delete(string $assignmentId): void
```
Soft delete assignment.

## Repositories

### AssignmentRepository

Implements: `AssignmentRepositoryInterface`

**Methods:**

```php
findById(string $id)
```
Find assignment by UUID.

```php
create(array $attributes)
```
Create new assignment record.

```php
update(string $id, array $attributes)
```
Update assignment with given attributes.

```php
delete(string $id): void
```
Soft delete assignment by ID.

```php
listByTenant(string $tenantId, int $page = 1, int $perPage = 15)
```
Paginated list of tenant assignments.

```php
listByUser(string $userId, int $page = 1, int $perPage = 15)
```
Paginated list of user assignments.

```php
listByUserAndTenant(string $userId, string $tenantId, int $page = 1, int $perPage = 15)
```
Paginated list of assignments for user in tenant.

```php
findByAccessToken(string $accessToken)
```
Find non-expired assignment by access token (SHA256).

```php
findByUserAndTest(string $userId, string $testId)
```
Find existing assignment for user+test combo.

## Models

### Assignment Model

**Traits:**
- `HasUuid` - Auto-generates UUID on creation
- `SoftDeletes` - Soft delete support

**Relationships:**
- `user()` - BelongsTo User
- `test()` - BelongsTo Test
- `tenant()` - BelongsTo Tenant
- `attempts()` - HasMany Attempt

**Attributes:**
- `id: uuid` (primary key)
- `tenant_id: uuid` (foreign key)
- `user_id: uuid` (foreign key to users.id)
- `test_id: uuid` (foreign key to tests.id)
- `due_date: timestamp` (nullable, when assignment expires)
- `access_token: string` (SHA256-hashed)
- `max_attempts: integer` (default: 1)
- `current_attempts: integer` (default: 0)
- `status: string` (assigned, started, completed, expired, archived, default: assigned)
- `created_at: timestamp`
- `updated_at: timestamp`
- `deleted_at: timestamp` (soft delete)

**Casts:**
- `current_attempts` → integer
- `max_attempts` → integer
- `due_date` → datetime
- `created_at` → datetime
- `updated_at` → datetime
- `deleted_at` → datetime

**Hidden Fields:**
- `access_token` (never returned in API responses after creation)

## Status Lifecycle

```
assigned → started → completed
      ↓
    expired
      ↓
    archived
```

**Status Definitions:**
- `assigned`: Initial state, awaiting user to start
- `started`: User has started taking the test
- `completed`: User has completed the test (after last attempt)
- `expired`: Due date passed, assignment is no longer accessible
- `archived`: Manually archived by admin

## Access Token Management

### Token Generation
```php
$accessToken = Str::random(32) . '.' . uniqid();
$hashedToken = hash('sha256', $accessToken);
```

### Token Usage Flow
1. **Creation**: Endpoint returns unencrypted token **once only**
2. **Storage**: Only SHA256 hash stored in DB
3. **Verification**: Client hashes token before sending to verify endpoint
4. **Security**: Never expose full token after creation

### Token Verification
```php
$providedToken = "random32chars.uniqid"; // From user
$hash = hash('sha256', $providedToken);
$assignment = Assignment::where('access_token', $hash)->first();
```

## Middleware Requirements

### auth:sanctum
All endpoints require authenticated user with valid Sanctum token.

### permission:resource,action
Write operations require specific permissions:
- `permission:assignment,create` - Create assignments
- `permission:assignment,update` - Update assignments
- `permission:assignment,delete` - Delete assignments

## Headers

### X-Tenant-ID (optional for write operations)
If tenant cannot be resolved from middleware, explicitly provide via header:
```
X-Tenant-ID: {tenant_uuid}
```

## Error Handling

### Validation Errors (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "test_id": ["Test not found"],
    "user_id": ["User not found"],
    "due_date": ["Due date must be in the future"]
  }
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Assignment not found"
}
```

### Business Logic Error (400)
```json
{
  "success": false,
  "message": "Test must be published before assigning"
}

{
  "success": false,
  "message": "Assignment already exists for this user and test"
}

{
  "success": false,
  "message": "Invalid or expired access token"
}

{
  "success": false,
  "message": "Assignment has expired"
}

{
  "success": false,
  "message": "Maximum attempts exceeded"
}
```

### Permission Denied (403)
```json
{
  "success": false,
  "message": "Forbidden"
}
```

## Testing Examples

### Using cURL

```bash
# Create assignment
curl -X POST http://localhost:8000/api/v1/assignments \
  -H "Authorization: Bearer {token}" \
  -H "X-Tenant-ID: {tenant_id}" \
  -H "Content-Type: application/json" \
  -d '{
    "test_id": "550e8400-e29b-41d4-a716-446655440000",
    "assignee_ids": [
      "650e8400-e29b-41d4-a716-446655440001",
      "650e8400-e29b-41d4-a716-446655440002"
    ],
    "due_at": "2026-06-30 23:59:59",
    "max_attempts": 3
  }'

# Response includes access_token (save this securely!)

# List assignments
curl -X GET "http://localhost:8000/api/v1/assignments?page=1&per_page=10" \
  -H "Authorization: Bearer {token}"

# Filter by user
curl -X GET "http://localhost:8000/api/v1/assignments?user_id={user_id}" \
  -H "Authorization: Bearer {token}"

# Get assignment
curl -X GET http://localhost:8000/api/v1/assignments/{id} \
  -H "Authorization: Bearer {token}"

# Verify access token
curl -X POST http://localhost:8000/api/v1/assignments/verify-token \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"access_token": "token_from_creation"}'

# Update assignment
curl -X PUT http://localhost:8000/api/v1/assignments/{id} \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"max_attempts": 5, "status": "started"}'

# Delete assignment
curl -X DELETE http://localhost:8000/api/v1/assignments/{id} \
  -H "Authorization: Bearer {token}"
```

## File Structure

```
app/Modules/Assignment/
├── Controllers/
│   └── AssignmentController.php
├── DTOs/
│   ├── CreateAssignmentDTO.php
│   └── UpdateAssignmentDTO.php
├── Models/
│   └── Assignment.php
├── Requests/
│   ├── CreateAssignmentRequest.php
│   └── UpdateAssignmentRequest.php
├── Repositories/
│   ├── Contracts/
│   │   └── AssignmentRepositoryInterface.php
│   └── AssignmentRepository.php
├── Resources/
│   └── AssignmentResource.php
├── Services/
│   └── AssignmentService.php
└── README.md

routes/modules/
└── assignment.php
```

## Integration Points

### With Test Module
- Validates test exists before assignment
- Checks test is published status
- Prevents assignment of unpublished tests

### With User Module
- Validates user exists
- Links assignment to `assignee_id`

### With Attempt Module (upcoming)
- Called when user starts exam via access token
- `incrementAttempts()` called on attempt submission
- Transition status to completed when max attempts reached

### With RBAC
The module uses the `permission` middleware for authorization:

```php
Route::post('/', [AssignmentController::class, 'store'])
    ->middleware('permission:assignment,create');
```

## Next Steps

1. Create Attempt module to start exams via access token
2. Add bulk assignment operations (CSV import, role-based)
3. Add assignment analytics (completion rates, performance)
4. Implement assignment renewal workflow
5. Add prerequisite assignment checks
6. Add assignment reminders (scheduled emails)
7. Add exam proctoring workflow
8. Integrate with answer recording and auto-scoring
