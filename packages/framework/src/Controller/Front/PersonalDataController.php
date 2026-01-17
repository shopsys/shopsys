<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Front;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\Exception\NotFoundRedirectToStorefrontException;
use Shopsys\FrameworkBundle\Component\HttpFoundation\XmlResponse;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade;
use Shopsys\FrameworkBundle\Model\Watchdog\WatchdogFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PersonalDataController extends AbstractController
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly OrderFacade $orderFacade,
        protected readonly NewsletterFacade $newsletterFacade,
        protected readonly PersonalDataAccessRequestFacade $personalDataAccessRequestFacade,
        protected readonly XmlResponse $xmlResponse,
        protected readonly ComplaintFacade $complaintFacade,
        protected readonly WatchdogFacade $watchdogFacade,
    ) {
    }

    public function exportXmlAction(string $hash): Response
    {
        $personalDataAccessRequest = $this->personalDataAccessRequestFacade->findByHashAndDomainId(
            $hash,
            $this->domain->getId(),
        );

        if (
            $personalDataAccessRequest !== null
            && $personalDataAccessRequest->getType() === PersonalDataAccessRequest::TYPE_EXPORT
        ) {
            $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain(
                $personalDataAccessRequest->getEmail(),
                $this->domain->getId(),
            );

            $orders = $this->orderFacade->getOrderListForEmailByDomainId(
                $personalDataAccessRequest->getEmail(),
                $this->domain->getId(),
            );

            $newsletterSubscriber = $this->newsletterFacade->findNewsletterSubscriberByEmailAndDomainId(
                $personalDataAccessRequest->getEmail(),
                $this->domain->getId(),
            );

            $complaints = [];

            if ($customerUser !== null) {
                $complaints = $this->complaintFacade->getComplaintsByCustomerUserAndDomainIdAndLocale($customerUser, $this->domain->getId(), $this->domain->getLocale());
            }

            $watchdogs = $this->watchdogFacade->getWatchdogsByEmail($personalDataAccessRequest->getEmail());

            $xmlContent = $this->render('@ShopsysFramework/Front/Content/PersonalData/export.xml.twig', [
                'customerUser' => $customerUser,
                'newsletterSubscriber' => $newsletterSubscriber,
                'orders' => $orders,
                'complaints' => $complaints,
                'watchdogs' => $watchdogs,
            ])->getContent();

            $fileName = $personalDataAccessRequest->getEmail() . '.xml';

            return $this->xmlResponse->getXmlResponse($fileName, $xmlContent);
        }

        throw new NotFoundRedirectToStorefrontException();
    }
}
