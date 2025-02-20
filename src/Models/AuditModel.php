<?php

declare(strict_types=1);

namespace Bgeneto\Audits\Models;

use Bgeneto\Audits\Entities\Audit;
use CodeIgniter\Model;

class AuditModel extends Model
{
    protected $table          = 'audits';
    protected $primaryKey     = 'id';
    protected $returnType     = Audit::class;
    protected $useTimestamps  = false;
    protected $useSoftDeletes = false;
    protected $skipValidation = true;
    protected $allowedFields  = ['source', 'source_id', 'user_id', 'event', 'summary', 'data', 'created_at'];
}
