<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Script\ScriptFacade;
use Symfony\Component\HttpFoundation\Response;

class ScriptController extends FrontBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Script\ScriptFacade
     */
    private $scriptFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Script\ScriptFacade $scriptFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ScriptFacade $scriptFacade,
        Domain $domain
    ) {
        $this->scriptFacade = $scriptFacade;
        $this->domain = $domain;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function embedAllPagesGoogleAnalyticsScriptAction(): Response
    {
        if (!$this->scriptFacade->isGoogleAnalyticsActivated($this->domain->getId())) {
            return new Response('');
        }

        return $this->render('Front/Inline/MeasuringScript/googleAnalytics.html.twig', [
            'trackingId' => $this->scriptFacade->getGoogleAnalyticsTrackingId($this->domain->getId()),
        ]);
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function embedOrderSentPageGoogleAnalyticsScriptAction(Order $order): Response
    {
        if (!$this->scriptFacade->isGoogleAnalyticsActivated($this->domain->getId())) {
            return new Response('');
        }

        return $this->render('Front/Inline/MeasuringScript/googleAnalyticsEcommerce.html.twig', [
            'order' => $order,
        ]);
    }
}
