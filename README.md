# Rental Management System (Laravel 11)

A landlord-first MVP for small property owners. Tracks properties, units,
tenant leases, rent invoices and payments, maintenance requests (with photo
uploads) and private landlord documents — all behind a login.

## Features

### Authentication
- **Email + password** registration and login.
- **Google / Gmail OAuth** via Laravel Socialite — "Sign in with Google"
  button on both the login and register pages.
- Auto-creates the landlord account on first Google sign-in, links existing
  accounts that share the same email, marks `email_verified_at` automatically
  (because Google has already verified the address).

### Dashboard
- Operational summary cards: expected monthly rent, collected this month,
  expenses this month, net cash flow, outstanding balance, occupancy bar.
- Open invoices table (with **auto-computed late fee** per row).
- Recent maintenance activity.
- Portfolio overview with per-property occupancy progress bar.
- Quick-action shortcuts.

### Properties & units
- A landlord owns many properties, each with many units.
- `Property.php` and `Unit.php` define those relationships.
- Per-unit active lease and history.

### Tenant leases
- Connects a tenant to a unit, stores lease dates, monthly rent and deposit.
- Statuses: active / upcoming / expired / terminated.
- Reports list leases expiring in the next 30 days.

### Rent invoicing & payments
- `RentInvoice` stores the invoice; `Payment` records each payment.
- Invoices show the original amount, optional advance-rent credit, and
  amount due after the credit.
- Balance = `amount_due − sum(payments)`.
- Status (open / partial / paid / overdue) is recomputed on every change.
- **Late fee** computed from landlord settings (grace days + flat fee + % of outstanding).
- **Recurring invoice generator**: `php artisan rent:generate-invoices`
  creates the monthly invoice for every active lease that doesn't already
  have one for the target month.
- **Overdue reminders**: `php artisan rent:overdue-reminders` emails
  landlords about every overdue/partial invoice they opted in for.

### Maintenance requests
- Title, description, category, priority, status, optional photo upload.
- Timeline of status changes.
- Email notification to the landlord whenever the status changes.

### Expenses
- Per-property or whole-portfolio expenses (mortgage, taxes, insurance,
  utilities, repairs, management fees, other).
- Receipt upload (stored privately).
- Recurring flag for monthly expenses.
- CSV export (filtered by property / category / date range).

### Reports & analytics
- Monthly income vs. expenses bar chart (12 months, current year).
- Year totals: income, expenses, net cash flow, occupancy rate.
- Expense breakdown by category.
- Maintenance breakdown by status.
- Leases expiring in the next 30 days.
- Top 10 outstanding balances.
- Per-property P&L breakdown.

### Private documents
- Uploaded files are stored on the `private` Laravel disk and streamed
  through the controller — never exposed by URL.

### Settings
- Business profile (name, email, phone).
- Currency, timezone, locale.
- Late fee policy (grace days, flat fee, percent).
- Per-event email opt-ins.

### Email notifications
- `WelcomeMail` on registration.
- `InvoiceCreatedMail` when an invoice is created (including via the
  recurring command).
- `PaymentReceivedMail` when a payment is recorded.
- `MaintenanceStatusUpdatedMail` on status change.
- `OverdueReminderMail` from the `rent:overdue-reminders` command.

## Stack

- PHP 8.2+
- Laravel 11
- Laravel Socialite 5 (Google OAuth)
- league/csv (CSV exports)
- Blade views (single dark UI, no external CSS / JS framework)
- SQLite by default, MySQL / Postgres supported via `.env`

## Requirements

- PHP 8.2+ with `pdo_sqlite` (or `pdo_mysql` / `pdo_pgsql`), `mbstring`,
  `openssl`, `gd` (for maintenance photo thumbnails if you add them).
- Composer 2.

## Local setup

```bash
# 1. Install PHP dependencies (this also pulls in Socialite + league/csv)
composer install

# 2. Copy env file and generate the application key
cp .env.example .env
php artisan key:generate

# 3. Create the SQLite database file (or set DB_CONNECTION=mysql in .env)
touch database/database.sqlite

# 4. Run migrations
php artisan migrate

# 5. Create the public storage symlink (for maintenance photos)
php artisan storage:link

# 6. Start the dev server
php artisan serve
```

Visit http://localhost:8000 — you'll be redirected to `/register`. You can
either register with email + password, or click **Sign in with Google** if
you've configured Google OAuth (see below).

## Seeding demo data

```bash
php artisan db:seed
```

