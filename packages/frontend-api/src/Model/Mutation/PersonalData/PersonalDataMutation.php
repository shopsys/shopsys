<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\PersonalData;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMailFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestDataFactory;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Mutation\PersonalData\Exception\InvalidPersonalDataRequestTypeUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\PersonalData\PersonalDataQuery;

class PersonalDataMutation extends AbstractMutation
{
    public function __construct(
        protected readonly PersonalDataAccessRequestFacade $personalDataAccessRequestFacade,
        protected readonly PersonalDataAccessRequestDataFactory $personalDataAccessRequestDataFactory,
        protected readonly PersonalDataAccessMailFacade $personalDataAccessMailFacade,
        protected readonly Domain $domain,
        protected readonly PersonalDataQuery $personalDataPageResolver,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function requestPersonalDataAccessMutation(Argument $argument, InputValidator $validator): array
    {
        $validator->validate();

        $input = $argument['input'];
        $type = $input['type'];

        if ($type === PersonalDataAccessRequest::TYPE_DISPLAY) {
            $personalDataAccessRequestData = $this->personalDataAccessRequestDataFactory->createForDisplay();
        } elseif ($type === PersonalDataAccessRequest::TYPE_EXPORT) {
            $personalDataAccessRequestData = $this->personalDataAccessRequestDataFactory->createForExport();
        } else {
            throw new InvalidPersonalDataRequestTypeUserError('Requested type of data is not valid.');
        }

        $personalDataAccessRequestData->email = $input['email'];

        $personalData = $this->personalDataAccessRequestFacade->createPersonalDataAccessRequest(
            $personalDataAccessRequestData,
            $this->domain->getId(),
        );

        $this->personalDataAccessMailFacade->sendMail($personalData);

        return $this->personalDataPageResolver->personalDataPageQuery();
    }
}
