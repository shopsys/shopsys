<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting;
use Symfony\Component\HttpFoundation\Response;

class HeurekaController extends FrontBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade $heurekaFacade
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting $heurekaSetting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(private readonly HeurekaFacade $heurekaFacade, private readonly HeurekaSetting $heurekaSetting, private readonly Domain $domain)
    {
    }

    public function embedWidgetAction()
    {
        $domainId = $this->domain->getId();

        if (!$this->heurekaFacade->isHeurekaWidgetActivated($domainId)) {
            return new Response('');
        }

        return $this->render('Front/Content/Heureka/widget.html.twig', [
            'widgetCode' => $this->heurekaSetting->getHeurekaShopCertificationWidgetByDomainId($domainId),
        ]);
    }
}
