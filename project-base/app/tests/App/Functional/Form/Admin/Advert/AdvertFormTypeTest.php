<?php

declare(strict_types=1);

namespace Tests\App\Functional\Form\Admin\Advert;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\DataFixtures\Demo\AdvertDataFixture;
use App\Model\Administrator\Administrator;
use League\Flysystem\MountManager;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Form\Admin\Advert\AdvertFormType;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Advert\AdvertDataFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\App\Test\FunctionalTestCase;

final class AdvertFormTypeTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private FormFactoryInterface $formFactory;

    /**
     * @inject
     */
    private AdvertDataFactory $advertDataFactory;

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

    private ?string $temporaryFilename = null;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createRequest();
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $this->tokenStorage->setToken(new UsernamePasswordToken($administrator, 'administration', $administrator->getRoles()));
    }

    #[Override]
    protected function tearDown(): void
    {
        if ($this->temporaryFilename !== null) {
            $this->fileUpload->tryDeleteTemporaryFile($this->temporaryFilename);
        }

        parent::tearDown();
    }

    /**
     * @param array<string> $invalidFields
     */
    #[DataProvider('getImageValidationData')]
    public function testAdvertRequiresBothImages(
        bool $existingImages,
        bool $deleteWeb,
        bool $deleteMobile,
        bool $uploadWeb,
        bool $uploadMobile,
        string $advertType,
        array $invalidFields,
    ): void {
        $advert = $existingImages
            ? $this->getReference(AdvertDataFixture::FOOTER_ADVERT_REFERENCE_PREFIX . Domain::FIRST_DOMAIN_ID, Advert::class)
            : null;
        $advertData = $advert === null
            ? $this->advertDataFactory->create()
            : $this->advertDataFactory->createFromAdvert($advert);
        $advertData->type = $advertType;
        $advertData->code = '<p>Advertisement</p>';
        $form = $this->formFactory->create(AdvertFormType::class, $advertData, [
            'scenario' => $advert === null ? AdvertFormType::SCENARIO_CREATE : AdvertFormType::SCENARIO_EDIT,
            'advert' => $advert,
            'web_image_exists' => $existingImages,
            'mobile_image_exists' => $existingImages,
            'csrf_protection' => false,
        ]);

        if ($existingImages) {
            $this->assertCount(1, $advertData->image->orderedImages);
            $this->assertCount(1, $advertData->mobileImage->orderedImages);
        }

        if ($uploadWeb || $uploadMobile) {
            $this->temporaryFilename = $this->fileUpload->getTemporaryFilename('image.jpg');
            $this->mountManager->copy(
                'local://' . __DIR__ . '/../../../Component/Image/Resources/image.jpg',
                'main://' . $this->fileUpload->getTemporaryFilepath($this->temporaryFilename),
            );
        }

        $form->submit([
            'image_group' => [
                'image' => [
                    'imagesToDelete' => $deleteWeb ? [(string)reset($advertData->image->orderedImages)->getId()] : [],
                    'uploadedFiles' => $uploadWeb ? [$this->temporaryFilename] : [],
                ],
                'mobileImage' => [
                    'imagesToDelete' => $deleteMobile ? [(string)reset($advertData->mobileImage->orderedImages)->getId()] : [],
                    'uploadedFiles' => $uploadMobile ? [$this->temporaryFilename] : [],
                ],
            ],
        ], false);

        $this->assertSame($invalidFields === [], $form->isValid(), (string)$form->getErrors(true));

        foreach (['image', 'mobileImage'] as $field) {
            $errors = $form->get('image_group')->get($field)->getErrors(true);
            $this->assertCount(in_array($field, $invalidFields, true) ? 1 : 0, $errors);

            foreach ($errors as $error) {
                $this->assertSame('Choose image', $error->getMessageTemplate());
            }
        }
    }

    /**
     * @return iterable<string, array{bool, bool, bool, bool, bool, string, array<string>}>
     */
    public static function getImageValidationData(): iterable
    {
        yield 'new advert without images' => [false, false, false, false, false, Advert::TYPE_IMAGE, ['image', 'mobileImage']];

        yield 'new advert without mobile image' => [false, false, false, true, false, Advert::TYPE_IMAGE, ['mobileImage']];

        yield 'new advert without desktop image' => [false, false, false, false, true, Advert::TYPE_IMAGE, ['image']];

        yield 'new advert with both images' => [false, false, false, true, true, Advert::TYPE_IMAGE, []];

        yield 'existing images kept' => [true, false, false, false, false, Advert::TYPE_IMAGE, []];

        yield 'desktop image deleted' => [true, true, false, false, false, Advert::TYPE_IMAGE, ['image']];

        yield 'mobile image deleted' => [true, false, true, false, false, Advert::TYPE_IMAGE, ['mobileImage']];

        yield 'both images deleted' => [true, true, true, false, false, Advert::TYPE_IMAGE, ['image', 'mobileImage']];

        yield 'desktop image replaced' => [true, true, false, true, false, Advert::TYPE_IMAGE, []];

        yield 'mobile image replaced' => [true, false, true, false, true, Advert::TYPE_IMAGE, []];

        yield 'both images replaced' => [true, true, true, true, true, Advert::TYPE_IMAGE, []];

        yield 'HTML advert without images' => [false, false, false, false, false, Advert::TYPE_CODE, []];

        yield 'switch to HTML and delete images' => [true, true, true, false, false, Advert::TYPE_CODE, []];
    }
}
