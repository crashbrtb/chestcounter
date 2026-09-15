<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Table\ApiTokensTable;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @uses \App\Controller\ApiTokensController
 * @uses \App\Controller\MembersController::toggleAdministrative()
 */
class ApiTokensControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use LocatorAwareTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Roles',
        'app.RolesUsers',
        'app.Config',
        'app.Members',
        'app.ApiTokens',
        'app.BankTransactions',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->enableCsrfToken();
        $this->enableRetainFlashMessages();
    }

    private function loginAdmin(): void
    {
        $this->session(['Auth' => ['id' => 1, 'email' => 'admin@example.com', 'created' => new DateTime('2026-01-01')]]);
    }

    public function testOnlyAdministratorsManageTokens(): void
    {
        $this->get('/api-tokens');
        $this->assertRedirectContains('/users/login');

        $this->session(['Auth' => ['id' => 2, 'email' => 'member@example.com']]);
        $this->get('/api-tokens');
        $this->assertResponseCode(403);
    }

    public function testATokenIsShownOnceAndOnlyItsHashIsStored(): void
    {
        $this->loginAdmin();
        $this->post('/api-tokens/add', ['name' => 'Clan PC']);
        $this->assertRedirect(['controller' => 'ApiTokens', 'action' => 'index']);

        $token = $this->fetchTable('ApiTokens')->find()->firstOrFail();
        $this->assertSame(1, $token->user_id);
        $this->assertSame(64, strlen($token->token_hash));

        $this->loginAdmin();
        // The redirect's next request carries the session the POST left behind.
        $this->assertSession('Clan PC', 'ApiTokens.issued.name');
        $this->session(['ApiTokens' => ['issued' => $_SESSION['ApiTokens']['issued']]]);
        $this->get('/api-tokens');
        $this->assertResponseOk();
        $body = (string)$this->_response->getBody();
        $this->assertMatchesRegularExpression('/value="(cct_[0-9a-f]{48})"/', $body);
        preg_match('/value="(cct_[0-9a-f]{48})"/', $body, $match);
        $this->assertSame($token->token_hash, ApiTokensTable::hash($match[1]));

        // Not stored in the table in the clear, and gone from the session once shown.
        $this->assertStringNotContainsString($match[1], json_encode($token->toArray()));
        $this->assertSession(null, 'ApiTokens.issued');
    }

    public function testRevokingATokenStopsIt(): void
    {
        [$token, $plain] = $this->fetchTable('ApiTokens')->issue(1, 'Old PC');

        $this->loginAdmin();
        $this->post("/api-tokens/revoke/{$token->id}");
        $this->assertRedirect(['controller' => 'ApiTokens', 'action' => 'index']);

        $this->assertNotNull($this->fetchTable('ApiTokens')->get($token->id)->revoked_at);
        $this->assertNull($this->fetchTable('ApiTokens')->findActiveByPlainToken($plain));
    }

    public function testMembersCanBeMarkedAsAdministrativeAccounts(): void
    {
        $member = $this->fetchTable('Members')->find()->firstOrFail();
        $this->assertFalse((bool)$member->administrative_account);

        $this->loginAdmin();
        $this->post("/members/toggle-administrative/{$member->id}");
        $this->assertRedirect();
        $this->assertTrue($this->fetchTable('Members')->get($member->id)->administrative_account);

        $this->loginAdmin();
        $this->get('/members');
        $this->assertResponseOk();
        $this->assertResponseContains('Administrative');
    }
}
