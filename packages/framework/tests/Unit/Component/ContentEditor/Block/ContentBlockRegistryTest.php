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

    public function testHigherPriorityBlockWinsRegardlessOfOrder(): void
    {
        $override = new #[ContentBlock(type: 'text', priority: 10)] class extends DummyTextBlock {
        };

        $registry = new ContentBlockRegistry([$override, new DummyTextBlock()], []);
        $reversedRegistry = new ContentBlockRegistry([new DummyTextBlock(), $override], []);

        $this->assertSame($override, $registry->get('text'));
        $this->assertSame($override, $reversedRegistry->get('text'));
    }

    public function testEqualPriorityForSameTypeThrows(): void
    {
        $registry = new ContentBlockRegistry([new DummyTextBlock(), new #[ContentBlock(type: 'text')] class extends DummyTextBlock {
        }], []);

        $this->expectException(ContentBlockTypeConflictException::class);

        $registry->getAll();
    }

    public function testEqualPriorityBelowHigherPriorityWinnerDoesNotConflict(): void
    {
        $winner = new #[ContentBlock(type: 'text', priority: 5)] class extends DummyTextBlock {
        };
        $registry = new ContentBlockRegistry([
            new DummyTextBlock(),
            new #[ContentBlock(type: 'text')] class extends DummyTextBlock {
            },
            $winner,
        ], []);

        $this->assertSame($winner, $registry->get('text'));
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
