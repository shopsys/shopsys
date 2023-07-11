<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Store;

use App\DataFixtures\Demo\StoreDataFixture;
use App\Model\Store\OpeningHours\OpeningHoursDataFactory;
use App\Model\Store\Store;
use App\Model\Store\StoreDataFactory;
use App\Model\Store\StoreFacade;
use App\Model\Store\StoreFriendlyUrlProvider;
use DateTime;
use DateTimeZone;
use Nette\Utils\Json;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
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

    public function testStoreOnSecondDomainIsNotAvailable(): void
    {
        /** @var \App\Model\Store\Store $storeOnSecondDomain */
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
     * @param string|null $openingOne
     * @param string|null $closingOne
     * @param string|null $openingTwo
     * @param string|null $closingTwo
     * @param bool $isOpen
     */
    public function testGetStoreOpeningHours(
        ?string $openingOne,
        ?string $closingOne,
        ?string $openingTwo,
        ?string $closingTwo,
        bool $isOpen,
    ): void {
        $store = $this->updateStoreOpeningHours($openingOne, $closingOne, $openingTwo, $closingTwo);
        $query = sprintf('{ store(uuid: "%s") { openingHours { isOpen } } }', $store->getUuid());
        $response = $this->getResponseContentForQuery($query);

        self::assertArrayHasKey('data', $response);
        self::assertArrayHasKey('store', $response['data']);
        self::assertArrayHasKey('openingHours', $response['data']['store']);
        self::assertArrayHasKey('isOpen', $response['data']['store']['openingHours']);
        self::assertEquals($isOpen, $response['data']['store']['openingHours']['isOpen']);
    }

    /**
     * @return array
     */
    protected function openingHoursDataProvider(): array
    {
        return [
            ['-1 hour', '+1 hour', null, null, true],
            [null, null, '-1 hour', '+1 hour', true],
            [null, null, null, null, false],
            ['+1 hour', '+2 hour', null, null, false],
            [null, null, '+1 hour', '+2 hour', false],
            ['-2 hour', '-1 hour', null, null, false],
            [null, null, '-2 hour', '-1 hour', false],
            ['-1 hour', null, null, '+1 hour', false],
            [null, '+1 hour', '-1 hour', null, false],
            ['+1 hour', '-1 hour', null, null, false],
            [null, null, '+1 hour', '-1 hour', false],
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
                'description' => t('Store in Ostrava Přívoz', [], 'dataFixture', $firstDomainLocale),
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
                        'name' => t('Obchodní domy'),
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
                'description' => t('Store v Pardubice', [], 'dataFixture', $firstDomainLocale),
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
                        'name' => t('Obchodní domy'),
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
        return (int)(new DateTime('now', new DateTimeZone('Europe/Prague')))->format('N');
    }

    /**
     * @param string $modifier
     * @return \DateTime
     */
    private function createOpeningOrClosingHour(string $modifier): DateTime
    {
        $now = (new DateTime('now', new DateTimeZone('Europe/Prague')))->setDate(1970, 1, 2);
        $hour = (clone $now)->modify($modifier);

        $this->restrictDateTimeToCurrentDay($hour, $now);

        return $hour;
    }

    /**
     * @param string|null $openingOne
     * @param string|null $closingOne
     * @param string|null $openingTwo
     * @param string|null $closingTwo
     * @return \App\Model\Store\Store
     */
    private function updateStoreOpeningHours(
        ?string $openingOne,
        ?string $closingOne,
        ?string $openingTwo,
        ?string $closingTwo,
    ): Store {
        $store = $this->storeFacade->getAllStores()[0];
        $dayOfWeek = $this->getCurrentDayOfWeek();

        $storeData = $this->storeDataFactory->createFromStore($store);
        $storeData->openingHours = $this->openingHourDataFactory->createWeek();

        $openingOneDateTime = $openingOne ? $this->createOpeningOrClosingHour($openingOne) : null;
        $closingOneDateTime = $closingOne ? $this->createOpeningOrClosingHour($closingOne) : null;
        $openingTwoDateTime = $openingTwo ? $this->createOpeningOrClosingHour($openingTwo) : null;
        $closingTwoDateTime = $closingTwo ? $this->createOpeningOrClosingHour($closingTwo) : null;

        $openingHour = $storeData->openingHours[$dayOfWeek - 1];
        $openingHour->dayOfWeek = $dayOfWeek;
        $openingHour->firstOpeningTime = $openingOneDateTime?->format('H:i');
        $openingHour->firstClosingTime = $closingOneDateTime?->format('H:i');
        $openingHour->secondOpeningTime = $openingTwoDateTime?->format('H:i');
        $openingHour->secondClosingTime = $closingTwoDateTime?->format('H:i');

        return $this->storeFacade->edit($store->getId(), $storeData);
    }

    /**
     * @param \DateTime|bool $hour
     * @param \DateTime $now
     */
    private function restrictDateTimeToCurrentDay(DateTime|bool $hour, DateTime $now): void
    {
        if ($hour->format('j') < $now->format('j')) {
            $hour->setDate(1970, 1, 1);
            $hour->setTime(0, 0, 0);
        } elseif ($hour->format('j') > $now->format('j')) {
            $hour->setDate(1970, 1, 1);
            $hour->setTime(23, 59, 59);
        }
    }
}
