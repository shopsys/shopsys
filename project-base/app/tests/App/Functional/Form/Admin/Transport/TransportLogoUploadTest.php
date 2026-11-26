<?php

declare(strict_types=1);

namespace Tests\App\Functional\Form\Admin\Transport;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\Model\Administrator\Administrator;
use League\Flysystem\MountManager;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public static function getLogos(): iterable
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">';
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAAAAAA6fptVAAAACklEQVR4nGNgAAAAAgABSK+kcQAAAABJRU5ErkJggg==', true);

        yield 'static logo' => ['logo.svg', $svg . '<circle r="8"/></svg>', 0];

        yield 'external reference' => ['logo.svg', $svg . '<image href="https://example.org/logo.png"/></svg>', 1];

        yield 'malformed SVG' => ['logo.svg', $svg . '<circle>', 1];

        yield 'valid PNG' => ['logo.png', $png, 0];

        yield 'truncated PNG' => ['logo.png', substr($png, 0, 16), 1];

        yield 'PNG with complete header but no pixel data' => ['logo.png', substr($png, 0, 33), 1];
    }

    #[DataProvider('getLogos')]
    public function testTransportAndGroupValidateLogo(
        string $originalFilename,
        string $content,
        int $expectedErrors,
    ): void {
        $this->createRequest();
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $this->tokenStorage->setToken(new UsernamePasswordToken($administrator, 'administration', $administrator->getRoles()));

        $transport = $this->formFactory->create(TransportFormType::class, $this->transportDataFactory->create(), ['transport' => null, 'csrf_protection' => false]);
        $group = $this->formFactory->create(TransportGroupFormType::class, $this->transportGroupDataFactory->create(), ['transportGroup' => null, 'csrf_protection' => false]);
        $filename = $this->fileUpload->getTemporaryFilename($originalFilename);
        $this->mountManager->write(
            'main://' . $this->fileUpload->getTemporaryFilepath($filename),
            $content,
        );

        try {
            $upload = ['uploadedFiles' => [$filename]];
            $transport->submit(['image' => ['image' => $upload]], false);
            $group->submit(['image' => $upload], false);

            foreach ([$transport->get('image')->get('image'), $group->get('image')] as $imageForm) {
                $this->assertSame([$filename], $imageForm->getData()->uploadedFiles);
                $errors = $imageForm->getErrors(true);
                $this->assertCount($expectedErrors, $errors, (string)$errors);

                foreach ($errors as $error) {
                    $this->assertSame('This file is not a valid image.', $error->getMessageTemplate());
                }
            }
        } finally {
            $this->fileUpload->tryDeleteTemporaryFile($filename);
        }
    }
}
