<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\PromoCode\PromoCodeFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeGridFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PromoCodeController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactoryInterface $promoCodeDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeGridFactory $promoCodeGridFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        protected readonly PromoCodeFacade $promoCodeFacade,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly PromoCodeDataFactoryInterface $promoCodeDataFactory,
        protected readonly PromoCodeGridFactory $promoCodeGridFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    /**
     * @Route("/promo-code/list")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request): Response
    {
        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator */
        $administrator = $this->getUser();

        $grid = $this->promoCodeGridFactory->create(true);

        $grid->enablePaging();

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $this->render('@ShopsysFramework/Admin/Content/PromoCode/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/promo-code/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     */
    public function deleteAction($id)
    {
        try {
            $code = $this->promoCodeFacade->getById($id)->getCode();

            $this->promoCodeFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Promo code <strong>{{ code }}</strong> deleted.'),
                [
                    'code' => $code,
                ],
            );
        } catch (PromoCodeNotFoundException $ex) {
            $this->addErrorFlash(t('Selected promo code doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_promocode_list');
    }

    /**
     * @Route("/promo-code/new")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function newAction(Request $request)
    {
        $promoCodeData = $this->promoCodeDataFactory->create();

        $form = $this->createForm(PromoCodeFormType::class, $promoCodeData, [
            'promo_code' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $promoCode = $this->promoCodeFacade->create($promoCodeData);

            $this->addSuccessFlashTwig(
                t('Promo code <strong><a href="{{ url }}">{{ code }}</a></strong> created'),
                [
                    'code' => $promoCode->getCode(),
                    'url' => $this->generateUrl('admin_promocode_edit', ['id' => $promoCode->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_promocode_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/PromoCode/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/promo-code/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     */
    public function editAction(Request $request, $id)
    {
        $promoCode = $this->promoCodeFacade->getById($id);
        $promoCodeData = $this->promoCodeDataFactory->createFromPromoCode($promoCode);

        $form = $this->createForm(PromoCodeFormType::class, $promoCodeData, [
            'promo_code' => $promoCode,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->promoCodeFacade->edit($id, $promoCodeData);

            $this->addSuccessFlashTwig(
                t('Promo code <strong><a href="{{ url }}">{{ code }}</a></strong> was modified'),
                [
                    'code' => $promoCode->getCode(),
                    'url' => $this->generateUrl('admin_promocode_edit', ['id' => $promoCode->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_promocode_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing promo code - %code%', ['%code%' => $promoCode->getCode()]),
        );

        return $this->render('@ShopsysFramework/Admin/Content/PromoCode/edit.html.twig', [
            'form' => $form->createView(),
            'promoCode' => $promoCode,
        ]);
    }
}
