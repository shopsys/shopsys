<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\FlashMessage\Bag;

class FlashMessageController extends AdminBaseController
{
    /** @var \Shopsys\FrameworkBundle\Component\FlashMessage\Bag */
    protected $flashMessageBag;

    /**
     * @param \Shopsys\FrameworkBundle\Component\FlashMessage\Bag $flashMessageBag
     */
    public function __construct(Bag $flashMessageBag)
    {
        $this->flashMessageBag = $flashMessageBag;
    }

    public function indexAction()
    {
        return $this->render('@ShopsysFramework/Admin/Inline/FlashMessage/index.html.twig', [
            'errorMessages' => $this->flashMessageBag->getErrorMessages(),
            'infoMessages' => $this->flashMessageBag->getInfoMessages(),
            'successMessages' => $this->flashMessageBag->getSuccessMessages(),
        ]);
    }
}
