<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\IncompleteChestsController Test Case
 *
 * @uses \App\Controller\IncompleteChestsController
 */
class IncompleteChestsControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use LocatorAwareTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.IncompleteChests',
        'app.Users',
        'app.Roles',
        'app.RolesUsers',
    ];

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->enableCsrfToken();
        $this->enableRetainFlashMessages();
    }

    /**
     * Sign in helper. User 1 holds role 1 (admin).
     */
    protected function signIn(int $id = 1): void
    {
        $this->session([
            'Auth' => [
                'id' => $id,
                'username' => 'tester',
                'email' => 'tester@example.com',
                'created' => new DateTime('2026-01-01 00:00:00'),
            ],
        ]);
    }

    /**
     * Test index page requires admin
     */
    public function testIndexRequiresAdmin(): void
    {
        $this->get('/incomplete-chests');
        $this->assertResponseCode(302); // Redirect to login when unauthenticated
    }

    /**
     * Test index page renders with bulk controls and table
     */
    public function testIndexRendersForAdmin(): void
    {
        $this->signIn(1);
        $this->get('/incomplete-chests');

        $this->assertResponseOk();
        $this->assertResponseContains('id="masterCheckbox"');
        $this->assertResponseContains('id="btnSubmitBulk"');
        $this->assertResponseContains('class="chest-select-box"');
        $this->assertResponseContains('Pending Chest 1');
        $this->assertResponseNotContains('table table-hover text-nowrap');
    }

    /**
     * Test bulk unresolved with selected IDs
     */
    public function testBulkUnresolvedSelected(): void
    {
        $this->signIn(1);

        $this->post('/incomplete-chests/bulk-unresolved', [
            'ids' => [1, 2],
            'status' => 'pending',
        ]);

        $this->assertRedirect(['action' => 'index', '?' => ['status' => 'pending']]);
        $this->assertFlashMessage('2 chest(s) marked as not recoverable.');

        $table = $this->fetchTable('IncompleteChests');
        $chest1 = $table->get(1);
        $chest2 = $table->get(2);

        $this->assertSame('unresolved', $chest1->status);
        $this->assertSame(1, $chest1->reviewed_by);
        $this->assertNotNull($chest1->reviewed_at);

        $this->assertSame('unresolved', $chest2->status);
        $this->assertSame(1, $chest2->reviewed_by);
        $this->assertNotNull($chest2->reviewed_at);
    }

    /**
     * Test bulk unresolved marking all pending
     */
    public function testBulkUnresolvedMarkAllPending(): void
    {
        $this->signIn(1);

        $this->post('/incomplete-chests/bulk-unresolved', [
            'mark_all_pending' => '1',
            'status' => 'pending',
        ]);

        $this->assertRedirect(['action' => 'index', '?' => ['status' => 'pending']]);
        $this->assertFlashMessage('2 pending chest(s) marked as not recoverable.');

        $table = $this->fetchTable('IncompleteChests');
        $pendingCount = $table->find()->where(['status' => 'pending'])->count();
        $this->assertSame(0, $pendingCount);

        // Corrected chest should remain corrected
        $chest3 = $table->get(3);
        $this->assertSame('corrected', $chest3->status);
    }

    /**
     * Test bulk unresolved with empty selection
     */
    public function testBulkUnresolvedEmptySelection(): void
    {
        $this->signIn(1);

        $this->post('/incomplete-chests/bulk-unresolved', [
            'ids' => [],
            'status' => 'pending',
        ]);

        $this->assertRedirect(['action' => 'index', '?' => ['status' => 'pending']]);
        $this->assertFlashMessage('No chests selected.');
    }
}
