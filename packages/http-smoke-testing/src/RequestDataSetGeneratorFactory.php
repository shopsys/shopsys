<?php

namespace Shopsys\HttpSmokeTesting;

class RequestDataSetGeneratorFactory
{
    /**
     * @param \Shopsys\HttpSmokeTesting\RouteInfo $routeInfo
     * @return \Shopsys\HttpSmokeTesting\RequestDataSetGenerator
     */
    public function create(RouteInfo $routeInfo): RequestDataSetGenerator
    {
        $requestDataSetGenerator = new RequestDataSetGenerator($routeInfo);
        $requestDataSetGenerator->fulfillRequestFromAnnotations();
        return $requestDataSetGenerator;
    }
}
