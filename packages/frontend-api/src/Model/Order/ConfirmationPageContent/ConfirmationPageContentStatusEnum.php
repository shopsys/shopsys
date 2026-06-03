<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order\ConfirmationPageContent;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class ConfirmationPageContentStatusEnum extends AbstractEnum
{
    public const string STATUS_SUCCESSFUL = 'successful';
    public const string STATUS_FAILED = 'failed';
    public const string STATUS_IN_PROCESS = 'in_process';
}
