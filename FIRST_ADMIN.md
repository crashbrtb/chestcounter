# How to Create the First Administrator

During the initial installation of the application, when no administrator user exists yet, you can use the console command to create the first administrator.

## Prerequisites

- Database configured and migrations executed
- Administrator role (ID: 1) must exist in the `roles` table

## Command Usage

### Interactive Mode (Recommended)

Run the command without parameters and follow the instructions:

```bash
php bin/cake.php create_admin
```

The command will prompt for:
- Administrator name
- Administrator email
- Administrator password

### Parameter Mode

You can also pass parameters directly:

```bash
php bin/cake.php create_admin --name "Admin Name" --email "admin@example.com" --password "password123"
```

Or using short options:

```bash
php bin/cake.php create_admin -n "Admin Name" -e "admin@example.com" -p "password123"
```

## Command Validations

The command performs the following validations:

1. **Checks if an administrator already exists**: If at least one user with `role_id = 1` already exists, the command informs that it's not necessary to create another and suggests using the web interface.

2. **Validates email**: Checks if the provided email is valid and not already registered.

3. **Validates password**: Password must be at least 6 characters long.

4. **Confirms creation**: Before creating, shows the data and requests confirmation.

## After Creating the Administrator

After creating the first administrator, you can:

1. Log in to the application using the created email and password
2. Access the administration area
3. Create new administrator users through the web interface (Users menu)

## Important Notes

- This command only works when **no administrator exists** in the system
- The password is automatically hashed before being saved to the database
- The created user will be automatically associated with `role_id = 1` (administrator)
- If you try to run the command when an admin already exists, it will only inform that it's not necessary

## Troubleshooting

### Error: "Administrator role (ID: 1) not found"

Run the database migrations:

```bash
php bin/cake.php migrations migrate
```

### Error: "This email is already registered"

The provided email is already in use. Use another email or log in with the existing user.

### Command does not appear in the list

Make sure that:
- The file is in `src/Command/CreateAdminCommand.php`
- The class is in the `App\Command` namespace
- The `console()` method is implemented in `Application.php`
