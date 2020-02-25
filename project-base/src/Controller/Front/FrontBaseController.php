<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FrontBaseController extends AbstractController
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageSender
     */
    public function getFlashMessageSender()
    {
        /** @var \Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageSender $flashMessageSender */
        $flashMessageSender = $this->get('shopsys.shop.component.flash_message.sender.front');
        return $flashMessageSender;
    }
}
