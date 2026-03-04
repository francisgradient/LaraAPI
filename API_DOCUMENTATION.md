# Todo API Documentation

**Base URL:** `http://your-domain.com/api`

---

## Authentication

All endpoints (except `/hello`) require authentication via **Laravel Sanctum**.

Include the token in the `Authorization` header:

```
Authorization: Bearer {your-token}
```

Also include these headers on every request:

```
Accept: application/json
Content-Type: application/json
```

---

## Endpoints

### Public

#### `GET /hello`

Health check endpoint.

**Response** `200 OK`

```json
{
  "message": "Hello World"
}
```

---

### Auth

#### `GET /user`

Returns the authenticated user's details.

**Headers:** `Authorization: Bearer {token}`

**Response** `200 OK`

```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "email_verified_at": "2026-03-04T10:00:00.000000Z",
  "created_at": "2026-03-04T10:00:00.000000Z",
  "updated_at": "2026-03-04T10:00:00.000000Z"
}
```

---

### Todos

All todo endpoints require authentication. Users can only access their own todos.

---

#### 1. List All Todos

`GET /todos`

Returns all todos for the authenticated user, sorted by most recent first.

**Response** `200 OK`

```json
[
  {
    "id": 1,
    "user_id": 1,
    "title": "Buy groceries",
    "description": "Milk, eggs, bread",
    "is_completed": false,
    "created_at": "2026-03-04T12:00:00.000000Z",
    "updated_at": "2026-03-04T12:00:00.000000Z"
  },
  {
    "id": 2,
    "user_id": 1,
    "title": "Walk the dog",
    "description": null,
    "is_completed": true,
    "created_at": "2026-03-04T11:00:00.000000Z",
    "updated_at": "2026-03-04T11:30:00.000000Z"
  }
]
```

---

#### 2. Create a Todo

`POST /todos`

**Request Body:**

| Field         | Type     | Required | Rules             |
|---------------|----------|----------|-------------------|
| `title`       | string   | Yes      | max 255 chars     |
| `description` | string   | No       | max 1000 chars    |

**Example Request:**

```json
{
  "title": "Buy groceries",
  "description": "Milk, eggs, bread"
}
```

**Response** `201 Created`

```json
{
  "id": 3,
  "user_id": 1,
  "title": "Buy groceries",
  "description": "Milk, eggs, bread",
  "is_completed": false,
  "created_at": "2026-03-04T14:00:00.000000Z",
  "updated_at": "2026-03-04T14:00:00.000000Z"
}
```

**Response** `422 Unprocessable Entity` (validation error)

```json
{
  "message": "The title field is required.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

---

#### 3. Show a Todo

`GET /todos/{id}`

Returns a single todo by ID.

**Response** `200 OK`

```json
{
  "id": 1,
  "user_id": 1,
  "title": "Buy groceries",
  "description": "Milk, eggs, bread",
  "is_completed": false,
  "created_at": "2026-03-04T12:00:00.000000Z",
  "updated_at": "2026-03-04T12:00:00.000000Z"
}
```

**Response** `403 Forbidden` — if the todo belongs to another user.

**Response** `404 Not Found` — if the todo doesn't exist.

---

#### 4. Update a Todo

`PUT /todos/{id}` or `PATCH /todos/{id}`

All fields are optional — send only what you want to change.

**Request Body:**

| Field          | Type    | Required | Rules             |
|----------------|---------|----------|-------------------|
| `title`        | string  | No       | max 255 chars     |
| `description`  | string  | No       | max 1000 chars    |
| `is_completed` | boolean | No       | `true` or `false` |

**Example — Mark as completed:**

```json
{
  "is_completed": true
}
```

**Example — Update title and description:**

```json
{
  "title": "Buy more groceries",
  "description": "Milk, eggs, bread, butter"
}
```

**Response** `200 OK`

```json
{
  "id": 1,
  "user_id": 1,
  "title": "Buy more groceries",
  "description": "Milk, eggs, bread, butter",
  "is_completed": true,
  "created_at": "2026-03-04T12:00:00.000000Z",
  "updated_at": "2026-03-04T15:00:00.000000Z"
}
```

**Response** `403 Forbidden` — if the todo belongs to another user.

**Response** `404 Not Found` — if the todo doesn't exist.

---

#### 5. Delete a Todo

`DELETE /todos/{id}`

**Response** `200 OK`

```json
{
  "message": "Todo deleted"
}
```

**Response** `403 Forbidden` — if the todo belongs to another user.

**Response** `404 Not Found` — if the todo doesn't exist.

---

## Todo Object Schema

| Field          | Type      | Description                        |
|----------------|-----------|------------------------------------|
| `id`           | integer   | Unique identifier                  |
| `user_id`      | integer   | Owner's user ID                    |
| `title`        | string    | Todo title (max 255 chars)         |
| `description`  | string?   | Optional details (max 1000 chars)  |
| `is_completed` | boolean   | Completion status                  |
| `created_at`   | datetime  | ISO 8601 timestamp                 |
| `updated_at`   | datetime  | ISO 8601 timestamp                 |

---

## Error Responses

| Status | Meaning                |
|--------|------------------------|
| `401`  | Unauthenticated — missing or invalid token |
| `403`  | Forbidden — accessing another user's todo  |
| `404`  | Not Found — todo doesn't exist             |
| `422`  | Validation Error — invalid request body    |
| `500`  | Server Error                               |
