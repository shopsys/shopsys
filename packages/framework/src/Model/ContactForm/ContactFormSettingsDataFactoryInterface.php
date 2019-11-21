<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ContactForm;

interface ContactFormSettingsDataFactoryInterface
{
    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsData
     */
    public function createFromSettingsByDomainId(int $domainId): ContactFormSettingsData;
}
