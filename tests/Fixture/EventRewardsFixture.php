<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Starts empty: listing it makes the test suite clear the table between tests.
 * Each test builds the rows it is about.
 */
class EventRewardsFixture extends TestFixture
{
    /**
     * @var array<array<string, mixed>>
     */
    public array $records = [];
}
