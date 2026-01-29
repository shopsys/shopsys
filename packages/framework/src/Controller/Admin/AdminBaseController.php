<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

class AdminBaseController extends AbstractController
{
    use FlashMessageTrait;

    protected Environment $twigEnvironment;

    /**
     * @param \Twig\Environment $twigEnvironment
     */
    #[Required]
    public function setTwigEnvironment(Environment $twigEnvironment): void
    {
        $this->twigEnvironment = $twigEnvironment;
    }
}
