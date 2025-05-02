<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat\Agent\FunctionCalling;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class AiFunction
{
    /**
     * @param string $aiFunctionName
     */
    public function __construct(
        public string $aiFunctionName,
    ) {
    }

    /**
     * @return string
     */
    public function getAiFunctionName(): string
    {
        return $this->aiFunctionName;
    }
}
