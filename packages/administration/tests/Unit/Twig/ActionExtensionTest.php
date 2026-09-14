<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Action\RouteData\CrudActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\Action\Exception\CrudActionNotFoundException;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlDataProviderInterface;
use Shopsys\AdministrationBundle\Twig\ActionExtension;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Router\AdministrationRouter;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\RouteAccessCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Tests\AdministrationBundle\Unit\Component\Crud\ReviewCrudControllerRegistryFactory;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\ReviewCrudController;

class ActionExtensionTest extends TestCase
{
    private const string CSRF_TOKEN = 'csrf-token-value';

    public function testUrlOfCustomActionContainsIdAndCsrfTokenOfTheHandlingMethod(): void
    {
        $extension = $this->createExtension(ReviewCrudControllerRegistryFactory::create());
        $actionRoute = new CrudActionRouteData(ReviewCrudController::class, 'approve', static fn (array $row): int => $row['id']);

        $url = $this->generateActionUrl($extension, $actionRoute, ['id' => 42]);

        $this->assertSame('/admin_crud_review_approve?id=42&routeCsrfToken=' . self::CSRF_TOKEN, $url);
    }

    public function testClosureMayReturnAllRouteParametersOfTheAction(): void
    {
        $extension = $this->createExtension(ReviewCrudControllerRegistryFactory::create());
        $actionRoute = new CrudActionRouteData(ReviewCrudController::class, 'move', static fn (array $row): array => ['id' => $row['id'], 'position' => 3]);

        $this->assertSame('/admin_crud_review_move?id=42&position=3', $this->generateActionUrl($extension, $actionRoute, ['id' => 42]));
    }

    public function testUrlOfBuiltInActionIsGeneratedTheSameWayAsBefore(): void
    {
        $extension = $this->createExtension(ReviewCrudControllerRegistryFactory::create());
        $actionRoute = new CrudActionRouteData(ReviewCrudController::class, ActionType::LIST);

        $this->assertSame('/admin_crud_review_list', $this->generateActionUrl($extension, $actionRoute, null));
    }

    public function testLinkToUnknownActionFailsWhenRendered(): void
    {
        $extension = $this->createExtension(ReviewCrudControllerRegistryFactory::create());
        $actionRoute = new CrudActionRouteData(ReviewCrudController::class, 'publish');

        $this->expectException(CrudActionNotFoundException::class);

        $this->generateActionUrl($extension, $actionRoute, null);
    }

    private function generateActionUrl(
        ActionExtension $extension,
        CrudActionRouteData $actionRoute,
        mixed $data,
    ): string {
        foreach ($extension->getFunctions() as $function) {
            if ($function->getName() === 'action_url') {
                return $function->getCallable()($actionRoute, $data);
            }
        }

        self::fail('The action_url Twig function is not registered.');
    }

    /**
     * The router fake generates "/<routeName>?<query>" so the URL can be asserted directly
     */
    private function createExtension(CrudControllerRegistry $registry): ActionExtension
    {
        $router = $this->createStub(AdministrationRouter::class);
        $router->method('generate')->willReturnCallback(
            static fn (string $name, array $parameters = []): string => '/' . $name . ($parameters === [] ? '' : '?' . http_build_query($parameters)),
        );

        $tokenManager = $this->createStub(CsrfTokenManagerInterface::class);
        $tokenManager->method('getToken')->willReturnCallback(
            static fn (string $tokenId): CsrfToken => new CsrfToken($tokenId, self::CSRF_TOKEN),
        );

        return new ActionExtension(
            $router,
            $this->createStub(RouteAccessCheckerInterface::class),
            new RouteCsrfProtector($tokenManager, new InMemoryCache()),
            $this->createStub(AccessControlDataProviderInterface::class),
            $registry,
        );
    }
}
