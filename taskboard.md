# 🧵 ThreadForge API — DevTrack Laravel SCRUM Board

**Stack:** Laravel 13 | PHP 8.5 | MySQL 8.0 | Laravel Sail | `laravel/ai` SDK | Laravel Sanctum

---

## 🐳 Docker Setup — Empty GitHub Repo (Start Here)

> Follow these steps **before anything else** on an empty cloned repo.

### Step 1 — Clone your repo and enter it

```bash
git clone git@github.com:<your-username>/threadforge-api.git
cd threadforge-api
```

### Step 2 — Create Laravel project in a temp folder, then move files

```bash
# Create Laravel app in a temp folder (outside the repo)
composer create-project laravel/laravel threadforge-temp

# Copy everything into your cloned repo
cp -r threadforge-temp/. .

# Remove the temp folder
rm -rf threadforge-temp
```

### Step 3 — Install Laravel Sail

```bash
composer require laravel/sail --dev
php artisan sail:install
# When prompted, select: mysql
```

### Step 4 — Files to create / configure

**`.env`** — Edit the generated `.env`:

```env
APP_NAME=ThreadForge
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=threadforge
DB_USERNAME=sail
DB_PASSWORD=password

QUEUE_CONNECTION=database

GROQ_API_KEY=groq-...
```

**`.gitignore`** — Verify these lines exist (add if missing):

```
/vendor/
/node_modules/
.env
.env.backup
/storage/*.key
/public/hot
/public/storage
```

**`compose.yaml`** — Created automatically by Sail. Verify it contains these services:

```yaml
services:
    laravel.test:
        build:
            context: ./vendor/laravel/sail/runtimes/8.5
            dockerfile: Dockerfile
        ports:
            - '${APP_PORT:-80}:80'
        environment:
            - DB_HOST=mysql
        depends_on:
            - mysql
    mysql:
        image: 'mysql/mysql-server:8.0'
        ports:
            - '${FORWARD_DB_PORT:-3306}:3306'
        environment:
            MYSQL_ROOT_PASSWORD: '${DB_PASSWORD}'
            MYSQL_DATABASE: '${DB_DATABASE}'
            MYSQL_USER: '${DB_USERNAME}'
            MYSQL_PASSWORD: '${DB_PASSWORD}'
        volumes:
            - 'sail-mysql:/var/lib/mysql'
    phpmyadmin:
        image: phpmyadmin/phpmyadmin
        ports:
            - '8081:80'
        environment:
            PMA_HOST: mysql
        depends_on:
            - mysql
volumes:
    sail-mysql:
        driver: local
```

> ⚠️ **phpMyAdmin is not added by Sail automatically** — add it manually to `compose.yaml` as shown above.
> ⚠️ Laravel 13 + PHP 8.5 may require building a custom Sail runtime if the official `8.5` image isn't published yet — check `vendor/laravel/sail/runtimes/` and fall back to `8.4` temporarily if needed.

### Step 5 — Start Docker and verify

```bash
./vendor/bin/sail up -d

# Add alias for convenience (add to ~/.bashrc)
alias sail='./vendor/bin/sail'

# Verify the app is running
# → http://localhost should show the Laravel welcome page
```

### Step 6 — Generate app key

```bash
sail artisan key:generate
```

### Step 7 — Install Laravel Sanctum

```bash
sail composer require laravel/sanctum
sail artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
sail artisan migrate
```

### Step 8 — Install the laravel/ai SDK

```bash
sail composer require laravel/ai
sail artisan ai:install
sail artisan migrate
```

### Step 9 — Install Scribe (API Documentation)

```bash
sail composer require --dev knuckleswtf/scribe
sail artisan vendor:publish --provider="Knuckles\Scribe\ScribeServiceProvider" --tag=scribe-config
```

### Step 10 — Install Laravel Debugbar

```bash
sail composer require barryvdh/laravel-debugbar --dev
```

### Step 11 — Initial commit

```bash
git add .
git commit -m "feat(setup): initialize Laravel 13 + Sail + Sanctum + laravel/ai SDK"
git push origin main
```

### Step 12 — Create feature branches

```bash
git checkout -b feature/auth
git push origin feature/auth

git checkout main
git checkout -b feature/blueprints
git push origin feature/blueprints

git checkout main
git checkout -b feature/content-generation
git push origin feature/content-generation

git checkout main
git checkout -b feature/agent-conversationnel
git push origin feature/agent-conversationnel
```

---

## 📋 Legend

| Label | Meaning |
|-------|---------|
| `ARCH` | Architecture / Setup |
| `DOCKER` | Docker / Infrastructure |
| `AUTH` | Authentication & Sanctum |
| `BLUEPRINT` | Blueprint Campaign Management |
| `CONTENT` | Raw Content & Post Generation |
| `AI-STRUCT` | Structured Output (Layer 1) |
| `AI-AGENT` | Agent / Tools / Memory (Layer 2) |
| `RESOURCE` | Laravel API Resources |
| `QA` | Code Quality / Security |
| `DEBUG` | Debugging Tools |
| `DOC` | Documentation / Scribe |
| `LEARN` | Autoformation / Concept Study |

---

## 📚 Sprint 0 — Autoformation (Study Before Coding)

**Objectif:** Master the key concepts required to build this project before writing a single line of feature code.
**Durée:** Jour 1 matin

