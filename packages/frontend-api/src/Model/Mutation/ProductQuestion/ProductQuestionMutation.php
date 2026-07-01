<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\ProductQuestion;

use Exception;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\ProductQuestion\Mail\ProductQuestionMailFacade;
use Shopsys\FrameworkBundle\Model\ProductQuestion\ProductQuestionData;
use Shopsys\FrameworkBundle\Model\ProductQuestion\ProductQuestionDataFactory;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\Exception\ProductNotFoundUserError;

class ProductQuestionMutation extends AbstractMutation
{
    public function __construct(
        protected readonly ProductQuestionMailFacade $productQuestionMailFacade,
        protected readonly ProductFacade $productFacade,
        protected readonly Domain $domain,
        protected readonly LoggerInterface $logger,
        protected readonly ProductQuestionDataFactory $productQuestionDataFactory,
    ) {
    }

    public function productQuestionMutation(Argument $argument, InputValidator $validator): bool
    {
        $validator->validate();

        try {
            $productQuestionData = $this->createProductQuestionDataFromArgument($argument);
        } catch (ProductNotFoundException) {
            throw new ProductNotFoundUserError(sprintf('Product with UUID "%s" not found', $argument['input']['productUuid']));
        }

        try {
            $this->productQuestionMailFacade->sendMail($productQuestionData);
        } catch (Exception $exception) {
            $this->logger->error(
                'Email was not sent from product question form',
                [
                    'error' => $exception->getMessage(),
                ],
            );

            return false;
        }

        return true;
    }

    protected function createProductQuestionDataFromArgument(Argument $argument): ProductQuestionData
    {
        $input = $argument['input'];

        $productQuestionData = $this->productQuestionDataFactory->create($this->domain->getId());
        $productQuestionData->customerName = $input['customerName'];
        $productQuestionData->email = $input['email'];
        $productQuestionData->question = $input['question'];
        $productQuestionData->product = $this->productFacade->getByUuid($input['productUuid']);

        return $productQuestionData;
    }
}
