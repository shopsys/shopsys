<?php

namespace Shopsys\HttpSmokeTesting;

class RequestDataSetGenerator implements RouteConfig
{
    /**
     * @var \Shopsys\HttpSmokeTesting\RouteInfo
     */
    private $routeInfo;

    /**
     * @var \Shopsys\HttpSmokeTesting\RequestDataSet
     */
    private $defaultRequestDataSet;

    /**
     * @var \Shopsys\HttpSmokeTesting\RequestDataSet[]
     */
    private $extraRequestDataSets;

    public function __construct(RouteInfo $routeInfo)
    {
        $this->routeInfo = $routeInfo;
        $this->defaultRequestDataSet = new RequestDataSet($this->routeInfo->getRouteName());
        $this->extraRequestDataSets = [];
    }

    public function getRouteInfo(): \Shopsys\HttpSmokeTesting\RouteInfo
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
     * @param string|null $debugNote
     */
    public function skipRoute($debugNote = null): \Shopsys\HttpSmokeTesting\RequestDataSetGenerator
    {
        $this->defaultRequestDataSet->skip();

        if ($debugNote !== null) {
            $this->defaultRequestDataSet->addDebugNote('Skipped test case: ' . $debugNote);
        }

        return $this;
    }

    /**
     * @param string|null $debugNote
     */
    public function changeDefaultRequestDataSet($debugNote = null): \Shopsys\HttpSmokeTesting\RequestDataSet
    {
        $requestDataSet = $this->defaultRequestDataSet;

        if ($debugNote !== null) {
            $requestDataSet->addDebugNote($debugNote);
        }

        return $requestDataSet;
    }

    /**
     * @param string|null $debugNote
     */
    public function addExtraRequestDataSet($debugNote = null): \Shopsys\HttpSmokeTesting\RequestDataSet
    {
        $requestDataSet = new RequestDataSet($this->routeInfo->getRouteName());
        $this->extraRequestDataSets[] = $requestDataSet;

        if ($debugNote !== null) {
            $requestDataSet->addDebugNote('Extra test case: ' . $debugNote);
        }

        return $requestDataSet;
    }
}
