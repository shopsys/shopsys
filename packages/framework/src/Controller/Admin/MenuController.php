<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade $administratorLocalizationFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly AdministratorLocalizationFacade $administratorLocalizationFacade,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[RequireRole(SystemRole::ADMIN)]
    public function menuAction(): Response
    {
        return $this->render('@ShopsysFramework/Admin/Inline/Menu/menu.html.twig', [
            'domainConfigs' => $this->domain->getAll(),
            'allowedLocales' => $this->administratorLocalizationFacade->getAllowedAdminLocales(),
        ]);
    }
}
