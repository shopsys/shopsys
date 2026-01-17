<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting;

class RequestDataSetGeneratorFactory
{
    public function create(RouteInfo $routeInfo): RequestDataSetGenerator
    {
        $requestDataSetGenerator = new RequestDataSetGenerator($routeInfo);
        $requestDataSetGenerator->fulfillRequestFromAnnotations();

        return $requestDataSetGenerator;
    }
}
