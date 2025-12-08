# Database Installation

This document explains how to install the database for the Chest Counter application.

## 📋 Overview

The project uses a **complete SQL dump** (`config/databasemodel.sql`) that contains the entire database structure, including:

- All necessary tables
- Essential initial data (roles, config, standard_chests)
- Indexes and relationships (foreign keys)
- Complete structure ready for use

---

## 🚀 Step-by-Step Installation

### 1. Create the Database

```bash
mysql -u root -p -e "CREATE DATABASE chestcounter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 2. Execute the SQL Dump

#### Via Command Line:

```bash
mysql -u your_user -p chestcounter < config/databasemodel.sql
```

#### Via phpMyAdmin:

1. Access phpMyAdmin
2. Select the `chestcounter` database (or create it if it doesn't exist)
3. Go to the "Import" tab
4. Click "Choose File" and select `config/databasemodel.sql`
5. Click "Execute"

### 3. Verify Installation

After executing the dump, verify that everything was installed correctly:

```sql
-- Verify that all tables exist
SHOW TABLES;

-- Verify initial data
SELECT COUNT(*) FROM roles; -- Should return 3
SELECT COUNT(*) FROM config; -- Should return 20
SELECT COUNT(*) FROM standard_chests; -- Should return 102
```

---

## 📝 SQL Dump Structure

The `config/databasemodel.sql` file contains:

### Created Tables (16 tables):

- `users` - System users
- `roles` - Roles/permissions (admin, user, bankers)
- `roles_users` - User-role relationships
- `members` - Clan members
- `collected_chests` - Collected chests
- `standard_chests` - Standard chest types (102 types)
- `config` - System configuration (20 parameters)
- `player_cycle_summaries` - Player cycle summaries
- `player_name_mappings` - Player name mappings
- `bank_accounts` - Member bank accounts
- `bank_transactions` - Bank transactions
- `bank_approval_logs` - Bank approval logs
- `errors` - Error log
- `events` - Events
- `incomplete_chests` - Incomplete chests
- `phinxlog` - Migration log (kept for compatibility)

### Initial Data Included:

- **roles**: 3 roles (admin, user, bankers)
- **config**: 20 initial configuration parameters
- **standard_chests**: 102 chest types with configured scores

---

## ⚠️ Troubleshooting

### Error: "Table already exists"

If you try to execute the dump on a database that already has tables:

```bash
# Option 1: Drop and recreate the database (WARNING: deletes all data!)
mysql -u root -p -e "DROP DATABASE chestcounter;"
mysql -u root -p -e "CREATE DATABASE chestcounter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u your_user -p chestcounter < config/databasemodel.sql

# Option 2: Execute only the missing parts of the dump
# (requires manual SQL editing - not recommended)
```

### Error: "Access denied"

Verify that the user has permissions:

```sql
-- Grant permissions (as root)
GRANT ALL PRIVILEGES ON chestcounter.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;
```

### Error: "Unknown database"

Make sure the database was created before executing the dump:

```sql
CREATE DATABASE chestcounter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Verify if the Dump was Executed Correctly

```sql
-- List all tables
SHOW TABLES;

-- Verify initial data count
SELECT COUNT(*) as total_roles FROM roles; -- Expected: 3
SELECT COUNT(*) as total_config FROM config; -- Expected: 20
SELECT COUNT(*) as total_chests FROM standard_chests; -- Expected: 102

-- Verify table structure
DESCRIBE users;
DESCRIBE roles;
```

---

## 🔄 Future Updates

To update the database in the future:

1. **Make a backup** before any changes:
   ```bash
   mysqldump -u user -p chestcounter > backup_$(date +%Y%m%d).sql
   ```

2. **Update the SQL dump** (`config/databasemodel.sql`) when making important structural changes

3. **For new installations**, always use the most recent SQL dump

---

## 🔐 Security

⚠️ **Important:**
- The SQL dump contains the complete structure, but **does not contain sensitive** user data
- After installation, create the first administrator using:
  ```bash
  php bin/cake.php create_admin
  ```
- Keep database credentials secure in `config/app_local.php`
- Never commit `config/app_local.php` to Git (already in `.gitignore`)

---

## 📚 References

- [MySQL Documentation](https://dev.mysql.com/doc/)
- [phpMyAdmin Documentation](https://www.phpmyadmin.net/docs/)
