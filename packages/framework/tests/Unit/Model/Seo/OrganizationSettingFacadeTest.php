<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Seo;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;
use Shopsys\FrameworkBundle\Model\Seo\Organization;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationDataFactory;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationFactory;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationSettingFacade;

final class OrganizationSettingFacadeTest extends TestCase
{
    public function testEditingOrganizationDoesNotChangeOtherDomain(): void
    {
        $imageFacade = $this->createMock(ImageFacade::class);
        $dataFactory = new OrganizationDataFactory(new ImageUploadDataFactory($imageFacade));
        $data = $dataFactory->create();
        $data->name = 'Original company';
        $firstOrganization = $this->createOrganizationFactory()->create(Domain::FIRST_DOMAIN_ID, $data);
        $secondOrganization = $this->createOrganizationFactory()->create(Domain::SECOND_DOMAIN_ID, $data);
        $facade = $this->createFacade($imageFacade, [$firstOrganization, $secondOrganization]);
        $data->name = 'Updated company';
        $imageFacade->expects($this->once())->method('manageImages')->with($firstOrganization, $data->image);

        $facade->saveSettings($data, Domain::FIRST_DOMAIN_ID);

        $this->assertSame('Updated company', $firstOrganization->getName());
        $this->assertSame('Original company', $secondOrganization->getName());
    }

    public function testOrganizationWithoutSettingsHasNoLogoOrCompanyName(): void
    {
        $facade = $this->createFacade($this->createStub(ImageFacade::class));

        $data = $facade->getOrganization(Domain::FIRST_DOMAIN_ID);

        $this->assertNull($data->name);
        $this->assertNull($data->logo);
        $this->assertSame(['https://example.com/profile'], $data->socialNetworkUrls);
    }

    public function testMissingLogoDoesNotHideCompanyDetails(): void
    {
        $imageFacade = $this->createStub(ImageFacade::class);
        $imageFacade->method('getImageUrl')->willThrowException(new ImageNotFoundException());
        $dataFactory = new OrganizationDataFactory(new ImageUploadDataFactory($imageFacade));
        $data = $dataFactory->create();
        $data->name = 'Example company';
        $data->companyTaxNumber = 'CZ12345678';
        $organization = $this->createOrganizationFactory()->create(Domain::FIRST_DOMAIN_ID, $data);
        $facade = $this->createFacade($imageFacade, [$organization]);

        $result = $facade->getOrganization(Domain::FIRST_DOMAIN_ID);

        $this->assertSame('Example company', $result->name);
        $this->assertSame('CZ12345678', $result->companyTaxNumber);
        $this->assertNull($result->logo);
    }

    public function testOrganizationUsesStandardImageUrl(): void
    {
        $imageFacade = $this->createStub(ImageFacade::class);
        $imageFacade->method('getImageUrl')->willReturn('https://example.com/content/images/organization/1.png');
        $dataFactory = new OrganizationDataFactory(new ImageUploadDataFactory($imageFacade));
        $organization = $this->createOrganizationFactory()->create(Domain::FIRST_DOMAIN_ID, $dataFactory->create());
        $facade = $this->createFacade($imageFacade, [$organization]);

        $result = $facade->getOrganization(Domain::FIRST_DOMAIN_ID);

        $this->assertSame('https://example.com/content/images/organization/1.png', $result->logo);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Organization[] $organizations
     */
    private function createFacade(ImageFacade $imageFacade, array $organizations = []): OrganizationSettingFacade
    {
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('findOneBy')->willReturnCallback(static function (array $criteria) use ($organizations): ?Organization {
            foreach ($organizations as $organization) {
                if ($organization->getDomainId() === $criteria['domainId']) {
                    return $organization;
                }
            }

            return null;
        });
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);
        $domain = $this->createStub(Domain::class);
        $domain->method('getDomainConfigById')->willReturn($this->createStub(DomainConfig::class));
        $mailSettingFacade = $this->createStub(MailSettingFacade::class);
        $mailSettingFacade->method('getFooterIconUrls')->willReturn(['', 'https://example.com/profile']);
        $dataFactory = new OrganizationDataFactory(new ImageUploadDataFactory($imageFacade));

        return new OrganizationSettingFacade(
            $em,
            $this->createOrganizationFactory(),
            $dataFactory,
            $imageFacade,
            $domain,
            $mailSettingFacade,
            $this->createStub(CleanStorefrontCacheFacade::class),
        );
    }

    private function createOrganizationFactory(): OrganizationFactory
    {
        $entityNameResolver = $this->createStub(EntityNameResolver::class);
        $entityNameResolver->method('resolve')->willReturn(Organization::class);

        return new OrganizationFactory($entityNameResolver);
    }
}
