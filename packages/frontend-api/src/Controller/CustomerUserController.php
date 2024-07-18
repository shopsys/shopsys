<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Controller;

use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginAsRememberedUserException;
use Shopsys\FrontendApiBundle\Model\Security\LoginAsUserFacade;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CustomerUserController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Security\LoginAsUserFacade $loginAsUserFacade
     */
    public function __construct(
        protected readonly LoginAsUserFacade $loginAsUserFacade,
    ) {
    }

    /**
     * @param int $customerUserId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAsCustomerUserAction(int $customerUserId): Response
    {
        try {
            return $this->render('@ShopsysFrontendApi/Admin/Content/Login/loginAsCustomerUser.html.twig', [
                'tokens' => $this->loginAsUserFacade->loginAdministratorAsCustomerUserAndGetAccessAndRefreshToken($customerUserId),
                'url' => $this->generateUrl('front_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
        } catch (CustomerUserNotFoundException) {
            $this->addErrorFlash(t('Customer not found.'));

            return $this->redirectToRoute('admin_customer_list');
        } catch (LoginAsRememberedUserException $e) {
            throw $this->createAccessDeniedException('Access denied', $e);
        }
    }
}
