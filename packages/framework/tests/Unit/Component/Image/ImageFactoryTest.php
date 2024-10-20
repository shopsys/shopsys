<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Image;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileRepository;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\EntityMultipleImageException;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageFactory;
use Shopsys\FrameworkBundle\Component\Image\ImageRepository;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class ImageFactoryTest extends TestCase
{
    public function testCreateMultipleException(): void
    {
        $imageEntityConfig = new ImageEntityConfig('entityName', 'entityClass', [], ['type' => false]);

        $imageProcessorMock = $this->getMockBuilder(ImageProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $imageFactory = new ImageFactory($imageProcessorMock, $this->getFileUpload(), new EntityNameResolver([]));

        $this->expectException(EntityMultipleImageException::class);
        $imageFactory->createMultiple($imageEntityConfig, 1, ['test1.png', 'test2.png'], ['test1_tmp.png', 'test2_tmp.png'], 'type');
    }

    public function testCreateMultiple(): void
    {
        $imageEntityConfig = new ImageEntityConfig('entityName', 'entityClass', [], ['type' => true]);
        $filenames = ['filename1.jpg', 'filename2.png'];

        $imageProcessorMock = $this->getMockBuilder(ImageProcessor::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['convertToShopFormatAndGetNewFilename'])
            ->getMock();
        $imageProcessorMock->expects($this->any())->method('convertToShopFormatAndGetNewFilename')
            ->willReturnCallback(function ($filepath) {
                return pathinfo($filepath, PATHINFO_BASENAME);
            });

        $imageFactory = new ImageFactory($imageProcessorMock, $this->getFileUpload(), new EntityNameResolver([]));
        $images = $imageFactory->createMultiple($imageEntityConfig, 1, [], $filenames, 'type');

        $this->assertCount(2, $images);

        foreach ($images as $image) {
            $temporaryFiles = $image->getTemporaryFilesForUpload();
            $this->assertSame(1, $image->getEntityId());
            $this->assertSame('entityName', $image->getEntityName());
            $this->assertContains(array_pop($temporaryFiles)->getTemporaryFilename(), $filenames);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload
     */
    private function getFileUpload(): FileUpload
    {
        $fileNamingConvention = new FileNamingConvention();
        $mountManager = new MountManager();
        $abstractFilesystem = $this->createMock(FilesystemOperator::class);
        $parameterBag = new ParameterBag();
        $parameterBag->set('kernel.project_dir', sys_get_temp_dir());
        $imageRepositoryMock = $this->createMock(ImageRepository::class);
        $customerUploadedFileRepositoryMock = $this->createMock(CustomerUploadedFileRepository::class);
        $inMemoryCache = new InMemoryCache();

        return new FileUpload(
            'temporaryDir',
            [Image::class => 'imageDir'],
            $fileNamingConvention,
            $mountManager,
            $abstractFilesystem,
            $parameterBag,
            $imageRepositoryMock,
            $customerUploadedFileRepositoryMock,
            $inMemoryCache,
        );
    }
}
