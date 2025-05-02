<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\OpenAi;

use Shopsys\FrameworkBundle\Model\Chat\Agent\Agent;
use Shopsys\FrameworkBundle\Model\Chat\Agent\FunctionCalling\DynamicFunctionRunner;
use Shopsys\FrameworkBundle\Model\Chat\Agent\FunctionCalling\FunctionRunnerSetup;
use Shopsys\FrameworkBundle\Model\Chat\Agent\FunctionCalling\ParameterSetup;

class OpenAiFunctionCallingFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\FunctionCalling\DynamicFunctionRunner $dynamicFunctionRunner
     */
    public function __construct(
        protected readonly DynamicFunctionRunner $dynamicFunctionRunner,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\Agent $agent
     * @return array
     */
    public function getFunctions(Agent $agent): array
    {
        $functions = [];

        foreach ($agent->getAvailableAiFunctions() as $aiFunctionName) {
            $functionCallingSetup = $this->dynamicFunctionRunner->findFunctionCallingSetupByAiFunctionName($aiFunctionName);

            if ($functionCallingSetup !== null) {
                $functions[] = $this->getFunction($functionCallingSetup);
            }
        }

        return $functions;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\FunctionCalling\FunctionRunnerSetup $functionCallingSetup
     * @return array
     */
    protected function getFunction(FunctionRunnerSetup $functionCallingSetup): array
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
     * @param \Shopsys\FrameworkBundle\Model\Chat\Agent\FunctionCalling\ParameterSetup[] $params
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
