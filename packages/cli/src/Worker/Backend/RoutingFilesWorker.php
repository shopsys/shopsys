<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Backend;

use Override;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Model\FileHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;
use Symfony\Component\Finder\Finder;

final class RoutingFilesWorker extends AbstractWorker
{
    private const string ROUTING_DIR = 'app/config/shopsys-routing';
    private const string ROUTING_FRONT_FILE_NAME_PATTERN = 'routing_front_*.yaml';

    public function __construct(
        private readonly FileHandler $fileHandler,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDescription(): string
    {
        return 'Creates routing_front_{locale}.yaml files for each unique locale';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 952;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult
    {
        $filesCreated = [];

        foreach ($config->getUniqueLocales() as $locale) {
            $filePath = $this->getRoutingFilePath($locale);
            $fullFilePath = $projectPath . '/' . $filePath;

            if (file_exists($fullFilePath)) {
                continue;
            }

            $this->fileHandler->copyFile(
                $projectPath . '/' . $this->getRoutingFilePath('en'),
                $fullFilePath,
            );

            $filesCreated[] = $filePath;
        }

        $filesDeleted = $this->deleteUnnecessaryRoutingFiles($config, $projectPath);

        $hints = [];

        if (count($filesCreated) > 0) {
            $hints = [
                'Remember to translate route paths in these files: ',
                ...(array_map(static fn (string $fileName): string => ' - ' . $fileName, $filesCreated)),
            ];
        }

        return WorkerResult::success(
            'Routing files created',
            filesCreated: $filesCreated,
            filesDeleted: $filesDeleted,
            hints: $hints,
        );
    }

    /**
     * @return string[]
     */
    private function deleteUnnecessaryRoutingFiles(CoreProjectConfig $config, string $projectPath): array
    {
        $existingFiles = Finder::create()
            ->files()
            ->in($projectPath . '/' . self::ROUTING_DIR)
            ->name(self::ROUTING_FRONT_FILE_NAME_PATTERN);

        $filesDeleted = [];

        foreach ($existingFiles as $existingFile) {
            $locale = str_replace(
                ['routing_front_', '.yaml'],
                '',
                $existingFile->getFilename(),
            );

            if (in_array($locale, $config->getUniqueLocales(), true)) {
                continue;
            }

            $this->fileHandler->deleteFile($existingFile->getRealPath());
            $filesDeleted[] = self::ROUTING_DIR . '/' . $existingFile->getFilename();
        }

        return $filesDeleted;
    }

    private function getRoutingFilePath(string $locale): string
    {
        return self::ROUTING_DIR . '/' . str_replace('*', $locale, self::ROUTING_FRONT_FILE_NAME_PATTERN);
    }
}
