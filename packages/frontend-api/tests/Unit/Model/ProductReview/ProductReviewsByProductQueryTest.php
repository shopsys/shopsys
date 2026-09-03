<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\ProductReview;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\ArgumentFactory;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Component\Validation\PageSizeValidator;
use Shopsys\FrontendApiBundle\Model\ProductReview\ProductReviewApiFacade;
use Shopsys\FrontendApiBundle\Model\ProductReview\ProductReviewElasticsearchRepository;
use Shopsys\FrontendApiBundle\Model\ProductReview\ProductReviewsPageResult;
use Shopsys\FrontendApiBundle\Model\Resolver\ProductReview\ProductReviewsQuery;

final class ProductReviewsByProductQueryTest extends TestCase
{
    public function testDisabledReviewsReturnNull(): void
    {
        $facade = $this->createStub(ProductReviewApiFacade::class);
        $facade->method('areProductReviewsEnabledOnCurrentDomain')->willReturn(false);
        $query = new ProductReviewsQuery(
            $this->createStub(CurrentCustomerUser::class),
            $facade,
            $this->createStub(ProductReviewElasticsearchRepository::class),
        );

        $argument = (new ArgumentFactory(Argument::class))->create(['first' => 5]);
        $this->assertInstanceOf(Argument::class, $argument);

        $result = $query->productReviewsByProductQuery(['id' => 1, 'is_variant' => false, 'main_variant_id' => null], $argument);

        $this->assertNull($result);
    }

    public function testReviewsAreReadFromTheMainVariantDocumentForProductArrayAndEntity(): void
    {
        $facade = $this->createStub(ProductReviewApiFacade::class);
        $facade->method('areProductReviewsEnabledOnCurrentDomain')->willReturn(true);
        $repository = $this->createStub(ProductReviewElasticsearchRepository::class);
        $repository->method('getReviewsPage')->willReturnCallback(
            static fn (int $productId): ProductReviewsPageResult => new ProductReviewsPageResult(
                array_map(static fn (int $index): array => ['uuid' => $productId . '-review-' . $index], range(1, 6)),
                6,
                ['average_rating' => 5.0, 'total_count' => 6, 'rating_counts' => []],
            ),
        );
        $query = new ProductReviewsQuery($this->createStub(CurrentCustomerUser::class), $facade, $repository);
        $query->autowirePageSizeValidator(new PageSizeValidator());
        $mainVariant = $this->createStub(Product::class);
        $mainVariant->method('getId')->willReturn(1);
        $mainVariant->method('isVariant')->willReturn(false);
        $variant = $this->createStub(Product::class);
        $variant->method('getId')->willReturn(2);
        $variant->method('isVariant')->willReturn(true);
        $variant->method('getMainVariant')->willReturn($mainVariant);
        $argument = (new ArgumentFactory(Argument::class))->create(['first' => 5]);
        $this->assertInstanceOf(Argument::class, $argument);

        $productsCarryingReviewsOnDocumentOne = [
            ['id' => 1, 'is_variant' => false, 'main_variant_id' => null],
            ['id' => 2, 'is_variant' => true, 'main_variant_id' => 1],
            $mainVariant,
            $variant,
        ];

        foreach ($productsCarryingReviewsOnDocumentOne as $productData) {
            $result = $query->productReviewsByProductQuery($productData, $argument);

            $this->assertNotNull($result);
            $this->assertCount(5, $result->getEdges());
            $this->assertSame(['uuid' => '1-review-5'], $result->getEdges()[4]->getNode(), 'The reviews of a variant must be read from the document of its main variant.');
            $this->assertSame(6, $result->getTotalCount());
            $this->assertSame(5.0, $result->getSummary()['average_rating']);
        }
    }
}
