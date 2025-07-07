<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use Metadata\MetadataFactory;
use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Localization\TranslatableListener;

class TestTranslatableListener extends TranslatableListener
{
    /**
     * @param \Metadata\MetadataFactory $factory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface $contextResolver
     */
    public function __construct(
        MetadataFactory $factory,
        Domain $domain,
        protected readonly Localization $localization,
        protected readonly ContextResolverInterface $contextResolver,
    ) {
        parent::__construct($domain, $factory);
    }

    /**
     * @return string
     */
    #[Override]
    public function getCurrentLocale()
    {
        if ($this->contextResolver->isCurrentContext(AdminContext::class)) {
            return $this->localization->getCurrentLocaleForTranslatableEntities();
        }

        try {
            return $this->domain->getLocale();
        } catch (NoDomainSelectedException) {
            return $this->getFirstDomainLocale();
        }
    }

    /**
     * @return string
     */
    protected function getFirstDomainLocale(): string
    {
        return $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
    }
}
