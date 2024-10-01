<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Inquiry;

use Overblog\GraphQLBundle\Definition\Argument;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Inquiry\InquiryData;
use Shopsys\FrameworkBundle\Model\Inquiry\InquiryDataFactory;
use Shopsys\FrameworkBundle\Model\Inquiry\InquiryFacade;
use Shopsys\FrameworkBundle\Model\Inquiry\Mail\InquiryMailFacade;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\Exception\ProductNotFoundUserError;

class CreateInquiryMutation extends AbstractMutation
{
    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\InquiryDataFactory $inquiryDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\InquiryFacade $inquiryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\Mail\InquiryMailFacade $inquiryMailFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly InquiryDataFactory $inquiryDataFactory,
        protected readonly InquiryFacade $inquiryFacade,
        protected readonly ProductFacade $productFacade,
        protected readonly InquiryMailFacade $inquiryMailFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return bool
     */
    public function createInquiryMutation(Argument $argument): bool
    {
        try {
            $inquiryData = $this->createInquiryDataFromArgument($argument);
            $inquiry = $this->inquiryFacade->create($inquiryData);

            $this->inquiryMailFacade->sendMail($inquiry);

            return true;
        } catch (ProductNotFoundException) {
            throw new ProductNotFoundUserError(sprintf('Product with UUID "%s" not found', $argument['input']['productUuid']));
        }
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Inquiry\InquiryData
     */
    protected function createInquiryDataFromArgument(Argument $argument): InquiryData
    {
        $input = $argument['input'];

        $inquiryData = $this->inquiryDataFactory->create($this->domain->getId());
        $product = $this->productFacade->getByUuid($input['productUuid']);

        $inquiryData->firstName = $input['firstName'];
        $inquiryData->lastName = $input['lastName'];
        $inquiryData->email = $input['email'];
        $inquiryData->telephone = $input['telephone'];
        $inquiryData->companyName = $input['companyName'] ?? null;
        $inquiryData->companyNumber = $input['companyNumber'] ?? null;
        $inquiryData->companyTaxNumber = $input['companyTaxNumber'] ?? null;
        $inquiryData->note = $input['note'] ?? null;
        $inquiryData->product = $product;

        return $inquiryData;
    }
}
