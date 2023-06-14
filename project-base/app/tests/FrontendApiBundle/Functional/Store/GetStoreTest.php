<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Store;

use App\DataFixtures\Demo\StoreDataFixture;
use App\Model\Store\StoreFacade;
use App\Model\Store\StoreFriendlyUrlProvider;
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
                    openingHours
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
                'openingHours' => t('Po-Pa: 8:00-16:00', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
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
                'openingHours' => t('Po-Pa: 8:00-17:00', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
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
}