| Done | # | Task | Label | Priority | Time | What to Study & Resources |
| :---: | :--- | :--- | :---: | :---: | :---: | :--- |
| [ ] | L-01 | Laravel Sanctum — Stateless API auth with Bearer Tokens | `LEARN` | High | 1h | **Concepts:**<br>- How Sanctum issues personal access tokens<br>- Difference between session auth and token auth<br>- `createToken()`, `plainTextToken`, `auth:sanctum` middleware<br>- How to send a Bearer Token in an HTTP request header<br>- How `401 Unauthorized` is returned on invalid/missing token<br>**Resources:**<br>- https://laravel.com/docs/13.x/sanctum<br>- Focus on the **API Token Authentication** section (not SPA) |
| [ ] | L-02 | Laravel API Resources — Formatting JSON responses | `LEARN` | High | 0.5h | **Concepts:**<br>- Why API Resources exist (hide internal fields, format output)<br>- `make:resource`, `toArray()` method<br>- `ResourceCollection` for lists<br>- Hiding `password`, raw `created_at`, internal keys<br>- Returning `JsonResource` from controllers<br>**Resources:**<br>- https://laravel.com/docs/13.x/eloquent-resources |
| [ ] | L-03 | Form Requests — Validation & 422 JSON errors | `LEARN` | High | 0.5h | **Concepts:**<br>- `make:request`, `authorize()`, `rules()`<br>- How Laravel auto-returns 422 JSON on API routes<br>- Common rules: `required`, `string`, `max`, `in`, `integer`, `between`<br>- Accessing validated data with `$request->validated()`<br>**Resources:**<br>- https://laravel.com/docs/13.x/validation#form-request-validation |
| [ ] | L-04 | Jobs & Queues — Async processing with database driver | `LEARN` | High | 1h | **Concepts:**<br>- Why async? (avoid HTTP timeout on slow AI calls)<br>- `make:job`, `dispatch()`, `handle()`<br>- `$tries`, `$backoff`, `$timeout` on a Job class<br>- `queue:table` migration, `queue:work` command<br>- Returning `202 Accepted` immediately while the job runs in background<br>- Failed jobs table and `queue:failed`<br>**Resources:**<br>- https://laravel.com/docs/13.x/queues |
| [ ] | L-05 | Eloquent Casts — JSON columns as PHP arrays | `LEARN` | High | 0.5h | **Concepts:**<br>- Declaring `casts()` method on a model<br>- `'array'` cast for JSON columns (no `json_decode` needed)<br>- `AsEnum` cast for typed enums<br>- Why manual `json_encode/json_decode` is forbidden in this project<br>**Resources:**<br>- https://laravel.com/docs/13.x/eloquent-mutators#attribute-casting |
| [ ] | L-06 | laravel/ai SDK — Structured Output (Layer 1) | `LEARN` | High | 1h | **Concepts:**<br>- What structured output means (forcing AI to return a specific JSON schema)<br>- How to define a schema class with the SDK<br>- Passing the schema to a Groq API call so the response is typed<br>- Why this eliminates manual JSON parsing<br>**Resources:**<br>- https://laravel.com/docs/13.x/ai (structured output section)<br>- https://console.groq.com — get your Groq API key here<br>- Read the `ai:install` published config file carefully |
| [ ] | L-07 | laravel/ai SDK — Agent with Tools & Conversation Memory (Layer 2) | `LEARN` | High | 1h | **Concepts:**<br>- What a Tool is: a PHP function the AI can call instead of hallucinating<br>- How to define a Tool class and register it on an Agent<br>- How conversation memory works (SDK tables store message history)<br>- Linking a conversation to a stable ID (e.g. `user_id + post_id`)<br>- How follow-up questions work without resending full context<br>**Resources:**<br>- https://laravel.com/docs/13.x/ai (agent & tools section)<br>- Inspect the tables published by `ai:install` |
| [ ] | L-08 | Scribe — Auto-generating API documentation | `LEARN` | Medium | 0.5h | **Concepts:**<br>- PHPDoc annotations on controllers (`@group`, `@bodyParam`, `@response`)<br>- Running `sail artisan scribe:generate`<br>- Where the generated docs appear (`public/docs`)<br>- Writing pre-filled request/response examples<br>**Resources:**<br>- https://scribe.knuckles.wtf/laravel/ |
| [ ] | L-09 | Eager Loading — Eliminating N+1 queries | `LEARN` | Medium | 0.5h | **Concepts:**<br>- What N+1 is (1 query for list + N queries for relations)<br>- `with()` and `load()` for eager loading<br>- Using Debugbar SQL tab to detect N+1<br>- `withCount()` for counting relations without loading them<br>**Resources:**<br>- https://laravel.com/docs/13.x/eloquent-relationships#eager-loading |
| [ ] | L-10 | RESTful API conventions — Routes, status codes, JSON structure | `LEARN` | Medium | 0.5h | **Concepts:**<br>- `routes/api.php` vs `routes/web.php` (no Blade in API routes)<br>- HTTP verbs: GET, POST, PUT/PATCH, DELETE<br>- Status codes: 200, 201, 202, 401, 422, 404, 403<br>- Standardized JSON response structure: `{ data, message, errors }`<br>- `Route::apiResource()` shorthand |

---

## 🏃 Sprint 1 — Infrastructure & Setup

**Objectif:** Docker up, Laravel 13 + Sanctum initialized, migrations ready, `laravel/ai` SDK installed, Scribe configured
**Durée:** Jour 1 après-midi

