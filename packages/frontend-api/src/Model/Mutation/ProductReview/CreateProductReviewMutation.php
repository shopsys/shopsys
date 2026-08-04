<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\ProductReview;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\ProductReview\Exception\TooManyProductReviewsUserError;
use Shopsys\FrontendApiBundle\Model\ProductReview\ProductReviewApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\Exception\ProductNotFoundUserError;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

class CreateProductReviewMutation extends AbstractMutation
{
    protected const string VALIDATION_GROUP_GUEST = 'guest';

    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ProductReviewApiFacade $productReviewApiFacade,
        protected readonly RateLimiterFactoryInterface $productReviewCreateRateLimiter,
        protected readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function createProductReviewMutation(Argument $argument, InputValidator $validator): array
    {
        $this->productReviewApiFacade->checkProductReviewsEnabledOnCurrentDomain();

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $validator->validate($this->computeValidationGroups($customerUser !== null));

        $this->checkProductReviewCreationRateLimit($customerUser);

        try {
            $productReview = $this->productReviewApiFacade->createFromProductReviewInputArgument(
                $argument,
                $customerUser,
            );
        } catch (ProductNotFoundException) {
            throw new ProductNotFoundUserError('Product not found.');
        }

        return $this->productReviewApiFacade->extractReviewToPublicArray($productReview);
    }

    protected function checkProductReviewCreationRateLimit(?CustomerUser $customerUser): void
    {
        $limiterKey = $customerUser !== null
            ? 'product-review-create:customer:' . $customerUser->getUuid()
            : 'product-review-create:ip:' . ($this->requestStack->getCurrentRequest()?->getClientIp() ?? 'unknown');

        $limit = $this->productReviewCreateRateLimiter
            ->create($limiterKey)
            ->consume();

        if (!$limit->isAccepted()) {
            throw new TooManyProductReviewsUserError('Too many product reviews were created. Try again later.');
        }
    }

    /**
     * @return string[]
     */
    protected function computeValidationGroups(bool $isCustomerUserLoggedIn): array
    {
        if ($isCustomerUserLoggedIn) {
            return ['Default'];
        }

        return ['Default', self::VALIDATION_GROUP_GUEST];
    }
}
