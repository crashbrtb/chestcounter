<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PlayerNameMappingsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PlayerNameMappingsTable Test Case
 */
class PlayerNameMappingsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PlayerNameMappingsTable
     */
    protected $PlayerNameMappings;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.PlayerNameMappings',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('PlayerNameMappings') ? [] : ['className' => PlayerNameMappingsTable::class];
        $this->PlayerNameMappings = $this->getTableLocator()->get('PlayerNameMappings', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->PlayerNameMappings);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\PlayerNameMappingsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\PlayerNameMappingsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
