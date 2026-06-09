# Question Module - ExamForge

## Overview

The Question module manages exam questions with support for multiple question types, difficulty levels, JSONB-based options and answers, and tagging system. Questions are tenant-isolated and created by users.

## Architecture

```
Route (api/v1/questions)
  ↓
Controller (QuestionController)
  ↓
DTO (CreateQuestionDTO, UpdateQuestionDTO)
  ↓
Service (QuestionService)
  ↓
Repository (QuestionRepository)
  ↓
Eloquent Model (Question)
```

## Features

- **Multiple Question Types**: multiple_choice, short_answer, essay, true_false
- **Difficulty Levels**: easy, medium, hard
- **JSONB Storage**: options and correct_answer stored as JSONB for flexibility
- **Tag System**: Questions can be tagged for organization
- **Question Status**: draft, published, archived
- **Tenant Isolation**: Questions isolated by tenant
- **Full CRUD**: Create, Read, Update, Delete operations
- **Filtering**: By status, by tags, pagination support
- **Events**: QuestionCreated event dispatched on creation
- **Relationships**: Belongs to Tenant and Creator (User)

## API Endpoints

### Public Operations (requires `auth:sanctum`)

#### 1. List All Questions
```http
GET /api/v1/questions?page=1&per_page=15
Authorization: Bearer {token}

Response: 200 OK
{
  "success": true,
  "message": "Questions retrieved",
  "data": {
    "data": [
      {
        "id": "uuid",
        "tenant_id": "uuid",
        "created_by": "uuid",
        "type": "multiple_choice",
        "content": "What is 2+2?",
        "options": ["3", "4", "5"],
        "correct_answer": ["4"],
        "max_score": 1,
        "difficulty": "easy",
        "tags": ["math", "basic"],
        "status": "draft",
        "created_at": "2026-06-09T...",
        "updated_at": "2026-06-09T..."
      }
    ],
    "pagination": {
      "total": 100,
      "count": 15,
      "per_page": 15,
      "current_page": 1,
      "last_page": 7,
      "from": 1,
      "to": 15
    }
  }
}
```

#### 2. Get Question by ID
```http
GET /api/v1/questions/{id}
Authorization: Bearer {token}

Response: 200 OK
{
  "success": true,
  "message": "Question retrieved",
  "data": { ... }
}
```

#### 3. Filter Questions by Status
```http
GET /api/v1/questions/by-status/{status}?page=1&per_page=15
Authorization: Bearer {token}

Query Parameters:
- page: int (default: 1)
- per_page: int (default: 15)

Status Values: draft, published, archived

Response: 200 OK
{
  "success": true,
  "message": "Questions with status 'published' retrieved",
  "data": {
    "data": [...],
    "pagination": {...}
  }
}
```

#### 4. Filter Questions by Tags
```http
GET /api/v1/questions/by-tags?tags[]=math&tags[]=basic&page=1&per_page=15
Authorization: Bearer {token}

Query Parameters:
- tags[]: array of strings (required)
- page: int (default: 1)
- per_page: int (default: 15)

Response: 200 OK
{
  "success": true,
  "message": "Questions by tags retrieved",
  "data": {
    "data": [...],
    "pagination": {...}
  }
}
```

### Privileged Operations (require `auth:sanctum` + permission)

#### 5. Create Question
```http
POST /api/v1/questions
Authorization: Bearer {token}
X-Tenant-ID: {tenant_id}
Content-Type: application/json

Required Permissions: question:create

{
  "type": "multiple_choice",
  "content": "What is the capital of France?",
  "options": ["London", "Berlin", "Paris", "Madrid"],
  "correct_answer": ["Paris"],
  "max_score": 1,
  "difficulty": "easy",
  "tags": ["geography", "capitals"]
}

Response: 201 Created
{
  "success": true,
  "message": "Question created successfully",
  "data": { ... }
}
```

#### 6. Update Question
```http
PUT /api/v1/questions/{id}
Authorization: Bearer {token}
Content-Type: application/json

Required Permissions: question:update

{
  "content": "Updated content",
  "difficulty": "medium",
  "tags": ["geography", "capitals", "europe"]
}

Response: 200 OK
{
  "success": true,
  "message": "Question updated successfully",
  "data": { ... }
}
```

#### 7. Delete Question
```http
DELETE /api/v1/questions/{id}
Authorization: Bearer {token}

Required Permissions: question:delete

Response: 200 OK
{
  "success": true,
  "message": "Question deleted successfully"
}
```

