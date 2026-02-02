<?php

declare(strict_types=1);

namespace Shopsys\Cli\Config;

use LogicException;

final class CoreDomainConfig
{
    /**
     * @var array<class-string<\Shopsys\Cli\Config\ConfigSectionInterface>, \Shopsys\Cli\Config\ConfigSectionInterface>
     */
    private array $configSections = [];

    /**
     * @param int $id
     * @param string $name
     * @param string $locale
     * @param string $timezone
     * @param string $type
     * @param string $currencyCode
     * @param bool $loadDemoData
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $locale,
        public readonly string $timezone,
        public readonly string $type,
        public readonly string $currencyCode,
        public readonly bool $loadDemoData,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param \Shopsys\Cli\Config\ConfigSectionRegistry|null $registry
     * @return self
     */
    public static function fromArray(array $data, ?ConfigSectionRegistry $registry = null): self
    {
        $domainConfig = new self(
            id: (int)$data['id'],
            name: CoreDomainConfigValidator::validateDomainName($data['name'] ?? ''),
            locale: CoreDomainConfigValidator::validateLocale($data['locale'] ?? ''),
            timezone: CoreDomainConfigValidator::validateTimeZone($data['timezone'] ?? ''),
            type: CoreDomainConfigValidator::validateDomainType($data['type'] ?? ''),
            currencyCode: CoreDomainConfigValidator::validateCurrencyCode($data['currency_code'] ?? ''),
            loadDemoData: (bool)($data['load_demo_data'] ?? true),
        );

        if ($registry !== null) {
            foreach ($registry->getDomainConfigSections() as $section) {
                $key = $section::getKey();
                $section->fromArray($data[$key] ?? []);
                $section->validate();
                $domainConfig->addConfigSection($section);
            }
        }

        return $domainConfig;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'type' => $this->type,
            'currency_code' => $this->currencyCode,
            'load_demo_data' => $this->loadDemoData,
        ];

        foreach ($this->configSections as $section) {
            $data[$section::getKey()] = $section->toArray();
        }

        return $data;
    }

    /**
     * @template T of \Shopsys\Cli\Config\ConfigSectionInterface
     * @param class-string<T> $sectionClass
     * @return T
     */
    public function getConfigSection(string $sectionClass): ConfigSectionInterface
    {
        return $this->configSections[$sectionClass] ?? throw new LogicException(sprintf('Unknown section class: %s', $sectionClass));
    }

    /**
     * @param \Shopsys\Cli\Config\ConfigSectionInterface $section
     */
    public function addConfigSection(ConfigSectionInterface $section): void
    {
        $this->configSections[$section::class] = $section;
    }
}
