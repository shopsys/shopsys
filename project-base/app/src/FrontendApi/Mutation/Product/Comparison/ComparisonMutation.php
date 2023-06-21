<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Product\Comparison;

use App\Model\Product\Comparison\Comparison;
use App\Model\Product\Comparison\ComparisonFacade;
use App\Model\Product\ProductFacade;
use Overblog\GraphQLBundle\Definition\Argument;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;

class ComparisonMutation extends AbstractMutation
{
    public const SUCCESS_RESULT = 'OK';

    /**
     * @param \App\Model\Product\Comparison\ComparisonFacade $comparisonFacade
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        private readonly ComparisonFacade $comparisonFacade,
        private readonly ProductFacade $productFacade,
        private readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\Model\Product\Comparison\Comparison
     */
    public function addProductToComparisonMutation(Argument $argument): Comparison
    {
        $productUuid = $argument['productUuid'];
        $product = $this->productFacade->getByUuid($productUuid);
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        return $this->comparisonFacade->addProductToComparison($product, $customerUser, $argument['comparisonUuid']);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\Model\Product\Comparison\Comparison|null
     */
    public function removeProductFromComparisonMutation(Argument $argument): ?Comparison
    {
        $productUuid = $argument['productUuid'];
        $product = $this->productFacade->getByUuid($productUuid);
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        return $this->comparisonFacade->removeProductFromComparison($product, $customerUser, $argument['comparisonUuid']);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return string
     */
    public function cleanComparisonMutation(Argument $argument): string
    {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $this->comparisonFacade->cleanComparison($customerUser, $argument['comparisonUuid']);

        return self::SUCCESS_RESULT;
    }
}
