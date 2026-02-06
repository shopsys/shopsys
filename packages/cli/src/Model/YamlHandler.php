<?php

declare(strict_types=1);

namespace Shopsys\Cli\Model;

use Symfony\Component\Yaml\Yaml;

final class YamlHandler
{
    public function __construct(
        private readonly FileHandler $fileHandler,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function readYaml(string $path): array
    {
        $content = $this->fileHandler->readFile($path);
        $parsed = Yaml::parse($content);

        return is_array($parsed) ? $parsed : [];
    }

    /**
     * @param array<mixed> $data
     */
    public function writeYaml(string $path, array $data, int $inline = 4): void
    {
        $content = Yaml::dump($data, $inline, 4, Yaml::DUMP_NULL_AS_TILDE | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE);
        $this->fileHandler->writeFile($path, $content);
    }
}
