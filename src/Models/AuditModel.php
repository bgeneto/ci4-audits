<?php

declare(strict_types=1);

namespace Bgeneto\Audits\Models;

use Bgeneto\Audits\Entities\Audit;
use CodeIgniter\Model;

class AuditModel extends Model
{
    protected string $table        = 'audits';
    protected string $primaryKey   = 'id';
    protected string $returnType   = Audit::class;
    protected bool $useTimestamps  = false;
    protected bool $useSoftDeletes = false;
    protected bool $skipValidation = true;
    protected array $allowedFields = ['source', 'source_id', 'user_id', 'event', 'summary', 'created_at', 'data'];
}
