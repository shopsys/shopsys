<?php

declare(strict_types=1);

namespace Shopsys\Cli\Input;

use RuntimeException;
use Shopsys\Cli\Config\ConfigSectionRegistry;
use Shopsys\Cli\Config\CoreDomainConfig;
use Shopsys\Cli\Config\CoreDomainConfigValidator;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Exception\CancelledException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

final class InteractiveInputCollector
{
    public function __construct(
        private readonly ConfigSectionRegistry $registry,
    ) {
    }

    public function collect(SymfonyStyle $io): CoreProjectConfig
    {
        $this->displayHeader($io);

        $domains = $this->collectDomains($io);

        $config = new CoreProjectConfig($domains);

        foreach ($this->registry->getProjectConfigSections() as $projectConfigSection) {
            $projectConfigSection->collectInteractive($io);
            $projectConfigSection->validate();

            $config->addConfigSection($projectConfigSection);
        }

        $this->displaySummary($config, $io);

        if (!$io->confirm('Proceed with configuration?')) {
            throw new CancelledException('Configuration cancelled by user');
        }

        return $config;
    }

    private function displayHeader(SymfonyStyle $io): void
    {
        $io->writeln('');
        $io->writeln('<fg=cyan>╔══════════════════════════════════════════════════════════════╗</>');
        $io->writeln('<fg=cyan>║                 ⚒️ Shopsys Cli ⚒️                            ║</>');
        $io->writeln('<fg=cyan>║              Project Configuration Tool                      ║</>');
        $io->writeln('<fg=cyan>╚══════════════════════════════════════════════════════════════╝</>');
        $io->writeln('');
    }

    /**
     * @return array<\Shopsys\Cli\Config\CoreDomainConfig>
     */
    private function collectDomains(SymfonyStyle $io): array
    {
        $domainCount = (int)$io->ask(
            'How many domains will this project have?',
            '2',
            function (string $answer): string {
                $count = (int)$answer;

                if ($count < 1 || $count > 100) {
                    throw new RuntimeException('Domain count must be between 1 and 100');
                }

                return $answer;
            },
        );

        $domains = [];

        for ($domainId = 1; $domainId <= $domainCount; $domainId++) {
            $domain = $this->collectCoreFields($io, $domainId);

            foreach ($this->registry->getDomainConfigSections() as $domainConfigSection) {
                $domainConfigSection->collectInteractive($io, $domainId);
                $domainConfigSection->validate();

                $domain->addConfigSection($domainConfigSection);
            }

            $domains[] = $domain;
        }

        return $domains;
    }

    private function collectCoreFields(SymfonyStyle $io, int $domainId): CoreDomainConfig
    {
        $io->section(sprintf('Domain %d Configuration', $domainId));

        return new CoreDomainConfig(
            id: $domainId,
            name: $io->ask(
                'Domain name',
                sprintf('Domain %d', $domainId),
                CoreDomainConfigValidator::validateDomainName(...),
            ),
            locale: $io->ask(
                'Locale (e.g., en, cs, de, fr)',
                'en',
                CoreDomainConfigValidator::validateLocale(...),
            ),
            timezone: $io->ask(
                'Timezone',
                'Europe/Prague',
                CoreDomainConfigValidator::validateTimeZone(...),
            ),
            type: $io->choice(
                'Domain type',
                CoreDomainConfigValidator::SUPPORTED_DOMAIN_TYPES,
                array_first(CoreDomainConfigValidator::SUPPORTED_DOMAIN_TYPES),
            ),
            currencyCode: $io->ask(
                'Currency code (e.g., EUR, CZK)',
                'EUR',
                CoreDomainConfigValidator::validateCurrencyCode(...),
            ),
            loadDemoData: $io->confirm('Load demo data?', $domainId < 3),
        );
    }

    private function displaySummary(CoreProjectConfig $config, SymfonyStyle $io): void
    {
        $io->section('Configuration Summary');

        $yamlConfig = Yaml::dump($config->toArray(), 4);

        $io->newLine();
        $io->writeln($yamlConfig);
        $io->newLine();
    }
}
