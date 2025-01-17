<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ContactForm;

class ContactFormSettingsDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade $contactFormSettingsFacade
     */
    public function __construct(protected readonly ContactFormSettingsFacade $contactFormSettingsFacade)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsData
     */
    protected function createInstance(): ContactFormSettingsData
    {
        return new ContactFormSettingsData();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsData
     */
    public function createFromSettingsByDomainId(int $domainId): ContactFormSettingsData
    {
        $contactFormSettingsData = $this->createInstance();
        $contactFormSettingsData->mainText = $this->contactFormSettingsFacade->getMainText($domainId);

        return $contactFormSettingsData;
    }
}
