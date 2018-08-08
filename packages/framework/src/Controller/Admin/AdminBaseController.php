<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminBaseController extends Controller
{
    public function getFlashMessageSender(): \Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageSender
    {
        return $this->get('shopsys.shop.component.flash_message.sender.admin');
    }
}
