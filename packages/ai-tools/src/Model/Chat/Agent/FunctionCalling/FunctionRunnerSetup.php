<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling;

class FunctionRunnerSetup
{
    public ?string $aiFunctionName = null;

    public ?string $className = null;

    public ?string $functionName = null;

    /**
     * @var \Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling\ParameterSetup[]
     */
    public array $params = [];

    public ?string $returnType = null;
}
