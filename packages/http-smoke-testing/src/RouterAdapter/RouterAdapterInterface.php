<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\RouterAdapter;

use Shopsys\HttpSmokeTesting\RequestDataSet;

interface RouterAdapterInterface
{
    /**
     * @return \Shopsys\HttpSmokeTesting\RouteInfo[]
     */
    public function getAllRouteInfo(): array;

    public function generateUri(RequestDataSet $requestDataSet): string;
}
