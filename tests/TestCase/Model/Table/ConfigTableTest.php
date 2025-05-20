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
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ConfigTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
