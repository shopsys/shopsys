<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Storefront;

use Override;
use RuntimeException;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Model\FileHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;

final class RoutesWorker extends AbstractWorker
{
    private const string FILE_PATH = 'storefront/config/routes.ts';

    /**
     * @var array<string, array<string, string>>
     */
    private array $routesByLocale = [];

    /**
     * @param \Shopsys\Cli\Model\FileHandler $fileHandler
     */
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
        return 'Generates storefront/config/routes.ts with route mappings per domain';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 440;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult
    {
        $filePath = $projectPath . '/' . self::FILE_PATH;

        $existingContent = $this->fileHandler->readFile($filePath);
        $this->parseExistingRoutes($existingContent);

        $routesContent = $this->generateRoutesContent($config);
        $this->fileHandler->writeFile($filePath, $routesContent);

        return WorkerResult::success(
            'Generated routes.ts',
            filesModified: [self::FILE_PATH],
            hints: [
                sprintf('Update route translations in "%s" for each locale.', self::FILE_PATH),
            ],
        );
    }

    /**
     * Parses existing routes.ts and stores routes by locale (1st=en, 2nd=cs, 3rd=sk)
     *
     * @param string $content
     */
    private function parseExistingRoutes(string $content): void
    {
        $localeOrder = ['en', 'cs', 'sk'];

        preg_match_all('/\{([^}]+)}/', $content, $matches);

        if (count($matches[1]) === 0) {
            throw new RuntimeException('Could not parse route objects from routes.ts');
        }

        foreach ($matches[1] as $index => $objectContent) {
            $routes = $this->parseRouteObject($objectContent);
            $locale = $localeOrder[$index] ?? 'en';
            $this->routesByLocale[$locale] = $routes;
        }
    }

    /**
     * @param string $objectContent
     * @return array<string, string>
     */
    private function parseRouteObject(string $objectContent): array
    {
        $routes = [];

        preg_match_all("/'([^']+)':\s*'([^']+)'/", $objectContent, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $routes[$match[1]] = $match[2];
        }

        return $routes;
    }

    /**
     * @param \Shopsys\Cli\Config\CoreProjectConfig $config
     * @return string
     */
    private function generateRoutesContent(CoreProjectConfig $config): string
    {
        $routeObjects = [];

        foreach ($config->domains as $domain) {
            $routes = $this->getRoutesForLocale($domain->locale);
            $routeObjects[] = $this->formatRouteObject($routes);
        }

        $routesArrayContent = implode(",\n", $routeObjects);

        return "export const routes = [\n" . $routesArrayContent . ",\n];\n";
    }

    /**
     * @param string $locale
     * @return array<string, string>
     */
    private function getRoutesForLocale(string $locale): array
    {
        return $this->routesByLocale[$locale] ?? $this->routesByLocale['en'] ?? throw new RuntimeException('English routes not found');
    }

    /**
     * @param array<string, string> $routes
     * @return string
     */
    private function formatRouteObject(array $routes): string
    {
        $lines = [];

        foreach ($routes as $key => $value) {
            $lines[] = sprintf("        '%s': '%s'", $key, $value);
        }

        return "    {\n" . implode(",\n", $lines) . ",\n    }";
    }
}
