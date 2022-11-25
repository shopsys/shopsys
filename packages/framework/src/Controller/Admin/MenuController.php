<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\DomainFacade;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends AdminBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\DomainFacade
     */
    protected $domainFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\DomainFacade $domainFacade
     */
    public function __construct(DomainFacade $domainFacade)
    {
        $this->domainFacade = $domainFacade;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function menuAction(): Response
    {
        return $this->render('@ShopsysFramework/Admin/Inline/Menu/menu.html.twig', [
            'domainConfigs' => $this->domainFacade->getAllDomainConfigs(),
        ]);
    }
}
