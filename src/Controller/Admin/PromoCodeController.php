<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Controller\Admin\PromoCodeController as BasePromoCodeController;
use Shopsys\FrameworkBundle\Form\Admin\PromoCode\PromoCodeFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeGridFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeInlineEdit;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Symfony\Component\HttpFoundation\Request;

/**
 * @property \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
 * @property \App\Model\Order\PromoCode\PromoCodeDataFactory|null $promoCodeDataFactory
 * @property \App\Model\Order\PromoCode\Grid\PromoCodeGridFactory|null $promoCodeGridFactory
 * @method setPromoCodeDataFactory(\App\Model\Order\PromoCode\PromoCodeDataFactory $promoCodeDataFactory)
 * @method setPromoCodeGridFactory(\App\Model\Order\PromoCode\Grid\PromoCodeGridFactory $promoCodeGridFactory)
 */
class PromoCodeController extends BasePromoCodeController
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    private $adminDomainTabsFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeInlineEdit $promoCodeInlineEdit
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \App\Model\Order\PromoCode\PromoCodeDataFactory|null $promoCodeDataFactory
     * @param \App\Model\Order\PromoCode\Grid\PromoCodeGridFactory|null $promoCodeGridFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider|null $breadcrumbOverrider
     * @param bool $useInlineEditation
     */
    public function __construct(
        Domain $domain,
        PromoCodeFacade $promoCodeFacade,
        PromoCodeInlineEdit $promoCodeInlineEdit,
        AdministratorGridFacade $administratorGridFacade,
        AdminDomainTabsFacade $adminDomainTabsFacade,
        ?PromoCodeDataFactoryInterface $promoCodeDataFactory = null,
        ?PromoCodeGridFactory $promoCodeGridFactory = null,
        ?BreadcrumbOverrider $breadcrumbOverrider = null,
        bool $useInlineEditation = false
    ) {
        parent::__construct($promoCodeFacade, $promoCodeInlineEdit, $administratorGridFacade, $promoCodeDataFactory, $promoCodeGridFactory, $breadcrumbOverrider, $useInlineEditation);
        $this->domain = $domain;
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function newAction(Request $request)
    {
        $promoCodeData = $this->promoCodeDataFactory->create();

        $form = $this->createForm(PromoCodeFormType::class, $promoCodeData, [
            'promo_code' => null,
            'isInlineEdit' => false,
            'domain_id' => $this->domain->getId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $promoCode = $this->promoCodeFacade->create($form->getData());

            $this->getFlashMessageSender()->addSuccessFlashTwig(
                t('Promo code <strong><a href="{{ url }}">{{ code }}</a></strong> created'),
                [
                    'code' => $promoCode->getCode(),
                    'url' => $this->generateUrl('admin_promocode_edit', ['id' => $promoCode->getId()]),
                ]
            );
            return $this->redirectToRoute('admin_promocode_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->getFlashMessageSender()->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/PromoCode/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function listAction()
    {
        $administrator = $this->getUser();
        /* @var $administrator \App\Model\Administrator\Administrator */

        $selectedDomainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        if ($this->useInlineEditation === true) {
            $grid = $this->promoCodeInlineEdit->getGrid();

            $grid->enablePaging();
        } else {
            $grid = $this->promoCodeGridFactory->create(true, $selectedDomainId);

            $grid->enablePaging();
        }

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $this->render('Admin/Content/PromoCode/list.html.twig', [
            'gridView' => $grid->createView(),
            'useInlineEditation' => $this->useInlineEditation,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $id
     */
    public function editAction(Request $request, $id)
    {
        $promoCode = $this->promoCodeFacade->getById($id);
        $promoCodeData = $this->promoCodeDataFactory->createFromPromoCode($promoCode);

        $form = $this->createForm(PromoCodeFormType::class, $promoCodeData, [
            'promo_code' => $promoCode,
            'isInlineEdit' => false,
            'domain_id' => $promoCode->getDomainId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->promoCodeFacade->edit($id, $promoCodeData);

            $this->getFlashMessageSender()->addSuccessFlashTwig(
                t('Promo code <strong><a href="{{ url }}">{{ code }}</a></strong> was modified'),
                [
                    'code' => $promoCode->getCode(),
                    'url' => $this->generateUrl('admin_promocode_edit', ['id' => $promoCode->getId()]),
                ]
            );
            return $this->redirectToRoute('admin_promocode_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->getFlashMessageSender()->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing promo code - %code%', ['%code%' => $promoCode->getCode()]));

        return $this->render('@ShopsysFramework/Admin/Content/PromoCode/edit.html.twig', [
            'form' => $form->createView(),
            'promoCode' => $promoCode,
        ]);
    }
}
