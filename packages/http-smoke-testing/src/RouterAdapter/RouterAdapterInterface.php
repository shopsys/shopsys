<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\RouterAdapter;

use Shopsys\HttpSmokeTesting\RequestDataSet;

interface RouterAdapterInterface
{
    /**
     * @return \Shopsys\HttpSmokeTesting\RouteInfo[]
     */
    public function getAllRouteInfo();

    /**
     * @return string
     */
    public function generateUri(RequestDataSet $requestDataSet);
}
