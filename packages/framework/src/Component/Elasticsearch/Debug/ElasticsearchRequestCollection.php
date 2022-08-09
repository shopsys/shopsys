<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Debug;

/**
 * @phpstan-type CollectedDataArray array{
 *     requestCurl: string,
 *     requestJson: ?string,
 *     requestData: mixed,
 *     method: string,
 *     uri: string,
 *     statusCode: ?int,
 *     response: mixed,
 *     duration: float,
 * }
 */
class ElasticsearchRequestCollection
{
    /**
     * @var CollectedDataArray[]
     */
    protected $collectedData = [];

    /**
     * @return array<mixed, array{requestCurl: string, requestJson: string|null, requestData: mixed, method: string, uri: string, statusCode: int|null, response: mixed, duration: float}>
     */
    public function getCollectedData(): array
    {
        return $this->collectedData;
    }

    /**
     * @return int
     */
    public function getCollectedDataCount(): int
    {
        return count($this->collectedData);
    }

    /**
     * @return float
     */
    public function getTotalTime(): float
    {
        $totalRequestsTime = 0;
        $collectedData = $this->getCollectedData();
        foreach ($collectedData as $requestData) {
            $totalRequestsTime += $requestData['duration'];
        }

        return $totalRequestsTime;
    }

    /**
     * @param string $requestCurl
     * @param string|null $requestJson
     * @param mixed $requestData
     * @param string $method
     * @param string $uri
     * @param int|null $statusCode
     * @param mixed $response
     * @param float $duration
     */
    public function addRequest(
        string $requestCurl,
        ?string $requestJson,
        mixed $requestData,
        string $method,
        string $uri,
        ?int $statusCode,
        mixed $response,
        float $duration
    ): void {
        $this->collectedData[] = [
            'requestCurl' => $requestCurl,
            'requestJson' => $requestJson,
            'requestData' => $requestData,
            'method' => $method,
            'uri' => $uri,
            'statusCode' => $statusCode,
            'response' => $response,
            'duration' => $duration,
        ];
    }
}
