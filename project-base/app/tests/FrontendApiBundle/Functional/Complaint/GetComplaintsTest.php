<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Complaint;

use App\DataFixtures\Demo\ComplaintDataFixture;
use App\DataFixtures\Demo\ComplaintStatusDataFixture;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;
use Symfony\Component\Clock\DatePoint;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;
use Tests\FrontendApiBundle\Test\ReferenceDataAccessor;

class GetComplaintsTest extends GraphQlWithLoginTestCase
{
    use ComplaintTestTrait;

    /**
     * @param array<string, mixed> $queryVariables
     * @param int[] $expectedComplaintIds
     */
    #[DataProvider('getComplaintsDataProvider')]
    public function testGetComplaints(array $queryVariables, array $expectedComplaintIds = []): void
    {
        $resolvedQueryVariables = $this->resolveReferenceDataAccessors($queryVariables);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/GetComplaintsQuery.graphql',
            $resolvedQueryVariables,
        );

        $responseData = $this->getResponseDataForGraphQlType($response, 'complaints');
        $this->assertArrayHasKey('edges', $responseData);

        $edges = $responseData['edges'];
        $this->assertSameSize(
            $expectedComplaintIds,
            $edges,
        );

        $expectedComplaints = $this->getExpectedComplaints($expectedComplaintIds);

        foreach ($edges as $edge) {
            $this->assertArrayHasKey('node', $edge);
            $complaint = $edge['node'];

            $expectedComplaint = array_shift($expectedComplaints);

            $this->assertComplaint($expectedComplaint, $complaint);
        }
    }

    /**
     * @return iterable<array{0: array<string, mixed>, 1: int[]}>
     */
    public static function getComplaintsDataProvider(): iterable
    {
        // first 2 complaints
        yield [['first' => 2], [2, 1]];

        // last 1 complaint
        yield [['last' => 1], [1]];

        // search by complaint number
        yield [
            [
                'filter' => [
                    'search' => new ReferenceDataAccessor(
                        ComplaintDataFixture::COMPLAINT_PREFIX . 2,
                        fn (Complaint $complaint) => $complaint->getNumber(),
                    ),
                ],
            ],
            [2],
        ];

        // search by product name
        yield [
            [
                'filter' => [
                    'search' => 'MG3550',
                ],
            ],
            [1],
        ];

        // search by catnum
        yield [
            [
                'filter' => [
                    'search' => '9184535',
                ],
            ],
            [1],
        ];

        // filter by complaint created after date
        yield [
            [
                'filter' => [
                    'createdAfter' => (new DatePoint())
                        ->modify('-1 year')
                        ->format(DateTimeInterface::ATOM),
                ],
            ],
            [2, 1],
        ];

        // filter by complaint created before date
        yield [
            [
                'filter' => [
                    'createdBefore' => (new DatePoint())
                        ->modify('-1 year')
                        ->format(DateTimeInterface::ATOM),
                ],
            ],
            [],
        ];

        // filter by complaint status
        yield [
            [
                'filter' => [
                    'statusCodes' => [
                        self::createComplaintStatusCodeAccessor(ComplaintStatusDataFixture::COMPLAINT_STATUS_RESOLVED),
                    ],
                ],
            ],
            [2],
        ];

        // filter by multiple complaint statuses
        yield [
            [
                'filter' => [
                    'statusCodes' => [
                        self::createComplaintStatusCodeAccessor(ComplaintStatusDataFixture::COMPLAINT_STATUS_NEW),
                        self::createComplaintStatusCodeAccessor(ComplaintStatusDataFixture::COMPLAINT_STATUS_RESOLVED),
                    ],
                ],
            ],
            [2, 1],
        ];
    }

    /**
     * @param array<string, mixed> $queryVariables
     * @param array<string, int> $expectedCountsByStatusReferenceName
     */
    #[DataProvider('getComplaintStatusCountsDataProvider')]
    public function testGetComplaintStatusCounts(
        array $queryVariables,
        array $expectedCountsByStatusReferenceName,
    ): void {
        $resolvedQueryVariables = $this->resolveReferenceDataAccessors($queryVariables);
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/GetComplaintsQuery.graphql',
            $resolvedQueryVariables,
        );

        $actualCountsByStatusCode = $this->getComplaintStatusCountsByStatusCode($response);

        foreach ($expectedCountsByStatusReferenceName as $complaintStatusReferenceName => $expectedCount) {
            $complaintStatus = $this->getReference($complaintStatusReferenceName, ComplaintStatus::class);

            $this->assertSame(
                $expectedCount,
                $actualCountsByStatusCode[$complaintStatus->getCode()],
            );
        }
    }

    /**
     * @return iterable<string, array{
     *     0: array<string, mixed>,
     *     1: array<string, int>,
     * }>
     */
    public static function getComplaintStatusCountsDataProvider(): iterable
    {
        yield 'all complaint status counts' => [
            [
                'first' => 1,
            ],
            [
                ComplaintStatusDataFixture::COMPLAINT_STATUS_NEW => 1,
                ComplaintStatusDataFixture::COMPLAINT_STATUS_IN_PROGRESS => 0,
                ComplaintStatusDataFixture::COMPLAINT_STATUS_RESOLVED => 1,
            ],
        ];

        yield 'status counts respect search and status filters' => [
            [
                'first' => 1,
                'statuslessFilter' => [
                    'search' => 'MG3550',
                    'statusCodes' => [
                        self::createComplaintStatusCodeAccessor(ComplaintStatusDataFixture::COMPLAINT_STATUS_RESOLVED),
                    ],
                ],
            ],
            [
                ComplaintStatusDataFixture::COMPLAINT_STATUS_NEW => 0,
                ComplaintStatusDataFixture::COMPLAINT_STATUS_RESOLVED => 0,
            ],
        ];
    }

    private static function createComplaintStatusCodeAccessor(string $referenceName): ReferenceDataAccessor
    {
        return new ReferenceDataAccessor(
            $referenceName,
            fn (ComplaintStatus $complaintStatus) => $complaintStatus->getCode(),
        );
    }

    /**
     * @param array<string, mixed> $response
     * @return array<string, int>
     */
    private function getComplaintStatusCountsByStatusCode(array $response): array
    {
        $statusCounts = $this->getResponseDataForGraphQlType($response, 'complaintStatusCounts');
        $statusCountsByStatusCode = [];

        foreach ($statusCounts as $statusCount) {
            $statusCountsByStatusCode[$statusCount['status']['code']] = $statusCount['count'];
        }

        return $statusCountsByStatusCode;
    }

    /**
     * @param int[] $expectedComplaintIds
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint[]
     */
    private function getExpectedComplaints(array $expectedComplaintIds): array
    {
        return array_map(
            fn (int $id) => $this->getReference(ComplaintDataFixture::COMPLAINT_PREFIX . $id),
            $expectedComplaintIds,
        );
    }
}
