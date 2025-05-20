<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CollectedChestsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CollectedChestsTable Test Case
 */
class CollectedChestsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CollectedChestsTable
     */
    protected $CollectedChests;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.CollectedChests',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('CollectedChests') ? [] : ['className' => CollectedChestsTable::class];
        $this->CollectedChests = $this->getTableLocator()->get('CollectedChests', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->CollectedChests);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\CollectedChestsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
