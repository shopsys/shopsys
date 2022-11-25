<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;

class FlashMessageController extends AdminBaseController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): Response
    {
        return $this->render('@ShopsysFramework/Admin/Inline/FlashMessage/index.html.twig', [
            'errorMessages' => $this->getErrorMessages(),
            'infoMessages' => $this->getInfoMessages(),
            'successMessages' => $this->getSuccessMessages(),
        ]);
    }
}
