<?php

declare(strict_types=1);

namespace Shopsys\Cli\Config;

use LogicException;

final class CoreProjectConfig
{
    /**
     * @var array<class-string<\Shopsys\Cli\Config\ConfigSectionInterface>, \Shopsys\Cli\Config\ConfigSectionInterface>
     */
    private array $sections = [];

    /**
     * @param array<\Shopsys\Cli\Config\CoreDomainConfig> $domains
     */
    public function __construct(
        public readonly array $domains = [],
    ) {
    }

    /**
     * @return array<string>
     */
    public function getUniqueLocales(): array
    {
        $locales = [];

        foreach ($this->domains as $domain) {
            if (!in_array($domain->locale, $locales, true)) {
                $locales[] = $domain->locale;
            }
        }

        return $locales;
    }

    /**
     * @return array<string>
     */
    public function getUniqueCurrencies(): array
    {
        $currencies = [];

        foreach ($this->domains as $domain) {
            if (!in_array($domain->currencyCode, $currencies, true)) {
                $currencies[] = $domain->currencyCode;
            }
        }

        return $currencies;
    }

    /**
     * @return array<int>
     */
    public function getAllDomainIds(): array
    {
        return array_map(static fn (CoreDomainConfig $domain) => $domain->id, $this->domains);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data, ?ConfigSectionRegistry $registry = null): self
    {
        $domains = [];

        if (isset($data['domains']) && is_array($data['domains'])) {
            foreach ($data['domains'] as $domainData) {
                $domains[] = CoreDomainConfig::fromArray($domainData, $registry);
            }
        }

        $projectConfig = new self(
            domains: $domains,
        );

        if ($registry !== null) {
            foreach ($registry->getProjectConfigSections() as $section) {
                $key = $section::getKey();
                $section->fromArray($data[$key] ?? []);
                $section->validate();
                $projectConfig->addConfigSection($section);
            }
        }

        return $projectConfig;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'domains' => array_map(
                static fn (CoreDomainConfig $domain) => $domain->toArray(),
                $this->domains,
            ),
        ];

        foreach ($this->sections as $section) {
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
        return $this->sections[$sectionClass] ?? throw new LogicException(sprintf('Unknown section class: %s', $sectionClass));
    }

    public function addConfigSection(ConfigSectionInterface $section): void
    {
        $this->sections[$section::class] = $section;
    }
}
