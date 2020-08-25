<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Component\Domain\Domain;
use App\Controller\Front\FrontBaseController as FrontFrontBaseController;
use App\Model\Gtm\GtmContainer;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Symfony\Component\HttpFoundation\Response;

class GtmController extends FrontFrontBaseController
{
    /**
     * @var \App\Model\Gtm\GtmContainer
     */
    private $gtmContainer;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Model\Gtm\GtmContainer $gtmContainer
     * @param \App\Component\Domain\Domain $domain
     */
    public function __construct(CurrencyFacade $currencyFacade, GtmContainer $gtmContainer, Domain $domain)
    {
        $this->gtmContainer = $gtmContainer;
        $this->domain = $domain;
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function headAction(): Response
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());

        return $this->render('/Front/Inline/MeasuringScript/Gtm/head.html.twig', [
            'currencyCode' => $currency->getCode(),
            'isGtmEnabled' => $this->gtmContainer->isEnabled(),
            'gtmContainerId' => $this->gtmContainer->getContainerId(),
            'gtmContainerEnvironment' => $this->gtmContainer->getContainerEnvironment(),
            'gtmDataLayer' => $this->gtmContainer->getDataLayer()->getData(),
            'gtmDataLayerPushes' => $this->gtmContainer->getDataLayer()->getPushes(),
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bodyAction(): Response
    {
        return $this->render('/Front/Inline/MeasuringScript/Gtm/body.html.twig', [
            'isGtmEnabled' => $this->gtmContainer->isEnabled(),
            'gtmContainerId' => $this->gtmContainer->getContainerId(),
            'gtmContainerEnvironment' => $this->gtmContainer->getContainerEnvironment(),
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pushesAction(): Response
    {
        return $this->render('/Front/Inline/MeasuringScript/Gtm/pushes.html.twig', [
            'isGtmEnabled' => $this->gtmContainer->isEnabled(),
            'gtmDataLayerPushes' => $this->gtmContainer->getDataLayer()->getPushes(),
        ]);
    }
}
