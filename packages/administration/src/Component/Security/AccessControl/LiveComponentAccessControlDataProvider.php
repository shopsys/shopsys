<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\AccessControl;

use ReflectionClass;
use ReflectionMethod;
use Shopsys\AdministrationBundle\Component\Security\Attribute\AttributeProcessor;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveListener;

class LiveComponentAccessControlDataProvider
{
    /**
     * @param iterable<object> $twigComponents
     */
    public function __construct(
        protected readonly AttributeProcessor $attributeProcessor,
        #[AutowireIterator('twig.component')]
        protected readonly iterable $twigComponents,
    ) {
    }

    /**
     * @return array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData>
     */
    public function getAll(): array
    {
        $liveComponentAccessControlData = [];

        foreach ($this->twigComponents as $twigComponent) {
            $reflectionClass = new ReflectionClass($twigComponent);
            $asLiveComponent = $this->getAsLiveComponent($reflectionClass);

            if ($asLiveComponent === null) {
                continue;
            }

            $componentName = $this->getComponentName($reflectionClass, $asLiveComponent);

            foreach ($this->getActionMethods($reflectionClass) as $method) {
                $identifier = sprintf('%s::%s', $componentName, $method->getName());
                $liveComponentAccessControlData[$identifier] = new RouteAccessControlData(
                    $identifier,
                    $this->attributeProcessor->processMethod($reflectionClass, $method),
                    $reflectionClass->getName(),
                    $method->getName(),
                );
            }
        }

        ksort($liveComponentAccessControlData);

        return $liveComponentAccessControlData;
    }

    protected function getAsLiveComponent(ReflectionClass $reflectionClass): ?AsLiveComponent
    {
        $attributes = $reflectionClass->getAttributes(AsLiveComponent::class);

        if ($attributes === []) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    protected function getComponentName(ReflectionClass $reflectionClass, AsLiveComponent $asLiveComponent): string
    {
        return $asLiveComponent->serviceConfig()['key'] ?? $reflectionClass->getName();
    }

    /**
     * @return array<string, \ReflectionMethod>
     */
    protected function getActionMethods(ReflectionClass $reflectionClass): array
    {
        $methods = [];

        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getAttributes(LiveAction::class) === [] && $method->getAttributes(LiveListener::class) === []) {
                continue;
            }

            $methods[$method->getName()] = $method;
        }

        ksort($methods);

        return $methods;
    }
}
