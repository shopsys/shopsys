<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Complaint;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\BaseTokenMutation;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CreateComplaintMutation extends BaseTokenMutation
{
    protected const string VALIDATION_GROUP_WITH_ORDER = 'withOrder';
    protected const string VALIDATION_GROUP_WITHOUT_ORDER = 'withoutOrder';

    public function __construct(
        TokenStorageInterface $tokenStorage,
        protected readonly ComplaintApiFacade $complaintApiFacade,
    ) {
        parent::__construct($tokenStorage);
    }

    public function createComplaintMutation(Argument $argument, InputValidator $validator): Complaint
    {
        $this->runCheckUserIsLogged();

        $validationGroups = $this->computeValidationGroups($argument);
        $validator->validate($validationGroups);

        return $this->complaintApiFacade->createFromComplaintInputArgument($argument);
    }

    /**
     * @return string[]
     */
    protected function computeValidationGroups(Argument $argument): array
    {
        $input = $argument['input'];
        $validationGroups = ['Default'];

        if ($input['orderUuid'] === null) {
            $validationGroups[] = self::VALIDATION_GROUP_WITHOUT_ORDER;
        } else {
            $validationGroups[] = self::VALIDATION_GROUP_WITH_ORDER;
        }

        return $validationGroups;
    }
}
