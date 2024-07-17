<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Image;

use App\Component\FileUpload\FileUpload;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Tests\App\Test\TransactionFunctionalTestCase;

class ImageMultipleFlushTest extends TransactionFunctionalTestCase
{
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
    private ImageLocator $imageLocator;

    /**
     * @inject
     */
    private ParameterBagInterface $parameterBag;

    public function testImageCanBeUpdatedAfterFlush(): void
    {
        $testImageName = 'image.jpg';
        $localImagePath = 'local://' . __DIR__ . '/Resources/' . $testImageName;
        $abstractImagePath = 'main://' . $this->fileUpload->getTemporaryDirectory() . '/' . $testImageName;
        $this->mountManager->copy($localImagePath, $abstractImagePath);

        $image = new Image(
            'product',
            856,
            [
                'cs' => 'produkt',
                'en' => 'the product',
            ],
            $testImageName,
            null,
        );

        $this->em->persist($image);
        $this->em->flush();

        $image->setPosition(5);
        $this->em->flush();

        $this->assertFileExists(
            $this->parameterBag->get('kernel.project_dir') .
            $this->imageLocator->getAbsoluteImageFilepath($image),
        );
    }
}
