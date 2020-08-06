<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Controller\Admin\PromoCodeController as BasePromoCodeController;
use Shopsys\FrameworkBundle\Form\Admin\PromoCode\PromoCodeFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
 * @property \App\Model\Order\PromoCode\PromoCodeDataFactory $promoCodeDataFactory
 * @property \App\Model\Order\PromoCode\Grid\PromoCodeGridFactory $promoCodeGridFactory
 * @method __construct(\App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade, \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade, \App\Model\Order\PromoCode\PromoCodeDataFactory $promoCodeDataFactory, \App\Model\Order\PromoCode\Grid\PromoCodeGridFactory $promoCodeGridFactory, \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider)
 */
class PromoCodeController extends BasePromoCodeController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $administrator = $this->getUser();
        /* @var $administrator \App\Model\Administrator\Administrator */

        $grid = $this->promoCodeGridFactory->create();
        $grid->enablePaging();

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $this->render('Admin/Content/PromoCode/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/promo-code/new-mass-generate")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newMassGenerateAction(Request $request): Response
    {
        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->massGenerate = true;

        $form = $this->createForm(PromoCodeFormType::class, $promoCodeData, [
            'promo_code' => null,
            'mass_generate' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->promoCodeFacade->massCreate($promoCodeData);

            $this->addSuccessFlashTwig(
                t('Bylo vytvořeno <strong>{{ quantity }}</strong> slevových kupónů'),
                [
                    'quantity' => $promoCodeData->quantity,
                ]
            );
            return $this->redirectToRoute('admin_promocode_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('Admin/Content/PromoCode/newMassGenerate.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/promo-code/new")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function newAction(Request $request)
    {
        $fillFromPromoCodeId = $request->query->get('fillFromPromoCodeId');

        if ($fillFromPromoCodeId === null) {
            $promoCodeData = $this->promoCodeDataFactory->create();
        } else {
            $promoCode = $this->promoCodeFacade->getById($fillFromPromoCodeId);
            $promoCodeData = $this->promoCodeDataFactory->createFromPromoCode($promoCode);
            $promoCodeData->code = null;
        }

        $form = $this->createForm(PromoCodeFormType::class, $promoCodeData, [
            'promo_code' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $promoCode = $this->promoCodeFacade->create($form->getData());

            $this->addSuccessFlashTwig(
                t('Promo code <strong><a href="{{ url }}">{{ code }}</a></strong> created'),
                [
                    'code' => $promoCode->getCode(),
                    'url' => $this->generateUrl('admin_promocode_edit', ['id' => $promoCode->getId()]),
                ]
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
}
