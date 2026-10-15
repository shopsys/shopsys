<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Media\MediaDataFactory;
use Shopsys\FrameworkBundle\Model\Media\MediaFacade;

final class MediaDataFixture extends AbstractReferenceFixture
{
    public const string MEDIA_PREFIX = 'media_';
    public const int MEDIA_COUNT = 6;

    // The images themselves are added by ImageDataFixture, so the size of the demo files is set here.
    private const int IMAGE_WIDTH = 512;
    private const int IMAGE_HEIGHT = 352;

    public function __construct(
        private readonly MediaFacade $mediaFacade,
        private readonly MediaDataFactory $mediaDataFactory,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        for ($number = 1; $number <= self::MEDIA_COUNT; $number++) {
            $mediaData = $this->mediaDataFactory->create();
            $mediaData->title = 'Demo image ' . $number;
            $mediaData->width = self::IMAGE_WIDTH;
            $mediaData->height = self::IMAGE_HEIGHT;

            $this->addReference(self::MEDIA_PREFIX . $number, $this->mediaFacade->create($mediaData));
        }
    }
}
