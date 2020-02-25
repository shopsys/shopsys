<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminBaseController extends AbstractController
{
    use FlashMessageTrait;
}
