<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Controller;

use Shopsys\ConvertimBundle\Model\Convertim\ConvertimConfigProvider;
use Shopsys\ConvertimBundle\Model\Convertim\ConvertimLogger;
use Shopsys\ConvertimBundle\Model\Convertim\Exception\ConvertimException;
use Shopsys\ConvertimBundle\Model\Login\LoginDetailFactory;
use Shopsys\ConvertimBundle\Model\OAuth\OAuthFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\LoginTypeEnum;
use Shopsys\FrontendApiBundle\Model\Security\LoginAsUserFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

class LoginController extends AbstractConvertimController
{
    /**
     * @param \Shopsys\ConvertimBundle\Model\Login\LoginDetailFactory $loginDetailsFactory
     * @param \Shopsys\ConvertimBundle\Model\Convertim\ConvertimLogger $convertimLogger
     * @param \Shopsys\ConvertimBundle\Model\OAuth\OAuthFactory $oAuthFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrontendApiBundle\Model\Security\LoginAsUserFacade $loginAsUserFacade
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     * @param \Shopsys\ConvertimBundle\Model\Convertim\ConvertimConfigProvider $convertimConfigProvider
     */
    public function __construct(
        protected readonly LoginDetailFactory $loginDetailsFactory,
        protected readonly ConvertimLogger $convertimLogger,
        protected readonly OAuthFactory $oAuthFactory,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly LoginAsUserFacade $loginAsUserFacade,
        protected readonly UrlGeneratorInterface $urlGenerator,
        ConvertimConfigProvider $convertimConfigProvider,
    ) {
        parent::__construct($convertimConfigProvider);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/get-login-details/{email}')]
    public function getLoginDetail(Request $request): Response
    {
        if (!$this->isConvertimEnabled()) {
            return $this->convertimNotEnabledResponse();
        }

        if ($this->isProtectedRequest($request) === false) {
            return $this->invalidAuthorizationResponse();
        }

        try {
            return new JsonResponse($this->loginDetailsFactory->createLoginDetail($request->attributes->get('email')));
        } catch (ConvertimException $e) {
            return $this->convertimLogger->logConvertimException($e);
        } catch (Throwable $e) {
            return $this->convertimLogger->logGenericException($e);
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/login')]
    public function login(Request $request): Response
    {
        if (!$this->isConvertimEnabled()) {
            return $this->convertimNotEnabledResponse();
        }

        try {
            /** @var \Lcobucci\JWT\Token\Plain $token */
            $token = $this->oAuthFactory->createConvertimOauth()->getUserLoginToken($request->get('authCode'));
            $customerUserUuid = $token->claims()->get('data')['eshopId'];
            $customerUser = $this->customerUserFacade->getByUuid($customerUserUuid);

            $loginResultData = $this->loginAsUserFacade->runLoginSteps(
                $customerUser,
                LoginTypeEnum::WEB,
                false,
                [],
                false,
                null,
                null,
            );

            return $this->render('@ShopsysFrontendApi/Admin/Content/Login/loginAsCustomerUser.html.twig', [
                'tokens' => $loginResultData->tokens,
                'url' => $this->urlGenerator->generate('front_cart'),
            ]);
        } catch (ConvertimException $e) {
            return $this->convertimLogger->logConvertimException($e);
        } catch (Throwable $e) {
            return $this->convertimLogger->logGenericException($e);
        }
    }
}
