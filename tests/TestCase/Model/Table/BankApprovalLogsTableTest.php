<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\BankApprovalLogsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\BankApprovalLogsTable Test Case
 */
class BankApprovalLogsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\BankApprovalLogsTable
     */
    protected $BankApprovalLogs;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.BankApprovalLogs',
        'app.BankTransactions',
        'app.AdminUsers',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('BankApprovalLogs') ? [] : ['className' => BankApprovalLogsTable::class];
        $this->BankApprovalLogs = $this->getTableLocator()->get('BankApprovalLogs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->BankApprovalLogs);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\BankApprovalLogsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\BankApprovalLogsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
