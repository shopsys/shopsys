<?php

declare(strict_types=1);

namespace Tests\App\Smoke\Http;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\HttpSmokeTesting\HttpSmokeTestCase;
use Shopsys\HttpSmokeTesting\RouteConfigCustomizer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class HttpSmokeTest extends HttpSmokeTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        static::getContainer()->get(Domain::class)
            ->switchDomainById(Domain::FIRST_DOMAIN_ID);
    }

    /**
     * @param \Shopsys\HttpSmokeTesting\RouteConfigCustomizer $routeConfigCustomizer
     */
    #[Override]
    protected static function customizeRouteConfigs(RouteConfigCustomizer $routeConfigCustomizer)
    {
        $routeConfigCustomization = new RouteConfigCustomization(KernelTestCase::getContainer());
        $routeConfigCustomization->customizeRouteConfigs($routeConfigCustomizer);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
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
