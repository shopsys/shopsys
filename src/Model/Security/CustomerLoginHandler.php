<?php

declare(strict_types=1);


namespace App\Model\Security;

use App\Component\Domain\Domain;
use App\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter;
use Shopsys\FrameworkBundle\Model\Security\CustomerLoginHandler as BaseCustomerLoginHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

class CustomerLoginHandler extends BaseCustomerLoginHandler
{
    /**
     * @var \App\Model\Customer\User\CustomerUserFacade
     */
    private CustomerUserFacade $customerUserFacade;
    /**
     * @var \App\Component\Domain\Domain
     */
    private Domain $domain;

    public function __construct(
        CurrentDomainRouter $router,
        CustomerUserFacade $customerUserFacade,
        Domain $domain
    ) {
        parent::__construct($router);
        $this->customerUserFacade = $customerUserFacade;
        $this->domain = $domain;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            $responseData = [
                'success' => false,
            ];

            $email = $request->get('front_login_form')['email'] ?? null;
            $this->checkFirstLogin($email, $responseData);

            return new JsonResponse($responseData);
        } else {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

            return new RedirectResponse($this->router->generate('front_login'));
        }
    }

    /**
     * @param string|null $email
     * @param array $responseData
     */
    private function checkFirstLogin(?string $email, array &$responseData): void
    {
        if ($email === null) {
            return;
        }

        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($email, $this->domain->getId());
        if ($customerUser->getPassword() === '') {
            $responseData['errorMessage'] = t('Přihlašujete se poprvé na novém e-shopu, prosím nastavte si heslo pomocí funkce “Obnovení hesla”.');
        }
    }
}
