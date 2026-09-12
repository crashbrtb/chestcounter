<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\I18n\DateTime;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\TroopCalculatorController Test Case
 *
 * @uses \App\Controller\TroopCalculatorController
 */
class TroopCalculatorControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Troops',
        // The layout's theme helper reads the config table on every render.
        'app.Config',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->enableCsrfToken();
        $this->enableRetainFlashMessages();
        // The shared header renders the signed-in user's join date, so the
        // identity has to look like a real Users row.
        $this->session([
            'Auth' => [
                'id' => 1,
                'username' => 'tester',
                'email' => 'tester@example.com',
                'created' => new DateTime('2026-01-01 00:00:00'),
            ],
        ]);
    }

    /**
     * The form renders with every level from the catalogue offered.
     *
     * @return void
     * @uses \App\Controller\TroopCalculatorController::index()
     */
    public function testIndexRendersTheForm(): void
    {
        $this->get('/calculator');

        $this->assertResponseOk();
        $this->assertResponseContains('Army Limits');
        $this->assertResponseContains('Kill Order');
        // Levels present in the fixture, and no mercenary or scout group.
        $this->assertResponseContains('value="G9"');
        $this->assertResponseContains('value="S9"');
        $this->assertResponseContains('value="E9"');
        $this->assertResponseNotContains('value="MERC"');
    }

    /**
     * The levels are offered one class per row, in the order G, S, M, E, with
     * no class sharing a row with another.
     *
     * @return void
     * @uses \App\Controller\TroopCalculatorController::index()
     */
    public function testLevelsAreOfferedOneClassPerRow(): void
    {
        $this->get('/calculator');
        $this->assertResponseOk();

        $body = (string)$this->_response->getBody();
        $section = $this->levelsSection($body);

        // Each row opens with its class label, and the rows run G, S, M, E.
        $labels = [];
        foreach (['Guardsmen', 'Specialists', 'Monsters', 'Engineer corps'] as $label) {
            $position = strpos($section, $label);
            $this->assertNotFalse($position, sprintf('the %s row is missing', $label));
            $labels[$label] = $position;
        }
        $this->assertSame(
            ['Guardsmen', 'Specialists', 'Monsters', 'Engineer corps'],
            array_keys($labels),
            'the rows are not in G, S, M, E order',
        );
        $previous = -1;
        foreach ($labels as $label => $position) {
            $this->assertGreaterThan($previous, $position, sprintf('%s is out of order', $label));
            $previous = $position;
        }

        // Split the section on the labels: each slice may only hold its own
        // class prefix, which is what "one class per row" means.
        $expected = ['Guardsmen' => 'G', 'Specialists' => 'S', 'Monsters' => 'M', 'Engineer corps' => 'E'];
        $bounds = array_values($labels);
        $index = 0;
        foreach ($expected as $label => $prefix) {
            $start = $bounds[$index];
            $end = $bounds[$index + 1] ?? strlen($section);
            $slice = substr($section, $start, $end - $start);

            preg_match_all('/name="groups\[\]"[^>]*value="([GSME])\d"/', $slice, $matches);
            $this->assertNotEmpty($matches[1], sprintf('the %s row has no levels', $label));
            $this->assertSame(
                [$prefix],
                array_values(array_unique($matches[1])),
                sprintf('the %s row mixes in other classes', $label),
            );
            $index++;
        }
    }

    /**
     * The category order starts on melee, ranged, mounted, flying.
     *
     * @return void
     * @uses \App\Controller\TroopCalculatorController::index()
     */
    public function testCategoryOrderDefaultsToMeleeRangedMountedFlying(): void
    {
        $this->get('/calculator');
        $this->assertResponseOk();

        $body = (string)$this->_response->getBody();
        preg_match_all(
            '/<select name="category_order\[(\d)\]".*?<\/select>/s',
            $body,
            $selects,
            PREG_SET_ORDER,
        );

        $this->assertCount(4, $selects, 'there should be one select per category');

        $chosen = [];
        foreach ($selects as $select) {
            preg_match('/<option value="([a-z]+)" selected="selected">/', $select[0], $option);
            $chosen[(int)$select[1]] = $option[1] ?? null;
        }
        ksort($chosen);

        $this->assertSame(['melee', 'ranged', 'mounted', 'flying'], array_values($chosen));
    }

    /**
     * Every bonus input keeps the name the solver reads it back from.
     *
     * @return void
     * @uses \App\Controller\TroopCalculatorController::index()
     */
    public function testBonusInputsKeepTheirFieldNames(): void
    {
        $this->get('/calculator');
        $this->assertResponseOk();

        foreach (['class' => 'guardsman', 'category' => 'melee', 'type' => 'beast'] as $grid => $row) {
            foreach (['health', 'strength'] as $stat) {
                $this->assertResponseContains(
                    sprintf('name="%s_%s[%s]"', $stat, $grid, $row),
                    sprintf('the %s %s input lost its name', $grid, $stat),
                );
            }
        }
    }

    /**
     * The markup between the "Levels you are bringing" label and the order
     * preset that follows it.
     *
     * @param string $body Rendered page.
     * @return string
     */
    private function levelsSection(string $body): string
    {
        $start = strpos($body, 'Levels you are bringing');
        $this->assertNotFalse($start, 'the levels picker is missing');

        $end = strpos($body, 'Order within each level', $start);

        return $end === false ? substr($body, $start) : substr($body, $start, $end - $start);
    }

    /**
     * A submitted form comes back with a planned march.
     *
     * @return void
     * @uses \App\Controller\TroopCalculatorController::index()
     */
    public function testCalculateReturnsAPlan(): void
    {
        $this->post('/calculator', [
            'leadership_cap' => '200000',
            'dominance_cap' => '2000',
            'authority_cap' => '3000',
            'groups' => ['E9', 'S9', 'G9', 'M9'],
            'order_preset' => 'specialists_first',
            'enemy_stack_count' => 4,
            'enforce_strike_order' => 1,
            'section_gap' => '0.95',
            'health_class' => ['guardsman' => 1000, 'specialist' => 1000, 'monster' => 450, 'engineer' => 800],
            'strength_class' => ['guardsman' => 2000, 'specialist' => 2000, 'monster' => 900, 'engineer' => 1200],
            'strength_vs_epic_monsters' => 300,
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('Planned March');
        $this->assertResponseContains('Purifier II');
        $this->assertResponseContains('Kraken II');
        $this->assertResponseContains('Overlord');
        // Scouts never take part in the kill order.
        $this->assertResponseNotContains('Panoptic II');
    }

    /**
     * Units with art get their portrait beside the name.
     *
     * @return void
     * @uses \App\Controller\TroopCalculatorController::index()
     */
    public function testPlannedUnitsShowTheirPortrait(): void
    {
        $this->post('/calculator', [
            'leadership_cap' => '200000',
            'groups' => ['G9'],
            'order_preset' => 'specialists_first',
            'health_class' => ['guardsman' => 1000],
            'strength_class' => ['guardsman' => 2000],
        ]);

        $this->assertResponseOk();
        // Asset.timestamp appends a cache-busting query string, so match the
        // path rather than the whole attribute.
        $this->assertResponseContains('src="/img/troops/purifier-ii.png');
        $this->assertResponseContains('class="troop-icon"');
    }

    /**
     * A unit the catalogue has no art for falls back to a tier badge rather
     * than a blank space, so no information is lost.
     *
     * @return void
     * @uses \App\Controller\TroopCalculatorController::index()
     */
    public function testUnitsWithoutArtFallBackToATierBadge(): void
    {
        $this->post('/calculator', [
            'leadership_cap' => '100000',
            'groups' => ['E1'],
            'order_preset' => 'specialists_first',
            'health_class' => ['engineer' => 800],
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('Unportrayed Engine');
        $this->assertResponseNotContains('src="/img/troops/fixture-no-portrait.png"');
        $this->assertResponseContains('troop-icon-fallback troop-icon--siege');
        // The badge still names the class and level.
        $this->assertResponseContains('>E1</span>');
    }

    /**
     * Thousands separators typed into the army limits are accepted.
     *
     * @return void
     * @uses \App\Controller\TroopCalculatorController::index()
     */
    public function testArmyLimitsAcceptThousandsSeparators(): void
    {
        $this->post('/calculator', [
            'leadership_cap' => '200.000',
            'groups' => ['G9'],
            'order_preset' => 'specialists_first',
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('Planned March');
    }

    /**
     * With no army limits there is nothing to plan and the player is told so.
     *
     * @return void
     * @uses \App\Controller\TroopCalculatorController::index()
     */
    public function testEmptyLimitsReportBackToThePlayer(): void
    {
        $this->post('/calculator', [
            'leadership_cap' => '0',
            'groups' => ['G9'],
            'order_preset' => 'specialists_first',
        ]);

        $this->assertResponseOk();
        $this->assertFlashElement('flash/error');
        $this->assertResponseNotContains('Planned March');
    }

    /**
     * The inputs are remembered between visits.
     *
     * @return void
     * @uses \App\Controller\TroopCalculatorController::index()
     */
    public function testSubmittingRemembersTheInputs(): void
    {
        $this->post('/calculator', [
            'leadership_cap' => '123456',
            'groups' => ['G9'],
            'order_preset' => 'specialists_first',
        ]);

        $this->assertResponseOk();
        $this->assertSession('123456', 'TroopCalculator.form.leadership_cap');
    }

    /**
     * Remembered inputs come back on the form, and can be cleared.
     *
     * @return void
     * @uses \App\Controller\TroopCalculatorController::reset()
     */
    public function testRememberedInputsAreShownAndCanBeCleared(): void
    {
        $this->session([
            'Auth' => [
                'id' => 1,
                'username' => 'tester',
                'email' => 'tester@example.com',
                'created' => new DateTime('2026-01-01 00:00:00'),
            ],
            'TroopCalculator' => ['form' => ['leadership_cap' => '123456']],
        ]);

        $this->get('/calculator');
        $this->assertResponseOk();
        $this->assertResponseContains('123456');

        $this->post('/troop-calculator/reset');
        $this->assertRedirect(['action' => 'index']);
        $this->assertSessionNotHasKey('TroopCalculator.form');
    }

    /**
     * A custom order overrides the preset.
     *
     * @return void
     * @uses \App\Controller\TroopCalculatorController::index()
     */
    public function testCustomOrderIsHonoured(): void
    {
        $this->post('/calculator', [
            'leadership_cap' => '200000',
            'order_preset' => 'custom',
            'custom_order' => 'G9, S9',
            'health_class' => ['guardsman' => 1000, 'specialist' => 1000],
            'strength_class' => ['guardsman' => 2000, 'specialist' => 2000],
        ]);

        $this->assertResponseOk();
        $body = (string)$this->_response->getBody();

        $guardsman = strpos($body, 'Purifier II');
        $specialist = strpos($body, 'Duelist II');

        $this->assertNotFalse($guardsman);
        $this->assertNotFalse($specialist);
        $this->assertLessThan($specialist, $guardsman, 'G9 was asked to die before S9');
    }

    /**
     * Excluded units stay out of the plan.
     *
     * @return void
     * @uses \App\Controller\TroopCalculatorController::index()
     */
    public function testExcludedUnitsAreLeftOut(): void
    {
        $this->post('/calculator', [
            'leadership_cap' => '200000',
            'groups' => ['G9'],
            'order_preset' => 'specialists_first',
            'excluded' => ['corax-ii'],
            'health_class' => ['guardsman' => 1000],
            'strength_class' => ['guardsman' => 2000],
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('Purifier II');
        $this->assertResponseNotContains('>Corax II');
    }

    /**
     * Unauthenticated visitors can access the calculator without login.
     *
     * @return void
     * @uses \App\Controller\TroopCalculatorController::index()
     */
    public function testUnauthenticatedUserCanAccessCalculator(): void
    {
        // Clear session so the request is made by a guest visitor
        $this->session([]);
        $this->get('/calculator');

        $this->assertResponseOk();
        $this->assertResponseContains('Troop Calculator');
    }

    /**
     * When calculator_function config is set to 0, accessing the calculator returns 404.
     *
     * @return void
     * @uses \App\Controller\TroopCalculatorController::index()
     */
    public function testDisabledCalculatorReturnsNotFound(): void
    {
        $configTable = $this->fetchTable('Config');
        $row = $configTable->find()->where(['param' => 'calculator_function'])->first();
        if (!$row) {
            $row = $configTable->newEntity([
                'param' => 'calculator_function',
                'value' => '0',
                'description' => 'Troop calculator toggle',
            ]);
        } else {
            $row->value = '0';
        }
        $configTable->saveOrFail($row);

        $this->get('/calculator');
        $this->assertResponseCode(404);
    }
}
