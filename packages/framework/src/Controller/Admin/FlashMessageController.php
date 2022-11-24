<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

class FlashMessageController extends AdminBaseController
{
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('@ShopsysFramework/Admin/Inline/FlashMessage/index.html.twig', [
            'errorMessages' => $this->getErrorMessages(),
            'infoMessages' => $this->getInfoMessages(),
            'successMessages' => $this->getSuccessMessages(),
        ]);
    }
}
