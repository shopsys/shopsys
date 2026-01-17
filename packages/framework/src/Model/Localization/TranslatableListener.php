<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Localization;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Metadata\MetadataFactory;
use Override;
use Prezent\Doctrine\Translatable\EventListener\TranslatableListener as PrezentTranslatableListener;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class TranslatableListener extends PrezentTranslatableListener
{
    public function __construct(
        protected readonly Domain $domain,
        MetadataFactory $factory,
    ) {
        parent::__construct($factory);

        // set default locale to NULL
        // (currentLocale of entities should be set by request or stay NULL)
        // @phpstan-ignore-next-line
        $this->setCurrentLocale(null);
    }

    /**
     * @param string $currentLocale
     * @return \Shopsys\FrameworkBundle\Model\Localization\TranslatableListener
     *
     * The current locale must always be one of the domain's available locales (i.e., the persisted entity translations).
     * This ensures that the listener does not attempt to load translations in an unavailable locale.
     */
    #[Override]
    public function setCurrentLocale($currentLocale): self
    {
        if ($currentLocale !== null && !in_array($currentLocale, $this->domain->getAllLocales(), true)) {
            $currentLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        }

        /** @var \Shopsys\FrameworkBundle\Model\Localization\TranslatableListener $self */
        $self = parent::setCurrentLocale($currentLocale);

        return $self;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->postLoad($args);
    }
}
