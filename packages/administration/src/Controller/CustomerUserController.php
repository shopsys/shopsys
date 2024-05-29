<?php

declare(strict_types=1);

namespace Shopsys\Administration\Controller;

use App\Model\Security\LoginAsUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginAsRememberedUserException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CustomerUserController extends CRUDController
{
    /**
     * @param \App\Model\Security\LoginAsUserFacade $loginAsUserFacade
     */
    public function __construct(
        protected readonly LoginAsUserFacade $loginAsUserFacade,
    ) {
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAsCustomerUserAction(int $id): Response
    {
        try {
            return $this->render('Admin/Content/Login/loginAsCustomerUser.html.twig', [
                'tokens' => $this->loginAsUserFacade->loginAsCustomerUserAndGetAccessAndRefreshToken($id, $this->getUser()),
                'url' => $this->generateUrl('front_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
        } catch (CustomerUserNotFoundException $e) {
            $this->addFlash(
                'sonata_flash_error',
                t('Customer not found.', [], 'SonataAdminBundle'),
            );

            return $this->redirectToRoute('admin_customer_list');
        } catch (LoginAsRememberedUserException $e) {
            throw $this->createAccessDeniedException('Access denied', $e);
        }
    }
}
