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
    protected bool $isImplicitLocaleDisabled = false;

    public function __construct(
        MetadataFactory $factory,
        Domain $domain,
        protected readonly Localization $localization,
        protected readonly ContextResolverInterface $contextResolver,
    ) {
        parent::__construct($domain, $factory);
    }

    /**
     * Makes entities behave as in a console process, where no request sets the current locale
     */
    public function disableImplicitLocale(): void
    {
        $this->isImplicitLocaleDisabled = true;
    }

    #[Override]
    public function getCurrentLocale(): ?string
    {
        if ($this->isImplicitLocaleDisabled) {
            return null;
        }

        if ($this->contextResolver->isCurrentContext(AdminContext::class)) {
            return $this->localization->getCurrentLocaleForTranslatableEntities();
        }

        try {
            return $this->domain->getLocale();
        } catch (NoDomainSelectedException) {
            return $this->getFirstDomainLocale();
        }
    }

    protected function getFirstDomainLocale(): string
    {
        return $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
    }
}
