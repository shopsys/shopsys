<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

abstract class AbstractTransportTypeEnum extends AbstractEnum
{
    /**
     * @return array<string, string>
     */
    abstract public function getAllIndexedByTranslations(): array;
}