| Done | # | Task | Label | Priority | Time | Detailed Implementation & Files |
| :---: | :--- | :--- | :---: | :---: | :---: | :--- |
| [ ] | T-01 | Initialize GitHub repo + branches | `ARCH` | High | 0.3h | **Action:**<br>- Create branches: `feature/auth`, `feature/blueprints`, `feature/content-generation`, `feature/agent-conversationnel`<br>- `.gitignore`: ignore `vendor/`, `.env`, `node_modules/`, `storage/*.key`<br>- `README.md`: push initial skeleton |
| [ ] | T-02 | Install Laravel 13 via Sail | `DOCKER` | High | 1h | **Action:**<br>- Follow the Docker Setup section above<br>- `composer create-project laravel/laravel threadforge-temp` (must resolve to Laravel ^13.0)<br>- Copy files into cloned repo<br>- `composer require laravel/sail --dev`<br>- `php artisan sail:install` → choose **mysql**<br>- Add phpMyAdmin service manually to `compose.yaml`<br>- Confirm `composer.json` requires `"php": "^8.5"` |
| [ ] | T-03 | Start Docker + verify environment | `DOCKER` | High | 0.5h | **Action:**<br>- `./vendor/bin/sail up -d`<br>- Verify `http://localhost` → Laravel welcome page<br>- Verify `http://localhost:8081` → phpMyAdmin login<br>- Add alias: `alias sail='./vendor/bin/sail'` |
| [ ] | T-04 | Configure `.env` | `ARCH` | High | 0.3h | **Files to Edit:**<br>- `.env`: `APP_NAME=ThreadForge`, `DB_HOST=mysql`, `DB_DATABASE=threadforge`, `DB_USERNAME=sail`, `DB_PASSWORD=password`<br>- `QUEUE_CONNECTION=database`<br>- `GROQ_API_KEY=groq-...`<br>- Run `sail artisan key:generate` if not done |
| [ ] | T-05 | Install & configure Laravel Sanctum | `AUTH` | High | 0.5h | **Action:**<br>- `sail composer require laravel/sanctum`<br>- `sail artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`<br>- `sail artisan migrate`<br>- Verify `personal_access_tokens` table exists in phpMyAdmin |
| [ ] | T-06 | Install `laravel/ai` SDK | `AI-STRUCT` | High | 1h | **Action:**<br>- `sail composer require laravel/ai`<br>- `sail artisan ai:install`<br>- `sail artisan migrate`<br>- Verify `config/ai.php` exists and `GROQ_API_KEY` is read correctly<br>- Confirm conversation memory tables are created (used in Sprint 4) |
| [ ] | T-07 | Install Scribe | `DOC` | High | 0.5h | **Action:**<br>- `sail composer require --dev knuckleswtf/scribe`<br>- `sail artisan vendor:publish --provider="Knuckles\Scribe\ScribeServiceProvider" --tag=scribe-config`<br>- Set `auth.enabled = true` and `auth.in = bearer` in `config/scribe.php`<br>- `sail artisan scribe:generate` → verify `public/docs` is created |
| [ ] | T-08 | Install Laravel Debugbar | `DEBUG` | High | 0.3h | **Action:**<br>- `sail composer require barryvdh/laravel-debugbar --dev`<br>- Verify SQL tab is visible on `http://localhost` |
| [ ] | T-09 | Migration — Table `blueprints` | `ARCH` | High | 0.5h | **Files to Create:**<br>- `sail artisan make:migration create_blueprints_table`<br>- **Columns:** `id` (PK), `name` (string), `tone` (string), `max_hashtag` (unsignedBigInteger, default 1), `max_characters` (unsignedBigInteger, default 280), `banned_word` (string, nullable), `extra_rules` (text, nullable), `user_id` (FK → users.id, onDelete cascade), `timestamps` |
| [ ] | T-10 | Migration — Table `raw_contents` | `ARCH` | High | 0.5h | **Files to Create:**<br>- `sail artisan make:migration create_raw_contents_table`<br>- **Columns:** `id` (PK), `body` (text), `status` (string, default `en_attente`), `blueprint_id` (FK → blueprints.id, onDelete cascade), `user_id` (FK → users.id, onDelete cascade), `timestamps` |
| [ ] | T-11 | Migration — Table `posts` | `ARCH` | High | 1h | **Files to Create:**<br>- `sail artisan make:migration create_posts_table`<br>- **Columns:** `id` (PK), `hook` (string, 280), `body_points` (json), `technical_readability_score` (integer, nullable), `suggested_hashtags` (json, nullable), `tone_compliance_justification` (text, nullable), `payload_brut` (json, nullable), `statut_publication` (string, default `draft`), `raw_content_id` (FK → raw_contents.id, onDelete cascade), `timestamps` |
| [ ] | T-12 | Model `Blueprint` + relationships + casts | `ARCH` | High | 0.5h | **Files to Create:**<br>- `sail artisan make:model Blueprint`<br>- `$fillable = ['name', 'tone', 'max_hashtag', 'max_characters', 'banned_word', 'extra_rules', 'user_id']`<br>- No JSON column cast needed<br>- Relationships:<br>&nbsp;&nbsp;- `user(): BelongsTo` → `User::class`<br>&nbsp;&nbsp;- `rawContents(): HasMany` → `RawContent::class` |
| [ ] | T-13 | Model `RawContent` + relationships | `ARCH` | High | 0.5h | **Files to Create:**<br>- `sail artisan make:model RawContent`<br>- `$fillable = ['body', 'status', 'blueprint_id', 'user_id']`<br>- No enum cast needed<br>- Relationships:<br>&nbsp;&nbsp;- `user(): BelongsTo` → `User::class`<br>&nbsp;&nbsp;- `blueprint(): BelongsTo` → `Blueprint::class`<br>&nbsp;&nbsp;- `posts(): HasMany` → `Post::class` |
| [ ] | T-14 | Model `Post` + relationships + casts | `ARCH` | High | 1h | **Files to Create:**<br>- `sail artisan make:model Post`<br>- `$fillable` = all post fields<br>- **Casts (using `casts()` method, Laravel 13 style):**<br>&nbsp;&nbsp;`body_points`, `suggested_hashtags`, `payload_brut` → `'array'`<br>&nbsp;&nbsp;`statut_publication` → `PostStatusEnum::class`<br>- Relationship: `rawContent(): BelongsTo` → `RawContent::class` |
| [ ] | T-15 | Enum `PostStatusEnum` | `ARCH` | High | 0.3h | **Files to Create:**<br>- `app/Enums/PostStatusEnum.php`<br>&nbsp;&nbsp;`enum PostStatusEnum: string { case Draft = 'draft'; case Archived = 'archived'; case Posted = 'posted'; }` |
| [ ] | T-16 | Configure Queue — database driver | `ARCH` | High | 0.3h | **Action:**<br>- `QUEUE_CONNECTION=database` in `.env`<br>- `sail artisan queue:table` (if not already done by Laravel 13 default)<br>- `sail artisan migrate`<br>- Verify `sail artisan queue:work` starts without error |
| [ ] | T-17 | Seeders | `ARCH` | High | 1h | **Files to Create:**<br>- `sail artisan make:seeder UserSeeder` → 1-2 creator users, password: `password`<br>- `sail artisan make:seeder BlueprintSeeder` → 2-3 blueprints with varied style rules<br>- `sail artisan make:seeder RawContentSeeder` → 3-4 raw content entries with realistic text<br>- `DatabaseSeeder.php`: call all three seeders in order<br>- `sail artisan migrate:fresh --seed` must pass ✅ |
| [ ] | T-18 | Create `AGENTS.md` at project root | `DOC` | High | 0.3h | **Files to Create:**<br>- `AGENTS.md` — stack, folder structure, conventions for any coding agent<br>- Reference the JSON contract for structured output<br>- Reference the 2 required tools (`getCampaignRules`, `getPostHistory`) |

