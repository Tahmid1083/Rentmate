# Deployment guide

> **Important: Cloudflare Pages and Cloudflare Workers do NOT run PHP.**
> They serve static files and JavaScript/Workers only. Because this is a
> Laravel (PHP) application, **it cannot be hosted directly on Cloudflare's
> free tier.**
>
> The options below are real, **free** hosts that support PHP + a database,
> and each takes about 10–15 minutes to set up.

Pick ONE of the sections below. All of them have a usable free tier.

---

## Option A — Render.com (recommended for free, modern deploy)

Render has a free "Web Service" tier that runs any Dockerized or
PHP-driven app. Free tier sleeps after 15 min of inactivity and spins
back up on the next request.

### Steps

1. **Push this project to a GitHub repository.**
   - Create an empty repo on GitHub (e.g. `rental-management`).
   - Locally:
     ```bash
     cd rental-management
     git init
     git add .
     git commit -m "Initial commit"
     git branch -M main
     git remote add origin https://github.com/<you>/rental-management.git
     git push -u origin main
     ```

2. **Create a free database.**
   - On Render, create a free **PostgreSQL** database (or use a free
     MySQL provider such as Aiven / TiDB Cloud / PlanetScale if you
     prefer MySQL — Laravel supports both).
   - Note the **internal connection URL** Render shows you
     (`postgres://user:pass@host:5432/dbname`).

3. **Create a Web Service on Render.**
   - New → Web Service → connect your GitHub repo.
   - Select **Docker** so Render uses the repository's `Dockerfile`.
     It installs Composer dependencies and runs migrations at startup.
     Do not put `php artisan key:generate` in a build or start command.
   - Free instance type: **Yes.**

4. **Environment variables** (Render → Environment tab):
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://<your-service>.onrender.com
   APP_KEY=base64:...  (generate locally with `php artisan key:generate --show`,
                        then paste the complete output here)
   DB_CONNECTION=pgsql
   DB_URL=postgres://...    (your Render Postgres internal URL)
   SESSION_DRIVER=database
   FILESYSTEM_DISK=local
   ```
   `APP_KEY` must be a non-empty, persistent Render environment variable.
   Do not leave it blank, generate it during each deploy, or commit it to
   GitHub. Laravel uses this key to encrypt cookies and sessions. Set
   `APP_DEBUG=false` in production to avoid exposing exception details.
   For MySQL set `DB_CONNECTION=mysql` and `DB_HOST`, `DB_PORT`,
   `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` instead of `DB_URL`.

5. Save the environment variables and deploy. The first cold start takes
   ~30s; subsequent requests are fast.

If you already created the service with a native PHP runtime rather than
Docker, add the same environment variables in its Environment tab. Use
`composer install --no-dev --optimize-autoloader` as the build command and
`php artisan serve --host 0.0.0.0 --port $PORT` as the start command; run
`php artisan migrate --force` from the Render Shell after connecting the
database. Never rely on a build command to generate `APP_KEY` for the
running service.

For an existing service showing **"No application encryption key has been
specified"**, add `APP_KEY` in Render → your service → Environment using
the locally generated value above, and set `APP_DEBUG=false`. Save the
changes; if Render does not deploy automatically, choose **Manual Deploy
→ Deploy latest commit**.

> Render's free PostgreSQL expires after 90 days. For a longer-lived
> free database, see **Option C** (InfinityFree) or use a free Neon /
> Supabase Postgres and point `DB_URL` at it.

---

## Option B — Railway.app (very simple, free trial credit)

Railway gives every new account a small free credit (enough for several
weeks of an MVP). PHP is auto-detected.

1. Push the repo to GitHub.
2. On Railway → **New Project → Deploy from GitHub repo**.
3. Add a **MySQL** or **PostgreSQL** plugin from Railway's catalog.
4. Set environment variables in Railway:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://<your-app>.up.railway.app
   APP_KEY=base64:...     (run `php artisan key:generate --show` locally and paste)
   DB_CONNECTION=mysql
   DB_HOST=...             (Railway exposes these automatically as MYSQL_* vars)
   DB_PORT=3306
   DB_DATABASE=railway
   DB_USERNAME=...
   DB_PASSWORD=...
   SESSION_DRIVER=database
   ```
5. Railway runs `composer install` automatically. To run migrations,
   add a one-off **command**: `php artisan migrate --force`.
6. Your app is live at `https://<your-app>.up.railway.app`.

---

## Option C — InfinityFree (truly free, forever, shared PHP hosting)

If you want a free host that **never** expires and supports PHP +
MySQL with no credit card, use InfinityFree.

