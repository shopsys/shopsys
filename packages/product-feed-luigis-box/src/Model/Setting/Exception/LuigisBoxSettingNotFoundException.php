<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle\Model\Setting\Exception;

use Exception;

class LuigisBoxSettingNotFoundException extends Exception
{
    /**
     * @param array<string, mixed> $criteria
     */
    public function __construct(array $criteria)
    {
        $message = sprintf('LuigisBoxSetting not found for criteria: %s', json_encode($criteria));

        parent::__construct($message);
    }
}
