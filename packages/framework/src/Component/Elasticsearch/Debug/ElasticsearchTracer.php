<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Debug;

use InvalidArgumentException;
use Nette\Utils\Json;
use Override;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Debug\Exception\NotSupportedException;
use Stringable;

/**
 * Implementation of this class is close related to used hardcoded strings/keys in \Elasticsearch\Connections\Connection
 */
class ElasticsearchTracer extends AbstractLogger
{
    protected ?string $lastRequestCurl = null;

    public function __construct(protected readonly ElasticsearchRequestCollection $elasticsearchRequestCollection)
    {
    }

    protected function extractData(string $requestMessage): mixed
    {
        $matches = null;

        if (preg_match('/^.* -d \'(?<json>.*)\'$/U', $requestMessage, $matches) === 0) {
            return null;
        }

        return Json::decode($matches['json'], true);
    }

    protected function formatData(mixed $requestData): string
    {
        return Json::encode($requestData, JSON_PRETTY_PRINT);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function log($level, string|Stringable $message, array $context = []): void
    {
        if ($level === LogLevel::INFO) {
            $this->lastRequestCurl = $message;

            return;
        }

        if ($level === LogLevel::DEBUG) {
            $this->logRequest($message, $context);

            return;
        }

        $exceptionMessage = sprintf('Not supported log level `%s`', $level);

        throw new NotSupportedException($exceptionMessage);
    }

    protected function logRequest(string $message, array $context = []): void
    {
        if ($message !== 'Response:') {
            $exceptionMessage = sprintf('Not supported message `%s`, It supports only exactly `Response:`', $message);

            throw new NotSupportedException($exceptionMessage);
        }

        $requestJson = null;
        $requestData = null;

        try {
            $requestData = $this->extractData($this->lastRequestCurl);
            $requestJson = $this->formatData($requestData);
        } catch (InvalidArgumentException $exception) {
            // It's ok, It'll not have formatted dump.
        }

        $this->elasticsearchRequestCollection->addRequest(
            $this->lastRequestCurl,
            $requestJson,
            $requestData,
            $context['method'],
            $context['uri'],
            $context['HTTP code'] === null ? null : (int)$context['HTTP code'],
            $context['response'],
            (float)$context['duration'],
        );
    }
}
