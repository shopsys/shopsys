<?php

namespace Shopsys\FrameworkBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MailerSettingExtension extends AbstractExtension
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var bool
     */
    protected $isDeliveryDisabled;

    /**
     * @var string
     */
    protected $mailerMasterEmailAddress;

    /**
     * @var string[]
     */
    protected $mailerWhitelistExpressions;

    /**
     * @var \Twig\Environment
     */
    protected $twigEnvironment;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param \Twig\Environment $twigEnvironment
     */
    public function __construct(ContainerInterface $container, Environment $twigEnvironment)
    {
        $this->container = $container;
        $this->isDeliveryDisabled = $this->container->getParameter('mailer_disable_delivery');
        $this->mailerMasterEmailAddress = $this->container->getParameter('mailer_master_email_address');
        $this->mailerWhitelistExpressions = $this->container->getParameter('mailer_delivery_whitelist');
        $this->twigEnvironment = $twigEnvironment;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('isMailerSettingUnusual', [$this, 'isMailerSettingUnusual']),
            new TwigFunction('getMailerSettingInfo', [$this, 'getMailerSettingInfo'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return bool
     */
    public function isMailerSettingUnusual()
    {
        return $this->isDeliveryDisabled || (!$this->isDeliveryDisabled && $this->mailerMasterEmailAddress !== null);
    }

    /**
     * @return string
     */
    public function getMailerSettingInfo()
    {
        return $this->twigEnvironment->render('@ShopsysFramework/Common/Mailer/settingInfo.html.twig', [
            'isDeliveryDisabled' => $this->isDeliveryDisabled,
            'mailerMasterEmailAddress' => $this->mailerMasterEmailAddress,
            'mailerWhitelistExpressions' => $this->mailerWhitelistExpressions,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'shopsys.twig.mailer_setting_extension';
    }
}
