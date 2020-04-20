<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class ReadyCategorySeoMixFriendlyUrlListener
{
    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixFacade
     */
    private $readyCategorySeoMixFacade;

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     */
    public function __construct(ReadyCategorySeoMixFacade $readyCategorySeoMixFacade)
    {
        $this->readyCategorySeoMixFacade = $readyCategorySeoMixFacade;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (!$event->isMasterRequest()) {
            return;
        }

        if ($request->attributes->has('readyCategorySeoMixId')) {
            $this->addReadyCategorySeoMixValuesToQuery($request);
        }

        if ($request->attributes->get('_controller')
            === 'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::redirectAction'
            && $request->attributes->get('_route') === 'front_category_seo'
        ) {
            $this->keepFrontCategorySeoQueryParametersForRedirectAction($request);
        }
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
}
