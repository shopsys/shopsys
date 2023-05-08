<?php

declare(strict_types=1);

namespace App\Controller\Front;

class FlashMessageController extends FrontBaseController
{
    public function indexAction()
    {
        return $this->render('Front/Inline/FlashMessage/index.html.twig', [
            'errorMessages' => $this->getErrorMessages(),
            'infoMessages' => $this->getInfoMessages(),
            'successMessages' => $this->getSuccessMessages(),
        ]);
    }
}
