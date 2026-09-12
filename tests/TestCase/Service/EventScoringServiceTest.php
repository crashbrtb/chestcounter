<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Entity\Event;
use App\Service\EventScoringService;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Service\EventScoringService Test Case
 *
 * The chest data is built here rather than in a fixture file because each test
 * is about a different way of counting the same chests, and the numbers only
 * mean something when the rows they come from are visible next to them.
 *
 * @uses \App\Service\EventScoringService
 */
class EventScoringServiceTest extends TestCase
{
    use LocatorAwareTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.CollectedChests',
        'app.StandardChests',
    ];

    /**
     * @var \App\Service\EventScoringService
     */
    protected EventScoringService $service;

    /**
     * The window every event in this test case runs over.
     *
     * @var \Cake\I18n\DateTime
     */
    protected DateTime $windowStart;

    /**
     * @var \Cake\I18n\DateTime
     */
    protected DateTime $windowEnd;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new EventScoringService();
        $this->windowStart = new DateTime('2026-03-01 00:00:00', 'UTC');
        $this->windowEnd = new DateTime('2026-03-31 23:59:59', 'UTC');

        $chests = $this->fetchTable('StandardChests');
        $chests->deleteAll([]);
        $chests->saveManyOrFail($chests->newEntities([
            ['source' => 'Gold Crypt', 'score' => 100, 'monster' => 0],
            ['source' => 'Silver Crypt', 'score' => 10, 'monster' => 0],
            ['source' => 'Epic Hydra squad', 'score' => 500, 'monster' => 1],
            ['source' => 'Wooden Box', 'score' => 0, 'monster' => 0],
        ]));

        $collected = $this->fetchTable('CollectedChests');
        $collected->deleteAll([]);

        // Ana: 2 gold (200) + 1 epic (500) + 1 worthless box = 4 chests, 700 points.
        // Bruno: 3 silver (30) + 2 epic (1000)               = 5 chests, 1030 points.
        // Carla: 1 gold (100), but outside the window        = not counted at all.
        $rows = [];
        $add = function (string $player, string $source, string $when) use (&$rows): void {
            $rows[] = [
                'name' => $source,
                'player' => $player,
                'source' => $source,
                'type' => 0,
                'collected_at' => new DateTime($when, 'UTC'),
            ];
        };

        $add('Ana', 'Gold Crypt', '2026-03-02 10:00:00');
        $add('Ana', 'Gold Crypt', '2026-03-05 10:00:00');
        $add('Ana', 'Epic Hydra squad', '2026-03-07 10:00:00');
        $add('Ana', 'Wooden Box', '2026-03-08 10:00:00');
        $add('Bruno', 'Silver Crypt', '2026-03-03 10:00:00');
        $add('Bruno', 'Silver Crypt', '2026-03-04 10:00:00');
        $add('Bruno', 'Silver Crypt', '2026-03-06 10:00:00');
        $add('Bruno', 'Epic Hydra squad', '2026-03-09 10:00:00');
        $add('Bruno', 'Epic Hydra squad', '2026-03-10 10:00:00');
        $add('Carla', 'Gold Crypt', '2026-02-27 10:00:00');

        $collected->saveManyOrFail($collected->newEntities($rows));
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->service);
        parent::tearDown();
    }

    /**
     * Build an unsaved event over the shared window.
     *
     * @param string $criteria One of the Event::CRITERIA_* values.
     * @param array<string, mixed> $extra Fields to override.
     * @return \App\Model\Entity\Event
     */
    protected function makeEvent(string $criteria, array $extra = []): Event
    {
        $events = $this->fetchTable('Events');
        $event = $events->newEntity([
            'name' => 'Test Event',
            'criteria' => $criteria,
            'custom_metric' => Event::METRIC_SCORE,
            'starts_at' => $this->windowStart,
            'ends_at' => $this->windowEnd,
            'prize' => 'A prize',
            'contact_player' => 'Someone',
        ] + $extra);

        // Saving would trip the "start must be in the future" rule, and nothing
        // under test needs the row to exist.
        $event->set('id', 1);
        $event->set('starts_at', $this->windowStart);
        $event->set('ends_at', $this->windowEnd);
        foreach ($extra as $field => $value) {
            $event->set($field, $value);
        }

        return $event;
    }

    /**
     * Scoring by chest points: each chest is worth what its type is worth.
     *
     * @return void
     */
    public function testChestScoreCriteria(): void
    {
        $results = $this->service->standings($this->makeEvent(Event::CRITERIA_CHEST_SCORE));

        $this->assertSame(2, $results['participants'], 'Only players inside the window count.');
        $this->assertSame(1730, $results['total_points']);
        $this->assertSame(9, $results['total_chests']);

        $this->assertSame('Bruno', $results['rows'][0]['player']);
        $this->assertSame(1030, $results['rows'][0]['points']);
        $this->assertSame(1, $results['rows'][0]['position']);

        $this->assertSame('Ana', $results['rows'][1]['player']);
        $this->assertSame(700, $results['rows'][1]['points']);
    }

    /**
     * Scoring by chest count: a worthless chest still counts as one chest.
     *
     * @return void
     */
    public function testChestCountCriteria(): void
    {
        $results = $this->service->standings($this->makeEvent(Event::CRITERIA_CHEST_COUNT));

        $this->assertSame('Bruno', $results['rows'][0]['player']);
        $this->assertSame(5, $results['rows'][0]['points']);
        $this->assertSame('Ana', $results['rows'][1]['player']);
        $this->assertSame(4, $results['rows'][1]['points'], 'The zero-score box still counts as a chest.');
        $this->assertSame(9, $results['total_points']);
    }

    /**
     * Scoring by epic monster chests: nothing else is counted at all.
     *
     * @return void
     */
    public function testEpicMonsterCriteria(): void
    {
        $results = $this->service->standings($this->makeEvent(Event::CRITERIA_EPIC_MONSTER));

        $this->assertSame(2, $results['participants']);
        $this->assertSame('Bruno', $results['rows'][0]['player']);
        $this->assertSame(2, $results['rows'][0]['points']);
        $this->assertSame(2, $results['rows'][0]['chest_count'], 'Non-monster chests are excluded from the count.');
        $this->assertSame('Ana', $results['rows'][1]['player']);
        $this->assertSame(1, $results['rows'][1]['points']);
    }

    /**
     * Custom chests: only the picked types count, and the metric decides whether
     * that means their score or how many of them there were.
     *
     * @return void
     */
    public function testCustomChestsCriteria(): void
    {
        $eventChests = $this->fetchTable('EventChests');
        $chestRows = [
            $eventChests->newEntity(['event_id' => 1, 'standard_chest_id' => 1, 'source' => 'Gold Crypt']),
        ];

        $byScore = $this->makeEvent(Event::CRITERIA_CUSTOM_CHESTS);
        $byScore->set('event_chests', $chestRows);

        $results = $this->service->standings($byScore);
        $this->assertSame(1, $results['participants'], 'Bruno collected none of the picked chests.');
        $this->assertSame('Ana', $results['rows'][0]['player']);
        $this->assertSame(200, $results['rows'][0]['points']);
        $this->assertSame(100.0, $results['rows'][0]['participation']);

        $byCount = $this->makeEvent(Event::CRITERIA_CUSTOM_CHESTS);
        $byCount->set('custom_metric', Event::METRIC_COUNT);
        $byCount->set('event_chests', $chestRows);

        $results = $this->service->standings($byCount);
        $this->assertSame(2, $results['rows'][0]['points'], 'Counting mode ranks by how many were collected.');
    }

    /**
     * Participation is each player's share of the event total, and the shares
     * add up to the whole.
     *
     * @return void
     */
    public function testParticipationSharesAddUp(): void
    {
        $results = $this->service->standings($this->makeEvent(Event::CRITERIA_CHEST_SCORE));

        $total = 0.0;
        foreach ($results['rows'] as $row) {
            $total += $row['participation'];
        }

        $this->assertEqualsWithDelta(100.0, $total, 0.05);
    }

    /**
     * An event nobody played in reports zeroes rather than failing.
     *
     * @return void
     */
    public function testEmptyEventReportsZeroes(): void
    {
        $event = $this->makeEvent(Event::CRITERIA_CHEST_SCORE);
        $event->set('starts_at', new DateTime('2026-06-01 00:00:00', 'UTC'));
        $event->set('ends_at', new DateTime('2026-06-30 00:00:00', 'UTC'));

        $results = $this->service->standings($event);

        $this->assertSame(0, $results['participants']);
        $this->assertSame(0, $results['total_points']);
        $this->assertNull($results['leader']);
        $this->assertSame(0.0, $results['average_points']);
    }

    /**
     * A custom event with nothing picked counts nothing, rather than silently
     * counting every chest.
     *
     * @return void
     */
    public function testCustomEventWithoutChestsCountsNothing(): void
    {
        $event = $this->makeEvent(Event::CRITERIA_CUSTOM_CHESTS);
        $event->set('event_chests', []);

        $results = $this->service->standings($event);

        $this->assertSame(0, $results['participants']);
    }

    /**
     * Closing an event writes the standings, and reading them back afterwards
     * gives the recorded result instead of recomputing it.
     *
     * @return void
     */
    public function testFinalizeStoresStandings(): void
    {
        $events = $this->fetchTable('Events');
        $event = $this->makeEvent(Event::CRITERIA_CHEST_SCORE);
        $event->unset('id');
        $event->set('starts_at', DateTime::now()->addHours(1));
        $event->set('ends_at', DateTime::now()->addDays(2));
        $events->saveOrFail($event);

        // Move the window onto the chest data now that the rules have had their say.
        $event->set('starts_at', $this->windowStart);
        $event->set('ends_at', $this->windowEnd);
        $events->saveOrFail($event, ['checkRules' => false]);

        $recorded = $this->service->finalize($event);
        $this->assertSame(2, $recorded);

        $stored = $this->fetchTable('EventStandings')->find()
            ->where(['event_id' => $event->id])
            ->orderBy(['position' => 'ASC'])
            ->all()
            ->toList();

        $this->assertCount(2, $stored);
        $this->assertSame('Bruno', $stored[0]->player);
        $this->assertSame(1030, (int)$stored[0]->points);

        // The window is in the past, so this reads the snapshot rather than the
        // chests it was built from.
        $event->set('finalized_at', DateTime::now());
        $results = $this->service->resultsFor($event);
        $this->assertSame('snapshot', $results['source']);
        $this->assertSame(1030, $results['rows'][0]['points']);
    }

    /**
     * Closing an event twice replaces the previous result rather than adding to it.
     *
     * @return void
     */
    public function testFinalizeReplacesPreviousStandings(): void
    {
        $events = $this->fetchTable('Events');
        $event = $this->makeEvent(Event::CRITERIA_CHEST_SCORE);
        $event->unset('id');
        $event->set('starts_at', DateTime::now()->addHours(1));
        $event->set('ends_at', DateTime::now()->addDays(2));
        $events->saveOrFail($event);

        $event->set('starts_at', $this->windowStart);
        $event->set('ends_at', $this->windowEnd);
        $events->saveOrFail($event, ['checkRules' => false]);

        $this->service->finalize($event);
        $this->service->finalize($event);

        $this->assertSame(
            2,
            $this->fetchTable('EventStandings')->find()->where(['event_id' => $event->id])->count()
        );
    }
}
