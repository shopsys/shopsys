<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\ElFinder;

use elFinderAbortException;
use FM\ElfinderBundle\Controller\ElFinderController as BaseElFinderController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ElfinderController extends BaseElFinderController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $instance
     * @param string $homeFolder
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function load(SessionInterface $session, EventDispatcherInterface $eventDispatcher, Request $request, string $instance, string $homeFolder): JsonResponse
    {
        try {
            return parent::load($session, $eventDispatcher, $request, $instance, $homeFolder);
        } catch (elFinderAbortException $ex) {
            return $this->json([]);
        }
    }
}
