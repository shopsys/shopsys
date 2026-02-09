<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Backend;

use Override;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Config\Section\SocialLoginSection;
use Shopsys\Cli\Model\YamlHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;

final class SocialNetworkConfigWorker extends AbstractWorker
{
    private const string FILE_PATH = 'app/config/packages/social_network_config.yaml';

    public function __construct(
        private readonly YamlHandler $yamlHandler,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDescription(): string
    {
        return 'Updates social login enabledOnDomains in social_network_config.yaml';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 784;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult
    {
        $filePath = $projectPath . '/' . self::FILE_PATH;

        $data = $this->yamlHandler->readYaml($filePath);

        if (!isset($data['parameters']['social_network_login_config']['providers'])) {
            return WorkerResult::failure('No social login providers configured in social_network_config.yaml');
        }

        $enabledDomains = $this->buildEnabledDomainsMap($config);

        foreach ($data['parameters']['social_network_login_config']['providers'] as $providerName => &$providerConfig) {
            if (!array_key_exists($providerName, $enabledDomains)) {
                return WorkerResult::failure(sprintf(
                    'Provider "%s" missing in social_login configuration section',
                    $providerName,
                ));
            }

            $providerConfig['enabledOnDomains'] = $enabledDomains[$providerName];
        }
        unset($providerConfig);

        $this->yamlHandler->writeYaml($filePath, $data, 6);

        return WorkerResult::success(
            'Updated social_network_config.yaml',
            filesModified: [self::FILE_PATH],
        );
    }

    /**
     * @return array<string, array<int>>
     */
    private function buildEnabledDomainsMap(CoreProjectConfig $config): array
    {
        $enabledDomains = [
            'facebook' => [],
            'google' => [],
            'seznam' => [],
        ];

        foreach ($config->domains as $domain) {
            $socialLogin = $domain->getConfigSection(SocialLoginSection::class);

            if ($socialLogin->facebook) {
                $enabledDomains['facebook'][] = $domain->id;
            }

            if ($socialLogin->google) {
                $enabledDomains['google'][] = $domain->id;
            }

            if ($socialLogin->seznam) {
                $enabledDomains['seznam'][] = $domain->id;
            }
        }

        return $enabledDomains;
    }
}
