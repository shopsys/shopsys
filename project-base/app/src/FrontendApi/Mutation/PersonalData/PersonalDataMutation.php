<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\PersonalData;

use App\FrontendApi\Mutation\PersonalData\Exception\InvalidPersonalDataRequestTypeUserError;
use App\FrontendApi\Resolver\PersonalData\PersonalDataQuery;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMailFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;

class PersonalDataMutation extends AbstractMutation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade $personalDataAccessRequestFacade
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestDataFactory $personalDataAccessRequestDataFactory
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMailFacade $personalDataAccessMailFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Resolver\PersonalData\PersonalDataQuery $personalDataPageResolver
     */
    public function __construct(
        private readonly PersonalDataAccessRequestFacade $personalDataAccessRequestFacade,
        private readonly PersonalDataAccessRequestDataFactoryInterface $personalDataAccessRequestDataFactory,
        private readonly PersonalDataAccessMailFacade $personalDataAccessMailFacade,
        private readonly Domain $domain,
        private readonly PersonalDataQuery $personalDataPageResolver
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
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
            $this->domain->getId()
        );

        $this->personalDataAccessMailFacade->sendMail($personalData);

        return $this->personalDataPageResolver->personalDataPageQuery();
    }
}
