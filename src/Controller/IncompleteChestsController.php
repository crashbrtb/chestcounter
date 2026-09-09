<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\IncompleteChest;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;

/**
 * IncompleteChests Controller
 *
 * The review queue for chests the collector opened but could not read. Each row
 * carries a picture of the chest as it was on screen, which is the only thing
 * left of it: the chest itself was consumed in the game.
 *
 * From here a chest is either finished by hand - which creates the
 * collected_chests row it should have become - or marked as examined and not
 * recoverable. Either way the row stays, so the same chest is never reviewed
 * twice and the record of what went wrong survives.
 *
 * @property \App\Model\Table\IncompleteChestsTable $IncompleteChests
 */
class IncompleteChestsController extends AppController
{
    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('CakeLte/layout/default');
    }

    /**
     * @param \Cake\Event\EventInterface $event The beforeFilter event.
     * @return void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
    }

    /**
     * Index method - the queue, pending first.
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->requireAdmin();

        $status = $this->request->getQuery('status', IncompleteChest::STATUS_PENDING);
        $query = $this->IncompleteChests->find('withoutImage');

        if ($status !== 'all') {
            $query = $query->where(['status' => $status]);
        }

        $query = $query->orderBy(['collected_at' => 'DESC']);

        $incompleteChests = $this->paginate($query, ['limit' => 25]);
        $counts = [
            IncompleteChest::STATUS_PENDING => $this->IncompleteChests->find()
                ->where(['status' => IncompleteChest::STATUS_PENDING])->count(),
            IncompleteChest::STATUS_CORRECTED => $this->IncompleteChests->find()
                ->where(['status' => IncompleteChest::STATUS_CORRECTED])->count(),
            IncompleteChest::STATUS_UNRESOLVED => $this->IncompleteChests->find()
                ->where(['status' => IncompleteChest::STATUS_UNRESOLVED])->count(),
        ];

        $this->set(compact('incompleteChests', 'status', 'counts'));
    }

    /**
     * View method - the picture, the fields as read, and what to do about it.
     *
     * @param string|null $id Incomplete Chest id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->requireAdmin();

        // Everything except the image, plus its size. The page shows the picture
        // through the `image` action, so pulling a hundred kilobytes of blob
        // into this request only to ask whether it exists would be waste.
        $incompleteChest = $this->IncompleteChests->find('withoutImage')
            ->select(['screenshot_bytes' => 'OCTET_LENGTH(screenshot)'])
            ->where(['IncompleteChests.id' => $id])
            ->firstOrFail();

        // Offered as suggestions in the form: the names already in use, so a
        // correction lands on an existing spelling instead of inventing a
        // variant that will need merging later.
        $collectedChests = TableRegistry::getTableLocator()->get('CollectedChests');
        $knownPlayers = $collectedChests->find()
            ->select(['player'])->distinct(['player'])->orderBy(['player' => 'ASC'])
            ->all()->extract('player')->toList();
        $knownChests = $collectedChests->find()
            ->select(['name'])->distinct(['name'])->orderBy(['name' => 'ASC'])
            ->all()->extract('name')->toList();
        $knownSources = TableRegistry::getTableLocator()->get('StandardChests')->find()
            ->select(['source'])->distinct(['source'])->orderBy(['source' => 'ASC'])
            ->all()->extract('source')->toList();

        $this->set(compact('incompleteChest', 'knownPlayers', 'knownChests', 'knownSources'));
    }

    /**
     * Image method - serves the stored PNG.
     *
     * Its own action rather than a data URI in the page: the images are hundreds
     * of kilobytes, and this way the browser caches them and a listing never has
     * to carry them.
     *
     * @param string|null $id Incomplete Chest id.
     * @return \Cake\Http\Response
     */
    public function image($id = null)
    {
        $this->requireAdmin();

        $incompleteChest = $this->IncompleteChests->get($id, fields: ['id', 'screenshot']);
        if (empty($incompleteChest->screenshot)) {
            throw new NotFoundException(__('This record has no screenshot.'));
        }

        $data = $incompleteChest->screenshot;
        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }

        return $this->response
            ->withType('png')
            ->withHeader('Cache-Control', 'private, max-age=3600')
            ->withStringBody($data);
    }

    /**
     * Correct method - what the chest should have been.
     *
     * Writes the collected_chests row this chest should have produced, keeping
     * the ORIGINAL collection time: scoring runs in cycles, and a chest
     * recovered today still belongs to the day it was collected.
     *
     * @param string|null $id Incomplete Chest id.
     * @return \Cake\Http\Response|null Redirects on success.
     */
    public function correct($id = null)
    {
        $this->requireAdmin();
        $this->request->allowMethod(['post', 'put']);

        $incompleteChest = $this->IncompleteChests->get($id);

        if (!$incompleteChest->is_pending) {
            $this->Flash->error(__('This chest has already been reviewed.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        $data = $this->request->getData();
        $candidate = $this->IncompleteChests->newEntity([
            'name' => trim((string)($data['name'] ?? '')),
            'player' => trim((string)($data['player'] ?? '')),
            'source' => trim((string)($data['source'] ?? '')),
        ], ['validate' => 'correction']);

        if ($candidate->getErrors()) {
            $this->Flash->error(__('Fill in the chest name, the player and the source.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        $collectedChests = TableRegistry::getTableLocator()->get('CollectedChests');
        $chest = $collectedChests->newEntity([
            'name' => $candidate->name,
            'player' => $candidate->player,
            'source' => $candidate->source,
            // `type` is documented in the schema as 0 = auto, 1 = Manual, and
            // until now every one of the 275 000 rows was 0: nothing had ever
            // been entered by hand. A chest recovered from a screenshot is
            // exactly the case the column was made for, and marking it keeps
            // the two kinds of row tellable apart afterwards.
            'type' => 1,
            'collected_at' => $incompleteChest->collected_at,
        ]);

        if (!$collectedChests->save($chest)) {
            $this->Flash->error(__('The chest could not be saved into collected chests.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        $incompleteChest = $this->IncompleteChests->patchEntity($incompleteChest, [
            'name' => $candidate->name,
            'player' => $candidate->player,
            'source' => $candidate->source,
            'status' => IncompleteChest::STATUS_CORRECTED,
            'collected_chest_id' => $chest->id,
            'review_notes' => trim((string)($data['review_notes'] ?? '')) ?: null,
            'reviewed_by' => $this->currentUserId(),
            'reviewed_at' => new DateTime(),
        ]);

        if ($this->IncompleteChests->save($incompleteChest)) {
            $this->Flash->success(__(
                'Chest corrected and recorded for "{0}". Collected chest #{1}.',
                $candidate->player,
                $chest->id
            ));
        } else {
            // The chest is already in collected_chests; say so plainly rather
            // than letting it look as though nothing happened.
            $this->Flash->error(__(
                'The chest was recorded as #{0}, but this row could not be marked as corrected.',
                $chest->id
            ));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Unresolved method - examined, and nothing could be recovered from it.
     *
     * @param string|null $id Incomplete Chest id.
     * @return \Cake\Http\Response|null Redirects.
     */
    public function unresolved($id = null)
    {
        $this->requireAdmin();
        $this->request->allowMethod(['post', 'put']);

        $incompleteChest = $this->IncompleteChests->get($id);

        if (!$incompleteChest->is_pending) {
            $this->Flash->error(__('This chest has already been reviewed.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        $incompleteChest = $this->IncompleteChests->patchEntity($incompleteChest, [
            'status' => IncompleteChest::STATUS_UNRESOLVED,
            'review_notes' => trim((string)$this->request->getData('review_notes')) ?: null,
            'reviewed_by' => $this->currentUserId(),
            'reviewed_at' => new DateTime(),
        ]);

        if ($this->IncompleteChests->save($incompleteChest)) {
            $this->Flash->success(__('Marked as reviewed and not recoverable.'));
        } else {
            $this->Flash->error(__('The record could not be updated. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Reopen method - puts a reviewed chest back in the queue.
     *
     * For a decision made in error. A chest that was corrected keeps its
     * collected_chests row: undoing that is a deletion, and deletions belong in
     * the collected chests screen where they can be seen.
     *
     * @param string|null $id Incomplete Chest id.
     * @return \Cake\Http\Response|null Redirects.
     */
    public function reopen($id = null)
    {
        $this->requireAdmin();
        $this->request->allowMethod(['post', 'put']);

        $incompleteChest = $this->IncompleteChests->get($id);
        $incompleteChest = $this->IncompleteChests->patchEntity($incompleteChest, [
            'status' => IncompleteChest::STATUS_PENDING,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        if ($this->IncompleteChests->save($incompleteChest)) {
            $this->Flash->success(__('Back in the queue.'));
        } else {
            $this->Flash->error(__('The record could not be updated. Please, try again.'));
        }

        return $this->redirect(['action' => 'view', $id]);
    }
}
