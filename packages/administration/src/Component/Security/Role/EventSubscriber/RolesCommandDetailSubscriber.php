<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\Role\EventSubscriber;

use Override;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlDataProviderInterface;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\Role\Event\RolesCommandDetailEvent;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RolesCommandDetailSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly AccessControlDataProviderInterface $routeAccessControlDataProvider,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            RolesCommandDetailEvent::class => 'onRoleDetail',
        ];
    }

    public function onRoleDetail(RolesCommandDetailEvent $event): void
    {
        if ($event->getContext() !== AdminContext::class) {
            return;
        }

        $routes = $this->getRoutesUsingRole($event->getRole()->getConstant());

        if (count($routes) > 0) {
            $event->addRenderCallback(function (SymfonyStyle $io, OutputInterface $output) use ($routes): void {
                $io->text('Routes using this role:');

                $table = new Table($output);
                $table->setHeaders(['Route Name', 'Controller', 'Required Permissions']);

                $rows = [];

                foreach ($routes as $route) {
                    $rows[] = [
                        $route['name'] ?? '-',
                        $route['controller'] ?? '-',
                        $route['permissions'] ?? 'Any',
                    ];
                }

                $table->setRows($rows);
                $table->render();

                $io->newLine();
            });
        }
    }

    /**
     * @return array<array{name: string, controller: string, permissions: string}>
     */
    protected function getRoutesUsingRole(string $roleConstant): array
    {
        $allRoutes = $this->routeAccessControlDataProvider->getAll();
        $routePermissions = [];

        foreach ($allRoutes as $routeName => $routeAccessControlData) {
            foreach ($routeAccessControlData->accessControlRules as $rule) {
                if ($rule->role->getConstant() === $roleConstant) {
                    if (!isset($routePermissions[$routeName])) {
                        $routePermissions[$routeName] = [
                            'name' => $routeName,
                            'controller' => $routeAccessControlData->formatControllerInfo(),
                            'permissions_data' => [],
                        ];
                    }

                    $httpMethods = $rule->httpMethods;
                    $methodsText = count($httpMethods) === 0 ? 'ALL' : implode(', ', array_map(fn ($method) => $method->value, $httpMethods));
                    $permission = $rule->permission->value ?? 'Any';
                    $routePermissions[$routeName]['permissions_data'][$permission][] = $methodsText;
                }
            }
        }
        $matchingRoutes = [];

        foreach ($routePermissions as $routeData) {
            $permissionStrings = [];

            foreach ($routeData['permissions_data'] as $permission => $methods) {
                $uniqueMethods = array_unique($methods);
                $methodsText = implode(', ', $uniqueMethods);
                $permissionStrings[] = "{$permission} ({$methodsText})";
            }

            $matchingRoutes[] = [
                'name' => $routeData['name'],
                'controller' => $routeData['controller'],
                'permissions' => implode(', ', $permissionStrings),
            ];
        }
        usort($matchingRoutes, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        return $matchingRoutes;
    }
}
