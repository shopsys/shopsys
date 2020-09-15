<?php

declare(strict_types=1);


namespace App\Component\Router\FriendlyUrl;

use App\Component\Domain\Domain;
use App\Model\Product\ProductFacade;
use App\Model\UrlRedirect\UrlRedirectFacade;
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
     * @var \App\Model\UrlRedirect\UrlRedirectFacade
     */
    private UrlRedirectFacade $urlRedirectFacade;

    /**
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter $router
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \App\Model\UrlRedirect\UrlRedirectFacade $urlRedirectFacade
     */
    public function __construct(
        Domain $domain,
        CurrentDomainRouter $router,
        ProductFacade $productFacade,
        FriendlyUrlRepository $friendlyUrlRepository,
        UrlRedirectFacade $urlRedirectFacade
    ) {
        $this->productFacade = $productFacade;
        $this->domain = $domain;
        $this->router = $router;
        $this->friendlyUrlRepository = $friendlyUrlRepository;
        $this->urlRedirectFacade = $urlRedirectFacade;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event->getThrowable() instanceof NotFoundHttpException) {
            $pathInfo = $event->getRequest()->getPathInfo();
            $this->resolveProductOldUrlByPath($pathInfo, $event);
            $this->resolveUrlRedirectByMatchingTable($pathInfo, $event);
            $this->resolveUrlRedirectByPattern($pathInfo, $event);
        }
    }

    /**
     * @param string $pathInfo
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    private function resolveUrlRedirectByPattern(string $pathInfo, ExceptionEvent $event): void
    {
        $matches = [];
        $pattern = '/^\/(?P<path>[^\.]+)\.html$/m';
        $results = preg_match_all($pattern, $pathInfo, $matches, PREG_SET_ORDER, 0);
        if ($results !== false && $results > 0) {
            $fullUrl = $this->domain->getUrl() . '/' . $matches[0]['path'];
            $event->setResponse(new RedirectResponse($fullUrl, 301));
        }
    }

    /**
     * @param string $pathInfo
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    private function resolveUrlRedirectByMatchingTable(string $pathInfo, ExceptionEvent $event): void
    {
        $urlRedirect = $this->urlRedirectFacade->findByOldUrlAndDomainId(ltrim($pathInfo, '/'), $this->domain->getId());
        if ($urlRedirect !== null) {
            $fullUrl = $this->domain->getUrl() . '/' . $urlRedirect->getNewUrl();
            $event->setResponse(new RedirectResponse($fullUrl, 301));
        }
    }

    /**
     * @param string $pathInfo
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    private function resolveProductOldUrlByPath(string $pathInfo, ExceptionEvent $event): void
    {
        if (strpos($pathInfo, 'artikel') === false) {
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
