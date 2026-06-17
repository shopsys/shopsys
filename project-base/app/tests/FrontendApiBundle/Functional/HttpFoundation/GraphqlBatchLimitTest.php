<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\HttpFoundation;

use Nette\Utils\Json;
use Symfony\Component\HttpFoundation\Response;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class GraphqlBatchLimitTest extends GraphQlTestCase
{
    private const int MAX_BATCH_OPERATIONS = 20;

    public function testBatchRequestWithLimitOperationsIsAccepted(): void
    {
        $this->sendBatchRequest(static::MAX_BATCH_OPERATIONS);

        $response = Json::decode((string)self::$client->getResponse()->getContent(), true);

        $this->assertSame(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        $this->assertCount(static::MAX_BATCH_OPERATIONS, $response);
    }

    public function testBatchRequestAboveLimitIsRejected(): void
    {
        $this->sendBatchRequest(static::MAX_BATCH_OPERATIONS + 1);

        $response = Json::decode((string)self::$client->getResponse()->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, self::$client->getResponse()->getStatusCode());
        $this->assertSame(
            'Batch request cannot contain more than 20 operations.',
            $response['errors'][0]['message'],
        );
    }

    private function sendBatchRequest(int $operationsCount): void
    {
        $path = $this->getLocalizedPathOnFirstDomainByRouteName('overblog_graphql_batch_endpoint');
        $query = file_get_contents(__DIR__ . '/graphql/TypenameQuery.graphql');

        if ($query === false) {
            $this->fail('Unable to load GraphQL query fixture.');
        }

        self::$client->request(
            'POST',
            $path,
            content: Json::encode($this->createBatchRequestData($operationsCount, $query)),
        );
    }

    /**
     * @return array<int, array{id: int, query: string}>
     */
    private function createBatchRequestData(int $operationsCount, string $query): array
    {
        $batchRequestData = [];

        for ($operationId = 1; $operationId <= $operationsCount; $operationId++) {
            $batchRequestData[] = [
                'id' => $operationId,
                'query' => $query,
            ];
        }

        return $batchRequestData;
    }
}
