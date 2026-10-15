<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Document;

use Shopsys\FrameworkBundle\Component\ContentEditor\Document\Exception\UnsupportedContentDocumentVersionException;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Webmozart\Assert\Assert;

class ContentDocumentFactory
{
    public function createEmpty(string $profile): ContentDocument
    {
        return new ContentDocument(ContentDocument::CURRENT_VERSION, $profile, []);
    }

    /**
     * @param array<mixed> $data
     */
    public function createFromArray(array $data): ContentDocument
    {
        $version = $data['version'] ?? ContentDocument::CURRENT_VERSION;

        if ($version !== ContentDocument::CURRENT_VERSION) {
            throw new UnsupportedContentDocumentVersionException($version);
        }

        $profile = is_string($data['profile'] ?? null) ? $data['profile'] : ContentProfileEnum::WEB;

        return new ContentDocument($version, $profile, $this->createBlocks($data['blocks'] ?? null));
    }

    public function createFromJson(?string $json): ContentDocument
    {
        if ($json === null || trim($json) === '') {
            return $this->createEmpty(ContentProfileEnum::WEB);
        }

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        Assert::isArray($data, 'Content document JSON must be an object');

        return $this->createFromArray($data);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[]
     */
    protected function createBlocks(mixed $blocksData): array
    {
        if (!is_array($blocksData)) {
            return [];
        }

        $blocks = [];

        foreach ($blocksData as $blockData) {
            if (is_array($blockData)) {
                $blocks[] = $this->createBlock($blockData);
            }
        }

        return $blocks;
    }

    /**
     * @param array<mixed> $blockData
     */
    protected function createBlock(array $blockData): ContentBlockInstance
    {
        $children = null;

        if (is_array($blockData['children'] ?? null)) {
            $children = array_map(
                fn (mixed $slot): array => $this->createBlocks($slot),
                array_values($blockData['children']),
            );
        }

        return new ContentBlockInstance(
            is_string($blockData['id'] ?? null) ? $blockData['id'] : '',
            is_string($blockData['type'] ?? null) ? $blockData['type'] : '',
            is_array($blockData['data'] ?? null) ? $blockData['data'] : [],
            $children,
        );
    }
}
