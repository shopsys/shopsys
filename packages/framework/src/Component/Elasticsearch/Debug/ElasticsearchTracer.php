<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Debug;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Debug\Exception\NotSupportedException;

/**
 * Implementation of this class is close related to used hardcoded strings/keys in \Elasticsearch\Connections\Connection
 */
class ElasticsearchTracer extends AbstractLogger
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\Debug\ElasticsearchDebugStack
     */
    protected $elasticsearchDebugStack;

    /**
     * @var string|null
     */
    protected $lastRequestCurl;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\Debug\ElasticsearchDebugStack $elasticsearchDebugStack
     */
    public function __construct(ElasticsearchDebugStack $elasticsearchDebugStack)
    {
        $this->elasticsearchDebugStack = $elasticsearchDebugStack;
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = []): void
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

    /**
     * @param string $message
     * @param array $context
     */
    protected function logRequest($message, array $context = []): void
    {
        if ($message !== 'Response:') {
            $exceptionMessage = sprintf('Not supported message `%s`, It supports only exactly `Response:`', $message);
            throw new NotSupportedException($exceptionMessage);
        }

        $matches = null;
        $requestJson = null;
        $requestData = null;

        if (preg_match('/^.* -d \'(?<json>.*)\'$/U', $this->lastRequestCurl, $matches) !== 0) {
            $requestJson = $matches['json'];
            try {
                $requestData = \GuzzleHttp\json_decode($requestJson, true);
                $requestJson = \GuzzleHttp\json_encode($requestData, JSON_PRETTY_PRINT);
            } catch (\InvalidArgumentException $exception) {
                // It's ok, It'll not have formatted dump.
            }
        }

        $this->elasticsearchDebugStack->addRequest(
            $this->lastRequestCurl,
            $requestJson,
            $requestData,
            $context['method'],
            $context['uri'],
            $context['HTTP code'] === null ? null : (int)$context['HTTP code'],
            $context['response'],
            (float)$context['duration']
        );
    }
}
