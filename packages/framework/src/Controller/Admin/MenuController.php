<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\DomainFacade;

class MenuController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\DomainFacade $domainFacade
     */
    public function __construct(protected readonly DomainFacade $domainFacade)
    {
    }

    public function menuAction()
    {
        return $this->render('@ShopsysFramework/Admin/Inline/Menu/menu.html.twig', [
            'domainConfigs' => $this->domainFacade->getAllDomainConfigs(),
        ]);
    }
}
