<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Symfony\Component\HttpFoundation\Response;

class FlashMessageController extends FrontBaseController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): Response
    {
        return $this->render('Front/Inline/FlashMessage/index.html.twig', [
            'errorMessages' => $this->getErrorMessages(),
            'infoMessages' => $this->getInfoMessages(),
            'successMessages' => $this->getSuccessMessages(),
        ]);
    }
}
