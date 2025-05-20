<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\StandardChestsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\StandardChestsTable Test Case
 */
class StandardChestsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\StandardChestsTable
     */
    protected $StandardChests;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.StandardChests',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('StandardChests') ? [] : ['className' => StandardChestsTable::class];
        $this->StandardChests = $this->getTableLocator()->get('StandardChests', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->StandardChests);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\StandardChestsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
