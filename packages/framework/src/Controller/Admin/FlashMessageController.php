<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

class FlashMessageController extends AdminBaseController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('@ShopsysFramework/Admin/Inline/FlashMessage/index.html.twig', [
            'errorMessages' => $this->getErrorMessages(),
            'infoMessages' => $this->getInfoMessages(),
            'successMessages' => $this->getSuccessMessages(),
        ]);
    }
}
