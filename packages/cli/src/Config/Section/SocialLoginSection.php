<?php

declare(strict_types=1);

namespace Shopsys\Cli\Config\Section;

use Override;
use Shopsys\Cli\Config\DomainConfigSectionInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SocialLoginSection implements DomainConfigSectionInterface
{
    public bool $facebook = false;

    public bool $google = false;

    public bool $seznam = false;

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getKey(): string
    {
        return 'social_login';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function collectInteractive(SymfonyStyle $io, int $domainId): void
    {
        $io->section(sprintf('Social Login Settings for Domain %d', $domainId));

        $this->facebook = $io->confirm('Enable Facebook login?', $this->facebook);
        $this->google = $io->confirm('Enable Google login?', $this->google);
        $this->seznam = $io->confirm('Enable Seznam login?', $this->seznam);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function fromArray(array $data): void
    {
        $this->facebook = (bool)($data['facebook'] ?? $this->facebook);
        $this->google = (bool)($data['google'] ?? $this->google);
        $this->seznam = (bool)($data['seznam'] ?? $this->seznam);
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
            'facebook' => $this->facebook,
            'google' => $this->google,
            'seznam' => $this->seznam,
        ];
    }
}
