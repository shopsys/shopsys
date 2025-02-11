<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Complaint;

use Shopsys\FrameworkBundle\Model\Complaint\ComplaintResolutionEnum;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetComplaintResolution extends GraphQlTestCase
{
    /**
     * @inject
     */
    private ComplaintResolutionEnum $complaintResolutionEnum;

    public function testGetComplaintResolution(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/GetComplaintResolutionQuery.graphql',
        );

        $responseData = $this->getResponseDataForGraphQlType($response, 'complaintResolution');

        $this->assertSame($this->complaintResolutionEnum->serialize(), $responseData);
    }
}
