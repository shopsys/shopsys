<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Component\Ai\Client\OpenAi;

use Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling\DynamicFunctionRunner;
use Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling\FunctionRunnerSetup;
use Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling\ParameterSetup;

class OpenAiFunctionCallingFactory
{
    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling\DynamicFunctionRunner $dynamicFunctionRunner
     */
    public function __construct(
        protected readonly DynamicFunctionRunner $dynamicFunctionRunner,
    ) {
    }

    /**
     * @param string[] $availableAiFunctionNames
     * @return array
     */
    public function getFunctions(array $availableAiFunctionNames): array
    {
        $functions = [];

        foreach ($availableAiFunctionNames as $aiFunctionName) {
            $functionCallingSetup = $this->dynamicFunctionRunner->findFunctionCallingSetupByAiFunctionName($aiFunctionName);

            if ($functionCallingSetup === null) {
                continue;
            }

            $function = [
                'type' => 'function',
                'function' => $this->getFunctionDefinition($functionCallingSetup),
            ];
            $functions[] = $function;
        }

        return $functions;
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling\FunctionRunnerSetup $functionCallingSetup
     * @return array
     */
    protected function getFunctionDefinition(FunctionRunnerSetup $functionCallingSetup): array
    {
        $function = [
            'name' => $functionCallingSetup->aiFunctionName,
            'description' => '',
            'parameters' => (object)[],
        ];

        if (count($functionCallingSetup->params) > 0) {
            $function['parameters'] = [
                'type' => 'object',
                'properties' => (object)$this->getProperties($functionCallingSetup->params),
                'required' => array_map(
                    fn (ParameterSetup $parameterSetup) => $parameterSetup->parameterName,
                    $functionCallingSetup->params,
                ),
            ];
        }

        return $function;
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling\ParameterSetup[] $params
     * @return array
     */
    protected function getProperties(array $params): array
    {
        $properties = [];

        foreach ($params as $param) {
            $property = [];
            $property['type'] = $param->parameterType;
            $property['description'] = '';


            $properties[$param->parameterName] = $property;
        }

        return $properties;
    }
}
