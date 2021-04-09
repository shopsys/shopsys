<?php

declare(strict_types=1);

namespace App\Twig;

use Shopsys\FrameworkBundle\Twig\MailerSettingExtension as BaseMailerSettingExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

class MailerSettingExtension extends BaseMailerSettingExtension
{
    /**
     * @var bool
     */
    private bool $showMailRestrictionInfoBar;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param \Twig\Environment $twigEnvironment
     * @param bool $showMailRestrictionInfoBar
     */
    public function __construct(ContainerInterface $container, Environment $twigEnvironment, bool $showMailRestrictionInfoBar)
    {
        parent::__construct($container, $twigEnvironment);

        $this->showMailRestrictionInfoBar = $showMailRestrictionInfoBar;
    }

    public function isMailerSettingUnusual()
    {
        return parent::isMailerSettingUnusual() && $this->showMailRestrictionInfoBar;
    }
}
