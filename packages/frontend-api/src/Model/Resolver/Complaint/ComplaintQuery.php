<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Complaint;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade;
use Shopsys\FrontendApiBundle\Model\Complaint\Exception\ComplaintNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Symfony\Component\Security\Core\Security;

class ComplaintQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade $complaintApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        protected readonly ComplaintApiFacade $complaintApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly Security $security,
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

        if ($this->security->isGranted(CustomerUserRole::ROLE_API_ALL)) {
            $complaint = $this->complaintApiFacade->findByComplaintNumberAndCustomer(
                $complaintNumber,
                $customerUser->getCustomer()
            );
        } else {
            $complaint = $this->complaintApiFacade->findByComplaintNumberAndCustomerUser(
                $complaintNumber,
                $customerUser
            );
        }

        if (!$complaint) {
            throw new ComplaintNotFoundUserError(sprintf('Complaint with number %s not found.', $complaintNumber));
        }

        return $complaint;
    }
}
