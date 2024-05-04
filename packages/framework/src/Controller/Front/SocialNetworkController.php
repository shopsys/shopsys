<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Front;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\RegistrationDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\RegistrationFacade;
use Shopsys\FrameworkBundle\Model\Security\Authenticator;
use Shopsys\FrameworkBundle\Model\SocialNetwork\Exception\SocialNetworkLoginException;
use Shopsys\FrameworkBundle\Model\SocialNetwork\SocialNetworkConfigFactory;
use Shopsys\FrameworkBundle\Model\SocialNetwork\SocialNetworkFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SocialNetworkController extends AbstractController
{
    protected const string REFERER_URL = 'refererUrl';
    public const string CART_UUID = 'cartUuid';
    public const string SHOULD_OVERWRITE_CART = 'shouldOverwriteCart';

    protected const string PARAMETER_CART_UUID = 'cartUuid';
    protected const string PARAMETER_SHOULD_OVERWRITE_CUSTOMER_USER_CART = 'shouldOverwriteCustomerUserCart';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Security\Authenticator $authenticator
     * @param \Shopsys\FrameworkBundle\Model\SocialNetwork\SocialNetworkConfigFactory $socialNetworkConfigFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\RegistrationFacade $registrationFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\RegistrationDataFactory $registrationDataFactory
     * @param \Shopsys\FrameworkBundle\Model\SocialNetwork\SocialNetworkFacade $socialNetworkFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(
        protected readonly Authenticator $authenticator,
        protected readonly SocialNetworkConfigFactory $socialNetworkConfigFactory,
        protected readonly RegistrationFacade $registrationFacade,
        protected readonly RegistrationDataFactory $registrationDataFactory,
        protected readonly SocialNetworkFacade $socialNetworkFacade,
        protected readonly Domain $domain,
        protected readonly CartFacade $cartFacade,
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
            $redirectUrl = $this->generateUrl('front_social_network_login', ['type' => $type], UrlGeneratorInterface::ABSOLUTE_URL);
            $tokens = $this->socialNetworkFacade->login($type, $redirectUrl, $request->getSession());

            return $this->render('@ShopsysFramework/Admin/Content/Login/loginAsCustomerUser.html.twig', [
                'tokens' => $tokens,
                'url' => $this->getRefererUrl($request, $type, false),
            ]);
        } catch (SocialNetworkLoginException $exception) {
            return $this->redirect($this->getRefererUrl($request, $type, true));
        }
    }

    /**
     * We need to save some data because login to social networks does redirect and after that, we would lose them
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    protected function saveNecessaryDataBeforeRedirectToSocialNetwork(Request $request): void
    {
        $session = $request->getSession();

        if ($session->has(self::REFERER_URL) === false) {
            $session->set(self::REFERER_URL, $request->server->get('HTTP_REFERER'));
        }

        if ($request->query->has(self::PARAMETER_CART_UUID)) {
            $cartUuid = $request->query->get(self::PARAMETER_CART_UUID);
            $session->set(self::CART_UUID, $cartUuid);
        }

        if (!$request->query->has(self::PARAMETER_SHOULD_OVERWRITE_CUSTOMER_USER_CART)) {
            return;
        }

        $shouldOverwriteCustomerUserCart = $request->query->get(self::PARAMETER_SHOULD_OVERWRITE_CUSTOMER_USER_CART);
        $session->set(self::SHOULD_OVERWRITE_CART, $shouldOverwriteCustomerUserCart);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $type
     * @param bool $addExceptionMessage
     * @return string
     */
    protected function getRefererUrl(Request $request, string $type, bool $addExceptionMessage): string
    {
        $homepageUrl = $this->generateUrl('front_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $refererUrl = $request->getSession()->get(self::REFERER_URL);
        $refererUrl = $refererUrl ?? $homepageUrl;
        $request->getSession()->remove(self::REFERER_URL);
        $refererUrl = str_replace($this->domain->getUrl(), '', $refererUrl);
        $url = '/social-login?redirect=' . $refererUrl;

        if ($addExceptionMessage) {
            $url .= '&exceptionType=socialNetworkLoginException&socialNetwork=' . $type;
        }

        return $url;
    }
}
