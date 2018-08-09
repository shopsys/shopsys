<?php

namespace Shopsys\FrameworkBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Templating\EngineInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class MailerSettingExtension extends Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var bool
     */
    private $isDeliveryDisabled;

    /**
     * @var string
     */
    private $mailerMasterEmailAddress;

    /**
     * @var string[]
     */
    private $mailerWhitelistExpressions;

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    private $templating;

    public function __construct(ContainerInterface $container, EngineInterface $templating)
    {
        $this->container = $container;
        $this->isDeliveryDisabled = $this->container->getParameter('mailer_disable_delivery');
        $this->mailerMasterEmailAddress = $this->container->getParameter('mailer_master_email_address');
        $this->mailerWhitelistExpressions = $this->container->getParameter('mailer_delivery_whitelist');
        $this->templating = $templating;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('isMailerSettingUnusual', [$this, 'isMailerSettingUnusual']),
            new Twig_SimpleFunction('getMailerSettingInfo', [$this, 'getMailerSettingInfo'], ['is_safe' => ['html']]),
        ];
    }

    public function isMailerSettingUnusual()
    {
        return $this->isDeliveryDisabled || (!$this->isDeliveryDisabled && $this->mailerMasterEmailAddress !== null);
    }

    public function getMailerSettingInfo()
    {
        return $this->templating->render('@ShopsysFramework/Common/Mailer/settingInfo.html.twig', [
            'isDeliveryDisabled' => $this->isDeliveryDisabled,
            'mailerMasterEmailAddress' => $this->mailerMasterEmailAddress,
            'mailerWhitelistExpressions' => $this->mailerWhitelistExpressions,
        ]);
    }

    public function getName()
    {
        return 'shopsys.twig.mailer_setting_extension';
    }
}
