<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\DemoDataFactory;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

/**
 * Builds the array of a web content document for demo data, e.g.
 * $this->contentDocumentBuilder->create()->text('<p>Hello</p>')->products(['9177759'])->build()
 */
final class ContentDocumentBuilder
{
    private const array COLUMNS_TYPES_BY_LAYOUT = [
        '1/2-1/2' => 'columns-1-1',
        '1/3-2/3' => 'columns-1-2',
        '2/3-1/3' => 'columns-2-1',
        '1/3-1/3-1/3' => 'columns-1-1-1',
    ];

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $blocks = [];

    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {
    }

    public function create(): self
    {
        return new self($this->productRepository);
    }

    public function text(string $html): self
    {
        return $this->addBlock('text', ['html' => $html]);
    }

    public function image(int $mediaId, string $alt, ?string $link = null): self
    {
        return $this->addBlock('image', ['mediaId' => $mediaId, 'alt' => $alt, 'link' => $link]);
    }

    public function video(string $url): self
    {
        return $this->addBlock('video', ['url' => $url]);
    }

    public function map(string $embedUrl): self
    {
        return $this->addBlock('map', ['url' => $embedUrl]);
    }

    public function buttonLink(string $text, string $url): self
    {
        return $this->addBlock('button-link', ['text' => $text, 'url' => $url]);
    }

    public function faq(string $question, string $answerHtml): self
    {
        return $this->addBlock('faq', ['question' => $question, 'answer' => $answerHtml]);
    }

    /**
     * @param string[] $catnums
     */
    public function products(array $catnums): self
    {
        return $this->addBlock('products', ['productIds' => $this->productRepository->getProductIdsByCatnums($catnums)]);
    }

    /**
     * @param string $layout one of the keys of COLUMNS_TYPES_BY_LAYOUT, e.g. "1/3-2/3"
     * @param self[][] $slots builders of the blocks of each column
     */
    public function columns(string $layout, array $slots): self
    {
        $children = [];

        foreach ($slots as $slot) {
            $children[] = array_merge(...array_map(static fn (self $builder): array => $builder->blocks, $slot));
        }

        $this->blocks[] = [
            'id' => Uuid::uuid4()->toString(),
            'type' => self::COLUMNS_TYPES_BY_LAYOUT[$layout],
            'data' => [],
            'children' => $children,
        ];

        return $this;
    }

    /**
     * @return array{version: int, profile: string, blocks: array<int, array<string, mixed>>}
     */
    public function build(): array
    {
        return [
            'version' => ContentDocument::CURRENT_VERSION,
            'profile' => ContentProfileEnum::WEB,
            'blocks' => $this->blocks,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function addBlock(string $type, array $data): self
    {
        $this->blocks[] = ['id' => Uuid::uuid4()->toString(), 'type' => $type, 'data' => $data];

        return $this;
    }
}
