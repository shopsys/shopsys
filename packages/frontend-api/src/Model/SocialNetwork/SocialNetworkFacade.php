<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\SocialNetwork;

use Hybridauth\Exception\InvalidArgumentException;
use Hybridauth\Exception\UnexpectedValueException;
use Hybridauth\Hybridauth;
use Hybridauth\User\Profile;
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
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SocialNetworkFacade
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationDataFactory $registrationDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationFacade $registrationFacade
     * @param \Shopsys\FrontendApiBundle\Model\SocialNetwork\SocialNetworkConfigFactory $socialNetworkConfigFactory
     * @param \Symfony\Bridge\Monolog\Logger $logger
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrontendApiBundle\Model\Security\LoginAsUserFacade $loginAsUserFacade
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \Shopsys\FrontendApiBundle\Model\Cart\MergeCartFacade $mergeCartFacade
     * @param \Shopsys\FrontendApiBundle\Model\Security\LoginResultDataFactory $loginResultDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade $productListFacade
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeDataFactory $customerUserLoginTypeDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade $customerUserLoginTypeFacade
     */
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
    ) {
    }

    /**
     * @param string $type
     * @param string $redirectUrl
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @return \Shopsys\FrontendApiBundle\Model\Security\LoginResultData
     */
    public function login(string $type, string $redirectUrl, SessionInterface $session): LoginResultData
    {
        try {
            $config = $this->socialNetworkConfigFactory->createConfig($redirectUrl);
            $hybridauth = new Hybridauth($config);

            $adapter = $hybridauth->authenticate($type);
            $userProfile = $adapter->getUserProfile();

            $this->validateDataFromSocialNetwork($userProfile);

            $registrationData = $this->registrationDataFactory->createFromSocialNetworkProfile($userProfile);

            try {
                $customerUser = $this->registrationFacade->register($registrationData);
            } catch (DuplicateEmailException) {
                $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($registrationData->email, $registrationData->domainId);
            }
            $adapter->disconnect();

            $cartUuid = $session->get(SocialNetworkController::CART_UUID);
            $shouldOverwriteCustomerUserCart = $session->get(SocialNetworkController::SHOULD_OVERWRITE_CART);

            $showCartMergeInfo = false;

            if ($cartUuid !== null) {
                if ($shouldOverwriteCustomerUserCart) {
                    $this->mergeCartFacade->overwriteCustomerCartWithCartByUuid($cartUuid, $customerUser);
                } else {
                    $this->mergeCartFacade->mergeCartByUuidToCustomerCart($cartUuid, $customerUser);
                    $showCartMergeInfo = true;
                }
            }

            $productListsUuids = $session->get(SocialNetworkController::PRODUCT_LIST_UUIDS);

            if ($productListsUuids !== null) {
                $this->productListFacade->mergeProductListsToCustomerUser(explode(',', $productListsUuids), $customerUser);
            }

            $session->remove(SocialNetworkController::CART_UUID);
            $session->remove(SocialNetworkController::SHOULD_OVERWRITE_CART);
            $session->remove(SocialNetworkController::PRODUCT_LIST_UUIDS);

            $loginResultData = $this->loginResultDataFactory->create(
                $this->loginAsUserFacade->loginAndReturnAccessAndRefreshToken($customerUser),
                $showCartMergeInfo,
            );

            $this->customerUserLoginTypeFacade->updateCustomerUserLoginTypes(
                $this->customerUserLoginTypeDataFactory->create($customerUser, $type, (string)$userProfile->identifier),
            );

            return $loginResultData;
        } catch (InvalidArgumentException | UnexpectedValueException $exception) {
            $message = sprintf('Login via %s doesn\'t work', $type);
            $this->logger->error($message, ['exception' => $exception]);

            throw new SocialNetworkLoginException(message: $message, previous: $exception);
        }
    }

    /**
     * @param \Hybridauth\User\Profile $userProfile
     */
    protected function validateDataFromSocialNetwork(Profile $userProfile): void
    {
        $violations = $this->validator->validate($userProfile->email, [
            new NotBlank(['message' => 'Email is not filled']),
            new Length([
                'max' => 255,
                'maxMessage' => 'Email cannot be longer than {{ limit }} characters',
            ]),
            new Email(['message' => 'Email is not valid']),
        ]);

        if (count($violations) > 0) {
            throw new SocialNetworkLoginException('Data from social network are not valid');
        }
    }
}
