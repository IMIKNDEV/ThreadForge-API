# ThreadForge API — AGENTS.md

## Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 13 |
| PHP | ^8.5 |
| Database | MySQL 8.0 |
| Docker | Laravel Sail |
| Auth | Laravel Sanctum (Bearer Tokens) |
| AI SDK | `laravel/ai` (Groq) |
| Queue | Database driver |
| API Docs | Scribe |

## Folder Structure

```
app/
├── AI/
│   ├── Schemas/          # Structured Output schemas (PostGenerationSchema)
│   ├── Tools/             # Agent tools (getCampaignRules, getPostHistory)
│   └── GhostwriterAgent.php
├── Enums/
│   └── PostStatusEnum.php
├── Http/
│   ├── Controllers/Api/  # AuthController, BlueprintController, RawContentController, PostController, ChatController
│   ├── Requests/          # Form Requests (validation + 422 on fail)
│   └── Resources/         # API Resources (JSON formatting)
└── Models/
    ├── User.php
    ├── Blueprint.php
    ├── RawContent.php
    └── Post.php
database/
├── migrations/
├── seeders/
    ├── DatabaseSeeder.php
    ├── UserSeeder.php
    ├── BlueprintSeeder.php
    └── RawContentSeeder.php
routes/
└── api.php
```

## Conventions

- **All responses** use API Resources — never return raw Eloquent models
- **All validation** uses Form Requests — never `$request->validate()` inline
- **All JSON columns** use Eloquent `casts()` — never `json_decode/encode` manually
- **All queries** are scoped to `auth()->id()` — no cross-user data leaks
- **AI calls** are dispatched as Jobs — never synchronous
- **Status codes**: 201 (create), 200 (success), 202 (accepted), 401 (unauth), 404 (not found), 422 (validation)

## Database Schema (migrations)

### `blueprints`
| Column | Type |
|--------|------|
| id | PK auto-increment |
| user_id | FK → users.id (cascade) |
| name | string |
| tone | string |
| max_hashtag | unsignedBigInteger, default 1 |
| max_characters | unsignedBigInteger, default 280 |
| banned_word | string, nullable |
| extra_rules | text, nullable |
| created_at | timestamp |
| updated_at | timestamp |

### `raw_contents`
| Column | Type |
|--------|------|
| id | PK auto-increment |
| user_id | FK → users.id (cascade) |
| blueprint_id | FK → blueprints.id (cascade) |
| body | text |
| status | string, default 'en_attente' |
| created_at | timestamp |
| updated_at | timestamp |

### `posts`
| Column | Type |
|--------|------|
| id | PK auto-increment |
| raw_content_id | FK → raw_contents.id (cascade) |
| hook | string(280) |
| body_points | json |
| technical_readability_score | integer, nullable |
| suggested_hashtags | json, nullable |
| tone_compliance_justification | text, nullable |
| payload_brut | json, nullable |
| statut_publication | string, default 'draft' |
| created_at | timestamp |
| updated_at | timestamp |

### Enum values

**PostStatusEnum**: `draft`, `archived`, `posted`

## JSON Contract — Structured Output (PostGenerationSchema)

The AI must return exactly this shape — no extra fields, no missing keys:

```json
{
  "hook": "string (max 280 chars)",
  "body_points": ["string", "string", ...],
  "technical_readability_score": "integer (0-100)",
  "suggested_hashtags": ["string", "string", ...],
  "tone_compliance_justification": "string"
}
```

## Agent Tools

### `getCampaignRules(int $blueprintId): array`
Returns the blueprint's style rules:
- `tone`, `max_hashtag`, `max_characters`, `banned_word`, `extra_rules`

### `getPostHistory(int $postId): array`
Returns the post data including:
- `hook`, `body_points`, `statut_publication`, `technical_readability_score`, `suggested_hashtags`, `tone_compliance_justification`, `payload_brut`
