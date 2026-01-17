<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Symfony\Component\HttpFoundation\Response;

class FlashMessageController extends AdminBaseController
{
    #[RequireRole(SystemRole::ADMIN)]
    public function indexAction(): Response
    {
        return $this->render('@ShopsysAdministration/partial/flash_message/index.html.twig', [
            'errorMessages' => $this->getErrorMessages(),
            'infoMessages' => $this->getInfoMessages(),
            'successMessages' => $this->getSuccessMessages(),
            'warningMessages' => $this->getWarningMessages(),
        ]);
    }
}
