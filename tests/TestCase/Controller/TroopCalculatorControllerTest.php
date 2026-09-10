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
        $this->assertResponseNotContains('Trailseeker VII');
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
}
