<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Component\FlashMessage\Bag;

class FlashMessageController extends FrontBaseController
{
    /** @var \Shopsys\FrameworkBundle\Component\FlashMessage\Bag */
    private $flashMessageBag;

    /**
     * @param \Shopsys\FrameworkBundle\Component\FlashMessage\Bag $flashMessageBag
     */
    public function __construct(Bag $flashMessageBag)
    {
        $this->flashMessageBag = $flashMessageBag;
    }

    public function indexAction()
    {
        return $this->render('Front/Inline/FlashMessage/index.html.twig', [
            'errorMessages' => $this->flashMessageBag->getErrorMessages(),
            'infoMessages' => $this->flashMessageBag->getInfoMessages(),
            'successMessages' => $this->flashMessageBag->getSuccessMessages(),
        ]);
    }
}
