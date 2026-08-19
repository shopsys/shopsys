<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Advert;

use App\DataFixtures\Demo\AdvertDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\Model\Category\Category;
use League\Flysystem\MountManager;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Advert\AdvertDataFactory;
use Shopsys\FrameworkBundle\Model\Advert\AdvertFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class GetAdvertsTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private AdvertDataFactory $advertDataFactory;

    /**
     * @inject
     */
    private AdvertFacade $advertFacade;

    /**
     * @inject
     */
    private FileUpload $fileUpload;

    /**
     * @inject
     */
    private ImageFacade $imageFacade;

    private Advert $advertWithImage;

    /**
     * @inject
     */
    private MountManager $mountManager;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->loadTestAdverts();
    }

    private function loadTestAdverts(): void
    {
        $domainId = $this->domain->getId();

        $advert1 = $this->advertDataFactory->create();
        $advert1->name = 'Test advert footer 1';
        $advert1->type = Advert::TYPE_CODE;
        $advert1->code = 'I <3 Shopsys';
        $advert1->positionName = 'footer';
        $advert1->hidden = false;
        $advert1->domainId = $domainId;
        $advert1->uuid = '19fe7c4b-ef93-4d33-aac3-f6eba0508f4d';
        $this->advertFacade->create($advert1);

        $advert2 = $this->advertDataFactory->create();
        $advert2->name = 'Test advert header 1';
        $advert2->type = Advert::TYPE_CODE;
        $advert2->code = '<a href="/foo">Foo</a>';
        $advert2->positionName = 'header';
        $advert2->hidden = false;
        $advert2->domainId = $domainId;
        $advert2->uuid = 'e0b22920-d9ca-4270-a548-3e4fb8f212c9';
        $this->advertFacade->create($advert2);

        $testImageName = 'logo.png';
        $localImagePath = 'local://' . __DIR__ . '/Resources/' . $testImageName;
        $abstractImagePath = 'main://' . $this->fileUpload->getTemporaryDirectory() . '/' . $testImageName;
        $this->mountManager->copy($localImagePath, $abstractImagePath);

        $imageUploadData = new ImageUploadData();
        $imageUploadData->uploadedFiles = [$testImageName];
        $imageUploadData->uploadedFilenames = [
            0 => [
                'cs' => 'Testovací obrázek',
                'en' => 'Test image',
            ],
        ];

        $advert3 = $this->advertDataFactory->create();
        $advert3->name = 'Test advert header 2';
        $advert3->type = Advert::TYPE_IMAGE;
        $advert3->positionName = 'header';
        $advert3->hidden = false;
        $advert3->domainId = $domainId;
        $advert3->uuid = 'bb06fb2d-871a-4a54-8fa0-3bb995ac2650';
        $advert3->image = $imageUploadData;
        $advert3->link = 'https://shopsys.com';
        $this->advertWithImage = $this->advertFacade->create($advert3);

        $advert4 = $this->advertDataFactory->create();
        $advert4->name = 'Test advert header 3';
        $advert4->type = Advert::TYPE_IMAGE;
        $advert4->positionName = 'header';
        $advert4->hidden = false;
        $advert4->domainId = $domainId;
        $advert4->uuid = 'bb06fb2d-881a-4a54-6aa0-3bb995ac2650';
        $this->advertFacade->create($advert4);
    }

    #[Override]
    public function tearDown(): void
    {
        $this->advertFacade->delete($this->advertWithImage->getId());

        parent::tearDown();
    }

    public function testGetAdverts(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetAdvertsQuery.graphql');
        $expectedAdvertsData = $this->getExpectedAdverts();

        $this->assetAdvertsAreAsExpected($response, $expectedAdvertsData);
    }

    public function testGetFooterAdverts(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetAdvertsQuery.graphql', [
            'positionNames' => ['footer'],
        ]);
        $expectedAdvertsData = array_merge(
            array_slice($this->getExpectedAdverts(), 0, 1),
            array_slice($this->getExpectedAdverts(), 2, 1),
        );

        $this->assetAdvertsAreAsExpected($response, $expectedAdvertsData);
    }

    public function testGetElectronicsAdverts(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetAdvertsQuery.graphql', [
            'positionNames' => ['productListSecondRow'],
            'categoryUuid' => $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class)->getUuid(),
        ]);

        $this->assetAdvertsAreAsExpected($response, array_slice($this->getExpectedAdverts(), 1, 1));
    }

    public function testGetNotExistingAdverts(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetAdvertsQuery.graphql', [
            'positionNames' => ['non-existing-position-name'],
        ]);
        $expectedAdvertsData = [];

        $this->assetAdvertsAreAsExpected($response, $expectedAdvertsData);
    }

    private function assetAdvertsAreAsExpected(array $response, array $expectedData): void
    {
        $graphQlType = 'adverts';
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        self::assertCount(count($expectedData), $responseData);

        foreach ($responseData as $advertData) {
            self::assertArrayHasKey('uuid', $advertData);
            self::assertTrue(Uuid::isValid($advertData['uuid']));
            unset($advertData['uuid']);

            self::assertSame(array_shift($expectedData), $advertData);
        }
    }

    private function getExpectedAdverts(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $footerAdvert = $this->getReference(
            AdvertDataFixture::FOOTER_ADVERT_REFERENCE_PREFIX . $this->domain->getId(),
            Advert::class,
        );
        $categoryAdvert = $this->getReference(
            AdvertDataFixture::CATEGORY_ADVERT_REFERENCE_PREFIX . $this->domain->getId(),
            Advert::class,
        );
        $footerAdvertImage = $this->imageFacade->getImageByEntity($footerAdvert, AdvertFacade::IMAGE_TYPE_WEB);
        $categoryAdvertImage = $this->imageFacade->getImageByEntity($categoryAdvert, AdvertFacade::IMAGE_TYPE_WEB);
        $testImage = $this->imageFacade->getImageByEntity($this->advertWithImage, AdvertFacade::IMAGE_TYPE_WEB);

        return [
            [
                'name' => t('Demo advert', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'type' => Advert::TYPE_IMAGE,
                'positionName' => 'footer',
                'categories' => [],
                'images' => [
                    $this->getExpectedAdvertImage(
                        $footerAdvertImage,
                        t('Demo advert', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ),
                ],
                'link' => 'https://www.shopsys.cz/',
            ],
            [
                'name' => t('Demo category advert', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'type' => Advert::TYPE_IMAGE,
                'positionName' => 'productListSecondRow',
                'categories' => [
                    ['name' => t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('Books', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                ],
                'images' => [
                    $this->getExpectedAdvertImage(
                        $categoryAdvertImage,
                        t('Demo category advert', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ),
                ],
                'link' => 'https://www.shopsys.com/',
            ],
            [
                'name' => 'Test advert footer 1',
                'type' => Advert::TYPE_CODE,
                'positionName' => 'footer',
                'categories' => [],
                'code' => 'I <3 Shopsys',
            ],
            [
                'name' => 'Test advert header 1',
                'type' => Advert::TYPE_CODE,
                'positionName' => 'header',
                'categories' => [],
                'code' => '<a href="/foo">Foo</a>',
            ],
            [
                'name' => 'Test advert header 2',
                'type' => Advert::TYPE_IMAGE,
                'positionName' => 'header',
                'categories' => [],
                'images' => [
                    $this->getExpectedAdvertImage(
                        $testImage,
                        t('Test image', [], Translator::TESTS_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ),
                ],
                'link' => 'https://shopsys.com',
            ],
            [
                'name' => 'Test advert header 3',
                'type' => Advert::TYPE_IMAGE,
                'positionName' => 'header',
                'categories' => [],
                'images' => [],
                'link' => null,
            ],
        ];
    }

    /**
     * @return array{url: string, name: string}
     */
    private function getExpectedAdvertImage(Image $image, string $name): array
    {
        return [
            'url' => sprintf(
                '%s/content-test/images/noticer/web/%s.%s',
                $this->currentBaseDomainUrl,
                $image->getId(),
                $image->getExtension(),
            ),
            'name' => $name,
        ];
    }
}
