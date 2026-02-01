<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting;

use Override;
use Shopsys\HttpSmokeTesting\Attribute\DataSet;
use Shopsys\HttpSmokeTesting\Attribute\Skipped;

class RequestDataSetGenerator implements RouteConfig
{
    private RequestDataSet $defaultRequestDataSet;

    /**
     * @var \Shopsys\HttpSmokeTesting\RequestDataSet[]
     */
    private array $extraRequestDataSets;

    public function __construct(private readonly RouteInfo $routeInfo)
    {
        $this->defaultRequestDataSet = new RequestDataSet($this->routeInfo->getRouteName());
        $this->extraRequestDataSets = [];
    }

    public function fulfillRequestFromAnnotations(): void
    {
        foreach ($this->routeInfo->getAnnotations() as $index => $annotation) {
            if ($annotation instanceof Skipped) {
                $this->defaultRequestDataSet->skip();
            } elseif ($annotation instanceof DataSet) {
                $this->fulfillRequestDataSetFromAnnotation($this->getRequestDataSetForIteration($index), $annotation);
            }
        }
    }

    private function getRequestDataSetForIteration(int $index): RequestDataSet
    {
        if ($index === 0) {
            return $this->defaultRequestDataSet;
        }

        return $this->addExtraRequestDataSet();
    }

    private function fulfillRequestDataSetFromAnnotation(RequestDataSet $requestDataSet, DataSet $annotation): void
    {
        if ($annotation->statusCode) {
            $requestDataSet->setExpectedStatusCode($annotation->statusCode);
        }

        foreach ($annotation->parameters as $parameter) {
            $requestDataSet->setParameter($parameter->name, $parameter->value);
        }
    }

    public function getRouteInfo(): RouteInfo
    {
        return $this->routeInfo;
    }

    /**
     * @return \Shopsys\HttpSmokeTesting\RequestDataSet[]
     */
    public function generateRequestDataSets(): array
    {
        $requestDataSets = [clone $this->defaultRequestDataSet];

        foreach ($this->extraRequestDataSets as $extraRequestDataSet) {
            $defaultRequestDataSetClone = clone $this->defaultRequestDataSet;
            $requestDataSets[] = $defaultRequestDataSetClone->mergeExtraValuesFrom($extraRequestDataSet);
        }

        return $requestDataSets;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function skipRoute(?string $debugNote = null): self
    {
        $this->defaultRequestDataSet->skip();

        if ($debugNote !== null) {
            $this->defaultRequestDataSet->addDebugNote('Skipped test case: ' . $debugNote);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function changeDefaultRequestDataSet(?string $debugNote = null): RequestDataSet
    {
        $requestDataSet = $this->defaultRequestDataSet;

        if ($debugNote !== null) {
            $requestDataSet->addDebugNote($debugNote);
        }

        return $requestDataSet;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function addExtraRequestDataSet(?string $debugNote = null): RequestDataSet
    {
        $requestDataSet = new RequestDataSet($this->routeInfo->getRouteName());
        $this->extraRequestDataSets[] = $requestDataSet;

        if ($debugNote !== null) {
            $requestDataSet->addDebugNote('Extra test case: ' . $debugNote);
        }

        return $requestDataSet;
    }
}
