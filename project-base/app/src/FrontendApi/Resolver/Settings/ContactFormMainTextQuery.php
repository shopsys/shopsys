<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ContactFormMainTextQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade $contactFormSettingsFacade
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly ContactFormSettingsFacade $contactFormSettingsFacade,
    ) {
    }

    /**
     * @return string
     */
    public function contactFormMainTextQuery(): string
    {
        return $this->contactFormSettingsFacade->getMainText($this->domain->getId());
    }
}
