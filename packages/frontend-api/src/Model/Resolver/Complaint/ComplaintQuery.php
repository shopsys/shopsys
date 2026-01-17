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
use Symfony\Bundle\SecurityBundle\Security;

class ComplaintQuery extends AbstractQuery
{
    public function __construct(
        protected readonly ComplaintApiFacade $complaintApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly Security $security,
    ) {
    }

    public function complaintQuery(Argument $argument): Complaint
    {
        $customerUser = $this->currentCustomerUser->getCurrentCustomerUser();

        $complaintNumber = $argument['number'];

        if ($this->security->isGranted(CustomerUserRole::ROLE_API_COMPANY_COMPLAINTS_VIEW)) {
            $complaint = $this->complaintApiFacade->findByComplaintNumberAndCustomer(
                $complaintNumber,
                $customerUser->getCustomer(),
            );
        } else {
            $complaint = $this->complaintApiFacade->findByComplaintNumberAndCustomerUser(
                $complaintNumber,
                $customerUser,
            );
        }

        if (!$complaint) {
            throw new ComplaintNotFoundUserError(sprintf('Complaint with number %s not found.', $complaintNumber));
        }

        return $complaint;
    }
}
