<?php

declare(strict_types=1);

namespace Tests\App\Smoke\Http;

use Override;
use Shopsys\HttpSmokeTesting\HttpSmokeTestCase;
use Shopsys\HttpSmokeTesting\RouteConfigCustomizer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class HttpSmokeTest extends HttpSmokeTestCase
{
    #[Override]
    protected static function customizeRouteConfigs(RouteConfigCustomizer $routeConfigCustomizer)
    {
        $routeConfigCustomization = new RouteConfigCustomization(KernelTestCase::getContainer());
        $routeConfigCustomization->customizeRouteConfigs($routeConfigCustomizer);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Override]
    protected function handleRequest(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $entityManager->beginTransaction();
        ob_start();
        $response = parent::handleRequest($request);
        ob_end_clean();
        $entityManager->rollback();

        return $response;
    }
}
