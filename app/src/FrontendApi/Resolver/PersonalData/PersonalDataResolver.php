<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\PersonalData;

use App\Component\Setting\Setting;
use App\Model\Customer\User\CustomerUserFacade;
use App\Model\Order\OrderFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting as BaseSetting;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade;

class PersonalDataResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Component\Setting\Setting
     */
    private Setting $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouter
     */
    private DomainRouter $router;

    /**
     * @var \App\Model\Customer\User\CustomerUserFacade
     */
    private CustomerUserFacade $customerUserFacade;

    /**
     * @var \App\Model\Order\OrderFacade
     */
    private OrderFacade $orderFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade
     */
    private NewsletterFacade $newsletterFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade
     */
    private PersonalDataAccessRequestFacade $personalDataAccessRequestFacade;

    /**
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \App\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade $personalDataAccessRequestFacade
     */
    public function __construct(
        Setting $setting,
        Domain $domain,
        DomainRouterFactory $domainRouterFactory,
        CustomerUserFacade $customerUserFacade,
        OrderFacade $orderFacade,
        NewsletterFacade $newsletterFacade,
        PersonalDataAccessRequestFacade $personalDataAccessRequestFacade
    ) {
        $this->setting = $setting;
        $this->domain = $domain;
        $this->router = $domainRouterFactory->getRouter($this->domain->getId());
        $this->customerUserFacade = $customerUserFacade;
        $this->orderFacade = $orderFacade;
        $this->newsletterFacade = $newsletterFacade;
        $this->personalDataAccessRequestFacade = $personalDataAccessRequestFacade;
    }

    /**
     * @return array<string, string>
     */
    public function resolvePersonalDataPage(): array
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
     * @return array
     */
    public function resolvePersonalDataAccess(string $hash, InputValidator $validator): array
    {
        $validator->validate();

        $domainId = $this->domain->getId();
        $personalDataAccessRequest = $this->personalDataAccessRequestFacade->findByHashAndDomainId($hash, $domainId);

        if ($personalDataAccessRequest === null || $personalDataAccessRequest->getType() === PersonalDataAccessRequest::TYPE_EXPORT) {
            throw new UserError('Provided hash does not exists or is no longer valid.');
        }

        $email = $personalDataAccessRequest->getEmail();
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($email, $domainId);
        $orders = $this->orderFacade->getOrderListForEmailByDomainId($email, $domainId);
        $newsletterSubscriber = $this->newsletterFacade->findNewsletterSubscriberByEmailAndDomainId($email, $domainId);

        return [
            'orders' => $orders,
            'customerUser' => $customerUser,
            'newsletterSubscriber' => $newsletterSubscriber,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolvePersonalDataPage' => 'personalDataPage',
            'resolvePersonalDataAccess' => 'accessPersonalData',
        ];
    }
}
