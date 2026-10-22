<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Complaint;

use App\DataFixtures\Demo\ComplaintDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;
use Tests\FrontendApiBundle\Test\ReferenceDataAccessor;

class GetComplaintTest extends GraphQlWithLoginTestCase
{
    use ComplaintTestTrait;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

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

    public function testComplaintItemWithHiddenProductHasNoProduct(): void
    {
        $complaint = $this->getReference(ComplaintDataFixture::COMPLAINT_PREFIX . 1, Complaint::class);
        $hiddenProduct = $complaint->getItems()[0]->getProduct();
        $this->assertInstanceOf(Product::class, $hiddenProduct);

        $productData = $this->productDataFactory->createFromProduct($hiddenProduct);
        $productData->hidden = true;
        $this->productFacade->edit($hiddenProduct->getId(), $productData);
        $this->handleDispatchedRecalculationMessages();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetComplaintItemsWithProductQuery.graphql', [
            'complaintNumber' => $complaint->getNumber(),
        ]);
        $complaintItemsByCatnum = array_column($this->getResponseDataForGraphQlType($response, 'complaint')['items'], null, 'catnum');

        $hiddenProductComplaintItem = $complaintItemsByCatnum[$hiddenProduct->getCatnum()];
        $this->assertNull($hiddenProductComplaintItem['product']);
        $this->assertSame($complaint->getItems()[0]->getProductName(), $hiddenProductComplaintItem['productName']);
    }

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
