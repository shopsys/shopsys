<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Complaint;

use App\DataFixtures\Demo\ComplaintDataFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;
use Tests\FrontendApiBundle\Test\ReferenceDataAccessor;

class GetComplaintTest extends GraphQlWithLoginTestCase
{
    use ComplaintTestTrait;

    /**
     * @param array $queryVariables
     * @param int $expectedComplaintId
     */
    #[DataProvider('getComplaintsDataProvider')]
    public function testGetComplaint(array $queryVariables, int $expectedComplaintId): void
    {
        $resolvedQueryVariables = $this->resolveReferenceDataAccessors($queryVariables);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/GetComplaintQuery.graphql',
            $resolvedQueryVariables,
        );

        $responseData = $this->getResponseDataForGraphQlType($response, 'complaint');
        $expectedComplaint = $this->getReference(ComplaintDataFixture::COMPLAINT_PREFIX . $expectedComplaintId);

        $this->assertComplaint($expectedComplaint, $responseData);
    }

    /**
     * @return iterable
     */
    public static function getComplaintsDataProvider(): iterable
    {
        // first 2 complaints
        yield [
            [
                'complaintNumber' => new ReferenceDataAccessor(
                    ComplaintDataFixture::COMPLAINT_PREFIX . 1,
                    fn (Complaint $complaint) => $complaint->getNumber(),
                ),
            ],
            1,
        ];

        yield [
            [
                'complaintNumber' => new ReferenceDataAccessor(
                    ComplaintDataFixture::COMPLAINT_PREFIX . 2,
                    fn (Complaint $complaint) => $complaint->getNumber(),
                ),
            ],
            2,
        ];
    }
}
