<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ContactForm;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class ContactFormSettingsFacade
{
    protected const string CONTACT_FORM_MAIN_TEXT = 'contactFormMainText';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(
        protected readonly Setting $setting,
        protected readonly Domain $domain,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getMainText(int $domainId): ?string
    {
        return $this->setting->getForDomain(static::CONTACT_FORM_MAIN_TEXT, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsData $contactFormSettingsData
     * @param int $domainId
     */
    public function editSettingsForDomain(ContactFormSettingsData $contactFormSettingsData, int $domainId): void
    {
        $this->setMainText($contactFormSettingsData->mainText, $domainId);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::SETTINGS_QUERY_KEY_PART);
    }

    /**
     * @param string|null $mainText
     * @param int $domainId
     */
    protected function setMainText(?string $mainText, int $domainId): void
    {
        $this->setting->setForDomain(static::CONTACT_FORM_MAIN_TEXT, $mainText, $domainId);
    }
}
