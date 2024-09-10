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
    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade $complaintApiFacade
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        protected readonly ComplaintApiFacade $complaintApiFacade,
    ) {
        parent::__construct($tokenStorage);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint
     */
    public function createComplaintMutation(Argument $argument, InputValidator $validator): Complaint
    {
        $this->runCheckUserIsLogged();

        $validator->validate();

        return $this->complaintApiFacade->createFromComplaintInputArgument($argument);
    }
}