**Sprint 1 — Definition of Done:**

- [ ] `sail up -d` starts all services without error
- [ ] `http://localhost` shows the Laravel welcome page
- [ ] `http://localhost:8081` shows phpMyAdmin
- [ ] `sail artisan migrate:fresh --seed` runs cleanly with no errors
- [ ] `personal_access_tokens` table exists
- [ ] `laravel/ai` SDK installed and `GROQ_API_KEY` confirmed working
- [ ] Scribe generates docs at `public/docs`
- [ ] Debugbar visible
- [ ] All 3 domain models have `$fillable`, casts, and relationships defined

---

## 🏃 Sprint 2 — Authentication (US1)

**Objectif:** Registration, Login and Logout returning Bearer Tokens — pure JSON, no Blade
**Durée:** Jour 2 matin
**Branch:** `feature/auth`

| Done | # | Task | Label | Priority | Time | Detailed Implementation & Files |
| :---: | :--- | :--- | :---: | :---: | :---: | :--- |
| [ ] | T-19 | `UserResource` — filter sensitive fields | `RESOURCE` | High | 0.5h | **Files to Create:**<br>- `sail artisan make:resource UserResource`<br>- `toArray()`: expose only `id`, `name`, `email`, `created_at` (formatted)<br>- **Never** expose `password`, raw timestamps, internal keys |
| [ ] | T-20 | `AuthController` — scaffold | `AUTH` | High | 0.3h | **Files to Create:**<br>- `sail artisan make:controller Api/AuthController`<br>- Methods: `register()`, `login()`, `logout()`<br>- All methods return JSON only — zero Blade |
| [ ] | T-21 | `US1` — Register | `AUTH` | High | 1h | **Files to Create/Edit:**<br>- `sail artisan make:request RegisterRequest`<br>&nbsp;&nbsp;- `rules()`: `name\|required\|string\|max:255`, `email\|required\|email\|unique:users`, `password\|required\|string\|min:8\|confirmed`<br>- `AuthController@register`:<br>&nbsp;&nbsp;- Validate via `RegisterRequest`<br>&nbsp;&nbsp;- `User::create([...,'password' => bcrypt(...)])`<br>&nbsp;&nbsp;- `$user->createToken('auth_token')->plainTextToken`<br>&nbsp;&nbsp;- Return `{ data: UserResource, token, token_type: 'Bearer' }` with **201**<br>- **Route:** `POST /api/register` |
| [ ] | T-22 | `US1` — Login | `AUTH` | High | 1h | **Files to Create/Edit:**<br>- `sail artisan make:request LoginRequest`<br>&nbsp;&nbsp;- `rules()`: `email\|required\|email`, `password\|required\|string`<br>- `AuthController@login`:<br>&nbsp;&nbsp;- `Auth::attempt()` → return **401** if fails<br>&nbsp;&nbsp;- `$user->createToken('auth_token')->plainTextToken`<br>&nbsp;&nbsp;- Return `{ data: UserResource, token, token_type: 'Bearer' }` with **200**<br>- **Route:** `POST /api/login` |
| [ ] | T-23 | `US1` — Logout | `AUTH` | High | 0.5h | **Files to Edit:**<br>- `AuthController@logout`:<br>&nbsp;&nbsp;- `$request->user()->currentAccessToken()->delete()`<br>&nbsp;&nbsp;- Return `{ message: 'Logged out successfully' }` with **200**<br>- **Route:** `POST /api/logout` → protected by `auth:sanctum` |
| [ ] | T-24 | Protect all private routes with `auth:sanctum` | `AUTH` | High | 0.3h | **Files to Edit:**<br>- `routes/api.php`: wrap all blueprint + content + chat routes in `Route::middleware('auth:sanctum')->group(function () { ... })`<br>- Test: request to `/api/blueprints` without token → must return **401** |
| [x] | T-25 | Scribe PHPDoc — Auth endpoints | `DOC` | High | 0.5h | **Action:**<br>- Add `@group`, `@bodyParam`, `@response` annotations to all 3 `AuthController` methods<br>- `sail artisan scribe:generate` → verify auth endpoints appear in docs |

**Sprint 2 — Definition of Done:**

- [ ] `POST /api/register` returns 201 with token
- [ ] `POST /api/login` returns 200 with token, returns 401 on bad credentials
- [ ] `POST /api/logout` deletes current token, returns 200
- [ ] Any protected route without token returns 401
- [ ] Password never appears in any JSON response
- [ ] Scribe docs show all auth endpoints

---

## 🏃 Sprint 3 — Blueprint Campaign Management (US2, US3)

