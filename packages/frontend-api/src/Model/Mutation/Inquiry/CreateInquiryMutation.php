<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Inquiry;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Inquiry\InquiryData;
use Shopsys\FrameworkBundle\Model\Inquiry\InquiryDataFactory;
use Shopsys\FrameworkBundle\Model\Inquiry\InquiryFacade;
use Shopsys\FrameworkBundle\Model\Inquiry\Mail\InquiryMailFacade;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\Exception\ProductNotFoundUserError;

class CreateInquiryMutation extends AbstractMutation
{
    public function __construct(
        protected readonly InquiryDataFactory $inquiryDataFactory,
        protected readonly InquiryFacade $inquiryFacade,
        protected readonly ProductFacade $productFacade,
        protected readonly InquiryMailFacade $inquiryMailFacade,
        protected readonly Domain $domain,
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

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

    protected function createInquiryDataFromArgument(Argument $argument): InquiryData
    {
        $input = $argument['input'];

        $inquiryData = $this->inquiryDataFactory->create($this->domain->getId());
        $product = $this->productFacade->getByUuid($input['productUuid']);

        $inquiryData->firstName = $input['firstName'];
        $inquiryData->lastName = $input['lastName'];
        $inquiryData->email = $input['email'];
        $inquiryData->telephone = PhoneData::fromArray($input['telephone']);
        $inquiryData->companyName = $input['companyName'] ?? null;
        $inquiryData->companyNumber = $input['companyNumber'] ?? null;
        $inquiryData->companyTaxNumber = $input['companyTaxNumber'] ?? null;
        $inquiryData->note = $input['note'] ?? null;
        $inquiryData->customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $inquiryData->product = $product;

        return $inquiryData;
    }
}
