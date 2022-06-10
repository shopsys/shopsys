<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MailerSettingExtension extends AbstractExtension
{
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
     * @param string $mailerWhitelist
     * @param string $mailerMasterEmailAddress
     * @param string $mailerDsn
     * @param \Twig\Environment $twigEnvironment
     */
    public function __construct(
        string $mailerWhitelist,
        string $mailerMasterEmailAddress,
        string $mailerDsn,
        Environment $twigEnvironment
    ) {
        $this->mailerWhitelistExpressions = $mailerWhitelist !== '' ? explode(',', $mailerWhitelist) : [];
        $this->mailerMasterEmailAddress = $mailerMasterEmailAddress;
        $this->isDeliveryDisabled = $mailerDsn === Mailer::DISABLED_MAILER_DSN;

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
        return $this->isDeliveryDisabled || ($this->isDeliveryDisabled === false && $this->mailerMasterEmailAddress !== null);
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
