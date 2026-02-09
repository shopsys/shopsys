<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayDataFactory;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Symfony\Component\Clock\DatePoint;

class ClosedDayDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly StoreFacade $storeFacade,
        private readonly ClosedDayFacade $closedDayFacade,
        private readonly ClosedDayDataFactory $closedDayDataFactory,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $stores = $this->storeFacade->getStoresByDomainId($domainId);

            if (!array_key_exists(0, $stores)) {
                continue;
            }

            foreach ($this->getClosedDays($domainConfig) as [$date, $name, $isPublicHoliday]) {
                $closedDayData = $this->closedDayDataFactory->create();
                $closedDayData->domainId = $domainId;
                $closedDayData->excludedStores = [$stores[0]];
                $closedDayData->date = $date;
                $closedDayData->name = $name;
                $closedDayData->isPublicHoliday = $isPublicHoliday;
                $this->closedDayFacade->create($closedDayData);
            }
        }
    }

    /**
     * @return iterable<array{\DateTimeImmutable, string, bool}>
     */
    private function getClosedDays(DomainConfig $domainConfig): iterable
    {
        $locale = $domainConfig->getLocale();

        yield [
            new DatePoint('24.12.' . date('Y')),
            t('Christmas Eve', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            true,
        ];

        yield [
            new DatePoint('25.12.' . date('Y')),
            t('Christmas Day', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            true,
        ];

        yield [
            new DatePoint('26.12.' . date('Y')),
            t('Second Christmas Day', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            true,
        ];

        yield [
            new DatePoint('1 January next year'),
            t('New Year\'s Day', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            true,
        ];
    }

    /**
     * @return string[]
     */
    #[Override]
    public function getDependencies(): array
    {
        return [StoreDataFixture::class];
    }
}
