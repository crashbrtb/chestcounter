<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add the site_theme parameter to the config table.
 *
 * The value is the slug of one of the themes declared in ThemeService, which is
 * what ends up in the `data-theme` attribute on the page. The palettes
 * themselves live in webroot/css/themes.css.
 */
class AddSiteThemeConfig extends AbstractMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        $row = $this->fetchRow("SELECT id FROM config WHERE param = 'site_theme'");
        if ($row) {
            return;
        }

        $description = 'Colour scheme for the whole site: morning-light, deepest-dark, '
            . 'wildflowers or twilight. Change it under Admin > Theme.';

        $this->execute(sprintf(
            "INSERT INTO config (param, value, description) VALUES ('site_theme', 'morning-light', '%s')",
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
        $this->execute("DELETE FROM config WHERE param = 'site_theme'");
    }
}
