<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Seo;

use App\DataFixtures\Demo\AdministratorDataFixture;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use Shopsys\FrameworkBundle\Form\Admin\Seo\SeoSettingFormType;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationData;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationDataFactory;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationFacade;
use Shopsys\FrontendApiBundle\Model\Seo\OrganizationApiFacade;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\App\Test\TransactionFunctionalTestCase;

final class OrganizationFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private OrganizationFacade $organizationFacade;

    /**
     * @inject
     */
    private OrganizationApiFacade $organizationApiFacade;

    /**
     * @inject
     */
    private OrganizationDataFactory $organizationDataFactory;

    /**
     * @inject
     */
    private FormFactoryInterface $formFactory;

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
    private ImageFacade $imageFacade;

    /**
     * @inject
     */
    private ImageLocator $imageLocator;

    /**
     * @inject
     */
    private FilesystemOperator $filesystem;

    /**
     * @inject
     */
    private TokenStorageInterface $tokenStorage;

    /**
     * @inject
     */
    private RequestStack $requestStack;

    public function testReplacingAndDeletingLogoPreservesOtherDomainLogo(): void
    {
        $paths = [];

        try {
            $firstPath = $this->uploadLogo(Domain::FIRST_DOMAIN_ID);
            $paths[] = $firstPath;
            $secondPath = $this->uploadLogo(Domain::SECOND_DOMAIN_ID);
            $paths[] = $secondPath;

            $replacementPath = $this->uploadLogo(Domain::FIRST_DOMAIN_ID);
            $paths[] = $replacementPath;

            $this->assertFalse($this->filesystem->fileExists($firstPath));
            $this->assertTrue($this->filesystem->fileExists($replacementPath));
            $this->assertTrue($this->filesystem->fileExists($secondPath));
            $this->assertNotEmpty($this->organizationDataFactory->findOrCreateForDomain(Domain::FIRST_DOMAIN_ID)->image->orderedImages);

            $data = $this->organizationDataFactory->findOrCreateForDomain(Domain::FIRST_DOMAIN_ID);
            $data->image->imagesToDelete = $data->image->orderedImages;
            $this->organizationFacade->edit(Domain::FIRST_DOMAIN_ID, $data);

            $this->assertFalse($this->filesystem->fileExists($replacementPath));
            $this->assertTrue($this->filesystem->fileExists($secondPath));
            $this->assertEmpty($this->organizationDataFactory->findOrCreateForDomain(Domain::FIRST_DOMAIN_ID)->image->orderedImages);
        } finally {
            foreach ($paths as $path) {
                $this->filesystem->delete($path);
            }
        }
    }

    private function uploadLogo(int $domainId): string
    {
        $filename = 'organization-test-' . bin2hex(random_bytes(8)) . '.jpg';
        $this->mountManager->copy(
            'local://' . __DIR__ . '/../../Component/Image/Resources/image.jpg',
            'main://' . $this->fileUpload->getTemporaryDirectory() . '/' . $filename,
        );
        $data = $this->organizationDataFactory->findOrCreateForDomain($domainId);
        $data->image->uploadedFiles = [$filename];
        $this->organizationFacade->edit($domainId, $data);
        $organization = $this->organizationFacade->findByDomainId($domainId);
        $image = $this->imageFacade->getImageByEntity($organization, null);

        return $this->imageLocator->getAbsoluteImageFilepath($image);
    }

    public function testOrganizationSettingsAreStoredSeparatelyForEachDomain(): void
    {
        $firstDomainData = $this->organizationDataFactory->findOrCreateForDomain(Domain::FIRST_DOMAIN_ID);
        $firstDomainData->name = 'First domain company';
        $firstDomainData->companyTaxNumber = '2120123456';
        $firstDomainData->companyVatNumber = 'SK2120123456';
        $secondDomainData = $this->organizationDataFactory->findOrCreateForDomain(Domain::SECOND_DOMAIN_ID);
        $secondDomainData->name = 'Second domain company';

        $this->organizationFacade->edit(Domain::FIRST_DOMAIN_ID, $firstDomainData);
        $this->organizationFacade->edit(Domain::SECOND_DOMAIN_ID, $secondDomainData);
        $this->em->clear();

        $firstOrganization = $this->organizationDataFactory->findOrCreateForDomain(Domain::FIRST_DOMAIN_ID);
        $secondOrganization = $this->organizationDataFactory->findOrCreateForDomain(Domain::SECOND_DOMAIN_ID);
        $this->assertSame('First domain company', $firstOrganization->name);
        $this->assertSame('2120123456', $firstOrganization->companyTaxNumber);
        $this->assertSame('SK2120123456', $firstOrganization->companyVatNumber);
        $this->assertNull($secondOrganization->companyVatNumber);
        $this->assertSame('Second domain company', $secondOrganization->name);
        $this->assertEmpty($firstOrganization->image->orderedImages);
        $apiOrganization = $this->organizationApiFacade->getOrganization(Domain::FIRST_DOMAIN_ID);
        $this->assertSame('2120123456', $apiOrganization->companyTaxNumber);
        $this->assertSame('SK2120123456', $apiOrganization->companyVatNumber);
    }

    public function testOrganizationGroupMapsSubmittedFieldsToDataObject(): void
    {
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $this->tokenStorage->setToken(new UsernamePasswordToken($administrator, 'admin', $administrator->getRoles()));
        $this->createRequest();

        $data = ['organization' => $this->organizationDataFactory->findOrCreateForDomain(Domain::FIRST_DOMAIN_ID)];
        $form = $this->formFactory->create(SeoSettingFormType::class, $data, ['domain_id' => Domain::FIRST_DOMAIN_ID, 'csrf_protection' => false]);
        $organizationForm = $form->get('organization');

        $organizationForm->submit(['name' => 'Submitted company', 'companyTaxNumber' => '2120123456', 'companyVatNumber' => 'SK2120123456', 'city' => 'Ostrava']);
        $view = $form->createView();
        $organization = $organizationForm->getData();

        $this->assertInstanceOf(OrganizationData::class, $organization);
        $this->assertSame('Submitted company', $organization->name);
        $this->assertSame('2120123456', $organization->companyTaxNumber);
        $this->assertSame('SK2120123456', $organization->companyVatNumber);
        $this->assertSame('Ostrava', $organization->city);
        $this->assertArrayHasKey('image', $view->children['organization']->children);
        $this->assertTrue($view->children['organization']->vars['renders_in_own_card']);
        $this->tokenStorage->setToken(null);
        $this->requestStack->pop();
    }
}
