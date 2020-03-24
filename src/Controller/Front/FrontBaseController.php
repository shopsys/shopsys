<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FrontBaseController extends AbstractController
{
    use FlashMessageTrait;
}
