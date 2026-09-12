# Chest Counter & Clan Bank - Total Battle Game

Chest counter and Clan Silver Bank management system for the Total Battle game, developed in CakePHP 5.

- **Chest Counter:** Collect chests via Python script, send them to MySQL, and display player scores, rankings, and cycle summaries for the clan.
- **Clan Silver Bank:** Complete banking system to manage clan silver, including deposits, withdrawals, player-to-player transfers, banker approval workflows, and transaction statements.

**Repository:** https://github.com/crashbrtb/chestcounter.git

---

## 📋 Prerequisites

Before starting the installation, make sure your hosting server has:

- **PHP >= 8.1** with the following extensions:
  - `pdo_mysql`
  - `mbstring`
  - `intl`
  - `openssl`
  - `json`
  - `xml`
  - `curl`
  - `gd` (validates and converts uploaded logos and favicons; see [Appearance](#-appearance))
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Composer** (PHP dependency manager)
- **Git**
- **SSH** access to the server
- **Apache** or **Nginx** with mod_rewrite enabled

---

## 🚀 Step-by-Step Installation Guide

### 1. Connect via SSH and Navigate to Directory

Connect to your server via SSH and navigate to the directory where you want to install the application (usually `public_html`, `www` or `htdocs`):

```bash
ssh user@your-server.com
cd ~/public_html
# or
cd /var/www/html
# or the directory configured on your server
```

### 2. Clone the Repository

Clone the repository from GitHub:

```bash
git clone https://github.com/crashbrtb/chestcounter.git .

```
Note: The command above extracts the files to the folder you are in. If you are going to manage multiple clans, you need to create a folder for each clan.

### 3. Install Dependencies with Composer

Install all project dependencies:

```bash
composer install --no-dev --optimize-autoloader
```

> **Note:** The `--no-dev` flag removes development dependencies and `--optimize-autoloader` optimizes the autoloader for production.

If Composer is not installed globally, you can download it:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
php composer.phar install --no-dev --optimize-autoloader
```
> **Note:**  If errors, try php -d disable_functions="" composer install

### 4. Configure the Database

Create a MySQL database and a user with permissions:

```sql
CREATE DATABASE chestcounter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'chestcounter_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON chestcounter.* TO 'chestcounter_user'@'localhost';
FLUSH PRIVILEGES;
```

> **Important:** Replace `chestcounter_user` and `secure_password_here` with secure credentials.

### 5. Configure the app_local.php File

Copy the example file and configure the database credentials:

```bash
cp config/app_local.example.php config/app_local.php
```

Edit the `config/app_local.php` file and configure:
```bash
vi config/app_local.php
```
Note: To edit the content of a file in vi type "i", to exit vi without saving press ":q" and to save and exit press ":wq"

```php
<?php
return [
    // Disable debug in production
    'debug' => filter_var(env('DEBUG', false), FILTER_VALIDATE_BOOLEAN),

    'Security' => [
        // Generate a secure random key
        'salt' => env('SECURITY_SALT', 'your_very_long_and_random_secret_key_here'),
    ],

    'Datasources' => [
        'default' => [
            'host' => 'localhost',
            'username' => 'chestcounter_user',
            'password' => 'secure_password_here',
            'database' => 'chestcounter',
            'encoding' => 'utf8mb4',
        ],
    ],
];
```

> **Security Tip:** To generate a secure key for `Security.salt`, you can use:
> ```bash
> php -r "echo hash('sha256', random_bytes(64));"
> ```

### 6. Configure Directory Permissions

Create and configure the correct permissions for directories that need to be writable:

```bash
mkdir -p tmp/cache/models tmp/cache/persistent tmp/cache/views tmp/sessions logs
chmod -R 775 tmp logs
chown -R username:username tmp logs
```

> **Note:** Replace `username` with the web server user (can be `apache`, `nginx`, `httpd`, or your own user, etc.)

### 7. Install the Database

Create the schema with the migrations, then load the initial data with the seed.
Both steps are required — migrations only create empty tables.

```bash
# 1. Create all tables, indexes and foreign keys
php bin/cake.php migrations migrate

# 2. Insert roles, configuration parameters and standard chest types
php bin/cake.php migrations seed
```

**Verify if it was installed correctly:**

```sql
-- Verify that all tables exist
SHOW TABLES;

-- Verify initial data
SELECT COUNT(*) FROM roles; -- Should return 3
SELECT COUNT(*) FROM config; -- Should return 21
SELECT COUNT(*) FROM standard_chests; -- Should return 96
```

If `config` does not return 21, the application will fail to render its pages
because parameters such as `reference_day` are missing. Re-run the seed — it is
idempotent and inserts only the rows that are absent.

Then adjust `kingdom_number`, `clan_acronym`, `clan_name` and `reference_day`
to your own clan's values, either in the Settings screen or directly in the
`config` table. The seed ships placeholders (`K001`, `ABC`, ...).

> **Note:** For more details about database installation, see [INSTALL_DATABASE.md](INSTALL_DATABASE.md).

### 8. Create the First Administrator User

Since the application requires an administrator to create users, use the console command to create the first administrator:

```bash
php bin/cake.php create_admin
```

The command will prompt for:
- **Administrator name**
- **Administrator email**
- **Administrator password**

Or you can pass the parameters directly:

```bash
php bin/cake.php create_admin --name "Administrator" --email "admin@example.com" --password "secure_password"
```

### 9. Configure the Web Server

#### For Apache (.htaccess already included)

Make sure the `mod_rewrite` module is enabled and the `.htaccess` file is present in the project root.

The `.htaccess` file is already configured to redirect all requests to `webroot/index.php`.


### 10. Configure Automated Maintenance (Cron Job)

The application includes an automated daily maintenance task (`daily_maintenance`) that performs three routines in order:
1. **Cycle Summaries Processing:** Automatically calculates and archives player scores for any completed cycles.
2. **Old Chest Data Purge:** Purges collected chests older than the configured retention period (`collected_chests_retention_days`, default 30 days) to optimize database size.
3. **Event Results:** Records the final standings of events whose window has closed, before the chests they were scored from are purged.

Nothing inside the application runs this task, so without a cron entry finished
cycles are never archived and closed events end up with an empty history.

#### From the site (recommended)

Sign in as an administrator and open **Admin > Maintenance**. The page reads the
crontab of the user the web server runs as and says whether the task is
scheduled; if it is not, choose how many times a day it should run and press
**Install in crontab**.

Times are picked on the **Brazil clock** (`America/Sao_Paulo`) and written to the
crontab in **UTC**, with `CRON_TZ=UTC` declared so the schedule means the same
moment whatever timezone the server keeps. The suggestion is twice a day, at
**14:15** and **02:15** Brazil time — **17:15** and **05:15** UTC.

The page only rewrites the lines between its own marker comments, so anything
else in the crontab is left alone. Cron output is appended to
`logs/maintenance_cron.log`, and the page reports when that file was last
written as evidence the task is really running.

> **Note:** installing from the page needs PHP to be able to run `exec()` and the
> `crontab` command to be available to the web server user. When it is not, the
> page says so and shows the exact block to paste in by hand.

#### By hand

Open the crontab of the user that should run the task:

```bash
crontab -e
```

Add the suggested schedule — twice a day, in UTC:

```cron
CRON_TZ=UTC
# 02:15 America/Sao_Paulo = 05:15 UTC
15 5 * * * cd /var/www/html/chestcounter && /usr/bin/php bin/cake.php daily_maintenance >> /var/www/html/chestcounter/logs/maintenance_cron.log 2>&1
# 14:15 America/Sao_Paulo = 17:15 UTC
15 17 * * * cd /var/www/html/chestcounter && /usr/bin/php bin/cake.php daily_maintenance >> /var/www/html/chestcounter/logs/maintenance_cron.log 2>&1
# Database backup at the 17:00 UTC game reset (only if you turned it on)
0 17 * * * cd /var/www/html/chestcounter && /usr/bin/php bin/cake.php database_backup >> /var/www/html/chestcounter/logs/database_backup_cron.log 2>&1
```

> **Note:** Replace `/var/www/html/chestcounter` with the actual path to your application.
> If your cron does not support `CRON_TZ`, drop that line and write the hours in the server's own timezone.

You can test the command manually in dry-run mode (without modifying the database):

```bash
php bin/cake.php daily_maintenance --dry-run
```

#### Nightly Database Backup

The same page carries the automated database backup (`database_backup`), which
replaces the old `backup_database.sh` script: a gzipped `mysqldump` of the whole
database, written as `backup_<database>_<date>.sql.gz`, with every step appended
to `backup.log` in the same folder and dumps older than the retention period
deleted after each run.

It is **off** until you turn it on under **Admin > Maintenance**, where three
things are yours to choose:

| Setting | Config parameter | Default |
| --- | --- | --- |
| Whether it runs | `database_backup_enabled` | off |
| Folder to save to | `database_backup_dir` | `~/bkpdb` |
| How long dumps are kept | `database_backup_retention_days` | 7 days |

The folder must be a full path, because cron does not start in the site
directory; `~` is the home directory of the user the site runs as, and the folder
is created on the first run. The page also reports the folder, how many dumps are
in it, their total size and when the newest one was taken.

The **time is fixed at 17:00 UTC**, the game's daily reset, so each dump holds
one cycle day exactly as the game closed it. Its cron line lives in the same
managed block as the maintenance, so turning the backup on or off rewrites that
block; its own output is appended to `logs/database_backup_cron.log`.

Credentials are taken from the application's own database connection — there is
nothing to fill in — and are passed to `mysqldump` through a temporary options
file rather than on the command line, where `ps` would show the password to
everyone logged in.

```bash
# Take a backup now
php bin/cake.php database_backup

# See what it would write and delete, without touching anything
php bin/cake.php database_backup --dry-run

# Take one even while the nightly backup is turned off
php bin/cake.php database_backup --force
```

> **Note:** this needs the `mysqldump` client tool installed on the server. The
> page says so plainly when it cannot find it.

---

### 11. Final Checks

1. **Test application access:**
   - Access `http://your-domain.com` in your browser
   - You should see the application home page

2. **Test login:**
   - Access the login page
   - Use the administrator credentials created in step 8


#### Disable Debug in Production

Make sure debug is disabled in `config/app_local.php`:

```php
'debug' => false,
```

---

## 🔄 Updating the Application

Updates are delivered through the same Git repository used in the installation,
so upgrading is a `git pull` plus the database and dependency steps below.

Nothing you configured locally is versioned — `config/app_local.php`, `logs/`
and `tmp/` are all in `.gitignore` — so your database credentials, `Security.salt`
and sessions survive the update untouched.

### 1. Back Up Before Updating

Always take a database dump (and ideally a copy of `config/app_local.php`)
before pulling a new version:

```bash
mysqldump -u user -p chestcounter > backup_$(date +%Y%m%d).sql
cp config/app_local.php config/app_local.php.bak
```

### 2. Pull the New Version

```bash
# Go to the installation directory (one per clan, if you manage several)
cd /var/www/html/chestcounter

# Check which branch you are on
git branch --show-current

# Bring in the new version
git pull origin main
```

> **Note:** If you installed from another branch, replace `main` with that
> branch name. Running `git pull` with no arguments also works when the branch
> already tracks its remote.

### 3. Update the Dependencies

New releases may add or bump Composer packages, so always re-run:

```bash
composer install --no-dev --optimize-autoloader
```

### 4. Apply New Migrations and Seeds

Run both steps after every update. Migrations create the new tables and columns;
the seed inserts configuration parameters and reference data added by the new
version. Both are idempotent — already-applied migrations and existing rows are
skipped.

```bash
# Apply any migration that is still pending
php bin/cake.php migrations migrate

# Insert new roles, config parameters and standard chests
php bin/cake.php migrations seed
```

To see what is pending before applying it:

```bash
php bin/cake.php migrations status
```

### 5. Clear the Cache and Fix Permissions

```bash
rm -rf tmp/cache/models/* tmp/cache/persistent/* tmp/cache/views/*
chmod -R 775 tmp logs
chown -R username:username tmp logs
```

> **Note:** Replace `username` with your web server user (`apache`, `nginx`,
> `www-data`, ...). This matters when `git pull` or Composer ran as a different
> user than the one serving the site.

### 6. Check the Update

1. Open the application in the browser and log in.
2. Confirm the Settings screen still shows your clan values
   (`kingdom_number`, `clan_acronym`, `clan_name`, `reference_day`).
3. Watch the log for errors: `tail -f logs/error.log`.
4. If a release ships new emblems, redraw the branding artwork:
   `php bin/cake.php branding_presets` (see [Appearance](#-appearance)).

### Update in One Block

For a routine update, the whole sequence is:

```bash
cd /var/www/html/chestcounter
mysqldump -u user -p chestcounter > backup_$(date +%Y%m%d).sql
git pull origin main
composer install --no-dev --optimize-autoloader
php bin/cake.php migrations migrate
php bin/cake.php migrations seed
rm -rf tmp/cache/models/* tmp/cache/persistent/* tmp/cache/views/*
```

### Problems During `git pull`

**`Your local changes to the following files would be overwritten by merge`**

You edited a versioned file. Either keep your edits aside or discard them:

```bash
# See what was changed locally
git status

# Option A: set your changes aside, update, then bring them back
git stash
git pull origin main
git stash pop

# Option B: discard the local change to a specific file
git checkout -- path/to/file
```

**`Permission denied` while writing files**

Run the update as the user that owns the directory (or with `sudo`), then
restore ownership of `tmp` and `logs` as shown in step 5.

**Page breaks or blank screen after the update**

Almost always a missing migration/seed or a stale cache. Re-run steps 4 and 5,
then check `logs/error.log`.

---

## 🎨 Appearance

Both of these live under **Admin → Branding** and **Admin → Theme** in the
navbar, and both are site-wide: they change what every visitor sees, players
included, not just the administrator making the change.

### Logo and favicon

Ten emblems ship with the application, on a medieval-strategy theme — dragons,
swords, armour, monsters:

`dragon-crest` · `crossed-swords` · `knight-helm` · `castle-keep` · `war-axe` ·
`dragon-claw` · `ogre-skull` · `crown-of-war` · `tower-shield` · `war-banner`

The logo and the favicon are chosen separately, so a detailed emblem in the
navbar can be paired with a simpler one in the browser tab.

Every image is a **file on disk** under `webroot/img/branding`, never a row in
the database, so the web server serves it as a plain static asset. Choosing a
favicon also refreshes `webroot/favicon.ico`, which is what browsers ask for by
name before they have read a line of the page.

You can upload your own instead. Uploads are re-encoded rather than stored as
sent, so what ends up in `webroot` is always something the server could decode:

| | Logo | Favicon |
|---|---|---|
| Formats | PNG, JPEG, GIF, WebP | PNG, JPEG, GIF, WebP, ICO |
| Dimensions | 128×128 to 2048×2048 | 32×32 to 512×512 |
| Shape | square, up to 3× wider than tall | square (±10%) |
| Maximum size | 2 MB | 512 KB |
| Stored as | PNG, 512px on its longest side | 256×256 PNG **and** a generated `.ico` holding 16, 32, 48 and 64px |

A rejected upload says which rule it broke and leaves the current choice alone.

The shipped artwork is drawn by code rather than checked in as opaque binaries,
and is committed, so a normal deployment never needs to regenerate it. If a
webroot is ever rebuilt without it:

```bash
# Draw any preset whose files are missing
php bin/cake.php branding_presets

# Redraw everything, including files already on disk
php bin/cake.php branding_presets --force

# Redraw just one
php bin/cake.php branding_presets --only=dragon-crest
```

This needs the **GD extension**, which is also what validates and converts
uploads. Without it the branding page still works for choosing between presets.

### Theme

Four colour schemes:

| Theme | Looks like |
|---|---|
| **Morning Light** | Cool white and indigo. The original look, and the default. |
| **Deepest Dark** | Near black, high contrast. |
| **Wildflowers** | Warm paper and poppy pink, colourful throughout. |
| **Twilight** | Grey slate with a dusk violet accent. |

A theme is a palette and nothing else. `webroot/css/theme-score.css` declares
the whole token set on `:root` and paints every surface, border and neutral
text colour through it; `webroot/css/themes.css` redefines those same tokens
under `[data-theme="..."]`, which the layout puts on the `<html>` element. To
add a fifth, add a palette there and an entry in `src/Service/ThemeService.php`.

Two things the test suite will hold you to, in
`tests/TestCase/Service/ThemeServiceTest.php`: a palette has to define the
**whole** token set (a missing token silently inherits the light default, which
on a dark theme means one white panel in the middle of the page), and every
text-on-surface pair has to meet **WCAG AA contrast**.

---

## 🔧 Troubleshooting

### Error 500 - Internal Server Error

A 500 error is one of the most common and can have several causes. Follow this checklist:

1. **Check error logs:**
   ```bash
   tail -f logs/error.log
   tail -f logs/debug.log
   ```

2. **Check permissions:**
   ```bash
   chmod -R 775 tmp logs
   chown -R username:username tmp logs
   ```

3. **Check the app_local.php file:**
   ```bash
   php -l config/app_local.php
   ```
   - Make sure it exists and is configured correctly
   - Verify that `Security.salt` is not `__SALT__`
   - Check database credentials

4. **Check .htaccess:**
   - If the application is at the **domain root**, comment or remove the line `RewriteBase /chestcounter/` in `webroot/.htaccess`
   - If it's in a **subdirectory**, adjust the `RewriteBase` to the correct path


6. **Clear cache:**
   ```bash
   rm -rf tmp/cache/*
   ```

7. **Enable debug temporarily** (for diagnosis only):
   ```php
   // In config/app_local.php
   'debug' => true,
   ```
   ⚠️ **Disable again after diagnosing!**


## 📚 Project Structure

```
chestcounter/
├── bin/                    # Executable scripts
├── config/                 # Configuration files
│   ├── Migrations/         # Database schema migrations
│   ├── Seeds/              # Initial data seeds
│   └── app_local.php       # Local configurations (not versioned)
├── logs/                   # Log files
├── src/                    # Application source code
│   ├── Command/            # Console commands
│   ├── Controller/         # Controllers
│   ├── Model/              # Models and entities
│   └── View/               # Views and helpers
├── templates/              # View templates
├── tmp/                    # Temporary files
├── vendor/                 # Composer dependencies
└── webroot/                # Public entry point
```

---

## 🔐 Security

- Use strong passwords for database and administrator
- Keep `Security.salt` secret and unique
- Disable `debug` in production
- Keep PHP and dependencies updated

---

## 📝 Useful Commands

```bash
# Create new administrator (only if none exists)
php bin/cake.php create_admin

# Show which migrations are applied and which are pending (see Updating)
php bin/cake.php migrations status

# Run daily maintenance manually (dry-run mode)
php bin/cake.php daily_maintenance --dry-run

# Run daily maintenance (processes pending summaries + purges old chests)
php bin/cake.php daily_maintenance

# Back the database up now (see Nightly Database Backup)
php bin/cake.php database_backup

# Redraw the shipped logo/favicon artwork into webroot (see Appearance)
php bin/cake.php branding_presets --force

# Clear cache
rm -rf tmp/cache/*

# View logs in real time
tail -f logs/error.log

# Backup database
mysqldump -u user -p chestcounter > backup_$(date +%Y%m%d).sql
```

---

## 🤝 Support

For more information on how to create the first administrator, see the file [FIRST_ADMIN.md](FIRST_ADMIN.md).

---

## 📄 License

MIT License

---

**Developed with CakePHP 5**
