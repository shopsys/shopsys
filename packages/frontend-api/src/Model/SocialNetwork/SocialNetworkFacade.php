<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\SocialNetwork;

use Hybridauth\Exception\Exception as HybridauthException;
use Hybridauth\Exception\InvalidArgumentException;
use Hybridauth\Exception\UnexpectedValueException;
use Hybridauth\Hybridauth;
use Hybridauth\User\Profile;
use Monolog\Logger;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrontendApiBundle\Controller\SocialNetworkController;
use Shopsys\FrontendApiBundle\Model\Cart\MergeCartFacade;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeDataFactory;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade;
use Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationDataFactory;
use Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationFacade;
use Shopsys\FrontendApiBundle\Model\Security\LoginAsUserFacade;
use Shopsys\FrontendApiBundle\Model\Security\LoginResultData;
use Shopsys\FrontendApiBundle\Model\Security\LoginResultDataFactory;
use Shopsys\FrontendApiBundle\Model\SocialNetwork\Exception\SocialNetworkLoginException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SocialNetworkFacade
{
    public function __construct(
        protected readonly RegistrationDataFactory $registrationDataFactory,
        protected readonly RegistrationFacade $registrationFacade,
        protected readonly SocialNetworkConfigFactory $socialNetworkConfigFactory,
        protected readonly Logger $logger,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly LoginAsUserFacade $loginAsUserFacade,
        protected readonly ValidatorInterface $validator,
        protected readonly MergeCartFacade $mergeCartFacade,
        protected readonly LoginResultDataFactory $loginResultDataFactory,
        protected readonly ProductListFacade $productListFacade,
        protected readonly CustomerUserLoginTypeDataFactory $customerUserLoginTypeDataFactory,
        protected readonly CustomerUserLoginTypeFacade $customerUserLoginTypeFacade,
        protected readonly Domain $domain,
        protected readonly FedcmAdapterFactory $fedcmAdapterFactory,
    ) {
    }

    public function login(string $type, string $redirectUrl, SessionInterface $session): LoginResultData
    {
        try {
            $config = $this->socialNetworkConfigFactory->createConfigForDomain($this->domain->getId(), $redirectUrl);
            $hybridauth = new Hybridauth($config);

            $adapter = $hybridauth->authenticate($type);
            $userProfile = $adapter->getUserProfile();

            $this->validateDataFromSocialNetwork($userProfile);

            $loginResultData = $this->registerOrLoginFromProfile($userProfile, $type, $session);

            $adapter->disconnect();

            return $loginResultData;
        } catch (InvalidArgumentException | UnexpectedValueException $exception) {
            $message = sprintf('Login via %s doesn\'t work', $type);
            $this->logger->error($message, ['exception' => $exception]);

            throw new SocialNetworkLoginException(message: $message, previous: $exception);
        }
    }

    public function loginWithCredential(string $type, string $credential, SessionInterface $session): LoginResultData
    {
        $adapter = $this->fedcmAdapterFactory->createForDomainAndType($this->domain->getId(), $type);

        if ($adapter === null) {
            throw new SocialNetworkLoginException(sprintf('FedCM is not enabled for %s on the current domain', $type));
        }

        try {
            $userProfile = $adapter->getUserProfileFromFedcmCredential($credential);
        } catch (HybridauthException $exception) {
            $message = sprintf('FedCM login via %s failed', $type);
            $this->logger->error($message, ['exception' => $exception]);

            throw new SocialNetworkLoginException(message: $message, previous: $exception);
        }

        $this->validateDataFromSocialNetwork($userProfile);

        return $this->registerOrLoginFromProfile($userProfile, $type, $session);
    }

    protected function registerOrLoginFromProfile(
        Profile $userProfile,
        string $type,
        SessionInterface $session,
    ): LoginResultData {
        $registrationData = $this->registrationDataFactory->createFromSocialNetworkProfile($userProfile);

        $isRegistration = false;

        try {
            $customerUser = $this->registrationFacade->register($registrationData);
            $isRegistration = true;
        } catch (DuplicateEmailException) {
            $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($registrationData->email, $registrationData->domainId);
        }

        $productListUuids = $session->get(SocialNetworkController::SESSION_PRODUCT_LIST_UUIDS);
        $loginResultData = $this->loginAsUserFacade->runLoginSteps(
            $customerUser,
            $type,
            $isRegistration,
            $productListUuids !== null ? explode(',', $productListUuids) : [],
            $session->get(SocialNetworkController::SESSION_SHOULD_OVERWRITE_CART, false),
            $session->get(SocialNetworkController::SESSION_CART_UUID),
            (string)$userProfile->identifier,
        );

        $session->remove(SocialNetworkController::SESSION_CART_UUID);
        $session->remove(SocialNetworkController::SESSION_SHOULD_OVERWRITE_CART);
        $session->remove(SocialNetworkController::SESSION_PRODUCT_LIST_UUIDS);

        return $loginResultData;
    }

    protected function validateDataFromSocialNetwork(Profile $userProfile): void
    {
        $violations = $this->validator->validate($userProfile->email, [
            new NotBlank(message: 'Email is not filled'),
            new Length(
                max: 255,
                maxMessage: 'Email cannot be longer than {{ limit }} characters',
            ),
            new Email(message: 'Email is not valid'),
        ]);

        if (count($violations) > 0) {
            throw new SocialNetworkLoginException('Data from social network are not valid');
        }
    }
}
