<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling;

use LogicException;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class DynamicFunctionRunner implements ServiceSubscriberInterface
{
    /**
     * @var \Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling\FunctionRunnerSetup[]
     */
    protected array $functionRunnerSetups = [];

    /**
     * @param string[] $aiFunctionList
     * @param \Symfony\Component\DependencyInjection\ServiceLocator $locator
     */
    public function __construct(
        protected readonly array $aiFunctionList,
        protected readonly ServiceLocator $locator,
    ) {
        foreach ($this->aiFunctionList as $aiFunction) {
            /** @var \Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling\FunctionRunnerSetup $functionRunnerSetup */
            $functionRunnerSetup = unserialize($aiFunction);
            $this->functionRunnerSetups[$functionRunnerSetup->aiFunctionName] = $functionRunnerSetup;
        }
    }

    /**
     * @return iterable<class-string, string>
     */
    public static function getSubscribedServices(): iterable
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAvailableFunctionList(): array
    {
        return $this->functionRunnerSetups;
    }

    /**
     * @param string $aiFunctionName
     * @return \Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling\FunctionRunnerSetup|null
     */
    public function findFunctionCallingSetupByAiFunctionName(string $aiFunctionName): ?FunctionRunnerSetup
    {
        return $this->functionRunnerSetups[$aiFunctionName] ?? null;
    }

    /**
     * @param string $fqcn
     * @return object|null
     */
    protected function findServiceByFqcn(string $fqcn)
    {
        if (!$this->locator->has($fqcn)) {
            throw new LogicException(sprintf('Service %s is not available.', $fqcn));
        }

        return $this->locator->get($fqcn);
    }

    /**
     * @param string $aiFunctionName
     * @param array $args
     */
    public function call(string $aiFunctionName, array $args = [])
    {
        $functionRunnerSetup = $this->findFunctionCallingSetupByAiFunctionName($aiFunctionName);

        if ($functionRunnerSetup === null) {
            throw new LogicException(sprintf('AI Method %s not exists.', $aiFunctionName));
        }

        $functionName = $functionRunnerSetup->functionName;

        $service = $this->findServiceByFqcn($functionRunnerSetup->className);

        if (!method_exists($service, $functionName)) {
            throw new LogicException(sprintf('Method %s() in %s not exists.', $functionName, get_debug_type($service)));
        }

        if (count($functionRunnerSetup->params) !== count($args)) {
            throw new LogicException(
                sprintf(
                    'Method %s() in %s has arguments: %s. Arguments contains: %s',
                    $functionName,
                    get_debug_type($service),
                    implode(', ', array_map(
                        fn (ParameterSetup $parameterSetup) => $parameterSetup->parameterName,
                        $functionRunnerSetup->params,
                    )),
                    implode(', ', array_keys($args)),
                ),
            );
        }

        //TODO - resolve data types by setup.

        $result = $service->{$functionName}(...$args);

        return $result;
    }
}
