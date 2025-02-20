<?php

namespace Bgeneto\Audits\Traits;

use Bgeneto\Audits\Models\AuditModel;

// CLASS
trait AuditsTrait
{
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
        $objectIds = array_column($objects, $this->primaryKey);

        // Start the query
        $query = model(AuditModel::class)->builder()->where('source', $this->table)->whereIn('source_id', $objectIds);

        if (is_string($events)) {
            $query = $query->where('event', $events);
        } elseif (is_array($events)) {
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
    protected function auditInsert(array $data)
    {
        if (! $data['result']) {
            return false;
        }
        $fieldNames = implode(', ', array_keys($data['data']));
        $audit      = [
            'source'    => $this->table,
            'source_id' => $this->db->insertID(), // @phpstan-ignore-line
            'event'     => 'insert',
            'summary'   => count($data['data']) . ' fields: ' . $fieldNames,
            'data'      => null,
        ];
        service('audits')->add($audit);

        return $data;
    }

    // record successful update events
    protected function auditUpdate(array $data)
    {
        $fieldNames = implode(', ', array_keys($data['data']));

        foreach ($data['id'] as $sourceId) {
            $audit = [
                'source'    => $this->table,
                'source_id' => $sourceId,
                'event'     => 'update',
                'summary'   => count($data['data']) . ' fields: ' . $fieldNames,
                'data'      => null,
            ];
            service('audits')->add($audit);
        }

        return $data;
    }

    // record successful delete events
    protected function auditDelete(array $data)
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
        $audits = service('audits');

        foreach ($data['id'] as $id) {
            $audit['source_id'] = $id;
            $audits->add($audit);
        }

        return $data;
    }

    // record event with method, class (with namespace) where it was called
    protected function auditEvent(string $event, string $summary, array $data = [])
    {
        $audit = [
            'source'    => get_class($this),
            'source_id' => null,
            'event'     => $event,
            'summary'   => $summary,
            'data'      => json_encode($data),
        ];
        service('audits')->add($audit);
    }
}
