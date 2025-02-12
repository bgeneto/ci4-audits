<?php

declare(strict_types=1);

namespace Bgeneto\Audits\Entities;

use CodeIgniter\Entity\Entity;

class Audit extends Entity
{
    protected string $table      = 'audits';
    protected string $primaryKey = 'id';
    protected array $dates       = ['created_at'];
    protected array $casts       = [
        'source_id' => 'int',
        'user_id'   => 'int',
    ];
}
