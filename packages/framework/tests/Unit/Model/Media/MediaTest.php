<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Media;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Media\Media;
use Shopsys\FrameworkBundle\Model\Media\MediaData;

class MediaTest extends TestCase
{
    public function testCreate(): void
    {
        $media = new Media($this->createMediaData('Banner', 800, 600));

        $this->assertSame('Banner', $media->getTitle());
        $this->assertSame(800, $media->getWidth());
        $this->assertSame(600, $media->getHeight());
        $this->assertNotNull($media->getCreatedAt());
        $this->assertSame('Banner', $media->toHumanReadable());
    }

    public function testEdit(): void
    {
        $media = new Media($this->createMediaData('Banner', 800, 600));

        $media->edit($this->createMediaData('New banner', 1024, 768));

        $this->assertSame('New banner', $media->getTitle());
        $this->assertSame(1024, $media->getWidth());
        $this->assertSame(768, $media->getHeight());
    }

    private function createMediaData(string $title, ?int $width, ?int $height): MediaData
    {
        $mediaData = new MediaData();
        $mediaData->title = $title;
        $mediaData->width = $width;
        $mediaData->height = $height;

        return $mediaData;
    }
}
