<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Shopsys\FrameworkBundle\Component\Doctrine\Exception\SqlLoggerAlreadyDisabledException;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\SqlLoggerAlreadyEnabledException;

class SqlLoggerFacade
{
    protected bool $isLoggerTemporarilyDisabled;

    public function __construct(
        protected readonly ToggleableDebugDataHolder $toggleableDebugDataHolder,
    ) {
        $this->isLoggerTemporarilyDisabled = false;
    }

    public function temporarilyDisableLogging(): void
    {
        if ($this->isLoggerTemporarilyDisabled) {
            $message = 'Trying to disable already disabled SQL logger.';

            throw new SqlLoggerAlreadyDisabledException($message);
        }

        $this->toggleableDebugDataHolder->disable();
        $this->isLoggerTemporarilyDisabled = true;
    }

    public function reenableLogging(): void
    {
        if (!$this->isLoggerTemporarilyDisabled) {
            $message = 'Trying to reenable already enabled SQL logger.';

            throw new SqlLoggerAlreadyEnabledException($message);
        }

        $this->toggleableDebugDataHolder->enable();
        $this->isLoggerTemporarilyDisabled = false;
    }
}
