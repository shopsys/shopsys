<?php

declare(strict_types = 1);

namespace App\Model\Gtm;

use Symfony\Component\HttpKernel\Event\ControllerEvent;

class GtmListener
{
    /**
     * @var \App\Model\Gtm\GtmFacade
     */
    private $gtmFacade;

    /**
     * @param \App\Model\Gtm\GtmFacade $gtmFacade
     */
    public function __construct(GtmFacade $gtmFacade)
    {
        $this->gtmFacade = $gtmFacade;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event): void
    {
        if ($event->isMasterRequest()) {
            $routeName = $event->getRequest()->get('_route');
        } elseif ($event->getController()[0] instanceof \App\Controller\Front\ErrorController) {
            $routeName = 'front_error';
        } else {
            return;
        }

        if (!$this->isFrontRoute($routeName)) {
            return;
        }

        $this->gtmFacade->onAllFrontPages($routeName);
    }

    /**
     * @param string $routeName
     * @return bool
     */
    private function isFrontRoute(string $routeName): bool
    {
        return strpos($routeName, 'front_') === 0;
    }
}
