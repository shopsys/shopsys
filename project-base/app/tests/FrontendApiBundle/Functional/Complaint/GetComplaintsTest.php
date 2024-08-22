<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Complaint;

use App\DataFixtures\Demo\ComplaintDataFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;
use Tests\FrontendApiBundle\Test\ReferenceDataAccessor;
use Tests\FrontendApiBundle\Test\SearchInputTestUtils;

class GetComplaintsTest extends GraphQlWithLoginTestCase
{
    use ComplaintTestTrait;

    /**
     * @param array $queryVariables
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
     * @return iterable
     */
    public static function getComplaintsDataProvider(): iterable
    {
        // first 2 complaints
        yield [['first' => 2], [2, 1]];

        // last 1 complaint
        yield [['last' => 1], [1]];

        // search by complaint number
        yield [
            SearchInputTestUtils::createSearchInputQueryVariablesByReference(
                new ReferenceDataAccessor(
                    ComplaintDataFixture::COMPLAINT_PREFIX . 2,
                    fn (Complaint $complaint) => $complaint->getNumber(),
                ),
            ),
            [2],
        ];

        // search by product name
        yield [
            SearchInputTestUtils::createSearchInputQueryVariables('MG3550'),
            [1],
        ];

        // search by catnum
        yield [
            SearchInputTestUtils::createSearchInputQueryVariables('9184535'),
            [1],
        ];
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
