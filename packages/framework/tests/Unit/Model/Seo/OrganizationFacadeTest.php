<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Seo;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Model\Seo\Organization;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationDataFactory;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationFacade;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationFactory;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationRepository;

final class OrganizationFacadeTest extends TestCase
{
    public function testEditingOrganizationDoesNotChangeOtherDomain(): void
    {
        $imageFacade = $this->createMock(ImageFacade::class);
        $data = $this->createDataFactory($imageFacade)->create();
        $data->name = 'Original company';
        $firstOrganization = $this->createOrganizationFactory()->create(Domain::FIRST_DOMAIN_ID, $data);
        $secondOrganization = $this->createOrganizationFactory()->create(Domain::SECOND_DOMAIN_ID, $data);
        $facade = $this->createFacade($imageFacade, [$firstOrganization, $secondOrganization]);
        $data->name = 'Updated company';
        $imageFacade->expects($this->once())->method('manageImages')->with($firstOrganization, $data->image);

        $facade->edit(Domain::FIRST_DOMAIN_ID, $data);

        $this->assertSame('Updated company', $firstOrganization->getName());
        $this->assertSame('Original company', $secondOrganization->getName());
    }

    public function testDataForDomainWithoutOrganizationIsEmpty(): void
    {
        $dataFactory = $this->createDataFactory($this->createStub(ImageFacade::class));

        $data = $dataFactory->findOrCreateForDomain(Domain::FIRST_DOMAIN_ID);

        $this->assertNull($data->name);
        $this->assertNull($data->companyTaxNumber);
    }

    public function testDataForDomainIsFilledFromExistingOrganization(): void
    {
        $imageFacade = $this->createStub(ImageFacade::class);
        $data = $this->createDataFactory($imageFacade)->create();
        $data->name = 'Example company';
        $organization = $this->createOrganizationFactory()->create(Domain::FIRST_DOMAIN_ID, $data);
        $dataFactory = $this->createDataFactory($imageFacade, [$organization]);

        $result = $dataFactory->findOrCreateForDomain(Domain::FIRST_DOMAIN_ID);

        $this->assertSame('Example company', $result->name);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Organization[] $organizations
     */
    private function createFacade(ImageFacade $imageFacade, array $organizations = []): OrganizationFacade
    {
        return new OrganizationFacade(
            $this->createStub(EntityManagerInterface::class),
            $this->createRepository($organizations),
            $this->createOrganizationFactory(),
            $imageFacade,
            $this->createStub(CleanStorefrontCacheFacade::class),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Organization[] $organizations
     */
    private function createDataFactory(ImageFacade $imageFacade, array $organizations = []): OrganizationDataFactory
    {
        return new OrganizationDataFactory(new ImageUploadDataFactory($imageFacade), $this->createRepository($organizations));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Organization[] $organizations
     */
    private function createRepository(array $organizations): OrganizationRepository
    {
        $repository = $this->createStub(OrganizationRepository::class);
        $repository->method('findByDomainId')->willReturnCallback(static function (int $domainId) use ($organizations): ?Organization {
            foreach ($organizations as $organization) {
                if ($organization->getDomainId() === $domainId) {
                    return $organization;
                }
            }

            return null;
        });

        return $repository;
    }

    private function createOrganizationFactory(): OrganizationFactory
    {
        $entityNameResolver = $this->createStub(EntityNameResolver::class);
        $entityNameResolver->method('resolve')->willReturn(Organization::class);

        return new OrganizationFactory($entityNameResolver);
    }
}
