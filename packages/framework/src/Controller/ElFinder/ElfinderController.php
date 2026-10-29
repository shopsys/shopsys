<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\ElFinder;

use elFinderAbortException;
use FM\ElfinderBundle\Controller\ElFinderController as BaseElFinderController;
use FM\ElfinderBundle\Loader\ElFinderLoaderInterface;
use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;

class ElfinderController extends BaseElFinderController
{
    /**
     * @param mixed[] $params
     */
    public function __construct(
        Environment $twig,
        array $params,
        ElFinderLoaderInterface $loader,
        protected readonly AccessCheckerInterface $accessChecker,
        protected readonly RoleRegistryInterface $roleRegistry,
    ) {
        parent::__construct($twig, $params, $loader);
    }

    #[Override]
    public function show(Request $request, string $instance, string $homeFolder): Response
    {
        $this->denyUnlessHasFileManagerAccess();

        return parent::show($request, $instance, $homeFolder);
    }

    #[Override]
    public function load(
        SessionInterface $session,
        HttpKernelInterface $httpKernel,
        EventDispatcherInterface $eventDispatcher,
        Request $request,
        string $instance,
        string $homeFolder,
    ): JsonResponse {
        $this->denyUnlessHasFileManagerAccess();

        try {
            return parent::load($session, $httpKernel, $eventDispatcher, $request, $instance, $homeFolder);
        } catch (elFinderAbortException $ex) {
            return new JsonResponse([]);
        }
    }

    protected function denyUnlessHasFileManagerAccess(): void
    {
        $this->accessChecker->denyUnlessHasAnyRole(
            $this->roleRegistry->getRoleIdentifiersWithAnyPermission(AdminContext::class, [Permission::CREATE, Permission::EDIT]),
        );
    }
}
