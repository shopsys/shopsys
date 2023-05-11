<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Nette\Utils\Json;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Shopsys\FrameworkBundle\DependencyInjection\SetterInjectionTrait;
use Shopsys\FrameworkBundle\Model\Mail\Exception\MasterMailNotSetException;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;

class MailerSettingProvider
{
    use SetterInjectionTrait;

    /**
     * @deprecated This will be removed in next major version
     * @var string[]
     */
    protected array $mailerWhitelistExpressions;

    /**
     * @deprecated This will be removed in next major version
     */
    protected ?string $mailerMasterEmailAddress;

    protected bool $deliveryDisabled;

    /**
     * @param string $mailerWhitelist
     * @param string $mailerMasterEmailAddress
     * @param string $mailerDsn
     * @param bool|null $whitelistForced
     * @param \Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade|null $mailSettingFacade
     */
    public function __construct(
        string $mailerWhitelist,
        string $mailerMasterEmailAddress,
        string $mailerDsn,
        protected readonly ?bool $whitelistForced = null,
        protected /* readonly */ ?MailSettingFacade $mailSettingFacade = null,
    ) {
        if ($mailerWhitelist !== '') {
            DeprecationHelper::trigger('Property "$mailerWhitelist" is deprecated and should not be passed to "%s"', __METHOD__);
        }

        if ($mailerMasterEmailAddress !== '') {
            DeprecationHelper::trigger('Property "$mailerMasterEmailAddress" is deprecated and should not be passed to "%s"', __METHOD__);
        }

        $this->mailerWhitelistExpressions = $mailerWhitelist !== '' ? explode(',', $mailerWhitelist) : [];
        $this->mailerMasterEmailAddress = $mailerMasterEmailAddress !== '' ? $mailerMasterEmailAddress : null;
        $this->deliveryDisabled = $mailerDsn === Mailer::DISABLED_MAILER_DSN;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade $mailSettingFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setMailSettingFacade(MailSettingFacade $mailSettingFacade): void
    {
        $this->setDependency($mailSettingFacade, 'mailSettingFacade');
    }

    /**
     * @return string[]
     * @deprecated This method will be removed in next major version
     */
    public function getMailerWhitelistExpressions(): array
    {
        DeprecationHelper::triggerMethod(__METHOD__);

        return $this->mailerWhitelistExpressions;
    }

    /**
     * @return bool
     */
    public function isMailerWhitelistExpressionsSet(): bool
    {
        return $this->mailerWhitelistExpressions !== [];
    }

    /**
     * @param int $domainId
     * @return string[]
     */
    public function getWhitelistPatternsAsArray(int $domainId): array
    {
        $mailerWhitelistExpressions = $this->getMailerWhitelistExpressions();

        if ($mailerWhitelistExpressions !== []) {
            return $mailerWhitelistExpressions;
        }

        $mailWhitelist = $this->mailSettingFacade->getMailWhitelist($domainId);

        return $mailWhitelist !== null ? Json::decode($mailWhitelist, Json::FORCE_ARRAY) : [];
    }

    /**
     * @return bool
     */
    public function isWhitelistForced(): bool
    {
        return $this->whitelistForced ?? false;
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isWhitelistEnabled(int $domainId): bool
    {
        return $this->isMailerWhitelistExpressionsSet()
            || $this->isWhitelistForced()
            || $this->mailSettingFacade->isWhitelistEnabled($domainId);
    }

    /**
     * @return string
     * @deprecated This method will be removed in next major version
     */
    public function getMailerMasterEmailAddress(): string
    {
        DeprecationHelper::triggerMethod(__METHOD__);

        if ($this->isMailerMasterEmailSet() === false) {
            throw new MasterMailNotSetException();
        }

        return $this->mailerMasterEmailAddress;
    }

    /**
     * @return bool
     */
    public function isDeliveryDisabled(): bool
    {
        return $this->deliveryDisabled;
    }

    /**
     * @return bool
     * @deprecated This method will be removed in next major version
     */
    public function isMailerMasterEmailSet(): bool
    {
        DeprecationHelper::triggerMethod(__METHOD__);

        return $this->mailerMasterEmailAddress !== null && $this->mailerMasterEmailAddress !== '';
    }
}
