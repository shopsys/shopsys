<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginAsRememberedUserException;
use Shopsys\FrontendApiBundle\Model\Cart\MergeCartFacade;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeDataFactory;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\LoginTypeEnum;
use Shopsys\FrontendApiBundle\Model\Token\TokenAuthenticator;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;
use Symfony\Component\HttpFoundation\RequestStack;

class LoginAsUserFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Shopsys\FrontendApiBundle\Model\Token\TokenAuthenticator $tokenAuthenticator
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade $administratorFacade
     * @param \Shopsys\FrontendApiBundle\Model\Token\TokenFacade $tokenFacade
     * @param \Shopsys\FrontendApiBundle\Model\Security\TokensDataFactory $tokensDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade $customerUserLoginTypeFacade
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeDataFactory $customerUserLoginTypeDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Security\LoginResultDataFactory $loginResultDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade $productListFacade
     * @param \Shopsys\FrontendApiBundle\Model\Cart\MergeCartFacade $mergeCartFacade
     */
    public function __construct(
        protected readonly CustomerUserRepository $customerUserRepository,
        protected readonly AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
        protected readonly RequestStack $requestStack,
        protected readonly TokenAuthenticator $tokenAuthenticator,
        protected readonly AdministratorFacade $administratorFacade,
        protected readonly TokenFacade $tokenFacade,
        protected readonly TokensDataFactory $tokensDataFactory,
        protected readonly CustomerUserLoginTypeFacade $customerUserLoginTypeFacade,
        protected readonly CustomerUserLoginTypeDataFactory $customerUserLoginTypeDataFactory,
        protected readonly LoginResultDataFactory $loginResultDataFactory,
        protected readonly ProductListFacade $productListFacade,
        protected readonly MergeCartFacade $mergeCartFacade,
    ) {
    }

    /**
     * @param int $customerUserId
     * @return \Shopsys\FrontendApiBundle\Model\Security\TokensData
     */
    public function loginAdministratorAsCustomerUserAndGetAccessAndRefreshToken(int $customerUserId): TokensData
    {
        if (!$this->administratorFrontSecurityFacade->isAdministratorLogged()) {
            throw new LoginAsRememberedUserException('Access denied');
        }

        $deviceId = Uuid::uuid4()->toString();
        $user = $this->customerUserRepository->getCustomerUserById($customerUserId);
        $administrator = $this->administratorFrontSecurityFacade->getCurrentAdministrator();

        $this->customerUserLoginTypeFacade->updateCustomerUserLoginTypes(
            $this->customerUserLoginTypeDataFactory->create($user, LoginTypeEnum::ADMIN),
        );

        return $this->tokensDataFactory->create(
            $this->tokenFacade->createAccessTokenAsString($user, $deviceId, $administrator),
            $this->tokenFacade->createRefreshTokenAsString($user, $deviceId, $administrator),
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    public function getCurrentAdministratorLoggedAsCustomer(): ?Administrator
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return null;
        }
        $tokenString = $this->tokenAuthenticator->getCredentials($request);

        if ($tokenString === null) {
            return null;
        }
        $unencryptedToken = $this->tokenFacade->getTokenByString($tokenString);
        $claims = $unencryptedToken->claims();
        $administratorUuid = $claims->get(FrontendApiUser::CLAIM_ADMINISTRATOR_UUID);

        if ($administratorUuid === null) {
            return null;
        }

        return $this->administratorFacade->findByUuid($administratorUuid);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrontendApiBundle\Model\Security\TokensData
     */
    public function loginAndReturnAccessAndRefreshToken(CustomerUser $customerUser): TokensData
    {
        $deviceId = Uuid::uuid4()->toString();

        return $this->tokensDataFactory->create(
            $this->tokenFacade->createAccessTokenAsString($customerUser, $deviceId),
            $this->tokenFacade->createRefreshTokenAsString($customerUser, $deviceId),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $loginType
     * @param bool $isRegistration
     * @param array $productListsUuids
     * @param bool $shouldOverwriteCustomerUserCart
     * @param string|null $cartUuid
     * @param string|null $externalId
     * @return \Shopsys\FrontendApiBundle\Model\Security\LoginResultData
     */
    public function runLoginSteps(
        CustomerUser $customerUser,
        string $loginType,
        bool $isRegistration,
        array $productListsUuids,
        bool $shouldOverwriteCustomerUserCart,
        ?string $cartUuid,
        ?string $externalId,
    ): LoginResultData {
        if ($cartUuid !== null) {
            if ($shouldOverwriteCustomerUserCart) {
                $this->mergeCartFacade->overwriteCustomerCartWithCartByUuid($cartUuid, $customerUser);
            } else {
                $this->mergeCartFacade->mergeCartByUuidToCustomerCart($cartUuid, $customerUser);
            }
        }

        if (count($productListsUuids) > 0) {
            $this->productListFacade->mergeProductListsToCustomerUser($productListsUuids, $customerUser);
        }

        $this->customerUserLoginTypeFacade->updateCustomerUserLoginTypes(
            $this->customerUserLoginTypeDataFactory->create($customerUser, $loginType, $externalId),
        );

        return $this->loginResultDataFactory->create(
            $this->loginAndReturnAccessAndRefreshToken($customerUser),
            $this->mergeCartFacade->shouldShowCartMergeInfo(),
            $isRegistration,
        );
    }
}
