<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Backend;

use Override;
use RuntimeException;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Model\YamlHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;

final class AdminLocaleWorker extends AbstractWorker
{
    private const string FILE_PATH = 'app/config/parameters_common.yaml';

    /**
     * @param \Shopsys\Cli\Model\YamlHandler $yamlHandler
     */
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
        return 'Set allowed administration locales in parameters_common.yaml';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 1000;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult
    {
        $filePath = $projectPath . '/' . self::FILE_PATH;

        try {
            $data = $this->yamlHandler->readYaml($filePath);

            $data['parameters']['shopsys.allowed_admin_locales'] = $config->getUniqueLocales();
            $data['parameters']['locale'] = $config->domains[0]->locale;

            $this->yamlHandler->writeYaml($filePath, $data);

            return WorkerResult::success(
                'Allowed admin locales set',
                filesModified: [self::FILE_PATH],
            );
        } catch (RuntimeException $e) {
            return WorkerResult::failure($e->getMessage());
        }
    }
}
