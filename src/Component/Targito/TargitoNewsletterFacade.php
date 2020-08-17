<?php

declare(strict_types=1);

namespace App\Component\Targito;

use App\Component\Setting\Setting;
use App\Component\Targito\Exception\TargitoNewsletterSubscriptionException;
use App\Component\Targito\Exception\TargitoNotEnabledException;
use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserFacade;
use App\Model\Newsletter\NewsletterRepository;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\RequestOptions;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class TargitoNewsletterFacade
{
    /**
     * @var \App\Component\Targito\TargitoConfig
     */
    private $targitoConfig;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Newsletter\NewsletterRepository
     */
    private $newsletterRepository;

    /**
     * @var \App\Model\Customer\User\CustomerUserFacade
     */
    private $customerUserFacade;

    /**
     * @param \App\Component\Targito\TargitoConfig $targitoConfig
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Newsletter\NewsletterRepository $newsletterRepository
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     */
    public function __construct(
        TargitoConfig $targitoConfig,
        Domain $domain,
        NewsletterRepository $newsletterRepository,
        Setting $setting,
        CustomerUserFacade $customerUserFacade
    ) {
        $this->targitoConfig = $targitoConfig;
        $this->domain = $domain;
        $this->newsletterRepository = $newsletterRepository;
        $this->setting = $setting;
        $this->customerUserFacade = $customerUserFacade;
    }

    /**
     * @var \App\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @var string
     */
    protected $origin;

    public function runSynchronization(): void
    {
        $now = new DateTime();
        $lastSyncDateTime = $this->setting->get(Setting::TARGITO_LAST_SYNC_DATETIME);

        foreach ($this->domain->getAllIds() as $domainId) {
            $this->setOrigin($domainId);
            $newsletterSubscribers = $this->newsletterRepository->getAllUpdatedSubscribersFromLastUpdate($domainId, $lastSyncDateTime);
            $this->synchronizeSubscribersOnDomain($newsletterSubscribers, $domainId);
        }

        $this->setting->set(Setting::TARGITO_LAST_SYNC_DATETIME, $now);
    }

    /**
     * @param \App\Model\Newsletter\NewsletterSubscriber[] $newsletterSubscribers
     * @param int $domainId
     */
    private function synchronizeSubscribersOnDomain(array $newsletterSubscribers, int $domainId): void
    {
        foreach ($newsletterSubscribers as $newsletterSubscriber) {
            $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($newsletterSubscriber->getEmail(), $domainId);

            if ($customerUser === null) {
                if ($newsletterSubscriber->isDeleted() === false) {
                    $this->subscribeToTargitoByForm($newsletterSubscriber->getEmail());
                }
            } else {
                if ($newsletterSubscriber->isDeleted() === false) {
                    $this->subscribeToTargitoByCustomerUser($customerUser);
                } else {
                    $this->unsubscribeFromTargitoByUser($customerUser);
                }
            }
        }
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     */
    private function subscribeToTargitoByCustomerUser(CustomerUser $customerUser): void
    {
        if (!$this->targitoConfig->enabled) {
            throw new TargitoNotEnabledException('Targito is disabled');
        }

        $gdprMarketingDate = new DateTime();
        $gdprMarketingExpiration = new DateTime(sprintf('+ %d days', TargitoConfig::GDPR_MARKETING_LIFETIME_IN_DAYS));

        $options = [
            'accountId' => $this->targitoConfig->eshopToTargitoAccountId,
            'email' => $customerUser->getEmail(),
            'origin' => $this->origin,
            'isOptedIn' => true,
            'forbidReOptIn' => false,
            'consents' => ['marketing'],
            'columns' => [
                'firstname' => $customerUser->getFirstName(),
                'lastname' => $customerUser->getLastName(),
                'gdpr_marketing_value' => 1,
                'gdpr_marketing_date' => $gdprMarketingDate->format(TargitoConfig::FORMAT_DATETIME_FOR_TARGITO),
                'gdpr_marketing_expiration_date' => $gdprMarketingExpiration->format(TargitoConfig::FORMAT_DATETIME_FOR_TARGITO),
                'gdpr_marketing_parent' => 'Newsletter',
            ],
        ];

        $result = $this->subscribeToTargito($options);

        if ($result['result']['isOptedIn'] !== true) {
            throw new TargitoNewsletterSubscriptionException('Targito API : contacts/AddContact : return FALSE');
        }
    }

    /**
     * @param string $email
     */
    public function subscribeToTargitoByForm(string $email): void
    {
        if (!$this->targitoConfig->enabled) {
            throw new TargitoNotEnabledException('Targito is disabled');
        }

        $gdprMarketingDate = new DateTime();
        $gdprMarketingExpiration = new DateTime(sprintf('+ %d days', TargitoConfig::GDPR_MARKETING_LIFETIME_IN_DAYS));

        $options = [
            'accountId' => $this->targitoConfig->eshopToTargitoAccountId,
            'email' => $email,
            'origin' => $this->origin,
            'isOptedIn' => false,
            'consents' => ['marketing'],
            'columns' => [
                'gdpr_marketing_value' => 1,
                'gdpr_marketing_date' => $gdprMarketingDate->format(TargitoConfig::FORMAT_DATETIME_FOR_TARGITO),
                'gdpr_marketing_expiration_date' => $gdprMarketingExpiration->format(TargitoConfig::FORMAT_DATETIME_FOR_TARGITO),
                'gdpr_marketing_parent' => 'Newsletter',
            ],
        ];

        $this->subscribeToTargito($options);
    }

    /**
     * @param array $options
     * @return array
     */
    private function subscribeToTargito(array $options): array
    {
        $accountId = $this->targitoConfig->eshopToTargitoAccountId;
        $password = $this->targitoConfig->eshopToTargitoPassword;

        try {
            $client = new Client(['base_uri' => TargitoConfig::TARGITO_BASE_URL]);
            $request = $client->post('contacts/AddContact', [
                RequestOptions::JSON => $options,
                RequestOptions::AUTH => [$accountId, $password],
            ]);
        } catch (BadResponseException $ex) {
            $response = $ex->getResponse();
            throw new TargitoNewsletterSubscriptionException(
                'Targito API : contacts/AddContact : HTTP Status code : ' . $response->getStatusCode() . ' : ' . $response->getBody()->getContents()
            );
        }

        $result = json_decode($request->getBody()->getContents(), true);

        return $result;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     */
    private function unsubscribeFromTargitoByUser(CustomerUser $customerUser)
    {
        if (!$this->targitoConfig->enabled) {
            throw new TargitoNotEnabledException('Targito is disabled');
        }

        $accountId = $this->targitoConfig->eshopToTargitoAccountId;
        $password = $this->targitoConfig->eshopToTargitoPassword;

        try {
            $client = new Client(['base_uri' => TargitoConfig::TARGITO_BASE_URL]);
            $request = $client->post('contacts/OptOutContact', [
                RequestOptions::JSON => [
                    'accountId' => $accountId,
                    'email' => $customerUser->getEmail(),
                    'origin' => $this->origin,
                ],
                RequestOptions::AUTH => [$accountId, $password],
            ]);
        } catch (BadResponseException $ex) {
            $response = $ex->getResponse();
            throw new TargitoNewsletterSubscriptionException(
                'Targito API : contacts/OptOutContact : HTTP Status code : ' . $response->getStatusCode() . ' : ' . $response->getBody()->getContents()
            );
        }

        if ($request->getBody()->getContents() !== 'true') {
            throw new TargitoNewsletterSubscriptionException('Targito API : contacts/OptOutContact : return FALSE : ' . $request->getBody()->getContents());
        }
    }

    /**
     * @param int $domainId
     */
    private function setOrigin(int $domainId)
    {
        $this->origin = str_replace('.', '_', $this->domain->getDomainConfigById($domainId)->getName());
    }
}
