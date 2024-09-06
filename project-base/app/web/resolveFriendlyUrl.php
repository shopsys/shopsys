<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

$projectRootDirectory = __DIR__ . '/..';

// change autoloading source for monorepo
if (file_exists(__DIR__ . '/../../../parameters_monorepo.yaml')) {
    $projectRootDirectory = __DIR__ . '/../../..';
}

require $projectRootDirectory . '/vendor/symfony/dotenv/Dotenv.php';
(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

const ROUTE = 'route';
const REDIRECT_TO = 'redirectTo';
const REDIRECT_CODE = 'redirectCode';

$databaseHost = $_ENV['DATABASE_HOST'] ?? '';
$databasePort = $_ENV['DATABASE_PORT'] ?? '';
$databaseUser = $_ENV['DATABASE_USER'] ?? '';
$databasePassword = $_ENV['DATABASE_PASSWORD'] ?? '';
$databaseName = $_ENV['DATABASE_NAME'] ?? '';

$slugData = json_decode(file_get_contents('php://input'), true);
$slug = $slugData['slug'] ?? null;
$domainId = $slugData['domainId'] ?? null;

function writeToLog(string $message): void
{
    fwrite(fopen('/tmp/log-pipe', 'a'), 'resolveFriendlyUrl: ' . $message . PHP_EOL);
}

function returnSlug(string $route, ?string $redirectTo, ?int $redirectCode = null): array
{
    if ($redirectCode === null) {
        $redirectCode = 301;
    }

    header("Content-Type: application/json");
    echo json_encode([
        ROUTE => $route,
        REDIRECT_TO => $redirectTo,
        REDIRECT_CODE => $redirectCode,
    ]);
    exit;
}

if ($slug === null || $domainId === null) {
    writeToLog(sprintf('400 Bad Request because slug (%s) or domainId (%d) is null',
        $slug,
        $domainId,
    ));

    header("HTTP/1.1 400 Bad Request");
    exit;
}

$slug = ltrim(trim($slug), '/');

try {
    $connection = new PDO(
        sprintf('pgsql:host=%s;port=%s;dbname=%s', $databaseHost, $databasePort, $databaseName),
        $databaseUser,
        $databasePassword
    );

    $statement = $connection->prepare('SELECT route_name, entity_id, main, redirect_to, redirect_code FROM friendly_urls WHERE slug = :slug AND domain_id = :domain_id');
    $statement->execute(['slug' => $slug, 'domain_id' => (int)$domainId]);
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if ($result === false) {
        writeToLog(sprintf('404 Not Found because friendly URL not found with slug (%s) and domainId (%d)',
            $slug,
            $domainId,
        ));

        header("HTTP/1.1 404 Not Found");
        exit;
    }

    if ($result['redirect_to'] !== null) {
        returnSlug($result['route_name'], $result['redirect_to'], $result['redirect_code']);
    }

    if ($result['main'] === true) {
        returnSlug($result['route_name'], null);
    }

    $statement = $connection->prepare('SELECT slug, redirect_code FROM friendly_urls WHERE entity_id = :entity_id AND domain_id = :domain_id AND main = true AND route_name = :route_name');
    $statement->execute(['entity_id' => $result['entity_id'], 'domain_id' => $domainId, 'route_name' => $result['route_name']]);
    $mainFriendlyUrl = $statement->fetch(PDO::FETCH_ASSOC);

    if ($mainFriendlyUrl === false) {
        writeToLog(sprintf('404 Not Found because main friendly URL not found with slug (%s) and domainId (%d)',
            $slug,
            $domainId,
        ));

        header("HTTP/1.1 404 Not Found");
        exit;
    }

    returnSlug($result['route_name'], '/' . $mainFriendlyUrl['slug'], $mainFriendlyUrl['redirect_code']);
} catch (PDOException $exception) {
    writeToLog(sprintf('500 Internal Server Error because friendly URL not found with slug (%s) and domainId (%d) and also exception (%s) thrown with message (%s)',
        $slug,
        $domainId,
        get_class($exception),
        $exception->getMessage(),
    ));

    header("HTTP/1.1 500 Internal Server Error");
}
