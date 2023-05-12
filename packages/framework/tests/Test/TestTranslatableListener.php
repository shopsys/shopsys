<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use Metadata\MetadataFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade;
use Shopsys\FrameworkBundle\Model\Localization\TranslatableListener;

class TestTranslatableListener extends TranslatableListener
{
    /**
     * @param \Metadata\MetadataFactory $factory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade $administrationFacade
     * @param string $adminLocale
     */
    public function __construct(MetadataFactory $factory, protected readonly Domain $domain, protected readonly AdministrationFacade $administrationFacade, protected readonly string $adminLocale)
    {
        parent::__construct($factory);
    }

    /**
     * @return string
     */
    public function getCurrentLocale()
    {
        if ($this->administrationFacade->isInAdmin()) {
            return $this->adminLocale;
        }

        return $this->getFirstDomainLocale();
    }

    /**
     * @return string
     */
    protected function getFirstDomainLocale(): string
    {
        return $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
    }
}
