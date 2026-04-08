# Form Builder

A multi-tenant Typeform-like form builder built with Laravel 11, Blade, Alpine.js, and Tailwind CSS.

- **Admin UI** — Google-Forms-style builder where each tenant designs forms with short answer, long answer, single choice, and multiple choice questions.
- **Public renderer** — A Typeform-style one-question-per-page experience served at `/f/{slug}`, themed per tenant.
- **Multi-tenant** — Every workspace is fully isolated. Registration creates a new tenant + first admin user atomically.

## Tech stack

- Laravel 11 / PHP 8.2+
- Tailwind CSS 3 + Alpine.js (via Vite)
- Laravel Breeze (Blade) for auth scaffolding
- SQLite for local development, PostgreSQL (e.g. Neon) for production

## Quick start

```bash
git clone <this-repo>
cd Form-Builder

composer install
npm install

cp .env.example .env
php artisan key:generate

# Creates database/database.sqlite, runs migrations, seeds demo data
touch database/database.sqlite
php artisan migrate:fresh --seed

# In one terminal:
php artisan serve

# In another:
npm run dev
```

Open http://127.0.0.1:8000.

The seeder creates a demo workspace you can log into immediately:

- **Email:** `admin@example.com`
- **Password:** `password`
- **Public form:** http://127.0.0.1:8000/f/lead-gen-form

You can also create a fresh tenant + admin user via http://127.0.0.1:8000/register.

## Using the app

1. **Sign up or log in.** Registration creates a brand-new isolated workspace.
2. **Dashboard** lists every form in your workspace. Click **+ New form** to create one.
3. **Form builder** lets you set the title, description, and add questions of four types (`text`, `textarea`, `mcq`, `multi`). Reorder with the up/down arrows. Click **Save form** to persist.
4. **Publish** toggles whether the form is visible at its public URL.
5. **Theme** (top-right link on the dashboard) lets you set the workspace's primary, secondary, background colors and font family. The theme is applied to every published form on this workspace.
6. **Public link** is `/f/{slug}` — share it with respondents. They'll get a one-question-at-a-time flow with your workspace theme.

## Switching to PostgreSQL / Neon for production

The repo defaults to SQLite so you can clone and run with zero database setup. All migrations use `$table->json()` (not Postgres-only `jsonb`), so the same schema runs on both backends.

To target Neon (or any PostgreSQL instance), update `.env`:

```
DB_CONNECTION=pgsql
DB_HOST=ep-xxxxx.us-east-2.aws.neon.tech
DB_PORT=5432
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_password
DB_SSLMODE=require
```

Then re-run:

```bash
php artisan migrate:fresh --seed
```

## Project layout

```
app/
  Models/
    Tenant.php           Workspace + theme defaults
    User.php             belongsTo Tenant
    Form.php             belongsTo Tenant, hasMany FormStep, hasMany Response
    FormStep.php         A question (text|textarea|mcq|multi) within a form
    Response.php         A submitted response, belongsTo Form + Tenant
    ResponseAnswer.php   One answer per step inside a Response
    Concerns/BelongsToTenant.php
                         Global scope that filters Form/Response queries
                         by auth()->user()->tenant_id, preventing cross-tenant
                         data leakage in the admin UI.
  Http/Controllers/
    DashboardController.php
    FormController.php           CRUD + publish toggle for the admin builder
    TenantThemeController.php    Workspace theme editor
    PublicFormController.php     Public renderer + submit + thanks page
    Auth/RegisteredUserController.php
                                 Overridden Breeze controller — creates tenant
                                 + first user atomically on /register
resources/views/
  forms/builder.blade.php        Alpine.js form builder
  theme/edit.blade.php           Color + font picker
  public/render.blade.php        Typeform-style public flow
  public/thanks.blade.php        Post-submit screen
  dashboard.blade.php            Form list + workspace stats
database/
  migrations/                    tenants, users (with tenant_id), forms,
                                 form_steps, responses, response_answers
  seeders/DatabaseSeeder.php     Creates the Acme demo tenant + Lead Gen form
```

## Manual smoke test

```bash
php artisan migrate:fresh --seed
php artisan serve &
npm run dev &
```

1. Visit `/register` and sign up a new workspace.
2. Log out, log back in as `admin@example.com` / `password`. Confirm you see the seeded **Lead Gen** form (and the registered tenant does not — proving isolation).
3. Edit the seeded form, add a question, save.
4. Visit `/theme` and change the primary color.
5. Open `/f/lead-gen-form` in a private window, walk through the form, and submit.
6. Inspect persisted data:
   ```bash
   php artisan tinker --execute="echo App\Models\Response::with('answers.step')->get()->toJson(JSON_PRETTY_PRINT);"
   ```

## What's intentionally NOT included (per MVP scope)

- Analytics dashboard
- AI builder
- Branching logic between questions
- Payments
- Subdomain-based tenant routing (uses auth-based instead — can be added later without schema changes)
