<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Store;

use App\DataFixtures\Demo\StoreDataFixture;
use DateTimeImmutable;
use DateTimeZone;
use Nette\Utils\Json;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayDataFactory;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreDataFactory;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Shopsys\FrameworkBundle\Model\Store\StoreFriendlyUrlProvider;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetStoreTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private StoreFacade $storeFacade;

    /**
     * @inject
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @inject
     */
    protected UrlGeneratorInterface $urlGenerator;

    /**
     * @inject
     */
    private StoreDataFactory $storeDataFactory;

    /**
     * @inject
     */
    private OpeningHoursDataFactory $openingHourDataFactory;

    /**
     * @inject
     */
    private ClosedDayDataFactory $closedDayDataFactory;

    /**
     * @inject
     */
    private ClosedDayFacade $closedDayFacade;

    /**
     * @inject
     */
    private DateTimeHelper $dateTimeHelper;

    private DateTimeImmutable $now;

    private DateTimeImmutable $today;

    public function testGetStoreByUuid(): void
    {
        foreach ($this->getStoreDataProvider() as $dataSet) {
            [$uuid, $expectedStoreData] = $dataSet;

            $graphQlType = 'store';
            $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/StoreQuery.graphql', [
                'uuid' => $uuid,
            ]);
            $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

            $this->assertKeysAreSameAsExpected(
                [
                    'name',
                    'slug',
                    'isDefault',
                    'description',
                    'street',
                    'city',
                    'postcode',
                    'country',
                    'specialMessage',
                    'locationLatitude',
                    'locationLongitude',
                    'breadcrumb',
                ],
                $responseData,
                $expectedStoreData,
            );
        }
    }

    /**
     * @group multidomain
     */
    public function testStoreOnSecondDomainIsNotAvailable(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Store\Store $storeOnSecondDomain */
        $storeOnSecondDomain = $this->getReference(StoreDataFixture::STORE_PREFIX . 3);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/StoreQuery.graphql', [
            'uuid' => $storeOnSecondDomain->getUuid(),
        ]);
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $response['errors'][0];
        self::assertArrayHasKey('message', $errors, Json::encode($errors));
        self::assertEquals(
            sprintf('Store with UUID "%s" does not exist.', $storeOnSecondDomain->getUuid()),
            $errors['message'],
        );

        $urlSlug = 'zilina';
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/StoreQuery.graphql', [
            'slug' => $urlSlug,
        ]);
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $response['errors'][0];
        self::assertArrayHasKey('message', $errors, Json::encode($errors));
        self::assertEquals(
            sprintf('Store with URL slug "%s" does not exist.', $urlSlug),
            $errors['message'],
        );
    }

    public function testGetStoreByUrlSlug(): void
    {
        foreach ($this->getStoreDataProviderByUrlSlug() as $dataSet) {
            [$urlSlug, $expectedStoreData] = $dataSet;

            $graphQlType = 'store';
            $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/StoreQuery.graphql', [
                'slug' => $urlSlug,
            ]);
            $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

            $this->assertKeysAreSameAsExpected(
                [
                    'name',
                    'slug',
                    'isDefault',
                    'description',
                    'street',
                    'city',
                    'postcode',
                    'country',
                    'specialMessage',
                    'locationLatitude',
                    'locationLongitude',
                    'breadcrumb',
                ],
                $responseData,
                $expectedStoreData,
            );
        }
    }

    /**
     * @dataProvider openingHoursDataProvider
     * @param string|null $firstOpeningTime
     * @param string|null $firstClosingTime
     * @param string|null $secondOpeningTime
     * @param string|null $secondClosingTime
     * @param \DateTimeImmutable|null $publicHolidayDate
     * @param array $publicHolidayExcludedStoresIds
     * @param bool $expectedIsOpen
     * @param string|null $expectedDaysFirstOpeningTime
     * @param string|null $expectedDaysFirstClosingTime
     * @param string|null $expectedDaysSecondOpeningTime
     * @param string|null $expectedDaysSecondClosingTime
     */
    public function testGetStoreOpeningHours(
        ?string $firstOpeningTime,
        ?string $firstClosingTime,
        ?string $secondOpeningTime,
        ?string $secondClosingTime,
        ?DateTimeImmutable $publicHolidayDate,
        array $publicHolidayExcludedStoresIds,
        bool $expectedIsOpen,
        ?string $expectedDaysFirstOpeningTime,
        ?string $expectedDaysFirstClosingTime,
        ?string $expectedDaysSecondOpeningTime,
        ?string $expectedDaysSecondClosingTime,
    ): void {
        $store = $this->updateStoreOpeningHours($firstOpeningTime, $firstClosingTime, $secondOpeningTime, $secondClosingTime);
        $dayOfWeek = $this->getCurrentDayOfWeek();

        if ($publicHolidayDate !== null) {
            $this->createClosedDay($publicHolidayDate, $publicHolidayExcludedStoresIds);
        }

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/StoreOpeningHoursQuery.graphql', [
            'uuid' => $store->getUuid(),
        ]);

        $expectedDays = $this->createExpectedOpeningDays($expectedDaysFirstOpeningTime, $expectedDaysFirstClosingTime, $expectedDaysSecondOpeningTime, $expectedDaysSecondClosingTime);

        self::assertArrayHasKey('data', $response);
        self::assertArrayHasKey('store', $response['data']);
        self::assertArrayHasKey('openingHours', $response['data']['store']);
        self::assertArrayHasKey('openingHoursOfDays', $response['data']['store']['openingHours']);
        self::assertArrayHasKey('isOpen', $response['data']['store']['openingHours']);
        self::assertEquals($expectedIsOpen, $response['data']['store']['openingHours']['isOpen']);
        self::assertEquals(
            array_merge($expectedDays, ['dayOfWeek' => $dayOfWeek]),
            $response['data']['store']['openingHours']['openingHoursOfDays'][$dayOfWeek - 1],
        );
    }

    /**
     * @return iterable
     */
    protected function openingHoursDataProvider(): iterable
    {
        yield 'store opened only forenoon' => [
            'firstOpeningTime' => '-1 hour',
            'firstClosingTime' => '+1 hour',
            'secondOpeningTime' => null,
            'secondClosingTime' => null,
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedIsOpen' => true,
            'expectedDaysFirstOpeningTime' => '-1 hour',
            'expectedDaysFirstClosingTime' => '+1 hour',
            'expectedDaysSecondOpeningTime' => null,
            'expectedDaysSecondClosingTime' => null,
        ];

        yield 'store opened only afternoon' => [
            'firstOpeningTime' => null,
            'firstClosingTime' => null,
            'secondOpeningTime' => '-1 hour',
            'secondClosingTime' => '+1 hour',
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedIsOpen' => true,
            'expectedDaysFirstOpeningTime' => null,
            'expectedDaysFirstClosingTime' => null,
            'expectedDaysSecondOpeningTime' => '-1 hour',
            'expectedDaysSecondClosingTime' => '+1 hour',
        ];

        yield 'store opened only forenoon and excluded from the public holiday' => [
            'firstOpeningTime' => '-1 hour',
            'firstClosingTime' => '+1 hour',
            'secondOpeningTime' => null,
            'secondClosingTime' => null,
            'publicHolidayDate' => $this->getToday(),
            'publicHolidayExcludedStoresIds' => [1],
            'expectedIsOpen' => true,
            'expectedDaysFirstOpeningTime' => '-1 hour',
            'expectedDaysFirstClosingTime' => '+1 hour',
            'expectedDaysSecondOpeningTime' => null,
            'expectedDaysSecondClosingTime' => null,
        ];

        yield 'store opened only afternoon and not excluded from the public holiday' => [
            'firstOpeningTime' => null,
            'firstClosingTime' => null,
            'secondOpeningTime' => '-1 hour',
            'secondClosingTime' => '+1 hour',
            'publicHolidayDate' => $this->getToday(),
            'publicHolidayExcludedStoresIds' => [],
            'expectedIsOpen' => false,
            'expectedDaysFirstOpeningTime' => null,
            'expectedDaysFirstClosingTime' => null,
            'expectedDaysSecondOpeningTime' => null,
            'expectedDaysSecondClosingTime' => null,
        ];

        yield 'store not opened at all' => [
            'firstOpeningTime' => null,
            'firstClosingTime' => null,
            'secondOpeningTime' => null,
            'secondClosingTime' => null,
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedIsOpen' => false,
            'expectedDaysFirstOpeningTime' => null,
            'expectedDaysFirstClosingTime' => null,
            'expectedDaysSecondOpeningTime' => null,
            'expectedDaysSecondClosingTime' => null,
        ];

        yield 'store opens in an hour (forenoon)' => [
            'firstOpeningTime' => '+1 hour',
            'firstClosingTime' => '+2 hour',
            'secondOpeningTime' => null,
            'secondClosingTime' => null,
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedIsOpen' => false,
            'expectedDaysFirstOpeningTime' => '+1 hour',
            'expectedDaysFirstClosingTime' => '+2 hour',
            'expectedDaysSecondOpeningTime' => null,
            'expectedDaysSecondClosingTime' => null,
        ];

        yield 'store opens in an hour (afternoon)' => [
            'firstOpeningTime' => null,
            'firstClosingTime' => null,
            'secondOpeningTime' => '+1 hour',
            'secondClosingTime' => '+2 hour',
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedIsOpen' => false,
            'expectedDaysFirstOpeningTime' => null,
            'expectedDaysFirstClosingTime' => null,
            'expectedDaysSecondOpeningTime' => '+1 hour',
            'expectedDaysSecondClosingTime' => '+2 hour',
        ];

        yield 'store closed an hour ago (forenoon)' => [
            'firstOpeningTime' => '-2 hour',
            'firstClosingTime' => '-1 hour',
            'secondOpeningTime' => null,
            'secondClosingTime' => null,
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedIsOpen' => false,
            'expectedDaysFirstOpeningTime' => '-2 hour',
            'expectedDaysFirstClosingTime' => '-1 hour',
            'expectedDaysSecondOpeningTime' => null,
            'expectedDaysSecondClosingTime' => null,
        ];

        yield 'store closed an hour ago (afternoon)' => [
            'firstOpeningTime' => null,
            'firstClosingTime' => null,
            'secondOpeningTime' => '-2 hour',
            'secondClosingTime' => '-1 hour',
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedIsOpen' => false,
            'expectedDaysFirstOpeningTime' => null,
            'expectedDaysFirstClosingTime' => null,
            'expectedDaysSecondOpeningTime' => '-2 hour',
            'expectedDaysSecondClosingTime' => '-1 hour',
        ];

        yield 'store with missing first closing time and missing second opening time' => [
            'firstOpeningTime' => '-1 hour',
            'firstClosingTime' => null,
            'secondOpeningTime' => null,
            'secondClosingTime' => '+1 hour',
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedIsOpen' => false,
            'expectedDaysFirstOpeningTime' => '-1 hour',
            'expectedDaysFirstClosingTime' => null,
            'expectedDaysSecondOpeningTime' => null,
            'expectedDaysSecondClosingTime' => '+1 hour',
        ];

        yield 'store with missing first opening time and missing second closing time' => [
            'firstOpeningTime' => null,
            'firstClosingTime' => '+1 hour',
            'secondOpeningTime' => '-1 hour',
            'secondClosingTime' => null,
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedIsOpen' => false,
            'expectedDaysFirstOpeningTime' => null,
            'expectedDaysFirstClosingTime' => '+1 hour',
            'expectedDaysSecondOpeningTime' => '-1 hour',
            'expectedDaysSecondClosingTime' => null,
        ];

        yield 'store that closes sooner then opens (forenoon)' => [
            'firstOpeningTime' => '+1 hour',
            'firstClosingTime' => '-1 hour',
            'secondOpeningTime' => null,
            'secondClosingTime' => null,
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedIsOpen' => false,
            'expectedDaysFirstOpeningTime' => '+1 hour',
            'expectedDaysFirstClosingTime' => '-1 hour',
            'expectedDaysSecondOpeningTime' => null,
            'expectedDaysSecondClosingTime' => null,
        ];

        yield 'store that closes sooner then opens (afternoon)' => [
            'firstOpeningTime' => null,
            'firstClosingTime' => null,
            'secondOpeningTime' => '+1 hour',
            'secondClosingTime' => '-1 hour',
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedIsOpen' => false,
            'expectedDaysFirstOpeningTime' => null,
            'expectedDaysFirstClosingTime' => null,
            'expectedDaysSecondOpeningTime' => '+1 hour',
            'expectedDaysSecondClosingTime' => '-1 hour',
        ];
    }

    /**
     * @param array $keys
     * @param array $actual
     * @param array $expected
     */
    private function assertKeysAreSameAsExpected(array $keys, array $actual, array $expected): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $actual);
            $this->assertSame($expected[$key], $actual[$key]);
        }
    }

    /**
     * @return array
     */
    private function getStoreDataProvider(): array
    {
        $data = [];
        $storeIds = [1, 2];

        foreach ($storeIds as $storeId) {
            $store = $this->storeFacade->getById($storeId);
            $data[] = [
                $store->getUuid(),
                $this->getExpectedStore($storeId),
            ];
        }

        return $data;
    }

    /**
     * @return array
     */
    private function getStoreDataProviderByUrlSlug(): array
    {
        $data = [];
        $urlSlugs = ['ostrava', 'pardubice'];

        foreach ($urlSlugs as $urlSlug) {
            $friendlyUrl = $this->friendlyUrlFacade->getFriendlyUrlByRouteNameAndSlug(
                Domain::FIRST_DOMAIN_ID,
                StoreFriendlyUrlProvider::ROUTE_NAME,
                $urlSlug,
            );

            $data[] = [
                $urlSlug,
                $this->getExpectedStore($friendlyUrl->getEntityId()),
            ];
        }

        return $data;
    }

    /**
     * @param int $storeId
     * @return array
     */
    private function getExpectedStore(int $storeId): array
    {
        $storesSlug = $this->urlGenerator->generate('front_stores');

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $data = [
            1 => [
                'name' => t('Ostrava', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'slug' => '/ostrava',
                'isDefault' => true,
                'description' => t('Store in Ostrava Přívoz', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'street' => 'Koksární 10',
                'city' => 'Ostrava',
                'postcode' => '70200',
                'country' => [
                    'code' => 'CZ',
                ],
                'contactInfo' => null,
                'specialMessage' => null,
                'locationLatitude' => '49.8574975',
                'locationLongitude' => '18.2738861',
                'breadcrumb' => [
                    [
                        'name' => t('Department stores', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $firstDomainLocale),
                        'slug' => $storesSlug,
                    ],
                    [
                        'name' => t('Ostrava', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        'slug' => $this->urlGenerator->generate('front_stores_detail', ['id' => 1]),
                    ],
                ],
            ],
            2 => [
                'name' => t('Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'slug' => '/pardubice',
                'isDefault' => false,
                'description' => t('Store v Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'street' => 'Bratranců Veverkových 2722',
                'city' => 'Pardubice',
                'postcode' => '53002',
                'country' => [
                    'code' => 'CZ',
                ],
                'contactInfo' => null,
                'specialMessage' => null,
                'locationLatitude' => '50.0346875',
                'locationLongitude' => '15.7707169',
                'breadcrumb' => [
                    [
                        'name' => t('Department stores', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $firstDomainLocale),
                        'slug' => $storesSlug,
                    ],
                    [
                        'name' => t('Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        'slug' => $this->urlGenerator->generate('front_stores_detail', ['id' => 2]),
                    ],
                ],
            ],
        ];

        return $data[$storeId];
    }

    /**
     * @return int
     */
    private function getCurrentDayOfWeek(): int
    {
        return (int)$this->getNow()->format('N');
    }

    /**
     * @param string $modifier
     * @return \DateTimeImmutable
     */
    private function createOpeningOrClosingHour(string $modifier): DateTimeImmutable
    {
        $now = $this->getNow()->setDate(1970, 1, 2);
        $hour = $now->modify($modifier);

        return $this->restrictDateTimeToCurrentDay($hour, $now);
    }

    /**
     * @param string|null $firstOpeningTime
     * @param string|null $firstClosingTime
     * @param string|null $secondOpeningTime
     * @param string|null $secondClosingTime
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
    private function updateStoreOpeningHours(
        ?string $firstOpeningTime,
        ?string $firstClosingTime,
        ?string $secondOpeningTime,
        ?string $secondClosingTime,
    ): Store {
        $store = $this->storeFacade->getAllStores()[0];
        $dayOfWeek = $this->getCurrentDayOfWeek();

        $storeData = $this->storeDataFactory->createFromStore($store);
        $storeData->openingHours = $this->openingHourDataFactory->createWeek();

        $openingOneDateTime = $firstOpeningTime ? $this->createOpeningOrClosingHour($firstOpeningTime) : null;
        $closingOneDateTime = $firstClosingTime ? $this->createOpeningOrClosingHour($firstClosingTime) : null;
        $openingTwoDateTime = $secondOpeningTime ? $this->createOpeningOrClosingHour($secondOpeningTime) : null;
        $closingTwoDateTime = $secondClosingTime ? $this->createOpeningOrClosingHour($secondClosingTime) : null;

        $openingHour = $storeData->openingHours[$dayOfWeek - 1];
        $openingHour->dayOfWeek = $dayOfWeek;
        $openingHour->firstOpeningTime = $openingOneDateTime?->format('H:i');
        $openingHour->firstClosingTime = $closingOneDateTime?->format('H:i');
        $openingHour->secondOpeningTime = $openingTwoDateTime?->format('H:i');
        $openingHour->secondClosingTime = $closingTwoDateTime?->format('H:i');

        return $this->storeFacade->edit($store->getId(), $storeData);
    }

    /**
     * @param \DateTimeImmutable $hour
     * @param \DateTimeImmutable $now
     * @return \DateTimeImmutable
     */
    private function restrictDateTimeToCurrentDay(DateTimeImmutable $hour, DateTimeImmutable $now): DateTimeImmutable
    {
        $result = $hour;

        if ($hour->format('j') < $now->format('j')) {
            $result = $result->setDate(1970, 1, 1);
            $result = $result->setTime(0, 0, 0);
        } elseif ($hour->format('j') > $now->format('j')) {
            $result = $result->setDate(1970, 1, 1);
            $result = $result->setTime(23, 59, 59);
        }

        return $result;
    }

    /**
     * @param string|null $firstOpeningTimeModifier
     * @param string|null $firstClosingTimeModifier
     * @param string|null $secondOpeningTimeModifier
     * @param string|null $secondClosingTimeModifier
     * @return array
     */
    private function createExpectedOpeningDays(
        ?string $firstOpeningTimeModifier,
        ?string $firstClosingTimeModifier,
        ?string $secondOpeningTimeModifier,
        ?string $secondClosingTimeModifier,
    ): array {
        return [
            'firstOpeningTime' => $this->getConvertedOpeningOrClosingTime($firstOpeningTimeModifier),
            'firstClosingTime' => $this->getConvertedOpeningOrClosingTime($firstClosingTimeModifier),
            'secondOpeningTime' => $this->getConvertedOpeningOrClosingTime($secondOpeningTimeModifier),
            'secondClosingTime' => $this->getConvertedOpeningOrClosingTime($secondClosingTimeModifier),
        ];
    }

    /**
     * @param \DateTimeImmutable $date
     * @param string[] $storesIds
     * @return \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay
     */
    private function createClosedDay(DateTimeImmutable $date, array $storesIds = []): ClosedDay
    {
        $closedDayData = $this->closedDayDataFactory->create();

        $closedDayData->domainId = $this->domain->getId();
        $closedDayData->name = '';
        $closedDayData->date = $date;
        $closedDayData->excludedStores = array_map(function (string $storeId): Store {
            /** @var \Shopsys\FrameworkBundle\Model\Store\Store $store */
            $store = $this->getReference(sprintf('%s%s', StoreDataFixture::STORE_PREFIX, $storeId));

            return $store;
        }, $storesIds);

        return $this->closedDayFacade->create($closedDayData);
    }

    /**
     * @return \DateTimeImmutable
     */
    private function getNow(): DateTimeImmutable
    {
        if (!isset($this->now)) {
            $this->now = DateTimeHelper::convertDateTimeFromTimezoneToUtc('now', new DateTimeZone('Europe/Prague'));
        }

        return $this->now;
    }

    /**
     * @return \DateTimeImmutable
     */
    private function getToday(): DateTimeImmutable
    {
        if (!isset($this->today)) {
            $this->today = DateTimeHelper::convertDateTimeFromTimezoneToUtc('today', new DateTimeZone('Europe/Prague'));
        }

        return $this->today;
    }

    /**
     * @param string|null $modifier
     * @return string|null
     */
    private function getConvertedOpeningOrClosingTime(?string $modifier): ?string
    {
        if ($modifier === null) {
            return null;
        }
        $hoursAndMinutes = $this->createOpeningOrClosingHour($modifier)->format('H:i');

        return $this->dateTimeHelper->convertHoursAndMinutesFromUtcToDisplayTimezone($hoursAndMinutes, Domain::FIRST_DOMAIN_ID);
    }
}
