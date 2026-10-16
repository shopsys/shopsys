<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\ElFinder;

use elFinderAbortException;
use FM\ElfinderBundle\Controller\ElFinderController as BaseElFinderController;
use FM\ElfinderBundle\Loader\ElFinderLoaderInterface;
use Override;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\FileManagerAccessChecker;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
        protected readonly FileManagerAccessChecker $fileManagerAccessChecker,
    ) {
        parent::__construct($twig, $params, $loader);
    }

    #[Override]
    public function show(Request $request, string $instance, string $homeFolder): Response
    {
        $this->denyAccessUnlessGranted();

        return parent::show($request, $instance, $homeFolder);
    }

    #[Override]
    public function mainJS(): Response
    {
        $this->denyAccessUnlessGranted();

        return parent::mainJS();
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
        $this->denyAccessUnlessGranted();

        try {
            return parent::load($session, $httpKernel, $eventDispatcher, $request, $instance, $homeFolder);
        } catch (elFinderAbortException $ex) {
            return new JsonResponse([]);
        }
    }

    protected function denyAccessUnlessGranted(): void
    {
        if (!$this->fileManagerAccessChecker->hasAccess()) {
            throw new AccessDeniedException('Access to the file manager requires permission to create or edit.');
        }
    }
}
