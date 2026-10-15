<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\Exception\ContentBlockTypeConflictException;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\Exception\UnknownContentBlockTypeException;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\DummyColumnsBlock;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\DummyImageBlock;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\DummyMailTextBlock;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\DummyTextBlock;

class ContentBlockRegistryTest extends TestCase
{
    public function testBlocksAreIndexedByType(): void
    {
        $text = new DummyTextBlock();
        $registry = new ContentBlockRegistry([$text, new DummyImageBlock()], []);

        $this->assertTrue($registry->has('text'));
        $this->assertFalse($registry->has('columns'));
        $this->assertSame($text, $registry->get('text'));
        $this->assertSame(['text', 'image'], array_keys($registry->getAll()));
    }

    public function testGetUnknownTypeThrows(): void
    {
        $registry = new ContentBlockRegistry([], []);

        $this->expectException(UnknownContentBlockTypeException::class);

        $registry->get('text');
    }

    public function testSameTypeTwiceThrows(): void
    {
        $registry = new ContentBlockRegistry([new DummyTextBlock(), new #[ContentBlock(type: 'text')] class extends DummyTextBlock {
        }], []);

        $this->expectException(ContentBlockTypeConflictException::class);

        $registry->getAll();
    }

    public function testDisabledTypesAreNotRegistered(): void
    {
        $registry = new ContentBlockRegistry([new DummyTextBlock(), new DummyImageBlock()], ['text']);

        $this->assertFalse($registry->has('text'));
        $this->assertSame(['image'], array_keys($registry->getAll()));
    }

    public function testGetForProfileFiltersByProfile(): void
    {
        $registry = $this->createRegistry();

        $this->assertSame(['text', 'image', 'columns'], array_keys($registry->getForProfile(ContentProfileEnum::WEB)));
        $this->assertSame(['mail-text', 'columns'], array_keys($registry->getForProfile(ContentProfileEnum::MAIL)));
    }

    public function testGetForProfileFiltersByAllowedTypes(): void
    {
        $registry = $this->createRegistry();

        $this->assertSame(['image'], array_keys($registry->getForProfile(ContentProfileEnum::WEB, ['image', 'mail-text'])));
    }

    public function testGetForProfileCanExcludeContainers(): void
    {
        $registry = $this->createRegistry();

        $this->assertSame(['text', 'image'], array_keys($registry->getForProfile(ContentProfileEnum::WEB, null, false)));
    }

    private function createRegistry(): ContentBlockRegistry
    {
        return new ContentBlockRegistry([
            new DummyTextBlock(),
            new DummyImageBlock(),
            new DummyMailTextBlock(),
            new DummyColumnsBlock(),
        ], []);
    }
}
