<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Complaint;

use App\Model\Customer\User\CurrentCustomerUser;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade;
use Shopsys\FrontendApiBundle\Model\Complaint\Exception\ComplaintNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;

class ComplaintQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade $complaintApiFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        protected readonly ComplaintApiFacade $complaintApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint
     */
    public function complaintQuery(Argument $argument): Complaint
    {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if (!$customerUser) {
            throw new InvalidTokenUserMessageException();
        }

        $complaintNumber = $argument['number'];
        $complaint = $this->complaintApiFacade->findByComplaintNumberAndCustomerUser($complaintNumber, $customerUser);

        if (!$complaint) {
            throw new ComplaintNotFoundUserError(sprintf('Complaint with number %s not found.', $complaintNumber));
        }

        return $complaint;
    }
}
