<?php

namespace Bgeneto\Audits\Config;

use Bgeneto\Audits\Audits;
use Bgeneto\Audits\Config\Audits as AuditsConfig;
use Config\Services as BaseServices;

class Services extends BaseServices
{
    public static function audits(?AuditsConfig $config = null, bool $getShared = true): Audits
    {
        if ($getShared) {
            return static::getSharedInstance('audits', $config);
        }

        // If no config was injected then load one
        if (! $config instanceof AuditsConfig) {
            $config = config('Audits');
        }

        return new Audits($config);
    }
}
