# Database Installation

This document explains how to install the database for the Chest Counter application.

## 📋 Overview

The project uses **CakePHP Migrations** to manage the database schema and **Seeds** to populate initial data. This ensures:

- Versioned, repeatable database setup
- Easy rollback if needed
- Consistent environment across development and production
- No manual SQL file imports required

---

## 🚀 Step-by-Step Installation (New Installation)

### 1. Create the Database

```bash
mysql -u root -p -e "CREATE DATABASE chestcounter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 2. Configure Database Connection

Edit `config/app_local.php` and set your database credentials:

```php
'Datasources' => [
    'default' => [
        'host' => 'localhost',
        'username' => 'your_user',
        'password' => 'your_password',
        'database' => 'chestcounter',
    ],
],
```

### 3. Run Migrations (Create Tables)

```bash
php bin/cake.php migrations migrate
```

This will create all 15 application tables with their indexes and foreign keys.

### 4. Run Seeds (Insert Initial Data)

```bash
php bin/cake.php migrations seed
```

This inserts the essential initial data:
- **roles**: 3 roles (admin, user, bankers)
- **config**: 20 configuration parameters
- **standard_chests**: 102 chest types with configured scores

### 5. Verify Installation

```bash
# Check migration status
php bin/cake.php migrations status
```

```sql
-- Verify initial data
SELECT COUNT(*) FROM roles;           -- Should return 3
SELECT COUNT(*) FROM config;          -- Should return 20
SELECT COUNT(*) FROM standard_chests; -- Should return 102
```

### 6. Create the First Administrator

```bash
php bin/cake.php create_admin
```

---

## 🔄 Migrating from SQL Dump (Existing Databases)

If you already have a database created from the old `databasemodel.sql` dump, you need to mark the initial migration as already applied:

```bash
php bin/cake.php migrations mark_migrated 20260822220000
```

This tells CakePHP that the `InitialSchema` migration has already been applied (since the tables already exist), preventing conflicts.

> ⚠️ **Important:** Only run `mark_migrated` if your database was already created from the SQL dump. For new installations, use `migrations migrate` as described above.

---

## 📝 Database Structure

### Tables (15 tables):

| Table | Description |
|-------|-------------|
| `users` | System users |
| `roles` | Roles/permissions (admin, user, bankers) |
| `roles_users` | User-role relationships |
| `members` | Clan members |
| `collected_chests` | Collected chests |
| `standard_chests` | Standard chest types (102 types) |
| `config` | System configuration (20 parameters) |
| `player_cycle_summaries` | Player cycle summaries |
| `player_name_mappings` | Player name mappings |
| `bank_accounts` | Member bank accounts |
| `bank_transactions` | Bank transactions |
| `bank_approval_logs` | Bank approval logs |
| `errors` | Error log |
| `events` | Events |
| `incomplete_chests` | Incomplete chests |

### Initial Data (via Seeds):

- **roles**: 3 roles (admin, user, bankers)
- **config**: 20 initial configuration parameters
- **standard_chests**: 102 chest types with configured scores

---

## ⚙️ Useful Migration Commands

```bash
# Check current migration status
php bin/cake.php migrations status

# Run all pending migrations
php bin/cake.php migrations migrate

# Rollback the last migration
php bin/cake.php migrations rollback

# Run all seeds
php bin/cake.php migrations seed

# Run a specific seed
php bin/cake.php migrations seed --seed InitialDataSeed
```

---

## ⚠️ Troubleshooting

### Error: "Table already exists"

If migrations fail because tables already exist, mark them as migrated:

```bash
php bin/cake.php migrations mark_migrated 20260822220000
```

### Error: "Access denied"

Verify that the user has permissions:

```sql
-- Grant permissions (as root)
GRANT ALL PRIVILEGES ON chestcounter.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;
```

### Error: "Unknown database"

Make sure the database was created before running migrations:

```sql
CREATE DATABASE chestcounter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Reset Database (Development Only)

```bash
# Rollback all migrations (WARNING: deletes all data!)
php bin/cake.php migrations rollback -t 0

# Re-run migrations
php bin/cake.php migrations migrate

# Re-seed data
php bin/cake.php migrations seed
```

---

## 🔐 Security

⚠️ **Important:**
- After installation, create the first administrator using:
  ```bash
  php bin/cake.php create_admin
  ```
- Keep database credentials secure in `config/app_local.php`
- Never commit `config/app_local.php` to Git (already in `.gitignore`)

---

## 📚 References

- [CakePHP Migrations Documentation](https://book.cakephp.org/migrations/4/en/index.html)
- [Phinx Documentation](https://phinx.org/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
