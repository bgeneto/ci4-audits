<?php

namespace Bgeneto\Audits\Traits;

use Bgeneto\Audits\Models\AuditModel;
use RuntimeException;

/**
 * AuditsTrait
 */
trait AuditsTrait
{
    /**
     * @var array List of allowed callbacks that can be used to trigger audits
     */
    private array $allowedCallbacks = [
        'afterInsert',
        'afterUpdate',
        'afterDelete',
        'afterInsertBatch',
        'afterUpdateBatch',
    ];

    /**
     * Takes an array of model $returnTypes
     * and returns an array of Audits,
     * arranged by object and event.
     * Optionally filter by $events
     * (string or array of strings).
     *
     * @param array|string|null $events
     *
     * @internal Due to a typo this function has never worked in a released version.
     *           It will be refactored soon without announcing a new major release
     *           so do not build on the signature or functionality.
     */
    public function getAudits(array $objects, $events = null): array
    {
        if ($objects === []) {
            return [];
        }

        // Get the primary keys from the objects
        $objectIds = \array_column($objects, $this->primaryKey);

        // Start the query
        $query = \model(AuditModel::class)->builder()->where('source', $this->table)->whereIn('source_id', $objectIds);

        if (\is_string($events)) {
            $query = $query->where('event', $events);
        } elseif (\is_array($events)) {
            $query = $query->whereIn('event', $events);
        }

        // Index by objectId, event
        $array = [];

        // @phpstan-ignore-next-line
        while ($audit = $query->getUnbufferedRow()) {
            if (empty($array[$audit->{$this->primaryKey}])) {
                $array[$audit->{$this->primaryKey}] = [];
            }
            if (empty($array[$audit->{$this->primaryKey}][$audit->event])) {
                $array[$audit->{$this->primaryKey}][$audit->event] = [];
            }

            $array[$audit->{$this->primaryKey}][$audit->event][] = $audit;
        }

        return $array;
    }

    // record successful insert events
    protected function auditInsertCallback(array $data)
    {
        if (! $data['result']) {
            return false;
        }
        $fieldNames = \implode(', ', \array_keys($data['data']));
        $audit      = [
            'source'    => $this->table,
            'source_id' => $this->db->insertID(), // @phpstan-ignore-line
            'event'     => 'insert',
            'summary'   => \count($data['data']) . ' fields: ' . $fieldNames,
            'data'      => null,
        ];
        \service('audits')->add($audit);

        return $data;
    }

    // record successful update events
    protected function auditUpdateCallback(array $data)
    {
        // TODO: check how to get the updated ids in the case that update() method is
        //       called without args (maybe using where and set).
        if (empty($data['id'])) {
            return false;
        }

        $fieldNames = \implode(', ', \array_keys($data['data']));

        foreach ($data['id'] as $sourceId) {
            $audit = [
                'source'    => $this->table,
                'source_id' => $sourceId,
                'event'     => 'update',
                'summary'   => \count($data['data']) . ' fields: ' . $fieldNames,
                'data'      => null,
            ];
            \service('audits')->add($audit);
        }

        return $data;
    }

    // record successful delete events
    protected function auditDeleteCallback(array $data)
    {
        if (! $data['result']) {
            return false;
        }
        if (empty($data['id'])) {
            return false;
        }

        $audit = [
            'source'  => $this->table,
            'event'   => 'delete',
            'summary' => ($data['purge']) ? 'purge' : 'soft',
            'data'    => null,
        ];

        // add an entry for each ID
        $audits = \service('audits');

        foreach ($data['id'] as $id) {
            $audit['source_id'] = $id;
            $audits->add($audit);
        }

        return $data;
    }

    /**
     * Enables the specified callbacks.
     *
     * @param array $callbacks The callbacks to enable.
     */
    public function setAuditsCallbacks(array $callbacks): void
    {
        // Checks if the callback is allowed and trigger exception if not
        foreach ($callbacks as $callback) {
            if (! \in_array($callback, $this->allowedCallbacks, true)) {
                throw new RuntimeException('Invalid (or not allowed) callback: ' . $callback);
            }
            $this->addCallback($callback);
        }
    }

    /**
     * Adds a callback to the model.
     *
     * @param string       $callback The name of the callback to add.
     * @param array|string $values   The values to merge with the existing callbacks.
     *
     * @throws RuntimeException If the specified callback is not allowed.
     */
    private function addCallback(string $callback): void
    {
        // check if words 'insert', 'update' or 'delete' presence in $callback
        $auditsCallback = match (true) {
            \str_contains($callback, 'Insert') => 'auditInsertCallback',
            \str_contains($callback, 'Update') => 'auditUpdateCallback',
            \str_contains($callback, 'Delete') => 'auditDeleteCallback',
            default                            => throw new RuntimeException('Invalid callback: ' . $callback),
        };

        $this->{$callback} = \array_merge($this->{$callback} ?? [], [$auditsCallback]);
    }
}