**Objectif:** CRUD Blueprints — style rule configurations linked to the authenticated creator
**Durée:** Jour 2 après-midi
**Branch:** `feature/blueprints`

| Done | # | Task | Label | Priority | Time | Detailed Implementation & Files |
| :---: | :--- | :--- | :---: | :---: | :---: | :--- |
| [ ] | T-26 | `BlueprintResource` + `BlueprintCollection` | `RESOURCE` | High | 0.5h | **Files to Create:**<br>- `sail artisan make:resource BlueprintResource`<br>- Expose: `id`, `name`, `tone`, `max_hashtag`, `max_characters`, `banned_word`, `extra_rules`, `raw_contents_count` (when loaded), `created_at` (formatted `d/m/Y`)<br>- `sail artisan make:resource BlueprintCollection` |
| [ ] | T-27 | `BlueprintController` — scaffold | `BLUEPRINT` | High | 0.3h | **Files to Create:**<br>- `sail artisan make:controller Api/BlueprintController --api`<br>- All responses via `BlueprintResource` — zero raw Eloquent output<br>- Every query uses `where('user_id', auth()->id())` — no cross-user data leaks |
| [ ] | T-28 | `US2` — Create a Blueprint | `BLUEPRINT` | High | 1h | **Files to Create/Edit:**<br>- `sail artisan make:request StoreBlueprintRequest`<br>&nbsp;&nbsp;- `rules()`: `name\|required\|string\|max:255`, `tone\|required\|string`, `max_hashtag\|required\|integer\|between:0,30`, `max_characters\|required\|integer\|between:1,280`, `banned_word\|nullable\|string`, `extra_rules\|nullable\|string`<br>- `BlueprintController@store`:<br>&nbsp;&nbsp;- `Blueprint::create([..., 'user_id' => auth()->id()])`<br>&nbsp;&nbsp;- Return `BlueprintResource` with **201**<br>- **Route:** `POST /api/blueprints` |
| [ ] | T-29 | `US3` — List Blueprints with post count | `BLUEPRINT` | High | 1h | **Files to Edit:**<br>- `BlueprintController@index`:<br>&nbsp;&nbsp;- `Blueprint::where('user_id', auth()->id())->withCount('rawContents')->latest()->get()`<br>&nbsp;&nbsp;- Return `BlueprintCollection` with **200**<br>- **Route:** `GET /api/blueprints` |
| [ ] | T-30 | `US3` — Blueprint detail | `BLUEPRINT` | High | 0.5h | **Files to Edit:**<br>- `BlueprintController@show`:<br>&nbsp;&nbsp;- Find blueprint scoped to `auth()->id()` → 404 if not found or not owner<br>&nbsp;&nbsp;- Return `BlueprintResource` with **200**<br>- **Route:** `GET /api/blueprints/{blueprint}` |
| [ ] | T-31 | Update + Delete Blueprint | `BLUEPRINT` | Medium | 0.5h | **Files to Create/Edit:**<br>- `sail artisan make:request UpdateBlueprintRequest` — same rules as Store<br>- `BlueprintController@update` → return `BlueprintResource` with **200**<br>- `BlueprintController@destroy` → return `{ message: 'Blueprint deleted' }` with **200**<br>- Both scoped to `auth()->id()` — 404 if not owner<br>- **Routes:** `PUT /api/blueprints/{blueprint}`, `DELETE /api/blueprints/{blueprint}` |
| [ ] | T-32 | Scribe PHPDoc — Blueprint endpoints | `DOC` | High | 0.5h | **Action:**<br>- Add `@group Blueprint Management`, `@bodyParam`, `@response` to all `BlueprintController` methods<br>- `sail artisan scribe:generate` |

**Sprint 3 — Definition of Done:**

- [ ] Create blueprint attaches `user_id` of authenticated creator automatically
- [ ] List only returns the authenticated creator's blueprints
- [ ] `posts_count` (or `raw_contents_count`) visible in list response
- [ ] Blueprint belonging to another user returns 404
- [ ] `banned_word` and `extra_rules` stored as plain strings — no JSON cast needed
- [ ] Scribe docs show all blueprint endpoints

---

## 🏃 Sprint 4 — Async Content Generation (US4, US5, US6)

**Objectif:** Submit raw content → 202 immediately → Job calls AI → Structured Output saved → Post lifecycle management
**Durée:** Jour 3
**Branch:** `feature/content-generation`

