<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Store\ClosedDay\ClosedDayDataFactory;
use App\Model\Store\ClosedDay\ClosedDayFacade;
use App\Model\Store\StoreFacade;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

class ClosedDayDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Store\StoreFacade $storeFacade
     * @param \App\Model\Store\ClosedDay\ClosedDayFacade $closedDayFacade
     * @param \App\Model\Store\ClosedDay\ClosedDayDataFactory $closedDayDataFactory
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
            $locale = $domainConfig->getLocale();
            $store = $this->storeFacade->getStoresListEnabledOnDomain($domainId)[0];

            foreach ($this->getClosedDays($locale) as [$date, $name]) {
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
     * @param string $locale
     * @return iterable<array{\DateTime, string}>
     */
    private function getClosedDays(string $locale): iterable
    {
        yield [
            new DateTime('24.12.' . date('Y')),
            t('Christmas Eve', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
        ];

        yield [
            new DateTime('25.12.' . date('Y')),
            t('Christmas Day', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
        ];

        yield [
            new DateTime('26.12.' . date('Y')),
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
