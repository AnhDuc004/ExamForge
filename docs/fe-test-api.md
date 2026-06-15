# FE API Guide: Test

Base URL:

- `GET/POST/PUT/DELETE /api/v1/...`

Auth:

- Tất cả route cần `Authorization: Bearer <token>`
- Các route ghi dữ liệu cần thêm `X-Tenant-ID: <tenant_uuid>` nếu tenant không resolve được từ middleware
- Permission:
  - `permission:tests,build` cho create/update/delete/section/question mapping
  - `permission:tests,publish` cho publish

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

## Test API

### 1) List tests

- `GET /api/v1/tests`

Query:

- `page` optional, default `1`
- `per_page` optional, default `15`
- `status` optional, example `draft` or `published`

Response `data`:

```json
{
  "data": [
    {
      "id": "uuid",
      "tenant_id": "uuid",
      "created_by": "uuid",
      "title": "Midterm English",
      "description": "Midterm test",
      "duration_seconds": 3600,
      "passing_score": 70,
      "status": "draft",
      "published_at": null,
      "creator": {
        "id": "uuid",
        "email": "creator@example.com",
        "display_name": "Creator"
      },
      "sections": [],
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

### 2) Get test detail

- `GET /api/v1/tests/{id}`

Response `data`:

```json
{
  "id": "uuid",
  "tenant_id": "uuid",
  "created_by": "uuid",
  "title": "Midterm English",
  "description": "Midterm test",
  "duration_seconds": 3600,
  "passing_score": 70,
  "status": "draft",
  "published_at": null,
  "creator": {
    "id": "uuid",
    "email": "creator@example.com",
    "display_name": "Creator"
  },
  "sections": [
    {
      "id": "uuid",
      "test_id": "uuid",
      "title": "Part 1",
      "instructions": "Read carefully",
      "position": 1,
      "questions": [
        {
          "id": "uuid",
          "section_id": "uuid",
          "question_id": "uuid",
          "position": 1,
          "score_override": 2,
          "question_snapshot": {
            "id": "uuid",
            "type": "multiple_choice",
            "content": "What is 2+2?",
            "options": ["3", "4"],
            "correct_answer": ["4"],
            "max_score": 1,
            "difficulty": "easy",
            "tags": ["math"],
            "status": "published",
            "created_at": "2026-06-15T00:00:00.000000Z",
            "updated_at": "2026-06-15T00:00:00.000000Z"
          }
        }
      ],
      "created_at": "2026-06-15T00:00:00.000000Z",
      "updated_at": "2026-06-15T00:00:00.000000Z"
    }
  ],
  "created_at": "2026-06-15T00:00:00.000000Z",
  "updated_at": "2026-06-15T00:00:00.000000Z"
}
```

### 3) Create test

- `POST /api/v1/tests`

Request body:

```json
{
  "title": "Midterm English",
  "description": "Midterm test",
  "duration_seconds": 3600,
  "passing_score": 70,
  "sections": [
    {
      "title": "Part 1",
      "instructions": "Read carefully",
      "position": 1,
      "questions": [
        {
          "question_id": "uuid-question-1",
          "position": 1,
          "score_override": 2
        }
      ]
    }
  ]
}
```

Rules:

- `title` required string
- `description` optional nullable string
- `duration_seconds` required integer, min 1
- `passing_score` required integer, min 0
- `sections` optional array
- `sections.*.title` required string
- `sections.*.position` required integer
- `sections.*.questions.*.question_id` required uuid, must exist in `questions`
- `sections.*.questions.*.position` required integer
- `sections.*.questions.*.score_override` optional nullable integer

Business rules:

- Test is created with status `draft`
- Question must already be `published` before adding into a test
- Question content is snapshotted into `question_snapshot`
- Once published, test cannot be modified

### 4) Update test

- `PUT /api/v1/tests/{id}`

Request body:

```json
{
  "title": "Updated title",
  "description": "Updated description",
  "duration_seconds": 5400,
  "passing_score": 75
}
```

Rules:

- All fields optional
- Only editable while status is `draft`

### 5) Delete test

- `DELETE /api/v1/tests/{id}`

Rules:

- Only editable while status is `draft`

### 6) Publish test

- `POST /api/v1/tests/{id}/publish`

Rules:

- Test must exist
- Test must have at least one section
- Test must have at least one question
- Once published, it becomes read-only

Response:

```json
{
  "success": true,
  "message": "Test published successfully",
  "data": { ... }
}
```

## Section API

### 7) Add section

- `POST /api/v1/tests/{testId}/sections`

Request body:

```json
{
  "title": "Part 1",
  "instructions": "Read carefully",
  "position": 1,
  "questions": [
    {
      "question_id": "uuid-question-1",
      "position": 1,
      "score_override": 2
    }
  ]
}
```

### 8) Update section

- `PUT /api/v1/tests/{testId}/sections/{sectionId}`

Request body:

```json
{
  "title": "Part 1 Updated",
  "instructions": "New instructions",
  "position": 2
}
```

### 9) Delete section

- `DELETE /api/v1/tests/{testId}/sections/{sectionId}`

## Section Question API

### 10) Attach question to section

- `POST /api/v1/tests/{testId}/sections/{sectionId}/questions`

Request body:

```json
{
  "question_id": "uuid-question-1",
  "position": 1,
  "score_override": 2
}
```

Rules:

- Question must be `published`
- Snapshot is saved into `question_snapshot`

### 11) Update section question

- `PUT /api/v1/tests/{testId}/sections/{sectionId}/questions/{sectionQuestionId}`

Request body:

```json
{
  "position": 2,
  "score_override": 3
}
```

### 12) Remove question from section

- `DELETE /api/v1/tests/{testId}/sections/{sectionId}/questions/{sectionQuestionId}`

## Notes For FE

- `sections` và `questions` trong response là mảng đã sắp xếp theo `position`
- `question_snapshot` là dữ liệu cố định của câu hỏi tại thời điểm gắn vào test
- FE không nên tự sửa `question_snapshot`
- Nếu test đã `published`, UI nên chuyển sang trạng thái chỉ đọc

