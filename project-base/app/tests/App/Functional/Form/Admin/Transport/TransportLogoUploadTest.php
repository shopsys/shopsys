<?php

declare(strict_types=1);

namespace Tests\App\Functional\Form\Admin\Transport;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\Model\Administrator\Administrator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Form\Admin\Transport\TransportFormType;
use Shopsys\FrameworkBundle\Form\Admin\Transport\TransportGroupFormType;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory;
use Shopsys\FrameworkBundle\Model\Transport\TransportGroupDataFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\App\Test\FunctionalTestCase;

final class TransportLogoUploadTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private FormFactoryInterface $formFactory;

    /**
     * @inject
     */
    private TransportDataFactory $transportDataFactory;

    /**
     * @inject
     */
    private TransportGroupDataFactory $transportGroupDataFactory;

    /**
     * @inject
     */
    private TokenStorageInterface $tokenStorage;

    /**
     * @inject
     */
    private FileUpload $fileUpload;

    /**
     * @inject
     */
    private MountManager $mountManager;

    public function testTransportAndGroupAcceptSvgLogo(): void
    {
        $this->createRequest();
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $this->tokenStorage->setToken(new UsernamePasswordToken($administrator, 'administration', $administrator->getRoles()));

        $transport = $this->formFactory->create(TransportFormType::class, $this->transportDataFactory->create(), ['transport' => null]);
        $group = $this->formFactory->create(TransportGroupFormType::class, $this->transportGroupDataFactory->create(), ['transportGroup' => null]);
        $filename = $this->fileUpload->getTemporaryFilename('logo.svg');
        $this->mountManager->write(
            'main://' . $this->fileUpload->getTemporaryFilepath($filename),
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><circle cx="10" cy="10" r="8"/></svg>',
        );

        try {
            $upload = ['uploadedFiles' => [$filename]];
            $transport->submit(['image' => ['image' => $upload]], false);
            $group->submit(['image' => $upload], false);

            foreach ([$transport->get('image')->get('image'), $group->get('image')] as $imageForm) {
                $this->assertSame([$filename], $imageForm->getData()->uploadedFiles);
                $this->assertCount(0, $imageForm->getErrors(true), (string)$imageForm->getErrors(true));
            }
        } finally {
            $this->fileUpload->tryDeleteTemporaryFile($filename);
        }
    }
}
