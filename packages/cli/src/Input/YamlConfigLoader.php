<?php

declare(strict_types=1);

namespace Shopsys\Cli\Input;

use InvalidArgumentException;
use RuntimeException;
use Shopsys\Cli\Config\ConfigSectionRegistry;
use Shopsys\Cli\Config\CoreProjectConfig;
use Symfony\Component\Yaml\Yaml;

final class YamlConfigLoader
{
    public function __construct(
        private readonly ConfigSectionRegistry $registry,
    ) {
    }

    public function load(string $configPath): CoreProjectConfig
    {
        if (!file_exists($configPath)) {
            throw new InvalidArgumentException(sprintf('Configuration file not found: %s', $configPath));
        }

        $content = file_get_contents($configPath);

        if ($content === false) {
            throw new RuntimeException(sprintf('Could not read configuration file: %s', $configPath));
        }

        $data = Yaml::parse($content);

        if (!is_array($data)) {
            throw new InvalidArgumentException('Invalid YAML configuration: root must be an array');
        }

        if (count($data['domains']) === 0) {
            throw new RuntimeException('At least one domain is required');
        }

        return CoreProjectConfig::fromArray($data, $this->registry);
    }
}
