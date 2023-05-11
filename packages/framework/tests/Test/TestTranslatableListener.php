<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use Metadata\MetadataFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade;
use Shopsys\FrameworkBundle\Model\Localization\TranslatableListener;

class TestTranslatableListener extends TranslatableListener
{
    protected Domain $domain;

    protected AdministrationFacade $administrationFacade;

    protected string $adminLocale;

    /**
     * @param \Metadata\MetadataFactory $factory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade $administrationFacade
     * @param string $adminLocale
     */
    public function __construct(MetadataFactory $factory, Domain $domain, AdministrationFacade $administrationFacade, string $adminLocale)
    {
        parent::__construct($factory);

        $this->domain = $domain;
        $this->administrationFacade = $administrationFacade;
        $this->adminLocale = $adminLocale;
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
