<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ContactForm;

class ContactFormSettingsDataFactory implements ContactFormSettingsDataFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade
     */
    protected $contactFormSettingsFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade $contactFormSettingsFacade
     */
    public function __construct(ContactFormSettingsFacade $contactFormSettingsFacade)
    {
        $this->contactFormSettingsFacade = $contactFormSettingsFacade;
    }

    /**
     * @inheritDoc
     */
    public function createFromSettingsByDomainId(int $domainId): ContactFormSettingsData
    {
        $contactFormSettingsData = new ContactFormSettingsData();
        $contactFormSettingsData->mainText = $this->contactFormSettingsFacade->getMainText($domainId);

        return $contactFormSettingsData;
    }
}
