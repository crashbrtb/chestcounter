<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add collected_chests_retention_days parameter to config table migration.
 */
class AddCollectedChestsRetentionDaysConfig extends AbstractMigration
{
    /**
     * Up Method.
     *
     * Inserts the collected_chests_retention_days configuration parameter into the config table if missing.
     *
     * @return void
     */
    public function up(): void
    {
        $row = $this->fetchRow("SELECT id FROM config WHERE param = 'collected_chests_retention_days'");
        if (!$row) {
            $this->execute("INSERT INTO config (param, value, description) VALUES ('collected_chests_retention_days', '30', 'Retention time in days for old collected chests. Minimum retention time must be greater than 7 days, and default is 30 days. Setting to 0 disables automatic purge.')");
        }
    }

    /**
     * Down Method.
     *
     * Removes the collected_chests_retention_days parameter from the config table.
     *
     * @return void
     */
    public function down(): void
    {
        $this->execute("DELETE FROM config WHERE param = 'collected_chests_retention_days'");
    }
}
