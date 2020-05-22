<?php

declare(strict_types=1);

namespace App\Component\Router\CategorySeoMix;

use App\Model\CategorySeo\Exception\UnableToFindReadyCategorySeoMixException;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CategorySeoMixFriendlyUrlListener
{
    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixFacade
     */
    private $readyCategorySeoMixFacade;

    /**
     * @var \App\Component\Router\CategorySeoMix\CategorySeoMixUrlGenerator
     */
    private $categorySeoMixUrlGenerator;

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \App\Component\Router\CategorySeoMix\CategorySeoMixUrlGenerator $categorySeoMixUrlGenerator
     */
    public function __construct(
        ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        CategorySeoMixUrlGenerator $categorySeoMixUrlGenerator
    ) {
        $this->readyCategorySeoMixFacade = $readyCategorySeoMixFacade;
        $this->categorySeoMixUrlGenerator = $categorySeoMixUrlGenerator;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (!$event->isMasterRequest() || $request->isXmlHttpRequest()) {
            return;
        }

        if ($request->attributes->has('readyCategorySeoMixId')) {
            $this->addReadyCategorySeoMixValuesToQuery($request);
        }

        if ($request->attributes->get('_controller') === RedirectController::class . '::redirectAction'
            && $request->attributes->get('_route') === 'front_category_seo'
        ) {
            $this->keepFrontCategorySeoQueryParametersForRedirectAction($request);
        }

        $this->checkAndRedirectProductListToCategorySeoMixUrl($event);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    private function addReadyCategorySeoMixValuesToQuery(Request $request): void
    {
        $queryArray = $request->query->all();

        $readyCategorySeoMixId = $request->attributes->get('readyCategorySeoMixId');

        $readyCategorySeoMix = $this->readyCategorySeoMixFacade->getById($readyCategorySeoMixId);
        foreach ($readyCategorySeoMix->getReadyCategorySeoMixParameterParameterValues() as $readyCategorySeoMixParameterParameterValue) {
            $parameterId = $readyCategorySeoMixParameterParameterValue->getParameter()->getId();
            $parameterValueId = $readyCategorySeoMixParameterParameterValue->getParameterValue()->getId();

            if (!isset($queryArray['product_filter_form']['parameters'][$parameterId])) {
                $queryArray['product_filter_form']['parameters'][$parameterId] = [];
            }

            $queryArray['product_filter_form']['parameters'][$parameterId][] = $parameterValueId;
        }

        if ($readyCategorySeoMix->getFlag() !== null) {
            $queryArray['product_filter_form']['flags'] = [];
            $queryArray['product_filter_form']['flags'][] = $readyCategorySeoMix->getFlag()->getId();
        }

        $request->query->replace($queryArray);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    private function keepFrontCategorySeoQueryParametersForRedirectAction(Request $request): void
    {
        $attributeArray = $request->attributes->all();

        $attributeArray['_route_params'] = array_merge(
            $attributeArray['_route_params'],
            $request->query->all()
        );

        $request->attributes->replace($attributeArray);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     */
    private function checkAndRedirectProductListToCategorySeoMixUrl(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->attributes->get('_route') === 'front_product_list'
            && $request->attributes->has('readyCategorySeoMixId') === false
            && $request->isXmlHttpRequest() === false
        ) {
            try {
                $url = $this->categorySeoMixUrlGenerator->tryGenerateCategorySeoMixUrl(
                    $request->attributes->get('id'),
                    $request->query->all(),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $event->setResponse(new RedirectResponse($url, 301));
            } catch (UnableToFindReadyCategorySeoMixException $exception) {
                // It is okay, current url is common product_list without CategorySeoMix
            }
        }
    }
}
