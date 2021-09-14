<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Mutation;

use App\FrontendApi\Model\Resolver\PersonalData\PersonalDataPageResolver;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMailFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade;

class PersonalDataMutation implements MutationInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade
     */
    private PersonalDataAccessRequestFacade $personalDataAccessRequestFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestDataFactory
     */
    private PersonalDataAccessRequestDataFactoryInterface $personalDataAccessRequestDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMailFacade
     */
    private PersonalDataAccessMailFacade $personalDataAccessMailFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\FrontendApi\Model\Resolver\PersonalData\PersonalDataPageResolver
     */
    private PersonalDataPageResolver $personalDataPageResolver;

    /**
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade $personalDataAccessRequestFacade
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestDataFactory $personalDataAccessRequestDataFactory
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMailFacade $personalDataAccessMailFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Model\Resolver\PersonalData\PersonalDataPageResolver $personalDataPageResolver
     */
    public function __construct(
        PersonalDataAccessRequestFacade $personalDataAccessRequestFacade,
        PersonalDataAccessRequestDataFactoryInterface $personalDataAccessRequestDataFactory,
        PersonalDataAccessMailFacade $personalDataAccessMailFacade,
        Domain $domain,
        PersonalDataPageResolver $personalDataPageResolver
    ) {
        $this->personalDataAccessRequestFacade = $personalDataAccessRequestFacade;
        $this->personalDataAccessRequestDataFactory = $personalDataAccessRequestDataFactory;
        $this->personalDataAccessMailFacade = $personalDataAccessMailFacade;
        $this->domain = $domain;
        $this->personalDataPageResolver = $personalDataPageResolver;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return array<string, string>
     */
    public function requestPersonalDataAccess(Argument $argument, InputValidator $validator): array
    {
        $validator->validate();

        $input = $argument['input'];
        $type = $input['type'];

        if ($type === PersonalDataAccessRequest::TYPE_DISPLAY) {
            $personalDataAccessRequestData = $this->personalDataAccessRequestDataFactory->createForDisplay();
        } elseif ($type === PersonalDataAccessRequest::TYPE_EXPORT) {
            $personalDataAccessRequestData = $this->personalDataAccessRequestDataFactory->createForExport();
        } else {
            throw new UserError('Requested type of data is not valid.');
        }

        $personalDataAccessRequestData->email = $input['email'];

        $personalData = $this->personalDataAccessRequestFacade->createPersonalDataAccessRequest(
            $personalDataAccessRequestData,
            $this->domain->getId()
        );

        $this->personalDataAccessMailFacade->sendMail($personalData);

        return $this->personalDataPageResolver->resolve();
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'requestPersonalDataAccess' => 'requestPersonalDataAccess',
        ];
    }
}
