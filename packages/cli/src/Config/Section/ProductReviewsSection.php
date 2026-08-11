<?php

declare(strict_types=1);

namespace Shopsys\Cli\Config\Section;

use Override;
use Shopsys\Cli\Config\DomainConfigSectionInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProductReviewsSection implements DomainConfigSectionInterface
{
    public bool $enabled = true;

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getKey(): string
    {
        return 'product_reviews';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function collectInteractive(SymfonyStyle $io, int $domainId): void
    {
        $io->section(sprintf('Product Reviews Settings for Domain %d', $domainId));

        $this->enabled = $io->confirm('Enable product reviews?', $this->enabled);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function fromArray(array $data): void
    {
        $this->enabled = (bool)($data['enabled'] ?? $this->enabled);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function validate(): void
    {
        // No specific validation needed for boolean values
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
        ];
    }
}
