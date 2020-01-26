<?php

namespace Shopsys\FrameworkBundle\Twig;

use Symfony\Component\Templating\EngineInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class MailerSettingExtension extends Twig_Extension
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
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templating;

    /**
     * @param \Symfony\Component\Templating\EngineInterface $templating
     */
    public function __construct(EngineInterface $templating)
    {
        $this->isDeliveryDisabled = (bool)getenv('MAILER_DISABLE_DELIVERY');
        $this->mailerMasterEmailAddress = getenv('MAILER_MASTER_EMAIL_ADDRESS');
        $this->mailerWhitelistExpressions = json_decode(getenv('MAILER_DELIVERY_WHITELIST'), true);
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
        return $this->templating->render('@ShopsysFramework/Common/Mailer/settingInfo.html.twig', [
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
