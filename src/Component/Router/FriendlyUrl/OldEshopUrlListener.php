<?php

declare(strict_types=1);


namespace App\Component\Router\FriendlyUrl;

use App\Component\Domain\Domain;
use App\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OldEshopUrlListener
{
    /**
     * @var \App\Model\Product\ProductFacade
     */
    private ProductFacade $productFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private Domain $domain;
    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter
     */
    private CurrentDomainRouter $router;
    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlRepository
     */
    private FriendlyUrlRepository $friendlyUrlRepository;

    /**
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter $router
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     */
    public function __construct(
        Domain $domain,
        CurrentDomainRouter $router,
        ProductFacade $productFacade,
        FriendlyUrlRepository $friendlyUrlRepository
    ) {
        $this->productFacade = $productFacade;
        $this->domain = $domain;
        $this->router = $router;
        $this->friendlyUrlRepository = $friendlyUrlRepository;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event->getThrowable() instanceof NotFoundHttpException) {
            $pathInfo = $event->getRequest()->getPathInfo();
            $this->resolveProductOlrUrlByPatch($pathInfo, $event);
        }
    }

    /**
     * @param string $pathInfo
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    private function resolveProductOlrUrlByPatch(string $pathInfo, ExceptionEvent $event): void
    {
        if (strpos($pathInfo, 'article') === false) {
            return;
        }

        $pathInfo = trim($pathInfo, '/');
        $urlParts = explode('/', $pathInfo);
        if (array_key_exists(1, $urlParts) === false) {
            return;
        }

        $product = $this->productFacade->findByCatnum((string) $urlParts[1]);
        if ($product !== null) {
            $url = $this->router->generate('front_product_detail', ['id' => $product->getId()]);
            $event->setResponse(new RedirectResponse($url, 301));
        }
    }
}
