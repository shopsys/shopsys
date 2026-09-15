<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Debug;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
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

    protected function extractBody(string $requestMessage): ?string
    {
        $matches = null;

        if (preg_match('/^.* -d \'(?<json>.*)\'$/sU', $requestMessage, $matches) === 0) {
            return null;
        }

        return trim($matches['json']);
    }

    protected function extractData(string $requestBody): mixed
    {
        // bulk APIs (e.g. _msearch) send NDJSON - multiple JSON objects separated by newlines
        if (str_contains($requestBody, "\n")) {
            return array_map(
                static fn (string $line) => Json::decode($line, true),
                explode("\n", $requestBody),
            );
        }

        return Json::decode($requestBody, true);
    }

    protected function formatBody(string $requestBody): string
    {
        // each NDJSON line is pretty-printed separately so the output stays usable in Kibana,
        // decoding to objects (not arrays) keeps empty objects as `{}` instead of `[]`
        $prettyLines = array_map(
            static fn (string $line) => Json::encode(Json::decode($line), JSON_PRETTY_PRINT),
            explode("\n", $requestBody),
        );

        return implode("\n", $prettyLines);
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

        $requestBody = $this->extractBody($this->lastRequestCurl);

        if ($requestBody !== null) {
            try {
                $requestData = $this->extractData($requestBody);
                $requestJson = $this->formatBody($requestBody);
            } catch (JsonException $exception) {
                // It's ok, It'll not have formatted dump.
            }
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