Demo landlord: `demo@rental.test` / `password`. Includes a property, two
units, one lease, one invoice (partially paid), one maintenance request,
three expenses and one document.

## Google OAuth (Sign in with Google) — local & production setup

1. Go to the **Google Cloud Console**: https://console.cloud.google.com/
2. Create (or pick) a project.
3. **APIs & Services → OAuth consent screen**:
   - Choose **External**.
   - Fill in app name, support email, developer email.
   - Add your own Google account as a **Test user**.
4. **APIs & Services → Credentials → Create Credentials → OAuth client ID**:
   - Application type: **Web application**.
   - Authorized JavaScript origins:
     `http://localhost:8000` (local), `https://your-domain.com` (prod)
   - Authorized redirect URIs (this must be exact):
     - Local dev:  `http://localhost:8000/auth/google/callback`
     - Production: `https://your-domain.com/auth/google/callback`
5. Copy the **Client ID** and **Client Secret**.
6. Put them in `.env`:
   ```
   GOOGLE_CLIENT_ID=xxxxxxxx.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=xxxxxxxx
   GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
   ```
   (When you deploy, change `GOOGLE_REDIRECT_URI` to the production URL
   and add the same value to Google's authorized redirect URIs.)
7. Restart `php artisan serve` and the "Sign in with Google" button on
   the login / register page will work.

For production with real users, also submit the OAuth consent screen for
verification (Google's "Publishing status" → **In production**). For a
private landlord app with only yourself as the user, leaving it in
**Testing** mode and adding yourself as a test user is enough.

## Scheduled tasks (optional, recommended for production)

Add to `app/Console/Kernel.php` or Laravel 11's `routes/console.php`
schedule block:

```php
Schedule::command('rent:generate-invoices')->monthlyOn(1, '00:00');
Schedule::command('rent:overdue-reminders')->dailyAt('08:00');
Schedule::command('rent:recompute-statuses')->dailyAt('00:30');
```

Then set up a cron job:

```
* * * * * cd /path/to/rental-management && php artisan schedule:run >> /dev/null 2>&1
```

## Database schema overview

Migrations live in `database/migrations/`:

| Table | Purpose |
| --- | --- |
| `users` | Landlord accounts (incl. `google_id`, `avatar`, `provider`) |
| `properties` | Properties owned by a landlord |
| `units` | Units inside a property |
| `tenant_leases` | A tenant + lease terms bound to a unit |
| `rent_invoices` | Invoices against a lease |
| `payments` | Payments against an invoice |
| `maintenance_requests` | Maintenance requests + photo path |
| `maintenance_timelines` | Status change timeline |
| `documents` | Private document metadata |
| `expenses` | Per-property / per-unit expenses |
| `settings` | Per-landlord business profile + notification prefs + late-fee policy |
| `sessions` | Session driver (database) |

## Project structure

```
app/
  Console/Commands/   GenerateRecurringInvoices, SendOverdueReminders,
                      RecomputeInvoiceStatuses
  Http/Controllers/   AuthController, GoogleAuthController,
                      DashboardController, PropertyController,
                      UnitController, TenantLeaseController,
                      RentInvoiceController, PaymentController,
                      MaintenanceRequestController, DocumentController,
                      ExpenseController, ReportController, SettingsController
  Mail/               WelcomeMail, InvoiceCreatedMail, PaymentReceivedMail,
                      MaintenanceStatusUpdatedMail, OverdueReminderMail
  Models/             User, Property, Unit, TenantLease, RentInvoice,
                      Payment, MaintenanceRequest, MaintenanceTimeline,
                      Document, Expense, Setting
  Providers/          AppServiceProvider
bootstrap/app.php     Laravel 11 bootstrap
config/               app, auth, database, filesystems, session, services, mail
database/migrations/  All schema migrations
public/               index.php entry point + .htaccess (Apache)
resources/views/      Blade views (layouts, auth, dashboard, properties,
                      units, leases, rent, maintenance, expenses, reports,
                      settings, documents, emails)
routes/web.php        Route file (guest vs auth groups, Google OAuth routes)
```

## Deployment

See **DEPLOYMENT.md** for step-by-step instructions for several truly free
PHP hosts. Note: Laravel is a PHP framework and **cannot run on Cloudflare
Pages or Cloudflare Workers** (those platforms only serve static files and
JavaScript/Workers). Use the hosts described in `DEPLOYMENT.md` instead.

## License

MIT — for educational / small-business use.
