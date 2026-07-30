<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Model\ProductReview;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\EditHandlerInterface;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\ProductReview\Exception\ProductReviewNotFoundException;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReview;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewData;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewDataFactory;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewEnabledChecker;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewFacade;
use Webmozart\Assert\Assert;

class ProductReviewEditHandler implements EditHandlerInterface
{
    public function __construct(
        protected readonly ProductReviewDataFactory $productReviewDataFactory,
        protected readonly ProductReviewEnabledChecker $productReviewEnabledChecker,
        protected readonly ProductReviewFacade $productReviewFacade,
    ) {
    }

    /**
     * Reviews of a domain that no longer has the feature enabled are hidden the same way as in the listing
     *
     * {@inheritdoc}
     */
    #[Override]
    public function getById(int $id): Presentable
    {
        $productReview = $this->productReviewFacade->getById($id);

        if (!$this->productReviewEnabledChecker->isEnabledForDomain($productReview->getDomainId())) {
            throw new ProductReviewNotFoundException($id);
        }

        return $productReview;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createDataFromEntity(object $entity): object
    {
        Assert::isInstanceOf($entity, ProductReview::class);

        return $this->productReviewDataFactory->createFromProductReview($entity);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function edit(object $entity, object $data): void
    {
        Assert::isInstanceOf($entity, ProductReview::class);
        Assert::isInstanceOf($data, ProductReviewData::class);

        $this->productReviewFacade->edit($entity, $data);
    }
}