1. Sign up at **https://infinityfree.com** and create a free account.
2. In the control panel, create a hosting account
   (you'll get a subdomain like `yourname.epizy.com`).
3. Open **File Manager** (or connect via FTP using the credentials shown
   in the control panel — host: `ftpupload.net`, username & password
   from the panel).
4. **Prepare the project locally first** (do this on your own machine,
   because InfinityFree cannot run `composer install`):
   ```bash
   cd rental-management
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   php artisan key:generate       # writes APP_KEY into .env
   ```
5. Edit `.env`:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourname.epizy.com
   DB_CONNECTION=mysql
   DB_HOST=sqlXXX.infinityfree.com   # from the MySQL Databases page
   DB_PORT=3306
   DB_DATABASE=epiz_XXXXXXXX_rental
   DB_USERNAME=epiz_XXXXXXXX
   DB_PASSWORD=...
   SESSION_DRIVER=database
   FILESYSTEM_DISK=local
   ```
6. Create the MySQL database in the InfinityFree control panel first
   (MySQL Databases → Create), then run migrations locally against the
   remote DB to build the tables:
   ```bash
   php artisan migrate --force
   ```
   Or upload `database/migrations/*.php` and run them via the phpMyAdmin
   "Import SQL" tab (export the schema with `php artisan schema:dump`).
7. **Upload all project files** into InfinityFree's `htdocs/` folder.
   - The folder structure must be: `htdocs/public/index.php`,
     `htdocs/app/...`, `htdocs/vendor/...`, etc.
   - InfinityFree serves files from `htdocs/`, so the document root is
     `htdocs/`. **Inside `htdocs/`, either move `public/*` up one level
     or create an `.htaccess` redirect:**

     Create `htdocs/.htaccess` with:
     ```
     <IfModule mod_rewrite.c>
         RewriteEngine On
         RewriteRule ^$ public/ [L]
         RewriteCond %{REQUEST_FILENAME} !-f
         RewriteCond %{REQUEST_FILENAME} !-d
         RewriteRule ^(.*)$ public/$1 [L]
     </IfModule>
     ```
8. Visit `https://yourname.epizy.com` — register your landlord account.

> InfinityFree has a 5 MB upload limit per request, which is enough for
> maintenance photos under 5 MB. For larger documents, raise the limit
> is not possible on the free plan — keep uploads small.

---

## Option D — Fly.io (free tier, Docker-based, full power)

Fly.io gives every account a small free allowance that runs a real
Linux VM (good for Laravel + SQLite-on-volume). Requires Docker
installed locally.

1. Install the Fly CLI: https://fly.io/docs/hands-on/install-flyctl/
2. From the project root:
   ```bash
   fly launch        # answer yes to deploying; let it write a Dockerfile
   fly secrets set APP_KEY=$(php artisan key:generate --show)
   fly secrets set APP_ENV=production
   fly secrets set APP_DEBUG=false
   fly secrets set SESSION_DRIVER=file
   fly secrets set DB_CONNECTION=sqlite
   fly volume create rental_data
   fly deploy
   ```
3. Edit the generated `fly.toml` to mount the volume under
   `/var/www/html/storage` so SQLite persists across deploys.

---

## Post-deploy checklist (any host)

Run once after the first deploy:

```bash
php artisan migrate --force         # build tables
php artisan storage:link            # public storage symlink
php artisan config:cache            # optional, speeds up app
php artisan route:cache             # optional, speeds up app
```

Set a persistent `APP_KEY` in the hosting provider's environment settings
before deploying. Generate its value locally with
`php artisan key:generate --show`; do not commit it or regenerate it on
every deploy.

If you ever change env variables or routes, clear caches:

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

## Using Cloudflare (only as a CDN / DNS proxy)

Cloudflare can still be useful in front of your real PHP host:

1. Add your domain to Cloudflare (free plan is fine).
2. Point the DNS A record at the IP of your Render / Railway / InfinityFree /
   Fly.io host.
3. Set the record to **Proxied** (orange cloud) to get free HTTPS,
   DDoS protection and edge caching of static assets.
4. In Cloudflare → SSL/TLS, set mode to **Full** or **Full (Strict)**.

This gives you Cloudflare's CDN edge for free **without** trying to run
PHP on Cloudflare itself (which is not supported).

## Setting up Google OAuth (Sign in with Google / Gmail)

Required once you deploy, so the "Sign in with Google" button works in
production.

### 1. Create Google OAuth credentials

1. Go to https://console.cloud.google.com/
2. Create (or pick) a project.
3. **APIs & Services → OAuth consent screen**:
   - User type: **External**.
   - App name: e.g. "Rental Manager".
   - Support email / developer email: your Gmail address.
   - Save and continue through the scopes screen (just `openid`,
     `email`, `profile` is enough — these are added automatically).
4. **APIs & Services → Credentials → Create Credentials → OAuth client ID**:
   - Application type: **Web application**.
   - Authorized JavaScript origins:
     `https://YOUR-DEPLOYED-DOMAIN`
   - Authorized redirect URIs:
     `https://YOUR-DEPLOYED-DOMAIN/auth/google/callback`
5. Copy the **Client ID** + **Client Secret**.

### 2. Put credentials in your host's env-var panel

On Render / Railway / Fly.io / InfinityFree, set:

```
GOOGLE_CLIENT_ID=xxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=xxxxxxxx
GOOGLE_REDIRECT_URI=https://YOUR-DEPLOYED-DOMAIN/auth/google/callback
```

(`GOOGLE_REDIRECT_URI` MUST exactly match what you entered in the Google
Cloud Console authorized redirect URIs list, including `https`.)

### 3. Publish the OAuth consent screen (or stay in Testing)

- **For a private landlord app (just you)**: leave the consent screen in
  **Testing** mode and add your Gmail address under **Test users**. You
  can sign in immediately.
- **For more than 100 users or external landlords**: click
  **Publish app** to move it to **In production**. Google may ask you to
  verify the app — for the `openid email profile` scopes that Socialite
  uses, this verification is usually light (you fill in a privacy policy
  URL and answer a few questions).

### 4. Test

Sign out, click **Sign in with Google** on the login page, complete the
Google consent screen — you should be redirected back to your dashboard
as an authenticated landlord.

## Setting up SMTP email (so notifications actually send)

Notifications (invoice created, payment received, maintenance status,
welcome, overdue reminders) need a working mailer. The app defaults to
`MAIL_MAILER=log` which writes emails to `storage/logs/laravel.log`
instead of sending them — useful for local dev, but in production you
need real SMTP.

### Cheapest free options

- **Brevo** (formerly Sendinblue): https://www.brevo.com — 300 free
  emails/day, free SMTP relay.
- **Resend**: https://resend.com — 3,000 free emails/month,
  Laravel supports it natively (set `MAIL_MAILER=resend` and
  `RESEND_KEY=...`).
- **Amazon SES**: 62,000 free emails/month if you send from EC2, but
  requires AWS account + verification.

### Quick Brevo SMTP setup

1. Sign up at Brevo, verify your email.
2. Go to **SMTP & API** → grab the SMTP host, port, username, password.
3. In your host's env-var panel set:
   ```
   MAIL_MAILER=smtp
   MAIL_HOST=smtp-relay.brevo.com
   MAIL_PORT=587
   MAIL_USERNAME=your-brevo-login-email
   MAIL_PASSWORD=your-brevo-smtp-key
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=you@yourdomain.com
   MAIL_FROM_NAME="Rental Manager"
   ```
4. Test by registering a new landlord — you should receive the welcome
   email. If you don't see it, check the host's mail log.

## Setting up scheduled reminders (optional but recommended)

Once deployed with a real mailer, set up a cron job so overdue reminders
and recurring invoices run automatically:

```
* * * * * cd /path/to/rental-management && php artisan schedule:run >> /dev/null 2>&1
```

And add this to `routes/console.php` (Laravel 11 schedule block):

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('rent:generate-invoices')->monthlyOn(1, '00:00');
Schedule::command('rent:overdue-reminders')->dailyAt('08:00');
Schedule::command('rent:recompute-statuses')->dailyAt('00:30');
```

## Troubleshooting

- **500 / "No application encryption key has been specified"** — run
  `php artisan key:generate --show` locally, then set the complete output
  as the persistent `APP_KEY` environment variable in the host's settings.
  Redeploy after saving it. Do not generate a different key on each restart.
- **Maintenance photos don't display** — run `php artisan storage:link`,
  or check that the `public/storage` symlink points to
  `storage/app/public` on the host.
- **Document downloads 403** — the controller authorizes by
  `document.user_id === auth()->id()`. Make sure you're logged in as the
  landlord who uploaded the file.
- **Sessions lost on every request** — set `SESSION_DRIVER=database`
  (already the default) and make sure the `sessions` table exists
  (`php artisan migrate`).
- **Google OAuth "redirect_uri_mismatch"** — the value in
  `GOOGLE_REDIRECT_URI` (in your `.env` or host env panel) must
  character-for-character match what you entered in the Google Cloud
  Console → Credentials → Authorized redirect URIs. Same scheme
  (http vs https), same trailing path `/auth/google/callback`, no
  trailing slash.
- **Google OAuth "Error 400: invalid_request" on sign-in** — usually
  means `GOOGLE_CLIENT_SECRET` is wrong or empty. Re-copy it from the
  Google Cloud Console.
- **Mail not sending in production** — by default `MAIL_MAILER=log`
  writes to a log file instead of sending. Set `MAIL_MAILER=smtp` and
  fill in `MAIL_HOST` / `MAIL_USERNAME` / `MAIL_PASSWORD` from your
  transactional email provider (Brevo / Resend / SES).
- **Socialite class not found after `composer install`** — make sure
  `laravel/socialite` is in `composer.json`. If you extracted an older
  version of the zip, run `composer require laravel/socialite
  league/csv`.
