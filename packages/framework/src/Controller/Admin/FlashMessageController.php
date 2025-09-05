<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Symfony\Component\HttpFoundation\Response;

class FlashMessageController extends AdminBaseController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[RequireRole(SystemRole::ADMIN)]
    public function indexAction(): Response
    {
        return $this->render('@ShopsysFramework/Admin/Inline/FlashMessage/index.html.twig', [
            'errorMessages' => $this->getErrorMessages(),
            'infoMessages' => $this->getInfoMessages(),
            'successMessages' => $this->getSuccessMessages(),
            'warningMessages' => $this->getWarningMessages(),
        ]);
    }
}
