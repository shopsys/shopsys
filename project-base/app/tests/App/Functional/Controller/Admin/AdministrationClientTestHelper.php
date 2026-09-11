<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;
use Tests\App\Test\Client;

final class AdministrationClientTestHelper
{
    private const string ADMIN_IP_ADDRESS = '127.0.0.1';

    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function logInAdministrator(int $administratorId): void
    {
        $container = $this->getClientTestContainer();
        $administrator = $container->get(AdministratorFacade::class)->getById($administratorId);
        $container->get(AdministratorActivityFacade::class)->create($administrator, self::ADMIN_IP_ADDRESS);
        $this->client->loginUser($administrator, 'administration');
    }

    /**
     * @param array<string, int|string> $parameters
     */
    public function generatePath(string $routeName, array $parameters = []): string
    {
        /** @var \Symfony\Component\Routing\RouterInterface $router */
        $router = $this->getClientTestContainer()->get('router');

        return $router->generate($routeName, $parameters);
    }

    public function getRouteCsrfTokenFromSessionOfLastRequest(string $routeName): string
    {
        $container = $this->getClientTestContainer();
        /** @var \Symfony\Component\HttpFoundation\RequestStack $requestStack */
        $requestStack = $container->get('request_stack');
        $requestStack->push($this->client->getRequest());

        try {
            return $container->get(RouteCsrfProtector::class)->getCsrfTokenByRoute($routeName);
        } finally {
            $requestStack->pop();
        }
    }

    private function getClientTestContainer(): TestContainer
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Test\TestContainer $testContainer */
        $testContainer = $this->client->getContainer();

        return $testContainer;
    }
}
