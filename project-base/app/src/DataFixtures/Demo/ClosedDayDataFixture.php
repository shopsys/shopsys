<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayDataFactory;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;

class ClosedDayDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade $closedDayFacade
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayDataFactory $closedDayDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProvider $displayTimeZoneProvider
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly StoreFacade $storeFacade,
        private readonly ClosedDayFacade $closedDayFacade,
        private readonly ClosedDayDataFactory $closedDayDataFactory,
        private readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $store = $this->storeFacade->getStoresByDomainId($domainId)[0];

            foreach ($this->getClosedDays($domainConfig) as [$date, $name]) {
                $closedDayData = $this->closedDayDataFactory->create();
                $closedDayData->domainId = $domainId;
                $closedDayData->excludedStores = [$store];
                $closedDayData->date = $date;
                $closedDayData->name = $name;
                $this->closedDayFacade->create($closedDayData);
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return iterable<array{\DateTimeImmutable, string}>
     */
    private function getClosedDays(DomainConfig $domainConfig): iterable
    {
        $locale = $domainConfig->getLocale();
        $domainTimeZone = $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($domainConfig->getId());

        yield [
            DateTimeHelper::convertDateTimeFromTimezoneToUtc('24.12.' . date('Y'), $domainTimeZone),
            t('Christmas Eve', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
        ];

        yield [
            DateTimeHelper::convertDateTimeFromTimezoneToUtc('25.12.' . date('Y'), $domainTimeZone),
            t('Christmas Day', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
        ];

        yield [
            DateTimeHelper::convertDateTimeFromTimezoneToUtc('26.12.' . date('Y'), $domainTimeZone),
            t(' Second Christmas Day', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
        ];
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [StoreDataFixture::class];
    }
}
