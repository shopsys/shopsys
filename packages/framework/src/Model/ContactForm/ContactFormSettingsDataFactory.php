<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ContactForm;

class ContactFormSettingsDataFactory
{
    public function __construct(protected readonly ContactFormSettingsFacade $contactFormSettingsFacade)
    {
    }

    protected function createInstance(): ContactFormSettingsData
    {
        return new ContactFormSettingsData();
    }

    public function createFromSettingsByDomainId(int $domainId): ContactFormSettingsData
    {
        $contactFormSettingsData = $this->createInstance();
        $contactFormSettingsData->mainText = $this->contactFormSettingsFacade->getMainText($domainId);

        return $contactFormSettingsData;
    }
}
