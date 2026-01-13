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
     * @param int $inline
     */
    public function writeYaml(string $path, array $data, int $inline = 4): void
    {
        $content = Yaml::dump($data, $inline, 4, Yaml::DUMP_NULL_AS_TILDE | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE);
        $this->fileHandler->writeFile($path, $content);
    }
}