| Done | # | Task | Label | Priority | Time | Detailed Implementation & Files |
| :---: | :--- | :--- | :---: | :---: | :---: | :--- |
| [ ] | T-33 | Structured Output Schema class | `AI-STRUCT` | High | 1.5h | **Files to Create:**<br>- `app/AI/Schemas/PostGenerationSchema.php`<br>- Define the typed schema imposed on `laravel/ai`:<br>```php<br>hook: string (max 280 chars)<br>body_points: array<string><br>technical_readability_score: integer (0-100)<br>suggested_hashtags: array<string><br>tone_compliance_justification: string<br>```<br>- The SDK **must impose** this schema — no manual JSON parsing in the Job |
| [ ] | T-34 | `PostResource` + `PostCollection` | `RESOURCE` | High | 0.5h | **Files to Create:**<br>- `sail artisan make:resource PostResource`<br>- Expose: `id`, `hook`, `body_points`, `technical_readability_score`, `suggested_hashtags`, `tone_compliance_justification`, `payload_brut`, `statut_publication` (enum label), `raw_content_id`, `created_at` (formatted)<br>- `sail artisan make:resource PostCollection` |
| [ ] | T-35 | `RawContentController` + `StoreRawContentRequest` | `CONTENT` | High | 0.5h | **Files to Create:**<br>- `sail artisan make:controller Api/RawContentController`<br>- `sail artisan make:request StoreRawContentRequest`<br>&nbsp;&nbsp;- `rules()`: `body\|required\|string`, `blueprint_id\|required\|integer\|exists:blueprints,id`<br>- Validate blueprint belongs to auth user |
| [ ] | T-36 | `US4` — Submit raw content (202 Accepted) | `CONTENT` | High | 1.5h | **Files to Edit:**<br>- `RawContentController@store`:<br>&nbsp;&nbsp;- `RawContent::create([..., 'user_id' => auth()->id()])`<br>&nbsp;&nbsp;- `GeneratePostJob::dispatch($rawContent)`<br>&nbsp;&nbsp;- Return `{ message: 'Content received. Generation in progress.', raw_content_id: $rawContent->id }` with **202**<br>&nbsp;&nbsp;- ⚠️ This endpoint must respond in < 100ms — no synchronous AI call<br>- **Route:** `POST /api/content/repurpose` |
| [ ] | T-37 | `US5` — Job `GeneratePostJob` (Structured Output) | `AI-STRUCT` | High | 3h | **Files to Create:**<br>- `sail artisan make:job GeneratePostJob`<br>- `handle()`:<br>&nbsp;&nbsp;1. Load `$rawContent->blueprint` (eager load)<br>&nbsp;&nbsp;2. Build prompt including raw content body + all blueprint style rules<br>&nbsp;&nbsp;3. Call `laravel/ai` with `PostGenerationSchema` imposed<br>&nbsp;&nbsp;4. Validate `technical_readability_score` is between 0-100<br>&nbsp;&nbsp;5. `Post::create([..., 'raw_content_id' => $rawContent->id])` from typed response<br>&nbsp;&nbsp;6. Handle errors (AI timeout, schema mismatch) with proper retry/fail<br>- Set `$tries = 3`, `$backoff = [10, 30]`, `$timeout = 60`<br>- Test with `sail artisan queue:work` running |
| [ ] | T-38 | `US6` — List posts with status filter | `CONTENT` | High | 1h | **Files to Create/Edit:**<br>- `sail artisan make:controller Api/PostController`<br>- `PostController@index`:<br>&nbsp;&nbsp;- Load posts via `auth()->user()->rawContents()->with('posts')`<br>&nbsp;&nbsp;- Optional filter: `?status=draft\|archived\|posted`<br>&nbsp;&nbsp;- Return `PostCollection` with **200**<br>- **Route:** `GET /api/posts` |
| [ ] | T-39 | `US6` — Update post status | `CONTENT` | High | 1h | **Files to Create/Edit:**<br>- `sail artisan make:request UpdatePostStatusRequest`<br>&nbsp;&nbsp;- `rules()`: `status\|required\|in:draft,archived,posted`<br>- `PostController@update`:<br>&nbsp;&nbsp;- Find post scoped to auth user → 404 if not found<br>&nbsp;&nbsp;- Update `status` field only<br>&nbsp;&nbsp;- Return `PostResource` with **200**<br>- **Route:** `PATCH /api/posts/{post}` |
| [ ] | T-40 | `PostController@show` — Get single post | `CONTENT` | Medium | 0.5h | **Files to Edit:**<br>- `PostController@show`:<br>&nbsp;&nbsp;- Find post scoped to auth user → 404 if not found<br>&nbsp;&nbsp;- Return `PostResource` with **200**<br>- **Route:** `GET /api/posts/{post}` |
| [ ] | T-41 | Scribe PHPDoc — Content & Post endpoints | `DOC` | High | 0.5h | **Action:**<br>- Annotate `RawContentController` and `PostController` with `@group`, `@bodyParam`, `@response`<br>- Show 202 example for repurpose endpoint<br>- `sail artisan scribe:generate` |

**Sprint 4 — Definition of Done:**

- [ ] `POST /api/content/repurpose` responds in < 100ms with 202
- [ ] Job runs asynchronously — AI is never called synchronously
- [ ] Generated `Post` matches the JSON schema exactly (`hook`, `body_points`, etc.)
- [ ] `technical_readability_score` always between 0-100
- [ ] `body_points` and `suggested_hashtags` are PHP arrays via Eloquent casts — zero `json_decode`
- [ ] Post status can be updated to `draft`, `archived`, or `posted`
- [ ] Posts of other users are never returned (scoped to auth user)
- [ ] Scribe docs show all endpoints with pre-filled examples

---

## 🏃 Sprint 5 — Ghostwriter Agent (US7, US8, US9)

**Objectif:** Conversational assistant with real PHP Tools + persistent conversation memory
**Durée:** Jour 4
**Branch:** `feature/agent-conversationnel`

