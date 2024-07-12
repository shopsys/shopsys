<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
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
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly StoreFacade $storeFacade,
        private readonly ClosedDayFacade $closedDayFacade,
        private readonly ClosedDayDataFactory $closedDayDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $stores = $this->storeFacade->getStoresByDomainId($domainId);

            if (!array_key_exists(0, $stores)) {
                continue;
            }

            foreach ($this->getClosedDays($domainConfig) as [$date, $name]) {
                $closedDayData = $this->closedDayDataFactory->create();
                $closedDayData->domainId = $domainId;
                $closedDayData->excludedStores = [$stores[0]];
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

        yield [
            new DateTimeImmutable('24.12.' . date('Y')),
            t('Christmas Eve', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
        ];

        yield [
            new DateTimeImmutable('25.12.' . date('Y')),
            t('Christmas Day', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
        ];

        yield [
            new DateTimeImmutable('26.12.' . date('Y')),
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
