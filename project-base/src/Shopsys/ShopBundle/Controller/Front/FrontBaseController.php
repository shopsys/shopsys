<?php

namespace Shopsys\ShopBundle\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FrontBaseController extends Controller
{
    public function getFlashMessageSender(): \Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageSender
    {
        return $this->get('shopsys.shop.component.flash_message.sender.front');
    }
}
