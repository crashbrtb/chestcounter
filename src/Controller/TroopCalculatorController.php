<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Troop;
use App\Service\Stacker\StackRequest;
use App\Service\Stacker\StackSolver;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * TroopCalculator Controller
 *
 * Plans a march: how many of each unit to bring so the stacks are lost in the
 * order the player chose, weakest first, spending the whole army limit.
 */
class TroopCalculatorController extends AppController
{
    /**
     * Session key holding the last submitted form, so a player does not have to
     * retype their bonuses on every visit.
     */
    private const SESSION_KEY = 'TroopCalculator.form';

    /**
     * Ready-made kill orders offered in the form.
     *
     * @var array<string, string>
     */
    public const ORDER_PRESETS = [
        'specialists_first' => 'Weakest first, specialists before guardsmen',
        'guardsmen_first' => 'Weakest first, guardsmen before specialists',
        'custom' => 'Custom order',
    ];

    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->Authentication->allowUnauthenticated(['index', 'reset']);
    }

    /**
     * beforeFilter callback.
     *
     * @param \Cake\Event\EventInterface $event An Event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $calculatorConfig = $this->fetchTable('Config')->find()
            ->where(['param' => 'calculator_function'])
            ->first();
        if ($calculatorConfig && (int)$calculatorConfig->value === 0) {
            throw new NotFoundException(__('Troop Calculator is currently disabled.'));
        }

        $this->viewBuilder()->setHelpers(['Html', 'Form', 'Paginator', 'Breadcrumbs']);
    }

    /**
     * Show the form and, once submitted, the planned march.
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        $troops = $this->fetchTable('Troops')->find('forCalculator')->toArray();

        $form = $this->request->getSession()->read(self::SESSION_KEY, []);
        $plan = null;

        if ($this->request->is(['post', 'put'])) {
            $form = (array)$this->request->getData();
            $this->request->getSession()->write(self::SESSION_KEY, $form);

            $request = StackRequest::fromArray($this->buildSolverPayload($form, $troops));
            $plan = (new StackSolver($troops))->solve($request);

            if ($plan->lines() === []) {
                $this->Flash->error(__('Nothing could be planned. Check the army limits and the selected levels.'));
            }
        }

        $this->set([
            // Scouts carry loot rather than fight, so they are never planned and
            // have no place in the "leave out" list either.
            'troops' => array_values(array_filter(
                $troops,
                static fn (Troop $troop): bool => $troop->category !== 'scout',
            )),
            'groupRows' => $this->groupRows($troops),
            'mercTiers' => $this->mercTiers($troops),
            'form' => $form,
            'plan' => $plan,
            'orderPresets' => self::ORDER_PRESETS,
        ]);
    }

    /**
     * Forget the remembered inputs.
     *
     * @return \Cake\Http\Response|null
     */
    public function reset()
    {
        $this->request->allowMethod(['post']);
        $this->request->getSession()->delete(self::SESSION_KEY);
        $this->Flash->success(__('The saved inputs were cleared.'));

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Translate the submitted form into the payload the solver expects.
     *
     * @param array<string, mixed> $form Submitted form.
     * @param list<\App\Model\Entity\Troop> $troops Unit catalogue.
     * @return array<string, mixed>
     */
    protected function buildSolverPayload(array $form, array $troops): array
    {
        $selected = array_values(array_filter((array)($form['groups'] ?? [])));

        return [
            'leadership_cap' => $this->number($form['leadership_cap'] ?? 0),
            'dominance_cap' => $this->number($form['dominance_cap'] ?? 0),
            'authority_cap' => $this->number($form['authority_cap'] ?? 0),
            'kill_order' => $this->killOrder($form, $selected, $troops),
            'category_order' => $this->categoryOrder($form),
            'excluded' => array_values(array_filter((array)($form['excluded'] ?? []))),
            'enemy_stack_count' => $this->number($form['enemy_stack_count'] ?? 4),
            'enforce_strike_order' => (bool)($form['enforce_strike_order'] ?? false),
            'section_gap' => (float)($form['section_gap'] ?? 0.95),
            'merc_tier' => $form['merc_tier'] ?? '',
            'bonuses' => [
                'health' => [
                    'class' => $this->numbers($form['health_class'] ?? []),
                    'category' => $this->numbers($form['health_category'] ?? []),
                    'type' => $this->numbers($form['health_type'] ?? []),
                ],
                'strength' => [
                    'class' => $this->numbers($form['strength_class'] ?? []),
                    'category' => $this->numbers($form['strength_category'] ?? []),
                    'type' => $this->numbers($form['strength_type'] ?? []),
                ],
                'strength_vs_epic_monsters' => (float)($form['strength_vs_epic_monsters'] ?? 0),
                'monster_bonus_includes_lowest_type' => (bool)($form['monster_bonus_includes_lowest_type'] ?? false),
            ],
        ];
    }

    /**
     * Work out the kill order from the chosen preset, restricted to the levels
     * the player selected.
     *
     * @param array<string, mixed> $form Submitted form.
     * @param list<string> $selected Group codes the player owns.
     * @param list<\App\Model\Entity\Troop> $troops Unit catalogue.
     * @return list<string>
     */
    protected function killOrder(array $form, array $selected, array $troops): array
    {
        $preset = (string)($form['order_preset'] ?? 'specialists_first');

        if ($preset === 'custom') {
            $custom = array_values(array_filter(array_map(
                'trim',
                explode(',', (string)($form['custom_order'] ?? '')),
            )));

            if ($custom !== []) {
                return $custom;
            }
        }

        if ($selected === []) {
            $selected = $this->availableGroups($troops);
        }

        $engineers = [];
        $levels = [];
        $monsters = [];

        foreach ($selected as $group) {
            $prefix = substr($group, 0, 1);
            $level = (int)substr($group, 1);

            if ($prefix === 'E') {
                $engineers[] = $group;
            } elseif ($prefix === 'M') {
                $monsters[] = $group;
            } else {
                $levels[$level][$prefix] = $group;
            }
        }

        // Sacrificial siege first, then each level from the bottom up, then the
        // monsters. Mercenaries are placed after everything by the solver.
        rsort($engineers);
        sort($monsters);
        ksort($levels);

        $pair = $preset === 'guardsmen_first' ? ['G', 'S'] : ['S', 'G'];

        $ordered = $engineers;
        foreach ($levels as $group) {
            foreach ($pair as $prefix) {
                if (isset($group[$prefix])) {
                    $ordered[] = $group[$prefix];
                }
            }
        }

        return array_merge($ordered, $monsters);
    }

    /**
     * Category order within a level, falling back to the default.
     *
     * @param array<string, mixed> $form Submitted form.
     * @return list<string>
     */
    protected function categoryOrder(array $form): array
    {
        $order = array_values(array_filter(array_map(
            'trim',
            (array)($form['category_order'] ?? []),
        )));

        return $order !== [] ? $order : StackRequest::DEFAULT_CATEGORY_ORDER;
    }

    /**
     * Every group code present in the catalogue, in a sensible display order.
     *
     * @param list<\App\Model\Entity\Troop> $troops Unit catalogue.
     * @return list<string>
     */
    protected function availableGroups(array $troops): array
    {
        $groups = [];
        foreach ($troops as $troop) {
            if (!$troop->is_mercenary && $troop->category !== 'scout') {
                $groups[$troop->group_code] = true;
            }
        }

        $codes = array_keys($groups);
        $weight = ['E' => 0, 'S' => 1, 'G' => 2, 'M' => 3];

        usort($codes, static function (string $a, string $b) use ($weight): int {
            $byClass = ($weight[$a[0]] ?? 9) <=> ($weight[$b[0]] ?? 9);

            return $byClass !== 0 ? $byClass : (int)substr($b, 1) <=> (int)substr($a, 1);
        });

        return $codes;
    }

    /**
     * The same group codes, split one row per class so the form can offer the
     * levels of each class on its own line.
     *
     * @param list<\App\Model\Entity\Troop> $troops Unit catalogue.
     * @return array<string, array{label: string, codes: list<string>}> Keyed by
     *   class prefix, in the order the rows should be shown. Classes absent from
     *   the catalogue are left out.
     */
    protected function groupRows(array $troops): array
    {
        $labels = [
            'G' => __('Guardsmen'),
            'S' => __('Specialists'),
            'M' => __('Monsters'),
            'E' => __('Engineer corps'),
        ];

        $rows = [];
        foreach ($this->availableGroups($troops) as $code) {
            $prefix = substr($code, 0, 1);
            if (!isset($labels[$prefix])) {
                continue;
            }
            $rows[$prefix][] = $code;
        }

        $ordered = [];
        foreach ($labels as $prefix => $label) {
            if (!empty($rows[$prefix])) {
                $ordered[$prefix] = ['label' => $label, 'codes' => $rows[$prefix]];
            }
        }

        return $ordered;
    }

    /**
     * The mercenary bands the catalogue holds, for the form's selector.
     *
     * @param list<\App\Model\Entity\Troop> $troops Unit catalogue.
     * @return array<string, string> Blank key first, meaning "work it out".
     */
    protected function mercTiers(array $troops): array
    {
        $bands = [];
        foreach ($troops as $troop) {
            if ($troop->is_mercenary && $troop->merc_tier !== null) {
                $bands[$troop->merc_tier] = true;
            }
        }

        $codes = array_keys($bands);
        sort($codes);

        $options = ['' => __('From my best guardsmen')];
        foreach ($codes as $code) {
            $options[(string)$code] = __('Tier {0}', $code);
        }

        return $options;
    }

    /**
     * Read a number that may have been typed with thousands separators.
     *
     * @param mixed $value Raw input.
     * @return float
     */
    protected function number(mixed $value): float
    {
        if (is_string($value)) {
            $value = str_replace([' ', ',', '.'], '', trim($value));
        }

        return (float)$value;
    }

    /**
     * Read a map of numbers.
     *
     * @param mixed $values Raw input.
     * @return array<string, float>
     */
    protected function numbers(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        $clean = [];
        foreach ($values as $key => $value) {
            $clean[(string)$key] = (float)$value;
        }

        return $clean;
    }
}
