<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ConfigTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ConfigTable Test Case
 */
class ConfigTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ConfigTable
     */
    protected $Config;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Config',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Config') ? [] : ['className' => ConfigTable::class];
        $this->Config = $this->getTableLocator()->get('Config', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Config);

        parent::tearDown();
    }

    /**
     * Test validation of collected_chests_retention_days
     *
     * @return void
     */
    public function testCollectedChestsRetentionDaysValidation(): void
    {
        // Valid cases: 0 (disabled), 30 (default), 8 (> 7)
        $validEntity = $this->Config->newEntity([
            'param' => 'collected_chests_retention_days',
            'value' => '30',
            'description' => 'Test description',
        ]);
        $this->assertEmpty($validEntity->getErrors());

        $zeroEntity = $this->Config->newEntity([
            'param' => 'collected_chests_retention_days',
            'value' => '0',
            'description' => 'Test description',
        ]);
        $this->assertEmpty($zeroEntity->getErrors());

        $eightEntity = $this->Config->newEntity([
            'param' => 'collected_chests_retention_days',
            'value' => '8',
            'description' => 'Test description',
        ]);
        $this->assertEmpty($eightEntity->getErrors());

        // Invalid cases: 7 (must be > 7), 1, -5, non-numeric
        $sevenEntity = $this->Config->newEntity([
            'param' => 'collected_chests_retention_days',
            'value' => '7',
            'description' => 'Test description',
        ]);
        $this->assertNotEmpty($sevenEntity->getErrors());
        $this->assertArrayHasKey('validRetentionDays', $sevenEntity->getErrors()['value']);

        $oneEntity = $this->Config->newEntity([
            'param' => 'collected_chests_retention_days',
            'value' => '1',
            'description' => 'Test description',
        ]);
        $this->assertNotEmpty($oneEntity->getErrors());

        $negativeEntity = $this->Config->newEntity([
            'param' => 'collected_chests_retention_days',
            'value' => '-5',
            'description' => 'Test description',
        ]);
        $this->assertNotEmpty($negativeEntity->getErrors());
    }
}
