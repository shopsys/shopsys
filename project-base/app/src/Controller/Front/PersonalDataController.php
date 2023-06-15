<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\XmlResponse;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PersonalDataController extends AbstractController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \App\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade $personalDataAccessRequestFacade
     * @param \Shopsys\FrameworkBundle\Component\HttpFoundation\XmlResponse $xmlResponse
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly CustomerUserFacade $customerUserFacade,
        private readonly OrderFacade $orderFacade,
        private readonly NewsletterFacade $newsletterFacade,
        private readonly PersonalDataAccessRequestFacade $personalDataAccessRequestFacade,
        private readonly XmlResponse $xmlResponse
    ) {
    }

    /**
     * @param string $hash
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

            $xmlContent = $this->render('Front/Content/PersonalData/export.xml.twig', [
                'customerUser' => $customerUser,
                'newsletterSubscriber' => $newsletterSubscriber,
                'orders' => $orders,
            ])->getContent();

            $fileName = $personalDataAccessRequest->getEmail() . '.xml';

            return $this->xmlResponse->getXmlResponse($fileName, $xmlContent);
        }

        throw new NotFoundHttpException();
    }
}
