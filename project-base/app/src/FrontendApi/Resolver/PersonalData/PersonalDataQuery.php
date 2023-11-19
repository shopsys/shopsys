<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\PersonalData;

use App\Component\Setting\Setting;
use App\FrontendApi\Resolver\PersonalData\Exception\PersonalDataHashInvalidUserError;
use App\Model\Customer\User\CustomerUserFacade;
use App\Model\Order\OrderFacade;
use App\Model\PersonalData\PersonalDataExportFacade;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting as BaseSetting;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class PersonalDataQuery extends AbstractQuery
{
    private DomainRouter $router;

    /**
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \App\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade $personalDataAccessRequestFacade
     * @param \App\Model\PersonalData\PersonalDataExportFacade $personalDataExportFacade
     */
    public function __construct(
        private readonly Setting $setting,
        private readonly Domain $domain,
        DomainRouterFactory $domainRouterFactory,
        private readonly CustomerUserFacade $customerUserFacade,
        private readonly OrderFacade $orderFacade,
        private readonly NewsletterFacade $newsletterFacade,
        private readonly PersonalDataAccessRequestFacade $personalDataAccessRequestFacade,
        private readonly PersonalDataExportFacade $personalDataExportFacade,
    ) {
        $this->router = $domainRouterFactory->getRouter($this->domain->getId());
    }

    /**
     * @return array<string, string>
     */
    public function personalDataPageQuery(): array
    {
        return [
            'displaySiteContent' => $this->setting->getForDomain(BaseSetting::PERSONAL_DATA_DISPLAY_SITE_CONTENT, $this->domain->getId()),
            'displaySiteSlug' => $this->router->generate('front_personal_data', []),
            'exportSiteContent' => $this->setting->getForDomain(BaseSetting::PERSONAL_DATA_EXPORT_SITE_CONTENT, $this->domain->getId()),
            'exportSiteSlug' => $this->router->generate('front_personal_data_export', []),
        ];
    }

    /**
     * @param string $hash
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return array<'customerUser'|'exportLink'|'newsletterSubscriber'|'orders', mixed>
     */
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

        return [
            'orders' => $orders,
            'customerUser' => $customerUser,
            'newsletterSubscriber' => $newsletterSubscriber,
            'exportLink' => $this->personalDataExportFacade->generateExportRequestAndGetLink($email),
        ];
    }
}