| Done | # | Task | Label | Priority | Time | Detailed Implementation & Files |
| :---: | :--- | :--- | :---: | :---: | :---: | :--- |
| [ ] | T-42 | Tool — `getCampaignRules(int $blueprintId)` | `AI-AGENT` | High | 1h | **Files to Create:**<br>- `app/AI/Tools/GetCampaignRulesTool.php`<br>- `handle(int $blueprintId): array`<br>&nbsp;&nbsp;- Find blueprint scoped to auth user → return style constraints as array<br>&nbsp;&nbsp;- Return: `tone`, `max_hashtag`, `max_characters`, `banned_word`, `extra_rules`<br>- Handle blueprint not found (return descriptive error message) |
| [ ] | T-43 | Tool — `getPostHistory(int $postId)` | `AI-AGENT` | High | 1h | **Files to Create:**<br>- `app/AI/Tools/GetPostHistoryTool.php`<br>- `handle(int $postId): array`<br>&nbsp;&nbsp;- Load post with `rawContent` → return full post data including previous versions<br>&nbsp;&nbsp;- Return: `hook`, `body_points`, `statut_publication`, `technical_readability_score`, `suggested_hashtags`, `tone_compliance_justification`, `payload_brut`<br>- Handle post not found |
| [ ] | T-44 | Agent `GhostwriterAgent` — definition | `AI-AGENT` | High | 2h | **Files to Create:**<br>- `app/AI/GhostwriterAgent.php`<br>- Register both tools: `GetCampaignRulesTool`, `GetPostHistoryTool`<br>- System prompt: agent must **always** use tools for factual data — never invent blueprint rules or post content<br>- Method `ask(Post $post, string $conversationId, string $message): string` |
| [ ] | T-45 | `US8` — Conversation memory (SDK tables) | `AI-AGENT` | High | 1.5h | **Action:**<br>- Use the conversation memory tables published by `laravel/ai` during `ai:install`<br>- Generate a stable conversation ID: `"post_{$post->id}_user_{auth()->id()}"`<br>- Pass it to the agent so history is persisted per post per user<br>- Verify: Q1 "translate this post to English" → Q2 "give me another hook for this one" → agent understands "this one" from memory |
| [ ] | T-46 | `US7` + `US9` — `ChatController` | `AI-AGENT` | High | 2h | **Files to Create:**<br>- `sail artisan make:controller Api/ChatController`<br>- `sail artisan make:request AskGhostwriterRequest`<br>&nbsp;&nbsp;- `rules()`: `message\|required\|string\|max:1000`<br>- `ChatController@ask`:<br>&nbsp;&nbsp;- Find post scoped to auth user → 404 if not found<br>&nbsp;&nbsp;- `$conversationId = "post_{$post->id}_user_{auth()->id()}"`<br>&nbsp;&nbsp;- `$reply = (new GhostwriterAgent)->ask($post, $conversationId, $validated['message'])`<br>&nbsp;&nbsp;- Return `{ reply, post_id }` with **200**<br>- **Route:** `POST /api/posts/{post}/chat` → protected by `auth:sanctum` |
| [ ] | T-47 | Scribe PHPDoc — Chat endpoint | `DOC` | High | 0.3h | **Action:**<br>- Annotate `ChatController@ask` with `@group Ghostwriter Agent`, `@bodyParam`, `@response`<br>- Show example request/response with a realistic message and reply<br>- `sail artisan scribe:generate` |

**Sprint 5 — Definition of Done:**

- [ ] `POST /api/posts/{post}/chat` returns a contextual reply based on real data
- [ ] `getCampaignRules` tool is triggered when asking about style rules — never invented
- [ ] `getPostHistory` tool is triggered when asking about post content — never hallucinated
- [ ] Follow-up question ("and for this one?") keeps context without resending full content
- [ ] Conversation history is persisted in SDK tables and reloaded correctly
- [ ] Accessing another user's post chat returns 404
- [ ] Zero hallucination verified manually on at least 5 test questions

---

## 🏃 Sprint 6 — QA, Debugging & Livrables

**Objectif:** Security audit, N+1 fix, Scribe finalization, commits check
**Durée:** Dernier jour

| Done | # | Task | Label | Priority | Time | Detailed Implementation & Files |
| :---: | :--- | :--- | :---: | :---: | :---: | :--- |
| [ ] | T-48 | Audit — All Form Requests in place | `QA` | High | 0.5h | **Files to Audit:**<br>- `AuthController` → `RegisterRequest`, `LoginRequest` ✅<br>- `BlueprintController@store/update` → `StoreBlueprintRequest`, `UpdateBlueprintRequest` ✅<br>- `RawContentController@store` → `StoreRawContentRequest` ✅<br>- `PostController@update` → `UpdatePostStatusRequest` ✅<br>- `ChatController@ask` → `AskGhostwriterRequest` ✅<br>- Zero `$request->validate()` inline remaining |
| [ ] | T-49 | Audit — All responses via API Resources | `RESOURCE` | High | 0.3h | **Action:**<br>- `grep -r "return response()->json(\$" app/Http/Controllers/` → every occurrence must use a Resource<br>- No raw Eloquent model should be returned directly<br>- No `password`, unformatted `created_at`, or internal keys in any response |
| [ ] | T-50 | Audit — Eloquent Casts on all models | `QA` | High | 0.3h | **Files to Check:**<br>- `Blueprint` — no JSON column cast needed (all plain string/int columns)<br>- `Post::casts()` covers `body_points`, `suggested_hashtags`, `payload_brut` → `'array'` and `statut_publication` → `PostStatusEnum::class`<br>- `RawContent` — no JSON column cast needed<br>- Zero `json_decode()` or `json_encode()` in any controller or resource |
| [ ] | T-51 | Audit — Zero data leaks | `QA` | High | 0.5h | **Scenarios to Test:**<br>- `GET /api/blueprints` → only returns auth user's blueprints<br>- `GET /api/posts` → only returns posts linked to auth user's raw contents<br>- `POST /api/posts/{post}/chat` with another user's post → 404<br>- Register/Login response → no `password` field anywhere |
| [ ] | T-52 | Debugbar — Detect and fix N+1 queries | `DEBUG` | High | 1h | **Action:**<br>- Open `GET /api/blueprints` → check SQL tab → should be 1-2 queries max<br>- Open `GET /api/posts` → check SQL for `raw_content + blueprint` chain → fix with `with(['rawContent.blueprint'])`<br>- Confirm query count is stable regardless of number of posts |
| [ ] | T-53 | Test async flow end-to-end | `QA` | High | 1h | **Scenarios to Verify:**<br>- `POST /api/content/repurpose` → responds < 100ms with 202<br>- `sail artisan queue:work` → job processes → Post created in DB<br>- `GET /api/posts` → newly generated post appears with all fields<br>- Post with minimal content → AI handles gracefully (no crash)<br>- `technical_readability_score` always 0-100<br>- `status` always a valid enum value |
| [ ] | T-54 | Test agent conversation flow end-to-end | `QA` | High | 1h | **Scenarios to Verify:**<br>- Ask about blueprint rules → `getCampaignRules` tool is called<br>- Ask about post content → `getPostHistory` tool is called<br>- Q1: "Give me 3 hook variations" → Q2: "Make the second one more aggressive" → context maintained<br>- Ask for comparison → correct tool triggered<br>- Another user's post → 404 |
| [ ] | T-55 | Finalize Scribe documentation | `DOC` | High | 1h | **Action:**<br>- All controllers have `@group`, `@bodyParam`, `@response` annotations<br>- `sail artisan scribe:generate` → verify all endpoints appear<br>- Pre-filled request examples use seeded test credentials<br>- Response examples show realistic data (not empty objects)<br>- Bearer auth documented correctly |
| [ ] | T-56 | `README.md` complet | `DOC` | High | 0.5h | **Verify README contains:**<br>- Project description<br>- Stack: Laravel 13, PHP 8.5, MySQL 8.0, Docker Sail, `laravel/ai`, Sanctum<br>- Full install instructions (clone → env → sail up → ai:install → migrate:fresh --seed → queue:work)<br>- Test credentials (email + password from seeders)<br>- Route table: all API endpoints with method, path, auth requirement<br>- MCD + MLD diagrams<br>- Section on key concepts: Structured Output, Eloquent Casts, Jobs & Queues, Tools, Conversation Memory |
| [ ] | T-57 | Git audit — commits & branches | `DOC` | High | 0.3h | **Action:**<br>- `git log --oneline` → minimum 20 commits, atomic, well-segmented<br>- Verify branches: `feature/auth`, `feature/blueprints`, `feature/content-generation`, `feature/agent-conversationnel`<br>- Zero direct commits on `main`<br>- Correct commit message examples:<br>&nbsp;&nbsp;`feat(auth): implement register with sanctum token`<br>&nbsp;&nbsp;`feat(ai): add structured output schema for post generation`<br>&nbsp;&nbsp;`fix(queue): handle AI timeout with retry backoff`<br>&nbsp;&nbsp;`refactor(posts): fix N+1 with eager loading on rawContent` |

