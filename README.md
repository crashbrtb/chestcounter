# Chest Counter - Total Battle Game

Chest counter for Total Battle game developed in CakePHP 5.

Use the Python script to collect the chests and send them to the MySQL database.

Use this front-end to display the score for the clan.

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

Execute the complete SQL dump that contains the entire structure and initial data:

```bash
mysql -u your_user -p chestcounter < config/databasemodel.sql
```

**Or via phpMyAdmin:**
1. Select the `chestcounter` database
2. Go to "Import"
3. Select the file `config/databasemodel.sql`
4. Click "Execute"

**Verify if it was installed correctly:**

```sql
-- Verify that all tables exist
SHOW TABLES;

-- Verify initial data
SELECT COUNT(*) FROM roles; -- Should return 3
SELECT COUNT(*) FROM config; -- Should return 20
SELECT COUNT(*) FROM standard_chests; -- Should return 102
```

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


### 10. Final Checks

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
│   ├── databasemodel.sql   # Complete database SQL dump
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
