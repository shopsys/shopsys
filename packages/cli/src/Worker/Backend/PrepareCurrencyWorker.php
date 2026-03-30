<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Backend;

use CommerceGuys\Intl\Currency\CurrencyRepository;
use CommerceGuys\Intl\Exception\UnknownCurrencyException;
use Override;
use RuntimeException;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Model\FileHandler;
use Shopsys\Cli\Model\TwigHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;

final class PrepareCurrencyWorker extends AbstractWorker
{
    private const string MIGRATIONS_DIR = 'app/src/Migrations/';

    private const string NEXT_CONFIG_FILE = 'storefront/buildPublicEnvConfig.ts';

    /**
     * @var string[]
     */
    private array $existingCurrencyCodes;

    public function __construct(
        private readonly FileHandler $fileHandler,
        private readonly TwigHandler $twigHandler,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDescription(): string
    {
        return 'Set other selected currencies to database';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 976;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult
    {
        $this->existingCurrencyCodes = $this->getExistingCurrencyCodes($projectPath);

        $migrationFilePath = $this->createDatabaseMigration($config, $projectPath);

        if ($migrationFilePath === null) {
            return WorkerResult::success('All selected currencies are already set');
        }

        return WorkerResult::success(
            'Currencies prepared',
            filesCreated: [$migrationFilePath],
        );
    }

    private function createDatabaseMigration(CoreProjectConfig $config, string $projectPath): ?string
    {
        $migrationName = 'Version' . date('YmdHis');
        $migrationFilePath = self::MIGRATIONS_DIR . $migrationName . '.php';
        $migrationFullFilePath = $projectPath . '/' . $migrationFilePath;

        $currencyCodes = $config->getUniqueCurrencies();
        $currencyCodes = array_filter(
            $currencyCodes,
            function ($currencyCode) {
                return !in_array($currencyCode, $this->existingCurrencyCodes, true);
            },
        );

        if (count($currencyCodes) === 0) {
            return null;
        }

        $sqlStatements = $this->getCreateCurrencySqlStatements($currencyCodes);

        $migrationContent = $this->twigHandler->render(
            __DIR__ . '/resources/DBMigrationTemplate.php.twig',
            [
                'migrationClassName' => $migrationName,
                'sqlStatements' => $sqlStatements,
            ],
        );

        $this->fileHandler->writeFile($migrationFullFilePath, $migrationContent);

        return $migrationFilePath;
    }

    /**
     * @param string[] $currencyCodes
     * @return string[]
     */
    private function getCreateCurrencySqlStatements(array $currencyCodes): array
    {
        $currencyRepository = new CurrencyRepository();
        $sqlStatements = [];

        foreach ($currencyCodes as $currencyCode) {
            try {
                $currencyMetadata = $currencyRepository->get($currencyCode);

                $escapedName = addslashes($currencyMetadata->getName());
                $escapedCode = addslashes($currencyMetadata->getCurrencyCode());

                $sqlStatements[] = sprintf(
                    '$this->sql(\'INSERT INTO currencies (name, code, exchange_rate, min_fraction_digits, rounding_type, rounding_places_price_without_vat)
                    VALUES (\\\'%s\\\', \\\'%s\\\', %f, %d, \\\'%s\\\', %d)\');',
                    $escapedName,
                    $escapedCode,
                    1,
                    $currencyMetadata->getFractionDigits(),
                    'integer',
                    $currencyMetadata->getFractionDigits(),
                );
            } catch (UnknownCurrencyException $e) {
                throw new RuntimeException(sprintf('Currency with code "%s" not found.', $currencyCode), 0, $e);
            }
        }

        return $sqlStatements;
    }

    /**
     * @return string[]
     */
    private function getExistingCurrencyCodes(string $projectPath): array
    {
        $filePath = $projectPath . '/' . self::NEXT_CONFIG_FILE;

        $content = $this->fileHandler->readFile($filePath);

        preg_match_all("/currencyCode:\s*['\"]([^'\"]+)['\"]/", $content, $matches);

        return array_unique($matches[1]);
    }
}
