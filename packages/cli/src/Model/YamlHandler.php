<?php

declare(strict_types=1);

namespace Shopsys\Cli\Model;

use Symfony\Component\Yaml\Yaml;

final class YamlHandler
{
    /**
     * @param \Shopsys\Cli\Model\FileHandler $fileHandler
     */
    public function __construct(
        private readonly FileHandler $fileHandler,
    ) {
    }

    /**
     * @param string $path
     * @return array<mixed>
     */
    public function readYaml(string $path): array
    {
        $content = $this->fileHandler->readFile($path);
        $parsed = Yaml::parse($content);

        return is_array($parsed) ? $parsed : [];
    }

    /**
     * @param string $path
     * @param array<mixed> $data
     */
    public function writeYaml(string $path, array $data): void
    {
        $content = Yaml::dump($data, 4, 4, Yaml::DUMP_NULL_AS_TILDE);
        $this->fileHandler->writeFile($path, $content);
    }
}
