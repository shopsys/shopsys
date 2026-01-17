<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\PersonalData;

use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataExportFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\PersonalData\Exception\PersonalDataHashInvalidUserError;

class PersonalDataQuery extends AbstractQuery
{
    protected DomainRouter $router;

    public function __construct(
        protected readonly Setting $setting,
        protected readonly Domain $domain,
        DomainRouterFactory $domainRouterFactory,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly OrderFacade $orderFacade,
        protected readonly NewsletterFacade $newsletterFacade,
        protected readonly PersonalDataAccessRequestFacade $personalDataAccessRequestFacade,
        protected readonly PersonalDataExportFacade $personalDataExportFacade,
        protected readonly ComplaintFacade $complaintFacade,
    ) {
        $this->router = $domainRouterFactory->getRouter($this->domain->getId());
    }

    /**
     * @return array<string, string>
     */
    public function personalDataPageQuery(): array
    {
        return [
            'displaySiteContent' => $this->setting->getForDomain(Setting::PERSONAL_DATA_DISPLAY_SITE_CONTENT, $this->domain->getId()),
            'displaySiteSlug' => $this->router->generate('front_personal_data', []),
            'exportSiteContent' => $this->setting->getForDomain(Setting::PERSONAL_DATA_EXPORT_SITE_CONTENT, $this->domain->getId()),
            'exportSiteSlug' => $this->router->generate('front_personal_data_export', []),
        ];
    }

    public function personalDataAccessQuery(string $hash, InputValidator $validator): array
    {
        $validator->validate();

        $domainId = $this->domain->getId();
        $personalDataAccessRequest = $this->personalDataAccessRequestFacade->findByHashAndDomainId($hash, $domainId);

        if ($personalDataAccessRequest === null || $personalDataAccessRequest->getType() === PersonalDataAccessRequest::TYPE_EXPORT) {
            throw new PersonalDataHashInvalidUserError('Provided hash does not exists or is no longer valid.');
        }

        $email = $personalDataAccessRequest->getEmail();
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($email, $domainId);
        $orders = $this->orderFacade->getOrderListForEmailByDomainId($email, $domainId);
        $newsletterSubscriber = $this->newsletterFacade->findNewsletterSubscriberByEmailAndDomainId($email, $domainId);
        $complaints = [];

        if ($customerUser !== null) {
            $complaints = $this->complaintFacade->getComplaintsByCustomerUserAndDomainIdAndLocale($customerUser, $domainId, $this->domain->getLocale());
        }

        return [
            'orders' => $orders,
            'customerUser' => $customerUser,
            'newsletterSubscriber' => $newsletterSubscriber,
            'exportLink' => $this->personalDataExportFacade->generateExportRequestAndGetLink($email),
            'complaints' => $complaints,
        ];
    }
}
