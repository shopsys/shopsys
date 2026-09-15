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
use Shopsys\FrameworkBundle\Model\Seo\OrganizationSettingFacade;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\App\Test\TransactionFunctionalTestCase;

final class OrganizationSettingFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private OrganizationSettingFacade $organizationSettingFacade;

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
            $this->assertNotNull($this->organizationSettingFacade->getOrganization(Domain::FIRST_DOMAIN_ID)->logo);

            $data = $this->organizationSettingFacade->getSettings(Domain::FIRST_DOMAIN_ID);
            $data->image->imagesToDelete = $data->image->orderedImages;
            $this->organizationSettingFacade->saveSettings($data, Domain::FIRST_DOMAIN_ID);

            $this->assertFalse($this->filesystem->fileExists($replacementPath));
            $this->assertTrue($this->filesystem->fileExists($secondPath));
            $this->assertNull($this->organizationSettingFacade->getOrganization(Domain::FIRST_DOMAIN_ID)->logo);
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
        $data = $this->organizationSettingFacade->getSettings($domainId);
        $data->image->uploadedFiles = [$filename];
        $this->organizationSettingFacade->saveSettings($data, $domainId);
        $organization = $this->organizationSettingFacade->findByDomainId($domainId);
        $image = $this->imageFacade->getImageByEntity($organization, null);

        return $this->imageLocator->getAbsoluteImageFilepath($image);
    }

    public function testOrganizationSettingsAreStoredSeparatelyForEachDomain(): void
    {
        $firstDomainData = $this->organizationSettingFacade->getSettings(Domain::FIRST_DOMAIN_ID);
        $firstDomainData->name = 'First domain company';
        $firstDomainData->companyTaxNumber = 'CZ12345678';
        $secondDomainData = $this->organizationSettingFacade->getSettings(Domain::SECOND_DOMAIN_ID);
        $secondDomainData->name = 'Second domain company';

        $this->organizationSettingFacade->saveSettings($firstDomainData, Domain::FIRST_DOMAIN_ID);
        $this->organizationSettingFacade->saveSettings($secondDomainData, Domain::SECOND_DOMAIN_ID);
        $this->em->clear();

        $firstOrganization = $this->organizationSettingFacade->getOrganization(Domain::FIRST_DOMAIN_ID);
        $secondOrganization = $this->organizationSettingFacade->getOrganization(Domain::SECOND_DOMAIN_ID);
        $this->assertSame('First domain company', $firstOrganization->name);
        $this->assertSame('CZ12345678', $firstOrganization->companyTaxNumber);
        $this->assertSame('Second domain company', $secondOrganization->name);
        $this->assertNull($firstOrganization->logo);
    }

    public function testOrganizationGroupMapsSubmittedFieldsToDataObject(): void
    {
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $this->tokenStorage->setToken(new UsernamePasswordToken($administrator, 'admin', $administrator->getRoles()));
        $request = Request::create('/admin/seo/');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $this->requestStack->push($request);

        $data = ['organization' => $this->organizationSettingFacade->getSettings(Domain::FIRST_DOMAIN_ID)];
        $form = $this->formFactory->create(SeoSettingFormType::class, $data, ['domain_id' => Domain::FIRST_DOMAIN_ID, 'csrf_protection' => false]);
        $organizationForm = $form->get('organization');

        $organizationForm->submit(['name' => 'Submitted company', 'companyTaxNumber' => 'CZ12345678', 'city' => 'Ostrava']);
        $view = $form->createView();
        $organization = $organizationForm->getData();

        $this->assertInstanceOf(OrganizationData::class, $organization);
        $this->assertSame('Submitted company', $organization->name);
        $this->assertSame('CZ12345678', $organization->companyTaxNumber);
        $this->assertSame('Ostrava', $organization->city);
        $this->assertArrayHasKey('image', $view->children['organization']->children);
        $this->assertTrue($view->children['organization']->vars['renders_in_own_card']);
        $this->tokenStorage->setToken(null);
        $this->requestStack->pop();
    }
}
