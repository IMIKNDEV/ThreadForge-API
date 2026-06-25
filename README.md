# ThreadForge API

> A headless Laravel REST API that transforms raw developer notes, blog articles, and GitHub READMEs into optimized X (Twitter) posts — powered by structured AI output, async job processing, and a conversational ghostwriter agent.

---

## Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Environment Variables](#environment-variables)
  - [Running the App](#running-the-app)
- [Database](#database)
  - [Schema Overview](#schema-overview)
  - [Seeded Test Data](#seeded-test-data)
- [Authentication](#authentication)
- [API Reference](#api-reference)
  - [Auth](#auth-endpoints)
  - [Blueprints](#blueprint-endpoints)
  - [Content Generation](#content-generation-endpoints)
  - [Posts](#post-endpoints)
  - [Ghostwriter Agent](#ghostwriter-agent-endpoints)
- [AI Layer](#ai-layer)
  - [Layer 1 — Structured Output](#layer-1--structured-output)
  - [Layer 2 — Agent with Tools & Memory](#layer-2--agent-with-tools--memory)
- [Queue Worker](#queue-worker)
- [API Documentation (Scribe)](#api-documentation-scribe)
- [Key Concepts](#key-concepts)

---

## Overview

ThreadForge API is a pure REST backend built for tech content creators who want to automate their X presence without paying for expensive SaaS tools like Taplio or Buffer.

**The core workflow:**

```
Raw Notes / Article / README
         ↓
   POST /api/content/repurpose
         ↓ (202 Accepted — instant)
   Background Job (async)
         ↓
   AI Structured Output (Groq)
         ↓
   Post saved to DB (hook, body points, score, hashtags)
         ↓
   Chat with Ghostwriter Agent to refine
```

You define your personal style rules once (called a **Blueprint**), submit raw content, and the AI generates X-ready posts that respect your tone, hashtag limits, and audience targeting — all without blocking the HTTP request.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 13 |
| Language | PHP 8.5 |
| Database | MySQL 8.0 |
| Auth | Laravel Sanctum (Bearer Tokens) |
| Containerization | Laravel Sail (Docker) |
| AI SDK | `laravel/ai` (Groq) |
| Queue Driver | Database |
| API Docs | Scribe (`knuckleswtf/scribe`) |
| Dev Tools | Laravel Debugbar |

---

## Getting Started

### Prerequisites

Make sure you have the following installed:

- **Docker Desktop** (running)
- **WSL2** with Ubuntu (Windows) or macOS/Linux
- **PHP 8.5** + **Composer** (inside WSL)
- **Git**

### Installation

**1. Clone the repository**

```bash
git clone git@github.com:IMIKNDEV/ThreadForge-API.git
cd ThreadForge-API
```

**2. Install PHP dependencies**

```bash
composer install
```

**3. Copy the environment file**

```bash
cp .env.example .env
```

**4. Add your Groq API key to `.env`**

```env
GROQ_API_KEY=groq-your-key-here
```

**5. Start Docker containers**

```bash
./vendor/bin/sail up -d
```

> Add `alias sail='./vendor/bin/sail'` to your `~/.bashrc` to use `sail` shorthand.

**6. Generate the application key**

```bash
sail artisan key:generate
```

**7. Run migrations and seed the database**

```bash
sail artisan migrate:fresh --seed
```

**8. Start the queue worker** (required for AI generation)

```bash
sail artisan queue:work
```

> Keep this running in a separate terminal. Without it, post generation will not process.

**9. Verify the setup**

| URL | Expected |
|-----|----------|
| `http://localhost` | Laravel welcome page |
| `http://localhost:8081` | phpMyAdmin login |
| `http://localhost/docs` | Scribe API documentation |

---

### Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_NAME` | Application name | `ThreadForge` |
| `APP_URL` | Base URL | `http://localhost` |
| `DB_HOST` | Database host (Sail) | `mysql` |
| `DB_DATABASE` | Database name | `threadforge` |
| `DB_USERNAME` | Database user | `sail` |
| `DB_PASSWORD` | Database password | `password` |
| `QUEUE_CONNECTION` | Queue driver | `database` |
| `GROQ_API_KEY` | Your Groq key | `groq-...` |
| `DEBUGBAR_ENABLED` | Show Debugbar (dev only) | `true` |

---

### Running the App

```bash
# Start all Docker services
sail up -d

# Stop all services
sail down

# Run migrations
sail artisan migrate

# Fresh migration with seed data
sail artisan migrate:fresh --seed

# Start queue worker (required for AI generation)
sail artisan queue:work

# Regenerate API documentation
sail artisan scribe:generate

# Open Tinker (interactive Laravel shell)
sail artisan tinker
```

---

## Database

### Schema Overview

```
users
  id, name, email, password, timestamps

blueprints
  id, name, tone, max_hashtags, max_characters,
  target_audience, banned_words (JSON),
  user_id (FK → users), timestamps

raw_contents
  id, body (longText), source_type (enum: notes|article|readme),
  blueprint_id (FK → blueprints),
  user_id (FK → users), timestamps

posts
  id, hook_propose, body_points (JSON), technical_readability_score,
  suggested_hashtags (JSON), tone_compliance_justification,
  status (enum: draft|archived|posted),
  raw_content_id (FK → raw_contents), timestamps
```

**Relationships:**

```
User ──< Blueprint ──< RawContent ──< Post
```

- One user has many blueprints
- One blueprint has many raw contents
- One raw content generates many posts

### Seeded Test Data

After running `migrate:fresh --seed`, the following test accounts are available:

| Name | Email | Password |
|------|-------|----------|
| Creator One | `creator@threadforge.test` | `password` |
| Creator Two | `creator2@threadforge.test` | `password` |

Each account comes with sample blueprints and raw content entries ready for testing.

---

## Authentication

ThreadForge uses **Laravel Sanctum** with stateless Bearer Token authentication. No sessions, no cookies — pure API.

**How it works:**

1. Register or login to receive a `token`
2. Include the token in every subsequent request as a header:

```
Authorization: Bearer <your-token>
```

3. Call `POST /api/logout` to revoke the token

**All routes except `/api/register` and `/api/login` require authentication.**

Requests without a valid token receive:

```json
{
  "message": "Unauthenticated."
}
```
**Status: `401 Unauthorized`**

---

## API Reference

### Base URL

```
http://localhost/api
```

All responses are in JSON. All protected routes require:
```
Authorization: Bearer <token>
Content-Type: application/json
Accept: application/json
```

---

### Auth Endpoints

#### Register

```
POST /api/register
```

**Body:**

```json
{
  "name": "Ayoub",
  "email": "ayoub@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

**Response `201 Created`:**

```json
{
  "data": {
    "id": 1,
    "name": "Ayoub",
    "email": "ayoub@example.com",
    "created_at": "22/06/2026"
  },
  "token": "1|abc123xyz...",
  "token_type": "Bearer"
}
```

---

#### Login

```
POST /api/login
```

**Body:**

```json
{
  "email": "ayoub@example.com",
  "password": "password"
}
```

**Response `200 OK`:**

```json
{
  "data": {
    "id": 1,
    "name": "Ayoub",
    "email": "ayoub@example.com",
    "created_at": "22/06/2026"
  },
  "token": "2|def456uvw...",
  "token_type": "Bearer"
}
```

**Wrong credentials → `401 Unauthorized`:**

```json
{
  "message": "Invalid credentials."
}
```

---

#### Logout

```
POST /api/logout
```
🔒 *Requires authentication*

**Response `200 OK`:**

```json
{
  "message": "Logged out successfully."
}
```

---

### Blueprint Endpoints

Blueprints define your personal style rules. All blueprint routes are scoped to the authenticated user — you can never see or modify another user's blueprints.

---

#### Create a Blueprint

```
POST /api/blueprints
```
🔒 *Requires authentication*

**Body:**

```json
{
  "name": "Tech Dev Tone",
  "tone": "professional but relaxed",
  "max_hashtags": 1,
  "max_characters": 280,
  "target_audience": "senior developers and tech leads",
  "banned_words": ["guru", "ninja", "rockstar"]
}
```

**Response `201 Created`:**

```json
{
  "data": {
    "id": 1,
    "name": "Tech Dev Tone",
    "tone": "professional but relaxed",
    "max_hashtags": 1,
    "max_characters": 280,
    "target_audience": "senior developers and tech leads",
    "banned_words": ["guru", "ninja", "rockstar"],
    "created_at": "22/06/2026"
  }
}
```

**Validation errors → `422 Unprocessable Entity`:**

```json
{
  "message": "The max hashtags field must be between 0 and 10.",
  "errors": {
    "max_hashtags": ["The max hashtags field must be between 0 and 10."]
  }
}
```

---

#### List Blueprints

```
GET /api/blueprints
```
🔒 *Requires authentication*

**Response `200 OK`:**

```json
{
  "data": [
    {
      "id": 1,
      "name": "Tech Dev Tone",
      "tone": "professional but relaxed",
      "max_hashtags": 1,
      "max_characters": 280,
      "target_audience": "senior developers and tech leads",
      "banned_words": ["guru", "ninja", "rockstar"],
      "raw_contents_count": 4,
      "created_at": "22/06/2026"
    }
  ]
}
```

---

#### Get a Blueprint

```
GET /api/blueprints/{id}
```
🔒 *Requires authentication*

**Response `200 OK`** — same structure as single blueprint above.

**Not found or not owner → `404 Not Found`:**

```json
{
  "message": "Blueprint not found."
}
```

---

#### Update a Blueprint

```
PUT /api/blueprints/{id}
```
🔒 *Requires authentication*

**Body:** Same fields as create (all optional for update).

**Response `200 OK`** — updated blueprint object.

---

#### Delete a Blueprint

```
DELETE /api/blueprints/{id}
```
🔒 *Requires authentication*

**Response `200 OK`:**

```json
{
  "message": "Blueprint deleted successfully."
}
```

---

### Content Generation Endpoints

#### Submit Raw Content

```
POST /api/content/repurpose
```
🔒 *Requires authentication*

This is the core endpoint. It accepts raw text and immediately returns `202 Accepted` — the AI generation runs in the background via a queue job.

**Body:**

```json
{
  "body": "## What I learned building a Redis cache layer\n\nAfter profiling our Laravel app we found 80% of DB queries were hitting the same 3 tables. Implemented a cache-aside pattern with Redis...",
  "source_type": "article",
  "blueprint_id": 1
}
```

| Field | Type | Values |
|-------|------|--------|
| `body` | string | Any raw text content |
| `source_type` | enum | `notes`, `article`, `readme` |
| `blueprint_id` | integer | Must belong to authenticated user |

**Response `202 Accepted`** *(in under 100ms)*:

```json
{
  "message": "Content received. Generation in progress.",
  "raw_content_id": 7
}
```

> The AI generation runs in the background. Poll `GET /api/posts` to check when the post appears.

---

### Post Endpoints

#### List Posts

```
GET /api/posts
```
🔒 *Requires authentication*

Optional filter by status:

```
GET /api/posts?status=draft
GET /api/posts?status=posted
GET /api/posts?status=archived
```

**Response `200 OK`:**

```json
{
  "data": [
    {
      "id": 3,
      "hook_propose": "80% of our DB queries hit the same 3 tables. Here's what we did about it.",
      "body_points": [
        "Profiled with Laravel Debugbar — found the bottleneck in minutes",
        "Implemented cache-aside pattern with Redis",
        "Query count dropped from 47 to 6 per page load"
      ],
      "technical_readability_score": 82,
      "suggested_hashtags": ["#Laravel"],
      "tone_compliance_justification": "Tone is direct and technical, matches the professional but relaxed blueprint. One hashtag used as per the 1-hashtag limit.",
      "status": "draft",
      "raw_content_id": 7,
      "created_at": "22/06/2026"
    }
  ]
}
```

---

#### Get a Post

```
GET /api/posts/{id}
```
🔒 *Requires authentication*

**Response `200 OK`** — same structure as single post above.

---

#### Update Post Status

```
PATCH /api/posts/{id}
```
🔒 *Requires authentication*

Use this to manage your editorial calendar — move posts between `draft`, `archived`, and `posted`.

**Body:**

```json
{
  "status": "posted"
}
```

**Response `200 OK`:**

```json
{
  "data": {
    "id": 3,
    "status": "posted",
    ...
  }
}
```

---

### Ghostwriter Agent Endpoints

#### Chat with the Agent

```
POST /api/posts/{id}/chat
```
🔒 *Requires authentication*

Ask the AI agent to refine, rewrite, or generate variations of a post. The agent has access to real database tools — it will never invent data.

**Body:**

```json
{
  "message": "Give me 3 more aggressive hook variations for this post."
}
```

**Response `200 OK`:**

```json
{
  "reply": "Here are 3 more aggressive hook variations based on your post:\n\n1. \"We were making 47 database queries per page. We fixed it in an afternoon.\"\n2. \"Your Laravel app is slow. It's probably this.\"\n3. \"Redis saved our app. Here's the exact pattern we used.\"",
  "post_id": 3
}
```

The agent remembers your conversation — follow-up questions work naturally:

```json
{ "message": "Make the second one shorter." }
```

```json
{ "message": "Now translate all three to French." }
```

**Available agent tools:**

| Tool | Triggered when you ask about |
|------|------------------------------|
| `getCampaignRules(blueprintId)` | Style rules, tone, hashtag limits |
| `getPostHistory(postId)` | Post content, score, previous versions |

---

## AI Layer

### Layer 1 — Structured Output

When the queue job processes your raw content, it calls the Groq API through the `laravel/ai` SDK with a **strict JSON schema** imposed. The AI must return exactly this structure — no extra fields, no missing fields:

```json
{
  "hook_propose": "string (max 280 characters)",
  "body_points": ["string", "string", "string"],
  "technical_readability_score": 82,
  "suggested_hashtags": ["#Laravel"],
  "tone_compliance_justification": "string"
}
```

The schema is enforced at the SDK level — the app never does manual `json_decode` parsing. All fields are stored with Eloquent casts:

```php
// Post model — JSON columns become PHP arrays automatically
protected function casts(): array
{
    return [
        'body_points'          => 'array',
        'suggested_hashtags'   => 'array',
        'status'               => PostStatusEnum::class,
    ];
}
```

### Layer 2 — Agent with Tools & Memory

The Ghostwriter Agent (`GhostwriterAgent`) uses two PHP tools registered at the SDK level:

**`getCampaignRules(int $blueprintId)`**
Fetches the blueprint style constraints from the database. The agent calls this when you ask about tone, hashtag rules, or audience targeting.

**`getPostHistory(int $postId)`**
Fetches the full generated post data. The agent calls this when you ask about the content, score, or want variations.

**Conversation memory** is persisted in the SDK's database tables, linked to a stable key (`post_{id}_user_{id}`). This means:

- Each post has its own conversation thread per user
- Follow-up questions work without resending full context
- Conversation history survives page refreshes and new sessions

---

## Queue Worker

The queue worker is **required** for AI post generation to work. Without it, submitted content will sit in the `jobs` table and never process.

**Start the worker:**

```bash
sail artisan queue:work
```

**Monitor failed jobs:**

```bash
sail artisan queue:failed
```

**Retry a failed job:**

```bash
sail artisan queue:retry {id}
```

**Retry all failed jobs:**

```bash
sail artisan queue:retry all
```

The `GeneratePostJob` is configured with:
- `$tries = 3` — retries up to 3 times on failure
- `$backoff = [10, 30]` — waits 10s then 30s between retries
- `$timeout = 60` — max 60 seconds per attempt

---

## API Documentation (Scribe)

Interactive API documentation is auto-generated by Scribe and available at:

```
http://localhost/docs
```

To regenerate after adding new endpoints or annotations:

```bash
sail artisan scribe:generate
```

---

## Key Concepts

**Why `202 Accepted` instead of `200 OK` for content generation?**
Calling the Groq API can take 5–15 seconds. Returning `202` immediately and processing in the background prevents HTTP timeouts and keeps the API responsive. The client polls `GET /api/posts` to check when the result is ready.

**Why Eloquent Casts instead of `json_decode`?**
Casts are declared once on the model and apply everywhere — controllers, resources, jobs, tinker. Manual `json_decode` in multiple places creates inconsistency and bugs. With casts, `$post->body_points` is always a PHP array, period.

**Why Bearer Tokens instead of session auth?**
This is a headless API consumed by external clients (mobile apps, CLI tools, other services). Sessions are browser-dependent. Bearer tokens are stateless, easy to revoke, and work with any HTTP client.

**Why Tools instead of letting the AI answer from its system prompt?**
An AI that answers from a static prompt will hallucinate your blueprint rules and post content. Tools make the agent query the actual database — the answer is always factual.

---

*ThreadForge API — Built with Laravel 13 · PHP 8.5 · Groq*