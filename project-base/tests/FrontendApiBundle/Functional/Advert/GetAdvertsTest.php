<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Advert;

use App\DataFixtures\Demo\CategoryDataFixture;
use League\Flysystem\MountManager;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Advert\AdvertDataFactory;
use Shopsys\FrameworkBundle\Model\Advert\AdvertFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetAdvertsTest extends GraphQlTestCase
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

    private Advert $advertWithImage;

    /**
     * @inject
     */
    private MountManager $mountManager;

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

    public function tearDown(): void
    {
        $this->advertFacade->delete($this->advertWithImage->getId());

        parent::tearDown();
    }

    public function testGetAdverts(): void
    {
        $query = $this->getAllAdvertsQuery();
        $expectedAdvertsData = $this->getExpectedAdverts();

        $this->assetAdvertsAreAsExpected($query, $expectedAdvertsData);
    }

    public function testGetFooterAdverts(): void
    {
        $query = $this->getAllAdvertsQuery('footer');
        $expectedAdvertsData = array_merge(
            array_slice($this->getExpectedAdverts(), 0, 1),
            array_slice($this->getExpectedAdverts(), 2, 1)
        );

        $this->assetAdvertsAreAsExpected($query, $expectedAdvertsData);
    }

    public function testGetElectronicsAdverts(): void
    {
        $query = $this->getAllAdvertsQuery(
            'productList',
            $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS)
        );

        $this->assetAdvertsAreAsExpected($query, array_slice($this->getExpectedAdverts(), 1, 1));
    }

    public function testGetNotExistingAdverts(): void
    {
        $query = $this->getAllAdvertsQuery('non-existing-position-name');
        $expectedAdvertsData = [];

        $this->assetAdvertsAreAsExpected($query, $expectedAdvertsData);
    }

    /**
     * @param string $query
     * @param array $expectedData
     */
    private function assetAdvertsAreAsExpected(string $query, array $expectedData): void
    {
        $graphQlType = 'adverts';
        $response = $this->getResponseContentForQuery($query);
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

    /**
     * @param string|null $positionName
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|null $category
     * @return string
     */
    private function getAllAdvertsQuery(?string $positionName = null, ?Category $category = null): string
    {
        if ($positionName !== null) {
            if ($category !== null) {
                $graphQlTypeWithFilters = 'adverts (positionName:"' . $positionName . '", categoryUuid: "' . $category->getUuid() . '")';
            } else {
                $graphQlTypeWithFilters = 'adverts (positionName:"' . $positionName . '")';
            }
        } else {
            $graphQlTypeWithFilters = 'adverts';
        }
        return '
            {
                ' . $graphQlTypeWithFilters . ' {
                    uuid
                    name
                    type
                    positionName
                    categories {
                        name
                    }
                    ... on AdvertCode {
                        code
                    }
                    ... on AdvertImage {
                        images {
                            url
                            type
                            size
                            width
                            height
                            position
                        }
                        link
                    }
                }
            }
        ';
    }

    /**
     * @return array
     */
    private function getExpectedAdverts(): array
    {
        $imageFacade = self::getContainer()->get(ImageFacade::class);
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $testImage = $imageFacade->getImageByEntity($this->advertWithImage, null);
        return [
            [
                'name' => t('Demo advert', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'type' => Advert::TYPE_CODE,
                'positionName' => 'footer',
                'categories' => [],
                'code' => '<a href="http://www.shopsys.cz/"><img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJXYXJzdHdhXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHdpZHRoPSIxNzcuOXB4IiBoZWlnaHQ9IjQxLjlweCIgdmlld0JveD0iMCAwIDE3Ny45IDQxLjkiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDE3Ny45IDQxLjkiIHhtbDpzcGFjZT0icHJlc2VydmUiPgo8cGF0aCBmaWxsPSJub25lIiBzdHJva2U9IiM1NjZDQjIiIHN0cm9rZS13aWR0aD0iMy44NTg1IiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBzdHJva2UtbWl0ZXJsaW1pdD0iMTAiIGQ9Ik0zOS4yLDIwLjUiLz4KPHBvbHlsaW5lIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzJEMzA4QyIgc3Ryb2tlLXdpZHRoPSIzLjg1ODUiIHN0cm9rZS1saW5lY2FwPSJzcXVhcmUiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgcG9pbnRzPSIxNS4zLDcuOQoJNy44LDE1LjYgMTUuMywyMy4yICIvPgo8Zz4KCTxwYXRoIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2Y5YjYyYyIgc3Ryb2tlLXdpZHRoPSIzLjg1ODUiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgZD0iTTIwLjQsMS45CgkJYzEwLjIsMCwxOC40LDguNSwxOC40LDE4LjkiLz4KCTxwYXRoIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2Y5YjYyYyIgc3Ryb2tlLXdpZHRoPSIzLjg1ODUiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgZD0iTTIwLjMsMzkuNwoJCWMtMTAuMiwwLTE4LjQtOC41LTE4LjQtMTguOSIvPgo8L2c+Cjxwb2x5bGluZSBmaWxsPSJub25lIiBzdHJva2U9IiMyRDMwOEMiIHN0cm9rZS13aWR0aD0iMy44NTg1IiBzdHJva2UtbGluZWNhcD0ic3F1YXJlIiBzdHJva2UtbWl0ZXJsaW1pdD0iMTAiIHBvaW50cz0iMjUuNCwzMy43CgkzMi45LDI2IDI1LjQsMTguNCAiLz4KPGc+Cgk8cGF0aCBmaWxsPSIjMkQzMDhDIiBkPSJNNzkuNiwxNC45Yy0yLjUsMC00LjMsMC45LTUuNCwyLjhWOC4yaC0zLjh2MjVoMy44di05LjZjMC0xLjcsMC40LTMsMS4yLTMuOWMwLjgtMC44LDEuOS0xLjMsMy4yLTEuMwoJCWMxLjIsMCwyLjIsMC40LDIuOSwxLjFjMC43LDAuNywxLDEuNywxLDMuMXYxMC42aDMuOHYtMTFjMC0yLjMtMC42LTQuMS0xLjgtNS40QzgzLjIsMTUuNiw4MS42LDE0LjksNzkuNiwxNC45eiIvPgoJPHBhdGggZmlsbD0iIzJEMzA4QyIgZD0iTTk4LjYsMTQuOWMtMi42LDAtNC43LDAuOS02LjUsMi43Yy0xLjgsMS44LTIuNyw0LTIuNyw2LjdjMCwyLjYsMC45LDQuOSwyLjcsNi43YzEuOCwxLjgsNCwyLjcsNi41LDIuNwoJCWMyLjYsMCw0LjgtMC45LDYuNS0yLjdjMS44LTEuOCwyLjctNCwyLjctNi43YzAtMi42LTAuOS00LjktMi43LTYuN0MxMDMuNCwxNS44LDEwMS4yLDE0LjksOTguNiwxNC45eiBNMTAyLjUsMjguNAoJCWMtMSwxLjEtMi4zLDEuNi0zLjksMS42Yy0xLjUsMC0yLjgtMC41LTMuOS0xLjZjLTEtMS4xLTEuNi0yLjQtMS42LTRjMC0xLjYsMC41LTMsMS42LTRjMS0xLjEsMi4zLTEuNiwzLjktMS42CgkJYzEuNSwwLDIuOCwwLjUsMy45LDEuNmMxLDEuMSwxLjYsMi40LDEuNiw0QzEwNCwyNiwxMDMuNSwyNy4zLDEwMi41LDI4LjR6Ii8+Cgk8cGF0aCBmaWxsPSIjMkQzMDhDIiBkPSJNMTIxLjEsMTQuOWMtMi43LDAtNC43LDEtNi4xLDN2LTIuNmgtMy44djI2LjVoMy44VjMwLjdjMS40LDIsMy41LDMsNi4xLDNjMi40LDAsNC40LTAuOSw2LjEtMi43CgkJYzEuNy0xLjgsMi41LTQsMi41LTYuN2MwLTIuNi0wLjktNC44LTIuNS02LjdDMTI1LjYsMTUuOCwxMjMuNSwxNC45LDEyMS4xLDE0Ljl6IE0xMjQuNSwyOC40Yy0xLDEuMS0yLjQsMS42LTMuOSwxLjYKCQljLTEuNiwwLTIuOS0wLjUtMy45LTEuNmMtMS0xLjEtMS42LTIuNS0xLjYtNC4xYzAtMS42LDAuNS0zLDEuNi00LjFjMS0xLjEsMi40LTEuNiwzLjktMS42YzEuNiwwLDIuOSwwLjUsMy45LDEuNgoJCWMxLDEuMSwxLjYsMi41LDEuNiw0LjFDMTI2LDI2LDEyNS41LDI3LjMsMTI0LjUsMjguNHoiLz4KCTxwYXRoIGZpbGw9IiMyRDMwOEMiIGQ9Ik0xNTUuNCwyOS4xbC01LjItMTMuN0gxNDZsNy40LDE4LjNsLTAuMiwxLjFjLTAuNSwxLjItMS4xLDIuMi0xLjgsMi43Yy0wLjcsMC42LTEuNywwLjgtMi45LDAuOHYzLjYKCQljNCwwLjIsNi44LTIsOC41LTYuNmw2LjctMTkuOWgtNEwxNTUuNCwyOS4xeiIvPgoJPHBhdGggZmlsbD0iIzJEMzA4QyIgZD0iTTE3NywyNS4yYy0wLjYtMC44LTEuNC0xLjQtMi40LTEuN2MtMC45LTAuMy0xLjktMC42LTIuOC0wLjljLTAuOS0wLjMtMS43LTAuNi0yLjMtMC45CgkJYy0wLjYtMC4zLTEtMC44LTEtMS40YzAtMC42LDAuMi0xLjEsMC43LTEuNGMwLDAsMC4xLTAuMSwwLjEtMC4xYzAuNS0wLjMsMS0wLjQsMS43LTAuNGMxLDAsMS44LDAuMywyLjQsMC44YzAsMCwwLDAsMCwwCgkJYzAuNCwwLjMsMC43LDAuNywxLDEuMWwwLjctMC43bDEuOC0xLjljMCwwLDAsMCwwLDBsMCwwYy0wLjUtMC44LTEuMi0xLjQtMi4xLTEuOWMtMS4xLTAuNy0yLjQtMS0zLjktMWMwLDAsMCwwLTAuMSwwCgkJYy0wLjEsMC0wLjEsMC0wLjIsMGMtMC4xLDAtMC4xLDAtMC4yLDBjMCwwLDAsMCwwLDBsMCwwYzAsMCwwLDAsMCwwYy0xLjYsMC4xLTMsMC42LTQuMSwxLjVjLTEuMiwxLTEuOCwyLjMtMS44LDQKCQljMCwxLjIsMC4zLDIuMiwxLDNjMC42LDAuOCwxLjQsMS4zLDIuNCwxLjZjMC45LDAuMywxLjksMC42LDIuOCwwLjljMC45LDAuMywxLjcsMC42LDIuMywxYzAuNiwwLjQsMSwwLjgsMSwxLjQKCQljMCwxLjEtMC43LDEuNy0yLDEuOWMtMC4zLDAtMC42LDAuMS0xLDAuMWMtMS4xLDAtMi0wLjMtMi43LTAuOGMtMC41LTAuNC0xLTEtMS40LTEuNWwtMi42LDIuNmMwLjUsMC45LDEuMywxLjYsMi4yLDIuMQoJCWMxLjIsMC43LDIuNywxLjEsNC40LDEuMWMyLDAsMy42LTAuNSw0LjktMS41YzEuMy0xLDEuOS0yLjMsMS45LTRDMTc3LjksMjcsMTc3LjYsMjYsMTc3LDI1LjJ6Ii8+Cgk8cGF0aCBmaWxsPSIjMkQzMDhDIiBkPSJNMTQyLjMsMjMuNWMtMC45LTAuMy0xLjktMC42LTIuOC0wLjljLTAuOS0wLjMtMS43LTAuNi0yLjMtMC45Yy0wLjYtMC4zLTEtMC44LTEtMS40CgkJYzAtMC42LDAuMi0xLjEsMC43LTEuNGMwLDAsMC4xLTAuMSwwLjEtMC4xYzAuNS0wLjMsMS0wLjQsMS43LTAuNGMxLDAsMS44LDAuMywyLjQsMC44YzAsMCwwLDAsMCwwYzAuNCwwLjMsMC43LDAuNywxLDEuMWwyLjUtMi42CgkJYy0wLjUtMC44LTEuMi0xLjQtMi4xLTEuOWMtMS4xLTAuNy0yLjQtMS0zLjktMWMtMC4xLDAtMC4yLDAtMC4zLDBjLTAuMSwwLTAuMSwwLTAuMiwwYy0xLjYsMC4xLTMsMC42LTQuMSwxLjUKCQljLTEuMiwxLTEuOCwyLjMtMS44LDRjMCwxLjIsMC4zLDIuMiwxLDNjMC42LDAuOCwxLjQsMS4zLDIuNCwxLjZjMC45LDAuMywxLjksMC42LDIuOCwwLjljMC45LDAuMywxLjcsMC42LDIuMywxCgkJYzAuNiwwLjQsMSwwLjgsMSwxLjRjMCwxLjEtMC43LDEuNy0yLDEuOWMtMC4zLDAtMC42LDAuMS0xLDAuMWMtMS4xLDAtMi0wLjMtMi43LTAuOGMtMC41LTAuNC0xLTEtMS40LTEuNWwtMi42LDIuNgoJCWMwLjUsMC45LDEuMywxLjYsMi4yLDIuMWMxLjIsMC43LDIuNywxLjEsNC40LDEuMWMyLDAsMy42LTAuNSw0LjktMS41YzEuMy0xLDEuOS0yLjMsMS45LTRjMC0xLjItMC4zLTIuMy0xLTMuMQoJCUMxNDQsMjQuNCwxNDMuMiwyMy44LDE0Mi4zLDIzLjV6Ii8+Cgk8cGF0aCBmaWxsPSIjMkQzMDhDIiBkPSJNNjQuOSwyMS4zYy0wLjItMC4xLTAuMy0wLjItMC41LTAuM2MtMC4yLTAuMS0wLjMtMC4yLTAuNi0wLjNjMCwwLDAsMCwwLDBjLTAuNC0wLjItMC44LTAuNC0xLjItMC42CgkJYy0wLjctMC4zLTEuNy0wLjYtMi44LTEuMWMtMC4yLDAtMC4zLTAuMS0wLjUtMC4xYy0wLjEsMC0wLjItMC4xLTAuNC0wLjFjLTEuMy0wLjQtMi4zLTAuOC0zLTEuMmMtMC4xLTAuMS0wLjItMC4xLTAuMy0wLjIKCQljLTAuMi0wLjEtMC4zLTAuMi0wLjUtMC4zYy0wLjctMC41LTEuMS0xLjItMS4xLTIuMmMwLTEsMC40LTEuNywxLTIuM2MwLjItMC4xLDAuNC0wLjMsMC42LTAuNGMwLjYtMC4zLDEuMy0wLjUsMi4yLTAuNQoJCWMxLDAsMS45LDAuMiwyLjYsMC42YzAsMCwwLDAsMCwwYzAuMywwLjEsMC41LDAuMywwLjcsMC41YzAsMCwwLjEsMCwwLjEsMC4xYzAuMiwwLjIsMC40LDAuNCwwLjYsMC43YzAuMywwLjQsMSwxLjMsMSwxLjNsMi44LTIuOAoJCWMtMC43LTEuMi0xLjYtMi4xLTIuNy0yLjhjLTAuMi0wLjEtMC40LTAuMy0wLjYtMC40Yy0wLjEsMC0wLjItMC4xLTAuMi0wLjFjLTEuMy0wLjctMi43LTEtNC4zLTFjLTIuMiwwLTQsMC43LTUuNSwyCgkJQzUwLjgsMTEsNTAsMTIuNyw1MCwxNWMwLDEuMSwwLjIsMi4xLDAuNiwyLjljMC40LDAuOCwxLDEuNSwxLjgsMi4xYzAuMiwwLjIsMC41LDAuMywwLjcsMC40YzAsMCwwLDAsMC4xLDAKCQljMC4xLDAuMSwwLjIsMC4xLDAuMywwLjJjMCwwLDAuMSwwLDAuMSwwLjFjMC4xLDAsMC4xLDAuMSwwLjIsMC4xYzAuMywwLjIsMC43LDAuMywxLDAuNWMwLjcsMC4zLDEuNywwLjYsMi44LDEuMQoJCWMwLjIsMC4xLDAuNSwwLjEsMC43LDAuMmMwLDAsMCwwLDAsMGMwLjEsMCwwLjEsMCwwLjIsMC4xYzEuNiwwLjUsMi43LDEsMy40LDEuNWMwLjgsMC41LDEuMiwxLjMsMS4yLDIuM2MwLDEtMC40LDEuOC0xLjEsMi40CgkJYzAsMCwwLDAtMC4xLDBjMCwwLDAsMCwwLDBjLTAuOCwwLjYtMS45LDAuOS0zLjMsMC45Yy0wLjYsMC0xLjEsMC0xLjYtMC4xYy0xLjEtMC4yLTEuOS0wLjYtMi43LTEuMmMtMC4yLTAuMS0wLjMtMC4zLTAuNC0wLjQKCQljMCwwLDAsMCwwLDBjLTAuMi0wLjItMC4zLTAuNC0wLjUtMC42Yy0wLjQtMC41LTAuNy0xLTEtMS42bC0yLjksMi45YzAuNywxLjQsMS43LDIuNiwzLDMuNGMwLjIsMC4yLDAuNSwwLjMsMC43LDAuNAoJCWMwLjEsMC4xLDAuMiwwLjEsMC4zLDAuMmMwLjEsMC4xLDAuMiwwLjEsMC40LDAuMmMxLjMsMC42LDIuOSwwLjksNC42LDAuOWMyLjYsMCw0LjctMC43LDYuMy0yYzAsMCwwLjEtMC4xLDAuMS0wLjEKCQljMCwwLDAsMCwwLjEtMC4xYzEuNS0xLjMsMi4zLTMuMSwyLjMtNS4zYzAtMS4xLTAuMi0yLjEtMC42LTIuOUM2Ni4zLDIyLjUsNjUuNywyMS44LDY0LjksMjEuM3oiLz4KPC9nPgo8L3N2Zz4K" alt="banner" /></a>',
            ],
            [
                'name' => t('Demo category advert', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'type' => Advert::TYPE_CODE,
                'positionName' => 'productList',
                'categories' => [
                    ['name' => t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                ],
                'code' => '<a href="http://www.shopsys.com/"><img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJXYXJzdHdhXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHdpZHRoPSIxNzcuOXB4IiBoZWlnaHQ9IjQxLjlweCIgdmlld0JveD0iMCAwIDE3Ny45IDQxLjkiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDE3Ny45IDQxLjkiIHhtbDpzcGFjZT0icHJlc2VydmUiPgo8cGF0aCBmaWxsPSJub25lIiBzdHJva2U9IiM1NjZDQjIiIHN0cm9rZS13aWR0aD0iMy44NTg1IiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBzdHJva2UtbWl0ZXJsaW1pdD0iMTAiIGQ9Ik0zOS4yLDIwLjUiLz4KPHBvbHlsaW5lIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzJEMzA4QyIgc3Ryb2tlLXdpZHRoPSIzLjg1ODUiIHN0cm9rZS1saW5lY2FwPSJzcXVhcmUiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgcG9pbnRzPSIxNS4zLDcuOQoJNy44LDE1LjYgMTUuMywyMy4yICIvPgo8Zz4KCTxwYXRoIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2Y5YjYyYyIgc3Ryb2tlLXdpZHRoPSIzLjg1ODUiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgZD0iTTIwLjQsMS45CgkJYzEwLjIsMCwxOC40LDguNSwxOC40LDE4LjkiLz4KCTxwYXRoIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2Y5YjYyYyIgc3Ryb2tlLXdpZHRoPSIzLjg1ODUiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgZD0iTTIwLjMsMzkuNwoJCWMtMTAuMiwwLTE4LjQtOC41LTE4LjQtMTguOSIvPgo8L2c+Cjxwb2x5bGluZSBmaWxsPSJub25lIiBzdHJva2U9IiMyRDMwOEMiIHN0cm9rZS13aWR0aD0iMy44NTg1IiBzdHJva2UtbGluZWNhcD0ic3F1YXJlIiBzdHJva2UtbWl0ZXJsaW1pdD0iMTAiIHBvaW50cz0iMjUuNCwzMy43CgkzMi45LDI2IDI1LjQsMTguNCAiLz4KPGc+Cgk8cGF0aCBmaWxsPSIjMkQzMDhDIiBkPSJNNzkuNiwxNC45Yy0yLjUsMC00LjMsMC45LTUuNCwyLjhWOC4yaC0zLjh2MjVoMy44di05LjZjMC0xLjcsMC40LTMsMS4yLTMuOWMwLjgtMC44LDEuOS0xLjMsMy4yLTEuMwoJCWMxLjIsMCwyLjIsMC40LDIuOSwxLjFjMC43LDAuNywxLDEuNywxLDMuMXYxMC42aDMuOHYtMTFjMC0yLjMtMC42LTQuMS0xLjgtNS40QzgzLjIsMTUuNiw4MS42LDE0LjksNzkuNiwxNC45eiIvPgoJPHBhdGggZmlsbD0iIzJEMzA4QyIgZD0iTTk4LjYsMTQuOWMtMi42LDAtNC43LDAuOS02LjUsMi43Yy0xLjgsMS44LTIuNyw0LTIuNyw2LjdjMCwyLjYsMC45LDQuOSwyLjcsNi43YzEuOCwxLjgsNCwyLjcsNi41LDIuNwoJCWMyLjYsMCw0LjgtMC45LDYuNS0yLjdjMS44LTEuOCwyLjctNCwyLjctNi43YzAtMi42LTAuOS00LjktMi43LTYuN0MxMDMuNCwxNS44LDEwMS4yLDE0LjksOTguNiwxNC45eiBNMTAyLjUsMjguNAoJCWMtMSwxLjEtMi4zLDEuNi0zLjksMS42Yy0xLjUsMC0yLjgtMC41LTMuOS0xLjZjLTEtMS4xLTEuNi0yLjQtMS42LTRjMC0xLjYsMC41LTMsMS42LTRjMS0xLjEsMi4zLTEuNiwzLjktMS42CgkJYzEuNSwwLDIuOCwwLjUsMy45LDEuNmMxLDEuMSwxLjYsMi40LDEuNiw0QzEwNCwyNiwxMDMuNSwyNy4zLDEwMi41LDI4LjR6Ii8+Cgk8cGF0aCBmaWxsPSIjMkQzMDhDIiBkPSJNMTIxLjEsMTQuOWMtMi43LDAtNC43LDEtNi4xLDN2LTIuNmgtMy44djI2LjVoMy44VjMwLjdjMS40LDIsMy41LDMsNi4xLDNjMi40LDAsNC40LTAuOSw2LjEtMi43CgkJYzEuNy0xLjgsMi41LTQsMi41LTYuN2MwLTIuNi0wLjktNC44LTIuNS02LjdDMTI1LjYsMTUuOCwxMjMuNSwxNC45LDEyMS4xLDE0Ljl6IE0xMjQuNSwyOC40Yy0xLDEuMS0yLjQsMS42LTMuOSwxLjYKCQljLTEuNiwwLTIuOS0wLjUtMy45LTEuNmMtMS0xLjEtMS42LTIuNS0xLjYtNC4xYzAtMS42LDAuNS0zLDEuNi00LjFjMS0xLjEsMi40LTEuNiwzLjktMS42YzEuNiwwLDIuOSwwLjUsMy45LDEuNgoJCWMxLDEuMSwxLjYsMi41LDEuNiw0LjFDMTI2LDI2LDEyNS41LDI3LjMsMTI0LjUsMjguNHoiLz4KCTxwYXRoIGZpbGw9IiMyRDMwOEMiIGQ9Ik0xNTUuNCwyOS4xbC01LjItMTMuN0gxNDZsNy40LDE4LjNsLTAuMiwxLjFjLTAuNSwxLjItMS4xLDIuMi0xLjgsMi43Yy0wLjcsMC42LTEuNywwLjgtMi45LDAuOHYzLjYKCQljNCwwLjIsNi44LTIsOC41LTYuNmw2LjctMTkuOWgtNEwxNTUuNCwyOS4xeiIvPgoJPHBhdGggZmlsbD0iIzJEMzA4QyIgZD0iTTE3NywyNS4yYy0wLjYtMC44LTEuNC0xLjQtMi40LTEuN2MtMC45LTAuMy0xLjktMC42LTIuOC0wLjljLTAuOS0wLjMtMS43LTAuNi0yLjMtMC45CgkJYy0wLjYtMC4zLTEtMC44LTEtMS40YzAtMC42LDAuMi0xLjEsMC43LTEuNGMwLDAsMC4xLTAuMSwwLjEtMC4xYzAuNS0wLjMsMS0wLjQsMS43LTAuNGMxLDAsMS44LDAuMywyLjQsMC44YzAsMCwwLDAsMCwwCgkJYzAuNCwwLjMsMC43LDAuNywxLDEuMWwwLjctMC43bDEuOC0xLjljMCwwLDAsMCwwLDBsMCwwYy0wLjUtMC44LTEuMi0xLjQtMi4xLTEuOWMtMS4xLTAuNy0yLjQtMS0zLjktMWMwLDAsMCwwLTAuMSwwCgkJYy0wLjEsMC0wLjEsMC0wLjIsMGMtMC4xLDAtMC4xLDAtMC4yLDBjMCwwLDAsMCwwLDBsMCwwYzAsMCwwLDAsMCwwYy0xLjYsMC4xLTMsMC42LTQuMSwxLjVjLTEuMiwxLTEuOCwyLjMtMS44LDQKCQljMCwxLjIsMC4zLDIuMiwxLDNjMC42LDAuOCwxLjQsMS4zLDIuNCwxLjZjMC45LDAuMywxLjksMC42LDIuOCwwLjljMC45LDAuMywxLjcsMC42LDIuMywxYzAuNiwwLjQsMSwwLjgsMSwxLjQKCQljMCwxLjEtMC43LDEuNy0yLDEuOWMtMC4zLDAtMC42LDAuMS0xLDAuMWMtMS4xLDAtMi0wLjMtMi43LTAuOGMtMC41LTAuNC0xLTEtMS40LTEuNWwtMi42LDIuNmMwLjUsMC45LDEuMywxLjYsMi4yLDIuMQoJCWMxLjIsMC43LDIuNywxLjEsNC40LDEuMWMyLDAsMy42LTAuNSw0LjktMS41YzEuMy0xLDEuOS0yLjMsMS45LTRDMTc3LjksMjcsMTc3LjYsMjYsMTc3LDI1LjJ6Ii8+Cgk8cGF0aCBmaWxsPSIjMkQzMDhDIiBkPSJNMTQyLjMsMjMuNWMtMC45LTAuMy0xLjktMC42LTIuOC0wLjljLTAuOS0wLjMtMS43LTAuNi0yLjMtMC45Yy0wLjYtMC4zLTEtMC44LTEtMS40CgkJYzAtMC42LDAuMi0xLjEsMC43LTEuNGMwLDAsMC4xLTAuMSwwLjEtMC4xYzAuNS0wLjMsMS0wLjQsMS43LTAuNGMxLDAsMS44LDAuMywyLjQsMC44YzAsMCwwLDAsMCwwYzAuNCwwLjMsMC43LDAuNywxLDEuMWwyLjUtMi42CgkJYy0wLjUtMC44LTEuMi0xLjQtMi4xLTEuOWMtMS4xLTAuNy0yLjQtMS0zLjktMWMtMC4xLDAtMC4yLDAtMC4zLDBjLTAuMSwwLTAuMSwwLTAuMiwwYy0xLjYsMC4xLTMsMC42LTQuMSwxLjUKCQljLTEuMiwxLTEuOCwyLjMtMS44LDRjMCwxLjIsMC4zLDIuMiwxLDNjMC42LDAuOCwxLjQsMS4zLDIuNCwxLjZjMC45LDAuMywxLjksMC42LDIuOCwwLjljMC45LDAuMywxLjcsMC42LDIuMywxCgkJYzAuNiwwLjQsMSwwLjgsMSwxLjRjMCwxLjEtMC43LDEuNy0yLDEuOWMtMC4zLDAtMC42LDAuMS0xLDAuMWMtMS4xLDAtMi0wLjMtMi43LTAuOGMtMC41LTAuNC0xLTEtMS40LTEuNWwtMi42LDIuNgoJCWMwLjUsMC45LDEuMywxLjYsMi4yLDIuMWMxLjIsMC43LDIuNywxLjEsNC40LDEuMWMyLDAsMy42LTAuNSw0LjktMS41YzEuMy0xLDEuOS0yLjMsMS45LTRjMC0xLjItMC4zLTIuMy0xLTMuMQoJCUMxNDQsMjQuNCwxNDMuMiwyMy44LDE0Mi4zLDIzLjV6Ii8+Cgk8cGF0aCBmaWxsPSIjMkQzMDhDIiBkPSJNNjQuOSwyMS4zYy0wLjItMC4xLTAuMy0wLjItMC41LTAuM2MtMC4yLTAuMS0wLjMtMC4yLTAuNi0wLjNjMCwwLDAsMCwwLDBjLTAuNC0wLjItMC44LTAuNC0xLjItMC42CgkJYy0wLjctMC4zLTEuNy0wLjYtMi44LTEuMWMtMC4yLDAtMC4zLTAuMS0wLjUtMC4xYy0wLjEsMC0wLjItMC4xLTAuNC0wLjFjLTEuMy0wLjQtMi4zLTAuOC0zLTEuMmMtMC4xLTAuMS0wLjItMC4xLTAuMy0wLjIKCQljLTAuMi0wLjEtMC4zLTAuMi0wLjUtMC4zYy0wLjctMC41LTEuMS0xLjItMS4xLTIuMmMwLTEsMC40LTEuNywxLTIuM2MwLjItMC4xLDAuNC0wLjMsMC42LTAuNGMwLjYtMC4zLDEuMy0wLjUsMi4yLTAuNQoJCWMxLDAsMS45LDAuMiwyLjYsMC42YzAsMCwwLDAsMCwwYzAuMywwLjEsMC41LDAuMywwLjcsMC41YzAsMCwwLjEsMCwwLjEsMC4xYzAuMiwwLjIsMC40LDAuNCwwLjYsMC43YzAuMywwLjQsMSwxLjMsMSwxLjNsMi44LTIuOAoJCWMtMC43LTEuMi0xLjYtMi4xLTIuNy0yLjhjLTAuMi0wLjEtMC40LTAuMy0wLjYtMC40Yy0wLjEsMC0wLjItMC4xLTAuMi0wLjFjLTEuMy0wLjctMi43LTEtNC4zLTFjLTIuMiwwLTQsMC43LTUuNSwyCgkJQzUwLjgsMTEsNTAsMTIuNyw1MCwxNWMwLDEuMSwwLjIsMi4xLDAuNiwyLjljMC40LDAuOCwxLDEuNSwxLjgsMi4xYzAuMiwwLjIsMC41LDAuMywwLjcsMC40YzAsMCwwLDAsMC4xLDAKCQljMC4xLDAuMSwwLjIsMC4xLDAuMywwLjJjMCwwLDAuMSwwLDAuMSwwLjFjMC4xLDAsMC4xLDAuMSwwLjIsMC4xYzAuMywwLjIsMC43LDAuMywxLDAuNWMwLjcsMC4zLDEuNywwLjYsMi44LDEuMQoJCWMwLjIsMC4xLDAuNSwwLjEsMC43LDAuMmMwLDAsMCwwLDAsMGMwLjEsMCwwLjEsMCwwLjIsMC4xYzEuNiwwLjUsMi43LDEsMy40LDEuNWMwLjgsMC41LDEuMiwxLjMsMS4yLDIuM2MwLDEtMC40LDEuOC0xLjEsMi40CgkJYzAsMCwwLDAtMC4xLDBjMCwwLDAsMCwwLDBjLTAuOCwwLjYtMS45LDAuOS0zLjMsMC45Yy0wLjYsMC0xLjEsMC0xLjYtMC4xYy0xLjEtMC4yLTEuOS0wLjYtMi43LTEuMmMtMC4yLTAuMS0wLjMtMC4zLTAuNC0wLjQKCQljMCwwLDAsMCwwLDBjLTAuMi0wLjItMC4zLTAuNC0wLjUtMC42Yy0wLjQtMC41LTAuNy0xLTEtMS42bC0yLjksMi45YzAuNywxLjQsMS43LDIuNiwzLDMuNGMwLjIsMC4yLDAuNSwwLjMsMC43LDAuNAoJCWMwLjEsMC4xLDAuMiwwLjEsMC4zLDAuMmMwLjEsMC4xLDAuMiwwLjEsMC40LDAuMmMxLjMsMC42LDIuOSwwLjksNC42LDAuOWMyLjYsMCw0LjctMC43LDYuMy0yYzAsMCwwLjEtMC4xLDAuMS0wLjEKCQljMCwwLDAsMCwwLjEtMC4xYzEuNS0xLjMsMi4zLTMuMSwyLjMtNS4zYzAtMS4xLTAuMi0yLjEtMC42LTIuOUM2Ni4zLDIyLjUsNjUuNywyMS44LDY0LjksMjEuM3oiLz4KPC9nPgo8L3N2Zz4K" alt="banner" /></a>',
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
                    [
                        'url' => sprintf(
                            '%s/content-test/images/noticer/header/%s.png',
                            $this->firstDomainUrl,
                            $testImage->getId()
                        ),
                        'type' => null,
                        'size' => 'header',
                        'width' => 1160,
                        'height' => null,
                        'position' => null,
                    ],
                    [
                        'url' => sprintf(
                            '%s/content-test/images/noticer/original/%s.png',
                            $this->firstDomainUrl,
                            $testImage->getId()
                        ),
                        'type' => null,
                        'size' => 'original',
                        'width' => null,
                        'height' => null,
                        'position' => null,
                    ],
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
}
