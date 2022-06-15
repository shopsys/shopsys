<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MailerSettingExtension extends AbstractExtension
{
    /**
     * @var \Twig\Environment
     */
    protected Environment $twigEnvironment;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider
     */
    protected MailerSettingProvider $mailerSettingProvider;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider $mailerSettingProvider
     * @param \Twig\Environment $twigEnvironment
     */
    public function __construct(
        MailerSettingProvider $mailerSettingProvider,
        Environment $twigEnvironment
    ) {
        $this->mailerSettingProvider = $mailerSettingProvider;
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
        return $this->mailerSettingProvider->isDeliveryDisabled() || $this->mailerSettingProvider->isMailerMasterEmailSet();
    }

    /**
     * @return string
     */
    public function getMailerSettingInfo()
    {
        return $this->twigEnvironment->render('@ShopsysFramework/Common/Mailer/settingInfo.html.twig', [
            'isDeliveryDisabled' => $this->mailerSettingProvider->isDeliveryDisabled(),
            'mailerMasterEmailAddress' => $this->mailerSettingProvider->isMailerMasterEmailSet() ? $this->mailerSettingProvider->getMailerMasterEmailAddress() : null,
            'mailerWhitelistExpressions' => $this->mailerSettingProvider->getMailerWhitelistExpressions(),
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
