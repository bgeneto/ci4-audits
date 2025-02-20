<?php

namespace Bgeneto\Audits;

use Bgeneto\Audits\Config\Audits as AuditsConfig;
use Bgeneto\Audits\Models\AuditModel;

// CLASS
class Audits
{
    /**
     * Our configuration instance.
     */
    protected AuditsConfig $config;

    /**
     * Audit rows waiting to add to the database.
     */
    protected array $queue = [];

    /**
     * Store the configuration
     *
     * @param AuditsConfig $config The Audits configuration to use
     */
    public function __construct(AuditsConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Checks the session for a logged in user based on config
     *
     * @return int The current user ID, 0 for "not logged in" or for CLI
     *
     * @deprecated This will be removed in the next major release; use codeigniter4/authentication-implementation
     */
    public function sessionUserId(): int
    {
        if (\is_cli()) {
            return 0;
        }

        if (\function_exists('user_id')) {
            return \user_id();
        }

        return \session($this->config->sessionUserId) ?? 0;
    }

    /**
     * Return the current queue (mostly for testing)
     */
    public function getQueue(): array
    {
        return $this->queue;
    }

    /**
     * Add an audit row to the queue
     *
     * @param array|null $audit The row to cache for insert
     */
    public function add(?array $audit = null): bool
    {
        if ($audit === null || $audit === []) {
            return false;
        }

        // Add common data
        $audit['user_id']    = $this->sessionUserId(); // @phpstan-ignore-line
        $audit['created_at'] = \date('Y-m-d H:i:s');

        $this->queue[] = $audit;

        return true;
    }

    /**
     * Batch insert all audits from the queue
     *
     * @return $this
     */
    public function save(): self
    {
        if ($this->queue !== []) {
            $audits = new AuditModel();
            $audits->insertBatch($this->queue);
        }

        return $this;
    }

    /**
     * record event with method, class (with namespace) where it was called
     */
    public static function auditData(array $data = [], string $summary = 'None'): void
    {
        $audit = [
            'source_id' => 0,
            'event'     => \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'Unknown',
            'summary'   => $summary,
            'data'      => \json_encode($data),
        ];

        $trace           = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $audit['source'] = $trace[1]['class'] ?? 'Unknown';

        $audits = new Audits(new AuditsConfig());
        $audits->add($audit);
        $audits->save();
    }
}
