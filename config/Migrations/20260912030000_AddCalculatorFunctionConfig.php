<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add the calculator_function parameter to the config table.
 *
 * Just like bank_function, setting this to 0 hides the Troop Calculator
 * menu item and disables the calculator tool for visitors and members.
 */
class AddCalculatorFunctionConfig extends AbstractMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        $row = $this->fetchRow("SELECT id FROM config WHERE param = 'calculator_function'");
        if ($row) {
            return;
        }

        $description = '1 = Troop Calculator active / 0 = no Troop Calculator';

        $this->execute(sprintf(
            "INSERT INTO config (param, value, description) VALUES ('calculator_function', '1', '%s')",
            str_replace("'", "''", $description)
        ));
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        $this->execute("DELETE FROM config WHERE param = 'calculator_function'");
    }
}
