<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Store;

use App\DataFixtures\Demo\StoreDataFixture;
use DateTimeImmutable;
use DateTimeZone;
use Nette\Utils\Json;
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

    private DateTimeImmutable $now;

    public function testGetStoreByUuid(): void
    {
        foreach ($this->getStoreDataProvider() as $dataSet) {
            [$uuid, $expectedStoreData] = $dataSet;

            $graphQlType = 'store';
            $response = $this->getResponseContentForQuery($this->getStoreQueryByUuid($uuid));
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
                    'openingHours',
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

        $response = $this->getResponseContentForQuery($this->getStoreQueryByUuid($storeOnSecondDomain->getUuid()));
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $response['errors'][0];
        self::assertArrayHasKey('message', $errors, Json::encode($errors));
        self::assertEquals(
            sprintf('Store with UUID "%s" does not exist.', $storeOnSecondDomain->getUuid()),
            $errors['message'],
        );

        $urlSlug = 'zilina';
        $response = $this->getResponseContentForQuery($this->getStoreQueryByUrlSlug($urlSlug));
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
            $response = $this->getResponseContentForQuery($this->getStoreQueryByUrlSlug($urlSlug));
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
                    'openingHours',
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

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/StoreQuery.graphql', [
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
     * @return array
     */
    protected function openingHoursDataProvider(): array
    {
        return [
            ['-1 hour', '+1 hour', null, null, null, [], true, '-1 hour', '+1 hour', null, null],
            [null, null, '-1 hour', '+1 hour', null, [], true, null, null, '-1 hour', '+1 hour'],
            ['-1 hour', '+1 hour', null, null, $this->getNow(), [1], true, '-1 hour', '+1 hour', null, null],
            [null, null, '-1 hour', '+1 hour', $this->getNow(), [], false, null, null, null, null],
            [null, null, null, null, null, [], false, null, null, null, null],
            ['+1 hour', '+2 hour', null, null, null, [], false, '+1 hour', '+2 hour', null, null],
            [null, null, '+1 hour', '+2 hour', null, [], false, null, null, '+1 hour', '+2 hour'],
            ['-2 hour', '-1 hour', null, null, null, [], false, '-2 hour', '-1 hour', null, null],
            [null, null, '-2 hour', '-1 hour', null, [], false, null, null, '-2 hour', '-1 hour'],
            ['-1 hour', null, null, '+1 hour', null, [], false, '-1 hour', null, null, '+1 hour'],
            [null, '+1 hour', '-1 hour', null, null, [], false, null, '+1 hour', '-1 hour', null],
            ['+1 hour', '-1 hour', null, null, null, [], false, '+1 hour', '-1 hour', null, null],
            [null, null, '+1 hour', '-1 hour', null, [], false, null, null, '+1 hour', '-1 hour'],
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
     * @param string $uuid
     * @return string
     */
    public function getStoreQueryByUuid(string $uuid): string
    {
        $graphQlTypeWithFilters = 'store (uuid:"' . $uuid . '")';

        return $this->getStoreQuery($graphQlTypeWithFilters);
    }

    /**
     * @param string $urlSlug
     * @return string
     */
    public function getStoreQueryByUrlSlug(string $urlSlug): string
    {
        $graphQlTypeWithFilters = 'store (urlSlug:"' . $urlSlug . '")';

        return $this->getStoreQuery($graphQlTypeWithFilters);
    }

    /**
     * @param string $graphQlTypeWithFilters
     * @return string
     */
    private function getStoreQuery(string $graphQlTypeWithFilters): string
    {
        return '
            query {
                ' . $graphQlTypeWithFilters . ' { 
                    name
                    slug
                    isDefault
                    description
                    street
                    city
                    postcode
                    country {
                        code
                    }
                    openingHours {
                        openingHoursOfDays {
                            firstOpeningTime
                            firstClosingTime
                            secondOpeningTime
                            secondClosingTime
                        }
                    }
                    specialMessage
                    locationLatitude
                    locationLongitude
                    breadcrumb {
                        name
                        slug
                    }
                }
            }
        ';
    }

    /**
     * @param int $storeId
     * @return array
     */
    private function getExpectedStore(int $storeId): array
    {
        $storesSlug = $this->urlGenerator->generate('front_stores');
        $openingHours = [
            'openingHoursOfDays' => [
                [
                    'firstOpeningTime' => '06:00',
                    'firstClosingTime' => '11:00',
                    'secondOpeningTime' => '13:00',
                    'secondClosingTime' => '18:00',
                ],
                [
                    'firstOpeningTime' => '07:00',
                    'firstClosingTime' => '11:00',
                    'secondOpeningTime' => '13:00',
                    'secondClosingTime' => '17:00',
                ],
                [
                    'firstOpeningTime' => '08:00',
                    'firstClosingTime' => '11:00',
                    'secondOpeningTime' => '13:00',
                    'secondClosingTime' => '16:00',
                ],
                [
                    'firstOpeningTime' => '09:00',
                    'firstClosingTime' => '11:00',
                    'secondOpeningTime' => '13:00',
                    'secondClosingTime' => '15:00',
                ],
                [
                    'firstOpeningTime' => '10:00',
                    'firstClosingTime' => '11:00',
                    'secondOpeningTime' => '13:00',
                    'secondClosingTime' => '14:00',
                ],
                [
                    'firstOpeningTime' => '08:00',
                    'firstClosingTime' => '11:00',
                    'secondOpeningTime' => null,
                    'secondClosingTime' => null,
                ],
                [
                    'firstOpeningTime' => '09:00',
                    'firstClosingTime' => '11:00',
                    'secondOpeningTime' => null,
                    'secondClosingTime' => null,
                ],
            ],
        ];

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
                'openingHours' => $openingHours,
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
                'openingHours' => $openingHours,
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
            'firstOpeningTime' => $firstOpeningTimeModifier ? $this->createOpeningOrClosingHour($firstOpeningTimeModifier)->format('H:i') : null,
            'firstClosingTime' => $firstClosingTimeModifier ? $this->createOpeningOrClosingHour($firstClosingTimeModifier)->format('H:i') : null,
            'secondOpeningTime' => $secondOpeningTimeModifier ? $this->createOpeningOrClosingHour($secondOpeningTimeModifier)->format('H:i') : null,
            'secondClosingTime' => $secondClosingTimeModifier ? $this->createOpeningOrClosingHour($secondClosingTimeModifier)->format('H:i') : null,
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
        $closedDayData->date = $date->setTime(0, 0);
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
            $this->now = new DateTimeImmutable('now', new DateTimeZone('Europe/Prague'));
        }

        return $this->now;
    }
}