**Sprint 6 — Definition of Done:**

- [ ] All routes use Form Requests — zero inline `$request->validate()`
- [ ] All responses use API Resources — zero raw Eloquent or password exposure
- [ ] All JSON columns use Eloquent Casts — zero `json_decode/encode`
- [ ] N+1 confirmed fixed via Debugbar
- [ ] Full content generation flow tested end-to-end (submit → 202 → job → post)
- [ ] Full conversational flow tested end-to-end (ask → tools → memory)
- [ ] Scribe docs complete with pre-filled examples
- [ ] README complete with install instructions + credentials
- [ ] Minimum 20 atomic commits with explicit AI usage mentions
- [ ] `AGENTS.md` accurate and up to date

---

## 📦 Final Deliverables Checklist

| Livrable | Critère | Statut |
|----------|---------|--------|
| GitHub Repo | Minimum 20 commits with explicit AI usage messages | ⬜ |
| GitHub Repo | Feature branches (`auth`, `blueprints`, `content-generation`, `agent-conversationnel`) | ⬜ |
| GitHub Repo | Zero direct commits on `main` | ⬜ |
| `AGENTS.md` | Present at root, reflects real project state | ⬜ |
| Scribe Docs | All endpoints documented with pre-filled examples | ⬜ |
| MCD | Entities, attributes, relations with cardinalities | ⬜ |
| MLD | Tables, types, PK, FK | ⬜ |
| `README.md` | Full install instructions + test credentials + route table | ⬜ |
| Migrations | All tables via migrations — zero manual SQL | ⬜ |
| Seeders | Users, blueprints, raw contents with realistic data | ⬜ |
| Structured Output | JSON contract respected 100%, Eloquent Casts in place | ⬜ |
| Jobs & Queues | AI generation dispatched as job — never synchronous | ⬜ |
| Agent + Tools | 2 tools functional, agent never hallucinates real data | ⬜ |
| Conversation Memory | Memory persisted via SDK tables, context maintained | ⬜ |
| Debugbar | N+1 identified and fixed | ⬜ |

---

## 🏆 Performance Criteria

### API Architecture & Security (35%)

| Critère | Statut |
|---------|--------|
| Zero password / raw timestamps / internal keys in JSON responses — strict API Resources | ⬜ |
| Protected routes return 401 instantly on missing/invalid token | ⬜ |
| All Form Requests in place — API never crashes with SQL null error | ⬜ |
| All JSON columns use Eloquent Casts — zero manual json_encode/json_decode | ⬜ |
| All user data scoped to authenticated user — zero cross-user data leaks | ⬜ |

### AI Integration & Asynchronism (30%)

| Critère | Statut |
|---------|--------|
| `POST /api/content/repurpose` responds < 100ms with 202 Accepted | ⬜ |
| Job validates JSON contract before DB insertion (exact keys required) | ⬜ |
| `body_points` and `suggested_hashtags` manipulated as native PHP arrays | ⬜ |
| `technical_readability_score` always between 0-100 | ⬜ |

### Agentic Layer — Tools & Memory (20%)

| Critère | Statut |
|---------|--------|
| `getCampaignRules` triggered when asking about blueprint rules — never invented | ⬜ |
| `getPostHistory` triggered when asking about post content — never hallucinated | ⬜ |
| Follow-up questions maintain context via conversation memory tables | ⬜ |

### Code Quality & Delivery (15%)

| Critère | Statut |
|---------|--------|
| Zero N+1 — systematic Eager Loading confirmed with Debugbar | ⬜ |
| Minimum 20 atomic commits segmented by feature | ⬜ |
| Scribe documentation complete with pre-filled request/response examples | ⬜ |
| `.env` ignored — zero sensitive data committed | ⬜ |

---

*Last updated: 22/06/2026*