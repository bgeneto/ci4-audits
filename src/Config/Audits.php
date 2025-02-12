<?php

namespace Bgeneto\Audits\Config;

use CodeIgniter\Config\BaseConfig;

class Audits extends BaseConfig
{
    // Session key in that contains the integer ID of a logged in user
    public string $sessionUserId = 'logged_in';

    // Whether to continue instead of throwing exceptions
    public bool $silent = true;
}
