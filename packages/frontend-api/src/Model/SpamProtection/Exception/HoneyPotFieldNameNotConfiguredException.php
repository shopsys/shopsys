<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\SpamProtection\Exception;

use Exception;
use Shopsys\FrontendApiBundle\Model\SpamProtection\SpamProtectedFormEnum;

class HoneyPotFieldNameNotConfiguredException extends Exception
{
    public function __construct(string $formName)
    {
        parent::__construct(
            sprintf(
                'Form "%s" has no honey pot field name configured in %s::getHoneyPotFieldNameIndexedByFormName()',
                $formName,
                SpamProtectedFormEnum::class,
            ),
        );
    }
}
