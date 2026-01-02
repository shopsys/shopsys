<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Controller;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrontendApiBundle\Model\SocialNetwork\Exception\SocialNetworkLoginException;
use Shopsys\FrontendApiBundle\Model\SocialNetwork\SocialNetworkFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SocialNetworkController extends AbstractController
{
    protected const string SESSION_REFERER_URL = 'socialLoginRefererUrl';
    public const string SESSION_CART_UUID = 'socialLoginCartUuid';
    public const string SESSION_PRODUCT_LIST_UUIDS = 'socialLoginProductListUuids';
    public const string SESSION_SHOULD_OVERWRITE_CART = 'socialLoginShouldOverwriteCart';

    protected const string PARAMETER_CART_UUID = 'cartUuid';

    protected const string PARAMETER_PRODUCT_LIST_UUIDS = 'productListUuids';
    protected const string PARAMETER_SHOULD_OVERWRITE_CUSTOMER_USER_CART = 'shouldOverwriteCustomerUserCart';

    /**
     * @param \Shopsys\FrontendApiBundle\Model\SocialNetwork\SocialNetworkFacade $socialNetworkFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        protected readonly SocialNetworkFacade $socialNetworkFacade,
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $type
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(Request $request, string $type): Response
    {
        $this->saveNecessaryDataBeforeRedirectToSocialNetwork($request);

        try {
            $domainRouter = $this->domainRouterFactory->getRouter($this->domain->getId());
            $redirectUrl = $domainRouter->generate('front_social_network_login', ['type' => $type], UrlGeneratorInterface::ABSOLUTE_URL);
            $loginResultData = $this->socialNetworkFacade->login($type, $redirectUrl, $request->getSession());

            return $this->render('@ShopsysFrontendApi/SocialLogin/loginAsCustomerUser.html.twig', [
                'tokens' => $loginResultData->tokens,
                'url' => $this->getRefererUrl(
                    $request,
                    $type,
                    false,
                    $loginResultData->showCartMergeInfo,
                    $loginResultData->isRegistration,
                ),
                'showCartMergeInfo' => $loginResultData->showCartMergeInfo ? 'true' : 'false',
                'domainId' => $this->domain->getId(),
            ]);
        } catch (SocialNetworkLoginException $exception) {
            return $this->redirect($this->getRefererUrl($request, $type, true));
        }
    }

    /**
     * We need to save some data because login to social networks does redirect, and after that, we would lose them
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    protected function saveNecessaryDataBeforeRedirectToSocialNetwork(Request $request): void
    {
        $session = $request->getSession();

        if (
            $session->has(self::SESSION_REFERER_URL) === false
            && $request->server->has('HTTP_REFERER')
        ) {
            $refererUrl = $request->server->get('HTTP_REFERER');

            $parsedUrl = parse_url($refererUrl);
            $isValidRelativePath = str_starts_with($refererUrl, '/') && !isset($parsedUrl['host']);
            $isCurrentDomainUrl = str_starts_with($refererUrl, $this->domain->getUrl());

            if ($isValidRelativePath || $isCurrentDomainUrl) {
                $session->set(self::SESSION_REFERER_URL, $refererUrl);
            }
        }

        if ($request->query->has(self::PARAMETER_CART_UUID)) {
            $cartUuid = $request->query->get(self::PARAMETER_CART_UUID);
            $session->set(self::SESSION_CART_UUID, $cartUuid);
        }

        if ($request->query->has(self::PARAMETER_PRODUCT_LIST_UUIDS)) {
            $productListsUuids = $request->query->get(self::PARAMETER_PRODUCT_LIST_UUIDS);
            $session->set(self::SESSION_PRODUCT_LIST_UUIDS, $productListsUuids);
        }

        if (!$request->query->has(self::PARAMETER_SHOULD_OVERWRITE_CUSTOMER_USER_CART)) {
            return;
        }

        $shouldOverwriteCustomerUserCart = $request->query->getBoolean(self::PARAMETER_SHOULD_OVERWRITE_CUSTOMER_USER_CART);
        $session->set(self::SESSION_SHOULD_OVERWRITE_CART, $shouldOverwriteCustomerUserCart);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $type
     * @param bool $addExceptionMessage
     * @param bool $showCartMergeInfo
     * @param bool $isRegistration
     * @return string
     */
    protected function getRefererUrl(
        Request $request,
        string $type,
        bool $addExceptionMessage,
        bool $showCartMergeInfo = false,
        bool $isRegistration = false,
    ): string {
        $domainRouter = $this->domainRouterFactory->getRouter($this->domain->getId());
        $parameters = [
            'redirect' => $this->getRedirectPathParameter($request, $domainRouter),
            'showCartMergeInfo' => $showCartMergeInfo ? 'true' : 'false',
            'isRegistration' => $isRegistration ? 'true' : 'false',
        ];

        if ($addExceptionMessage) {
            $parameters['exceptionType'] = 'socialNetworkLoginException';
            $parameters['socialNetwork'] = $type;
        }

        return $domainRouter->generate('front_social_network_login_page', $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouter $domainRouter
     * @return string
     */
    protected function getRedirectPathParameter(Request $request, DomainRouter $domainRouter): string
    {
        $refererUrl = $request->getSession()->get(self::SESSION_REFERER_URL);

        if ($refererUrl === null) {
            return $domainRouter->generate('front_homepage');
        }

        $request->getSession()->remove(self::SESSION_REFERER_URL);
        $redirectPath = str_replace($this->domain->getUrl(), '', $refererUrl);

        if ($redirectPath === '') {
            return '/';
        }

        return $redirectPath;
    }
}
