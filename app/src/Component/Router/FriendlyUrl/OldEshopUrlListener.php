<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use App\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
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
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter
     */
    private CurrentDomainRouter $router;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter $router
     * @param \App\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        Domain $domain,
        CurrentDomainRouter $router,
        ProductFacade $productFacade
    ) {
        $this->productFacade = $productFacade;
        $this->domain = $domain;
        $this->router = $router;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event->getThrowable() instanceof NotFoundHttpException) {
            $pathInfo = $event->getRequest()->getPathInfo();

            if ($this->resolveProductOldUrlByPath($pathInfo, $event)) {
                return;
            }
            if ($this->resolveUrlRedirectByDotHtmlPattern($pathInfo, $event)) {
                return;
            }
        }
    }

    /**
     * @param string $pathInfo
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     * @return bool
     */
    private function resolveUrlRedirectByDotHtmlPattern(string $pathInfo, ExceptionEvent $event): bool
    {
        $matches = [];
        $pattern = '/^\/(?P<path>[^\.]+)\.html$/m';
        $results = preg_match_all($pattern, $pathInfo, $matches, PREG_SET_ORDER, 0);
        if ($results !== false && $results > 0) {
            $fullUrl = $this->domain->getUrl() . '/' . $matches[0]['path'];
            $event->setResponse(new RedirectResponse($fullUrl, 301));

            return true;
        }

        return false;
    }

    /**
     * @param string $pathInfo
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     * @return bool
     */
    private function resolveProductOldUrlByPath(string $pathInfo, ExceptionEvent $event): bool
    {
        if (strpos($pathInfo, 'artikel') === false) {
            return false;
        }

        $pathInfo = trim($pathInfo, '/');
        $urlParts = explode('/', $pathInfo);
        if (array_key_exists(1, $urlParts) === false) {
            return false;
        }

        $product = $this->productFacade->findByCatnum((string)$urlParts[1]);
        if ($product !== null) {
            $url = $this->router->generate('front_product_detail', ['id' => $product->getId()]);
            $event->setResponse(new RedirectResponse($url, 301));

            return true;
        }

        return false;
    }
}
