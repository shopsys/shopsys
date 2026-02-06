<?php

declare(strict_types=1);

namespace Shopsys\Cli\Config\Section;

use Override;
use RuntimeException;
use Shopsys\Cli\Config\ProjectConfigSectionInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MetatagSiteNameSection implements ProjectConfigSectionInterface
{
    public string $siteName = 'Shopsys Platform';

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getKey(): string
    {
        return 'metatag_site_name';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getPriority(): int
    {
        return 40;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function collectInteractive(SymfonyStyle $io): void
    {
        $io->section('Metatag Site Name');

        $this->siteName = $io->ask(
            'Enter the site name for metatag',
            $this->siteName,
            fn ($v) => $this->validateSiteNameInput($v),
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function fromArray(array $data): void
    {
        $this->siteName = (string)($data['site_name'] ?? $this->siteName);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function validate(): void
    {
        $this->assertSiteName($this->siteName);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'site_name' => $this->siteName,
        ];
    }

    protected function validateSiteNameInput(mixed $value): string
    {
        $string = trim((string)$value);
        $this->assertSiteName($string);

        return $string;
    }

    protected function assertSiteName(string $value): void
    {
        if ($value === '') {
            throw new RuntimeException('Site name cannot be empty');
        }

        if (mb_strlen($value) > 100) {
            throw new RuntimeException('Site name must be at most 100 characters');
        }
    }
}
