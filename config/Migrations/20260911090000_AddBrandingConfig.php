<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add the brand_logo and brand_favicon parameters to the config table.
 *
 * Only the choice is stored. The artwork itself lives on disk under
 * webroot/img/branding, so these rows hold either the slug of a shipped preset
 * or the word "custom" for something an administrator uploaded.
 */
class AddBrandingConfig extends AbstractMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        $rows = [
            [
                'param' => 'brand_logo',
                'value' => 'dragon-crest',
                'description' => 'Logo shown in the navbar. The slug of one of the presets in '
                    . 'webroot/img/branding/presets, or "custom" for an uploaded image. '
                    . 'Change it under Admin > Branding.',
            ],
            [
                'param' => 'brand_favicon',
                'value' => 'dragon-crest',
                'description' => 'Icon shown in the browser tab. The slug of one of the presets in '
                    . 'webroot/img/branding/presets, or "custom" for an uploaded image. '
                    . 'Change it under Admin > Branding.',
            ],
        ];

        foreach ($rows as $row) {
            $existing = $this->fetchRow(sprintf(
                "SELECT id FROM config WHERE param = '%s'",
                $row['param']
            ));

            if ($existing) {
                continue;
            }

            $this->execute(sprintf(
                "INSERT INTO config (param, value, description) VALUES ('%s', '%s', '%s')",
                $row['param'],
                $row['value'],
                str_replace("'", "''", $row['description'])
            ));
        }
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        $this->execute("DELETE FROM config WHERE param IN ('brand_logo', 'brand_favicon')");
    }
}
