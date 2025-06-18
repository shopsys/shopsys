<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class GraphQlB2bDomainTestCase extends GraphQlTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $b2bDomain = $this->domain->findFirstB2bDomain();

        if ($b2bDomain === null) {
            $this->markTestSkipped('No B2B domain found');
        }

        $this->domain->switchDomainById($b2bDomain->getId());
    }

    /**
     * @param string $routeName
     * @param array<string, mixed> $parameters
     * @param int $pathType
     * @return string
     */
    protected function getLocalizedPathOnFirstDomainByRouteName(
        string $routeName,
        array $parameters = [],
        int $pathType = UrlGeneratorInterface::ABSOLUTE_URL,
    ): string {
        $domainRouterFactory = self::getContainer()->get(DomainRouterFactory::class);

        return $domainRouterFactory->getRouter($this->domain->getId())
            ->generate($routeName, $parameters, $pathType);
    }
}
