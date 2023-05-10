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
     * @return \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsData
     */
    protected function createInstance(): ContactFormSettingsData
    {
        return new ContactFormSettingsData();
    }

    /**
     * {@inheritdoc}
     */
    public function createFromSettingsByDomainId(int $domainId): ContactFormSettingsData
    {
        $contactFormSettingsData = $this->createInstance();
        $contactFormSettingsData->mainText = $this->contactFormSettingsFacade->getMainText($domainId);

        return $contactFormSettingsData;
    }
}
