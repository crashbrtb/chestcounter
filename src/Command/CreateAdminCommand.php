<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * CreateAdmin command.
 * 
 * This command creates the first administrator user of the application.
 * Use this command during initial installation when no administrator exists yet.
 */
class CreateAdminCommand extends Command
{
    use LocatorAwareTrait;

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/5/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        
        $parser->setDescription('Creates the first administrator user of the application.')
            ->addOption('name', [
                'short' => 'n',
                'help' => 'Administrator name',
            ])
            ->addOption('email', [
                'short' => 'e',
                'help' => 'Administrator email',
            ])
            ->addOption('password', [
                'short' => 'p',
                'help' => 'Administrator password',
            ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null|void The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $rolesUsersTable = $this->fetchTable('RolesUsers');
        $usersTable = $this->fetchTable('Users');
        $rolesTable = $this->fetchTable('Roles');

        // Check if an administrator already exists
        $adminExists = $rolesUsersTable->exists([
            'role_id' => 1,
        ]);

        if ($adminExists) {
            $io->warning('At least one administrator already exists in the system.');
            $io->out('Use the web interface to create new administrator users.');
            return self::CODE_SUCCESS;
        }

        $io->out('<info>=== Create First Administrator ===</info>');
        $io->out('');

        // Get user data
        $name = $args->getOption('name');
        if (empty($name)) {
            $name = $io->ask('Administrator name:');
        }

        $email = $args->getOption('email');
        if (empty($email)) {
            $email = $io->ask('Administrator email:');
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Invalid email!');
            return self::CODE_ERROR;
        }

        // Check if email already exists
        if ($usersTable->exists(['email' => $email])) {
            $io->error('This email is already registered!');
            return self::CODE_ERROR;
        }

        $password = $args->getOption('password');
        if (empty($password)) {
            $password = $io->ask('Administrator password:');
        }

        if (empty($password) || strlen($password) < 6) {
            $io->error('Password must be at least 6 characters long!');
            return self::CODE_ERROR;
        }

        // Confirm creation
        $io->out('');
        $io->out("Name: $name");
        $io->out("Email: $email");
        $confirm = $io->askChoice('Do you want to create this administrator?', ['y', 'n'], 'y');

        if ($confirm !== 'y') {
            $io->info('Operation cancelled.');
            return self::CODE_SUCCESS;
        }

        // Create the user
        $user = $usersTable->newEmptyEntity();
        $user->name = $name;
        $user->email = $email;
        $user->password = $password; // Hash will be done automatically by _setPassword

        // Associate with role_id = 1 (admin)
        $adminRole = $rolesTable->get(1);
        if (!$adminRole) {
            $io->error('Administrator role (ID: 1) not found! Please check if the database was installed correctly using the SQL dump.');
            return self::CODE_ERROR;
        }

        $user->roles = [$adminRole];

        if ($usersTable->save($user)) {
            $io->success("Administrator created successfully!");
            $io->out("ID: {$user->id}");
            $io->out("Name: {$user->name}");
            $io->out("Email: {$user->email}");
            return self::CODE_SUCCESS;
        } else {
            $io->error('Error creating administrator:');
            foreach ($user->getErrors() as $field => $errors) {
                foreach ($errors as $error) {
                    $io->error("  - $field: $error");
                }
            }
            return self::CODE_ERROR;
        }
    }
}

