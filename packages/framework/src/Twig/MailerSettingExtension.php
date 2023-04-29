<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\DependencyInjection\SetterInjectionTrait;
use Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MailerSettingExtension extends AbstractExtension
{
    use SetterInjectionTrait;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider $mailerSettingProvider
     * @param \Twig\Environment $twigEnvironment
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain|null $domain
     */
    public function __construct(
        protected /* readonly */ MailerSettingProvider $mailerSettingProvider,
        protected /* readonly */ Environment $twigEnvironment,
        protected /* readonly */ ?Domain $domain = null,
    ) {
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setDomain(Domain $domain): void
    {
        $this->setDependency($domain, 'domain');
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
        return $this->mailerSettingProvider->isDeliveryDisabled()
            || $this->mailerSettingProvider->isMailerMasterEmailSet()
            || $this->mailerSettingProvider->isWhitelistEnabled($this->domain->getId());
    }

    /**
     * @return string
     */
    public function getMailerSettingInfo()
    {
        return $this->twigEnvironment->render('@ShopsysFramework/Common/Mailer/settingInfo.html.twig', [
            'isDeliveryDisabled' => $this->mailerSettingProvider->isDeliveryDisabled(),
            'mailerMasterEmailAddress' => $this->mailerSettingProvider->isMailerMasterEmailSet() ? $this->mailerSettingProvider->getMailerMasterEmailAddress() : null,
            'isWhitelistEnabled' => $this->mailerSettingProvider->isWhitelistEnabled($this->domain->getId()),
            'mailerWhitelistExpressions' => $this->mailerSettingProvider->getWhitelistPatternsAsArray($this->domain->getId()),
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
