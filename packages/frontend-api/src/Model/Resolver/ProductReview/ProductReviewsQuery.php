<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\ProductReview;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrontendApiBundle\Model\ProductReview\Connection\ProductReviewConnection;
use Shopsys\FrontendApiBundle\Model\ProductReview\ProductReviewApiFacade;
use Shopsys\FrontendApiBundle\Model\ProductReview\ProductReviewElasticsearchRepository;
use Shopsys\FrontendApiBundle\Model\ProductReview\ProductReviewOrderingModeEnum;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\Exception\ProductNotFoundUserError;

class ProductReviewsQuery extends AbstractQuery
{
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ProductReviewApiFacade $productReviewApiFacade,
        protected readonly ProductReviewElasticsearchRepository $productReviewElasticsearchRepository,
    ) {
    }

    public function productReviewsQuery(Argument $argument): ProductReviewConnection
    {
        $this->pageSizeValidator->checkMaxPageSize($argument);
        $this->setDefaultFirstOffsetIfNecessary($argument);
        $this->productReviewApiFacade->checkProductReviewsEnabledOnCurrentDomain();

        $orderingMode = $argument['orderingMode'] ?? ProductReviewOrderingModeEnum::NEWEST;
        $pageResult = null;

        try {
            $paginator = new Paginator(
                function (int $offset, int $limit) use ($argument, $orderingMode, &$pageResult) {
                    $pageResult = $this->productReviewElasticsearchRepository->getReviewsPage(
                        $argument['productUuid'],
                        $orderingMode,
                        $offset,
                        $limit,
                    );

                    return $pageResult->reviewArrays;
                },
            );
            // for the forward pagination the total comes from the very same search that fetched the page,
            // the backward pagination asks for the total first, so a minimal search has to provide it
            $connection = $paginator->auto($argument, function () use ($argument, $orderingMode, &$pageResult) {
                $pageResult ??= $this->productReviewElasticsearchRepository->getReviewsPage(
                    $argument['productUuid'],
                    $orderingMode,
                    0,
                    1,
                );

                return $pageResult->totalCount;
            });
        } catch (ProductNotFoundException) {
            throw new ProductNotFoundUserError('Product not found.');
        }

        return new ProductReviewConnection(
            $connection->getEdges(),
            $connection->getPageInfo(),
            static fn () => $pageResult->summary,
            $orderingMode,
            $connection->getTotalCount(),
        );
    }

    /**
     * Without the productUuid argument, the connection pages through all reviews of the customer on the current domain
     */
    public function currentCustomerUserProductReviewsQuery(Argument $argument): ConnectionInterface
    {
        $this->pageSizeValidator->checkMaxPageSize($argument);
        $this->setDefaultFirstOffsetIfNecessary($argument);
        $this->productReviewApiFacade->checkProductReviewsEnabledOnCurrentDomain();

        $customerUser = $this->currentCustomerUser->getCurrentCustomerUser();
        $mainProduct = null;

        if (($argument['productUuid'] ?? null) !== null) {
            try {
                $mainProduct = $this->productReviewApiFacade->getMainProductByUuid($argument['productUuid']);
            } catch (ProductNotFoundException) {
                throw new ProductNotFoundUserError('Product not found.');
            }
        }

        $paginator = new Paginator(
            fn (int $offset, int $limit) => $this->productReviewApiFacade->getCustomerUserReviewArrays(
                $customerUser,
                $mainProduct,
                $limit,
                $offset,
            ),
        );

        return $paginator->auto(
            $argument,
            $this->productReviewApiFacade->getCustomerUserReviewsCount($customerUser, $mainProduct),
        );
    }
}
