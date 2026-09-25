<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Settings;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationDataFactory;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class SeoSettingTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private OrganizationFacade $organizationFacade;

    /**
     * @inject
     */
    private OrganizationDataFactory $organizationDataFactory;

    /**
     * @inject
     */
    private MailSettingFacade $mailSettingFacade;

    /**
     * @inject
     */
    private ImageFacade $imageFacade;

    /**
     * @inject
     */
    private ImageLocator $imageLocator;

    /**
     * @inject
     */
    private FileUpload $fileUpload;

    /**
     * @inject
     */
    private MountManager $mountManager;

    /**
     * @inject
     */
    private FilesystemOperator $filesystem;

    public function testGetSeoSettings(): void
    {
        $query = '
            query {
                settings {
                    seo {
                        title
                        titleAddOn
                        metaDescription
                    }
                }
            }
        ';

        $response = $this->getResponseContentForQuery($query);
        $data = $this->getResponseDataForGraphQlType($response, 'settings');

        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $expectedTitle = t('Shopsys Platform - Title page', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale);
        $expectedTitleAddOn = t('| Demo eshop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale);
        $expectedDescription = t('Shopsys Platform - the best solution for your eshop.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale);

        self::assertEquals($expectedTitle, $data['seo']['title']);
        self::assertEquals($expectedTitleAddOn, $data['seo']['titleAddOn']);
        self::assertEquals($expectedDescription, $data['seo']['metaDescription']);
    }

    public function testOrganizationWithoutSettingsHasNoDetailsAndNoLogo(): void
    {
        $organization = $this->getOrganizationFromApi();

        self::assertNull($organization['name']);
        self::assertNull($organization['companyTaxNumber']);
        self::assertNull($organization['logo']);
        self::assertNotContains('', $organization['socialNetworkUrls']);
    }

    public function testOrganizationSettingsAndLogoAreReturned(): void
    {
        $this->mailSettingFacade->setFacebookUrl('https://www.facebook.com/shopsys', Domain::FIRST_DOMAIN_ID);
        $this->mailSettingFacade->setInstagramUrl(null, Domain::FIRST_DOMAIN_ID);
        $this->mailSettingFacade->setYoutubeUrl(null, Domain::FIRST_DOMAIN_ID);
        $this->mailSettingFacade->setLinkedInUrl(null, Domain::FIRST_DOMAIN_ID);
        $this->mailSettingFacade->setTiktokUrl(null, Domain::FIRST_DOMAIN_ID);
        $filename = 'organization-api-test-' . bin2hex(random_bytes(8)) . '.jpg';
        $this->mountManager->copy(
            'local://' . __DIR__ . '/../../../App/Functional/Component/Image/Resources/image.jpg',
            'main://' . $this->fileUpload->getTemporaryDirectory() . '/' . $filename,
        );
        $organizationData = $this->organizationDataFactory->findOrCreateForDomain(Domain::FIRST_DOMAIN_ID);
        $organizationData->name = 'Shopsys s.r.o.';
        $organizationData->companyTaxNumber = 'CZ12345678';
        $organizationData->city = 'Ostrava';
        $organizationData->image->uploadedFiles = [$filename];
        $organizationEntity = $this->organizationFacade->edit(Domain::FIRST_DOMAIN_ID, $organizationData);
        $image = $this->imageFacade->getImageByEntity($organizationEntity, null);
        $expectedLogo = $this->imageFacade->getImageUrl($this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID), $organizationEntity);

        try {
            $organization = $this->getOrganizationFromApi();

            self::assertSame('Shopsys s.r.o.', $organization['name']);
            self::assertSame('CZ12345678', $organization['companyTaxNumber']);
            self::assertSame('Ostrava', $organization['city']);
            self::assertSame($expectedLogo, $organization['logo']);
            self::assertSame(['https://www.facebook.com/shopsys'], $organization['socialNetworkUrls']);
        } finally {
            $this->filesystem->delete($this->imageLocator->getAbsoluteImageFilepath($image));
        }
    }

    /**
     * @return array{name: string|null, companyTaxNumber: string|null, city: string|null, logo: string|null, socialNetworkUrls: string[]}
     */
    private function getOrganizationFromApi(): array
    {
        $query = '
            query {
                settings {
                    seo {
                        organization {
                            name
                            companyTaxNumber
                            city
                            logo
                            socialNetworkUrls
                        }
                    }
                }
            }
        ';

        $response = $this->getResponseContentForQuery($query);

        return $this->getResponseDataForGraphQlType($response, 'settings')['seo']['organization'];
    }
}
