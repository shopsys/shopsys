<?php

namespace Shopsys\HttpSmokeTesting;

use Shopsys\HttpSmokeTesting\Annotation\DataSet;
use Shopsys\HttpSmokeTesting\Annotation\Parameter;
use Shopsys\HttpSmokeTesting\Annotation\Skipped;

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

    /**
     * @param \Shopsys\HttpSmokeTesting\RouteInfo $routeInfo
     */
    public function __construct(RouteInfo $routeInfo)
    {
        $this->routeInfo = $routeInfo;
        $this->defaultRequestDataSet = new RequestDataSet($this->routeInfo->getRouteName());
        $this->extraRequestDataSets = [];

        if ($routeInfo->getAnnotations() !== null) {
            foreach ($routeInfo->getAnnotations() as $index => $annotation) {
                if ($annotation instanceof Skipped) {
                    $this->defaultRequestDataSet->skip();
                } elseif ($annotation instanceof DataSet) {
                    if ($index === 0) {
                        $requestDataSet = $this->defaultRequestDataSet;
                    } else {
                        $requestDataSet = $this->addExtraRequestDataSet();
                    }

                    if ($annotation->statusCode) {
                        $requestDataSet->setExpectedStatusCode($annotation->statusCode);
                    }

                    /** @var \Shopsys\HttpSmokeTesting\Annotation\Parameter $parameter */
                    foreach ($annotation->parameters as $parameter) {
                        $requestDataSet->setParameter($parameter->name, $parameter->value);
                    }
                }
            }
        }
    }

    /**
     * @return \Shopsys\HttpSmokeTesting\RouteInfo
     */
    public function getRouteInfo()
    {
        return $this->routeInfo;
    }

    /**
     * @return \Shopsys\HttpSmokeTesting\RequestDataSet[]
     */
    public function generateRequestDataSets()
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
     * @return \Shopsys\HttpSmokeTesting\RequestDataSetGenerator
     */
    public function skipRoute($debugNote = null)
    {
        $this->defaultRequestDataSet->skip();

        if ($debugNote !== null) {
            $this->defaultRequestDataSet->addDebugNote('Skipped test case: ' . $debugNote);
        }

        return $this;
    }

    /**
     * @param string|null $debugNote
     * @return \Shopsys\HttpSmokeTesting\RequestDataSet
     */
    public function changeDefaultRequestDataSet($debugNote = null)
    {
        $requestDataSet = $this->defaultRequestDataSet;

        if ($debugNote !== null) {
            $requestDataSet->addDebugNote($debugNote);
        }

        return $requestDataSet;
    }

    /**
     * @param string|null $debugNote
     * @return \Shopsys\HttpSmokeTesting\RequestDataSet
     */
    public function addExtraRequestDataSet($debugNote = null)
    {
        $requestDataSet = new RequestDataSet($this->routeInfo->getRouteName());
        $this->extraRequestDataSets[] = $requestDataSet;

        if ($debugNote !== null) {
            $requestDataSet->addDebugNote('Extra test case: ' . $debugNote);
        }

        return $requestDataSet;
    }
}
