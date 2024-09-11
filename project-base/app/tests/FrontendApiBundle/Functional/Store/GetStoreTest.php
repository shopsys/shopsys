<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Store;

use App\DataFixtures\Demo\StoreDataFixture;
use DateTimeImmutable;
use DateTimeZone;
use Nette\Utils\Json;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayDataFactory;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeDataFactory;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreDataFactory;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Shopsys\FrameworkBundle\Model\Store\StoreFriendlyUrlProvider;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrontendApiBundle\Model\Store\OpeningHours\StoreOpeningTypeEnum;
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
    private OpeningHoursRangeDataFactory $openingHoursRangeDataFactory;

    private DateTimeImmutable $now;

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
                    'latitude',
                    'longitude',
                    'breadcrumb',
                ],
                $responseData,
                $expectedStoreData,
            );
        }
    }

    #[Group('multidomain')]
    public function testStoreOnSecondDomainIsNotAvailable(): void
    {
        $storeOnSecondDomain = $this->getReference(StoreDataFixture::STORE_PREFIX . 9, Store::class);

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
                    'latitude',
                    'longitude',
                    'breadcrumb',
                ],
                $responseData,
                $expectedStoreData,
            );
        }
    }

    /**
     * @param array $openingRangesModifiers
     * @param \DateTimeImmutable|null $publicHolidayDate
     * @param array $publicHolidayExcludedStoresIds
     * @param string $expectedStatus
     * @param array $expectedOpeningRangesModifiers
     */
    #[DataProvider('openingHoursDataProvider')]
    public function testGetStoreOpeningHours(
        array $openingRangesModifiers,
        ?DateTimeImmutable $publicHolidayDate,
        array $publicHolidayExcludedStoresIds,
        string $expectedStatus,
        array $expectedOpeningRangesModifiers,
    ): void {
        $store = $this->updateStoreOpeningHours($openingRangesModifiers);
        $dayOfWeek = $this->getCurrentDayOfWeek();

        if ($publicHolidayDate !== null) {
            $this->createClosedDay($publicHolidayDate, $publicHolidayExcludedStoresIds);
        }

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/StoreOpeningHoursQuery.graphql', [
            'uuid' => $store->getUuid(),
        ]);

        $expectedOpeningRanges = $this->createExpectedOpeningRanges($expectedOpeningRangesModifiers);

        self::assertArrayHasKey('data', $response);
        self::assertArrayHasKey('store', $response['data']);
        self::assertArrayHasKey('openingHours', $response['data']['store']);
        self::assertArrayHasKey('openingHoursOfDays', $response['data']['store']['openingHours']);
        self::assertArrayHasKey('status', $response['data']['store']['openingHours']);
        self::assertEquals($expectedStatus, $response['data']['store']['openingHours']['status']);
        self::assertEquals(
            array_merge($expectedOpeningRanges, ['dayOfWeek' => $dayOfWeek]),
            $response['data']['store']['openingHours']['openingHoursOfDays'][0], // today is always first
        );
    }

    /**
     * @return iterable
     */
    public static function openingHoursDataProvider(): iterable
    {
        yield 'store with one opening range' => [
            'openingRangesModifiers' => [
                ['openingTime' => '-2 hour', 'closingTime' => '+2 hour'],
            ],
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedStatus' => StoreOpeningTypeEnum::STATUS_OPEN,
            'expectedOpeningRangesModifiers' => [
                ['openingTime' => '-2 hour', 'closingTime' => '+2 hour'],
            ],
        ];

        yield 'store excluded from the public holiday' => [
            'openingRangesModifiers' => [
                ['openingTime' => '-1 hour', 'closingTime' => '+1 hour'],
            ],
            'publicHolidayDate' => static::getToday(),
            'publicHolidayExcludedStoresIds' => [1],
            'expectedStatus' => StoreOpeningTypeEnum::STATUS_CLOSED_SOON,
            'expectedOpeningRangesModifiers' => [
                ['openingTime' => '-1 hour', 'closingTime' => '+1 hour'],
            ],
        ];

        yield 'store not excluded from the public holiday' => [
            'openingRangesModifiers' => [
                ['openingTime' => '-1 hour', 'closingTime' => '+1 hour'],
            ],
            'publicHolidayDate' => static::getToday(),
            'publicHolidayExcludedStoresIds' => [],
            'expectedStatus' => StoreOpeningTypeEnum::STATUS_CLOSED,
            'expectedOpeningRangesModifiers' => [],
        ];

        yield 'store not opened at all' => [
            'openingRangesModifiers' => [],
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedStatus' => StoreOpeningTypeEnum::STATUS_CLOSED,
            'expectedOpeningRangesModifiers' => [],
        ];

        yield 'store opens in an hour' => [
            'openingRangesModifiers' => [
                ['openingTime' => '+1 hour', 'closingTime' => '+2 hour'],
            ],
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedStatus' => StoreOpeningTypeEnum::STATUS_OPEN_SOON,
            'expectedOpeningRangesModifiers' => [
                ['openingTime' => '+1 hour', 'closingTime' => '+2 hour'],
            ],
        ];

        yield 'store closed an hour ago' => [
            'openingRangesModifiers' => [
                ['openingTime' => '-2 hour', 'closingTime' => '-1 hour'],
            ],
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedStatus' => StoreOpeningTypeEnum::STATUS_CLOSED,
            'expectedOpeningRangesModifiers' => [
                ['openingTime' => '-2 hour', 'closingTime' => '-1 hour'],
            ],
        ];

        yield 'store that closes sooner then opens' => [
            'openingRangesModifiers' => [
                ['openingTime' => '+1 hour', 'closingTime' => '-1 hour'],
            ],
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedStatus' => StoreOpeningTypeEnum::STATUS_OPEN_SOON,
            'expectedOpeningRangesModifiers' => [
                ['openingTime' => '+1 hour', 'closingTime' => '-1 hour'],
            ],
        ];

        yield 'closed store with multiple opening ranges' => [
            'openingRangesModifiers' => [
                ['openingTime' => '-5 hour', 'closingTime' => '-4 hour'],
                ['openingTime' => '-2 hour', 'closingTime' => '-1 hour'],
                ['openingTime' => '+1 hour', 'closingTime' => '+2 hour'],
            ],
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedStatus' => StoreOpeningTypeEnum::STATUS_OPEN_SOON,
            'expectedOpeningRangesModifiers' => [
                ['openingTime' => '-5 hour', 'closingTime' => '-4 hour'],
                ['openingTime' => '-2 hour', 'closingTime' => '-1 hour'],
                ['openingTime' => '+1 hour', 'closingTime' => '+2 hour'],
            ],
        ];

        yield 'open store with multiple opening ranges' => [
            'openingRangesModifiers' => [
                ['openingTime' => '-5 hour', 'closingTime' => '-4 hour'],
                ['openingTime' => '-2 hour', 'closingTime' => '+1 hour'],
                ['openingTime' => '+2 hour', 'closingTime' => '+3 hour'],
            ],
            'publicHolidayDate' => null,
            'publicHolidayExcludedStoresIds' => [],
            'expectedStatus' => StoreOpeningTypeEnum::STATUS_CLOSED_SOON,
            'expectedOpeningRangesModifiers' => [
                ['openingTime' => '-5 hour', 'closingTime' => '-4 hour'],
                ['openingTime' => '-2 hour', 'closingTime' => '+1 hour'],
                ['openingTime' => '+2 hour', 'closingTime' => '+3 hour'],
            ],
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
                'latitude' => '49.8574975',
                'longitude' => '18.2738861',
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
                'latitude' => '50.0346875',
                'longitude' => '15.7707169',
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
     * @param array $openingRangesModifiers
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
    private function updateStoreOpeningHours(
        array $openingRangesModifiers,
    ): Store {
        $store = $this->storeFacade->getStoresByDomainId(Domain::FIRST_DOMAIN_ID)[0];
        $dayOfWeek = $this->getCurrentDayOfWeek();

        $storeData = $this->storeDataFactory->createFromStore($store);
        $storeData->openingHours = $this->openingHourDataFactory->createWeek();

        $openingHoursData = $this->openingHourDataFactory->createForDayOfWeek($dayOfWeek);

        foreach ($openingRangesModifiers as $modifier) {
            $openingHoursData->openingHoursRanges[] = $this->openingHoursRangeDataFactory->create(
                $this->createOpeningOrClosingHour($modifier['openingTime'])->format('H:i'),
                $this->createOpeningOrClosingHour($modifier['closingTime'])->format('H:i'),
            );
        }

        $storeData->openingHours = [$openingHoursData];

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
     * @param array $openingHoursModifiers
     * @return array
     */
    private function createExpectedOpeningRanges(
        array $openingHoursModifiers,
    ): array {
        $expectedRanges = [];

        foreach ($openingHoursModifiers as $modifier) {
            $expectedRanges[] = [
                'openingTime' => $this->getFormattedTime($modifier['openingTime']),
                'closingTime' => $this->getFormattedTime($modifier['closingTime']),
            ];
        }

        return ['openingHoursRanges' => $expectedRanges];
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
            $store = $this->getReference(sprintf('%s%s', StoreDataFixture::STORE_PREFIX, $storeId), Store::class);

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
            $this->now = new DateTimeImmutable('now', new DateTimeZone('Europe/Prague'));
        }

        return $this->now;
    }

    /**
     * @return \DateTimeImmutable
     */
    protected static function getToday(): DateTimeImmutable
    {
        return new DateTimeImmutable('today', new DateTimeZone('Europe/Prague'));
    }

    /**
     * @param string|null $modifier
     * @return string|null
     */
    private function getFormattedTime(?string $modifier): ?string
    {
        if ($modifier === null) {
            return null;
        }

        return $this->createOpeningOrClosingHour($modifier)->format('H:i');
    }
}
