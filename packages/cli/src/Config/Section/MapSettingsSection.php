<?php

declare(strict_types=1);

namespace Shopsys\Cli\Config\Section;

use Override;
use RuntimeException;
use Shopsys\Cli\Config\DomainConfigSectionInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MapSettingsSection implements DomainConfigSectionInterface
{
    public float $latitude = 49.956;

    public float $longitude = 15.5173;

    public int $zoom = 7;

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getKey(): string
    {
        return 'map_settings';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function collectInteractive(SymfonyStyle $io, int $domainId): void
    {
        $io->section(sprintf('Map Settings for Domain %d', $domainId));

        $this->latitude = (float)$io->ask(
            'Map center latitude (-90 to 90)',
            (string)$this->latitude,
            fn ($v) => $this->validateLatitudeInput($v),
        );

        $this->longitude = (float)$io->ask(
            'Map center longitude (-180 to 180)',
            (string)$this->longitude,
            fn ($v) => $this->validateLongitudeInput($v),
        );

        $this->zoom = (int)$io->ask(
            'Map zoom level (1-20)',
            (string)$this->zoom,
            fn ($v) => $this->validateZoomInput($v),
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function fromArray(array $data): void
    {
        $this->latitude = (float)($data['latitude'] ?? $this->latitude);
        $this->longitude = (float)($data['longitude'] ?? $this->longitude);
        $this->zoom = (int)($data['zoom'] ?? $this->zoom);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function validate(): void
    {
        $this->assertLatitude($this->latitude);
        $this->assertLongitude($this->longitude);
        $this->assertZoom($this->zoom);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'zoom' => $this->zoom,
        ];
    }

    protected function validateLatitudeInput(mixed $value): float
    {
        $float = (float)$value;
        $this->assertLatitude($float);

        return $float;
    }

    protected function validateLongitudeInput(mixed $value): float
    {
        $float = (float)$value;
        $this->assertLongitude($float);

        return $float;
    }

    protected function validateZoomInput(mixed $value): int
    {
        $int = (int)$value;
        $this->assertZoom($int);

        return $int;
    }

    protected function assertLatitude(float $value): void
    {
        if ($value < -90 || $value > 90) {
            throw new RuntimeException('Latitude must be between -90 and 90');
        }
    }

    protected function assertLongitude(float $value): void
    {
        if ($value < -180 || $value > 180) {
            throw new RuntimeException('Longitude must be between -180 and 180');
        }
    }

    protected function assertZoom(int $value): void
    {
        if ($value < 1 || $value > 20) {
            throw new RuntimeException('Zoom must be between 1 and 20');
        }
    }
}
