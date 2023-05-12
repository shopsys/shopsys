<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ContactForm;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class ContactFormSettingsFacade
{
    protected const CONTACT_FORM_MAIN_TEXT = 'contactFormMainText';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(protected readonly Setting $setting, protected readonly Domain $domain)
    {
    }

    /**
     * @return string[]|null[]
     */
    public function getAllMainTextsIndexedByDomainId(): array
    {
        $mainTexts = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $mainTexts[$domainId] = $this->getMainText($domainId);
        }

        return $mainTexts;
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