#### 8. Publish Question
```http
POST /api/v1/questions/{id}/publish
Authorization: Bearer {token}

Required Permissions: question:publish

Response: 200 OK
{
  "success": true,
  "message": "Question published successfully",
  "data": { ... }
}
```

#### 9. Archive Question
```http
POST /api/v1/questions/{id}/archive
Authorization: Bearer {token}

Required Permissions: question:archive

Response: 200 OK
{
  "success": true,
  "message": "Question archived successfully",
  "data": { ... }
}
```

## Request Validation

### CreateQuestionRequest

**Rules:**
- `type`: required, in [multiple_choice, short_answer, essay, true_false]
- `content`: required, string
- `options`: required if type=multiple_choice, array
- `options.*`: string
- `correct_answer`: nullable, array
- `max_score`: required, integer (1-1000)
- `difficulty`: required, in [easy, medium, hard]
- `tags`: nullable, array
- `tags.*`: string, max 50 chars

**Example:**
```json
{
  "type": "multiple_choice",
  "content": "What is 2+2?",
  "options": ["3", "4", "5", "6"],
  "correct_answer": ["4"],
  "max_score": 10,
  "difficulty": "easy",
  "tags": ["math", "arithmetic"]
}
```

### UpdateQuestionRequest

**Rules:** Same as CreateQuestionRequest, all fields optional

**Example:**
```json
{
  "difficulty": "medium",
  "tags": ["math", "arithmetic", "beginner"]
}
```

## DTOs

### CreateQuestionDTO
- `type: string` (multiple_choice, short_answer, essay, true_false)
- `content: string`
- `options?: array`
- `correct_answer?: array`
- `max_score?: int` (default: 1)
- `difficulty: string` (easy, medium, hard)
- `tags?: array` (strings)

### UpdateQuestionDTO
- `type?: string`
- `content?: string`
- `options?: array`
- `correct_answer?: array`
- `max_score?: int`
- `difficulty?: string`
- `tags?: array`
- `status?: string` (draft, published, archived)

## Services

### QuestionService

**Methods:**

```php
create(CreateQuestionDTO $dto, string $tenantId, string $userId): array
```
Create new question. Automatically sets status to 'draft' and dispatches QuestionCreated event.

```php
update(string $questionId, UpdateQuestionDTO $dto): array
```
Update question fields (partial update supported).

```php
getById(string $questionId): array
```
Retrieve question by ID.

```php
list(string $tenantId, int $page = 1, int $perPage = 15): Paginator
```
List all questions for tenant with pagination.

```php
listByStatus(string $tenantId, string $status, int $page = 1, int $perPage = 15): Paginator
```
Filter questions by status (draft, published, archived).

```php
listByTags(string $tenantId, array $tags, int $page = 1, int $perPage = 15): Paginator
```
Filter questions by tags array.

```php
delete(string $questionId): void
```
Soft/hard delete question.

```php
publish(string $questionId): array
```
Change question status from draft to published.

```php
archive(string $questionId): array
```
Change question status to archived.

## Repositories

### QuestionRepository

Implements: `QuestionRepositoryInterface`

**Methods:**

```php
findById(string $id)
```
Find question by UUID.

```php
create(array $attributes)
```
Create new question record.

```php
update(string $id, array $attributes)
```
Update question with given attributes.

```php
delete(string $id): void
```
Delete question by ID.

```php
listByTenant(string $tenantId, int $page = 1, int $perPage = 15): Paginator
```
Paginated list of tenant questions.

```php
listByStatus(string $tenantId, string $status, int $page = 1, int $perPage = 15): Paginator
```
Paginated list filtered by status.

```php
listByTags(string $tenantId, array $tags, int $page = 1, int $perPage = 15): Paginator
```
Paginated list filtered by tags using PostgreSQL JSON containment operator.

## Models

### Question Model

**Traits:**
- `HasUuid` - Auto-generates UUID on creation

**Relationships:**
- `tenant()` - BelongsTo Tenant
- `creator()` - BelongsTo User (created_by foreign key)

**Attributes:**
- `id: uuid` (primary key)
- `tenant_id: uuid` (foreign key)
- `created_by: uuid` (foreign key to users.id)
- `type: string` (multiple_choice, short_answer, essay, true_false)
- `content: text`
- `options: jsonb` (nullable, array of options)
- `correct_answer: jsonb` (nullable, correct answer(s))
- `max_score: integer` (default: 1)
- `difficulty: string` (easy, medium, hard)
- `tags: text[]` (PostgreSQL array for tagging)
- `status: string` (draft, published, archived, default: draft)
- `created_at: timestamp`
- `updated_at: timestamp`

**Casts:**
- `options` → array
- `correct_answer` → array
- `tags` → array
- `created_at` → datetime
- `updated_at` → datetime

## Question Types

### multiple_choice
```json
{
  "type": "multiple_choice",
  "content": "Select the correct answer",
  "options": ["A", "B", "C", "D"],
  "correct_answer": ["B"]
}
```

### short_answer
```json
{
  "type": "short_answer",
  "content": "What is the capital of France?",
  "options": null,
  "correct_answer": ["Paris", "paris"]
}
```

### essay
```json
{
  "type": "essay",
  "content": "Explain why...",
  "options": null,
  "correct_answer": null
}
```

### true_false
```json
{
  "type": "true_false",
  "content": "The Earth is flat.",
  "options": ["True", "False"],
  "correct_answer": ["False"]
}
```

## Events

### QuestionCreated

**Dispatched:** When question is successfully created

**Payload:**
- `questionId: string` (UUID)

**Listeners:** (queueable)
- AuditLogListener - Logs question creation event
- NotificationListener - Sends notifications

**Example Usage:**
```php
// In service
QuestionCreated::dispatch($question->id);

// Subscribe in EventServiceProvider
\App\Events\QuestionCreated::class => [
    \App\Listeners\AuditLogListener::class,
    \App\Listeners\NotificationListener::class,
],
```

## Middleware Requirements

### auth:sanctum
All endpoints require authenticated user with valid Sanctum token.

### permission:resource,action
Endpoints like create, update, delete, publish, archive require specific permissions:
- `permission:question,create` - Create questions
- `permission:question,update` - Update questions
- `permission:question,delete` - Delete questions
- `permission:question,publish` - Publish questions
- `permission:question,archive` - Archive questions

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
    "type": ["Invalid question type."],
    "content": ["Question content is required."]
  }
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Question not found"
}
```

### Permission Denied (403)
```json
{
  "success": false,
  "message": "Forbidden"
}
```

### Unprocessable (400)
```json
{
  "success": false,
  "message": "Question is already published"
}
```

## Testing Examples

### Using cURL

```bash
# Create question
curl -X POST http://localhost:8000/api/v1/questions \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "X-Tenant-ID: {tenant_id}" \
  -d '{
    "type": "multiple_choice",
    "content": "What is 2+2?",
    "options": ["3", "4", "5"],
    "correct_answer": ["4"],
    "max_score": 1,
    "difficulty": "easy",
    "tags": ["math"]
  }'

# List questions
curl -X GET "http://localhost:8000/api/v1/questions?page=1&per_page=10" \
  -H "Authorization: Bearer {token}"

# Filter by status
curl -X GET "http://localhost:8000/api/v1/questions/by-status/published" \
  -H "Authorization: Bearer {token}"

# Filter by tags
curl -X GET "http://localhost:8000/api/v1/questions/by-tags?tags[]=math&tags[]=basic" \
  -H "Authorization: Bearer {token}"

# Update question
curl -X PUT http://localhost:8000/api/v1/questions/{id} \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"difficulty": "medium"}'

# Publish question
curl -X POST http://localhost:8000/api/v1/questions/{id}/publish \
  -H "Authorization: Bearer {token}"

# Delete question
curl -X DELETE http://localhost:8000/api/v1/questions/{id} \
  -H "Authorization: Bearer {token}"
```

## File Structure

```
app/Modules/Question/
├── Controllers/
│   └── QuestionController.php
├── DTOs/
│   ├── CreateQuestionDTO.php
│   └── UpdateQuestionDTO.php
├── Models/
│   └── Question.php
├── Requests/
│   ├── CreateQuestionRequest.php
│   └── UpdateQuestionRequest.php
├── Repositories/
│   ├── Contracts/
│   │   └── QuestionRepositoryInterface.php
│   └── QuestionRepository.php
├── Resources/
│   └── QuestionResource.php
├── Services/
│   └── QuestionService.php
└── README.md

routes/modules/
└── question.php
```

## Integration with RBAC

The module uses the `permission` middleware for authorization:

```php
Route::post('/', [QuestionController::class, 'store'])
    ->middleware('permission:question,create');
```

To grant a user question creation permissions:
1. Create or update user's role
2. Assign role permissions
3. Include `question:create` permission

## Next Steps

1. Implement question cloning functionality
2. Add batch question creation from CSV
3. Add question analytics (usage, performance)
4. Implement question versioning
5. Add question duplicates detection
6. Add question review workflow
7. Implement AI-powered question generation
