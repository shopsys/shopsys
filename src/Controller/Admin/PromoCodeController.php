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
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request){
        return parent::newAction($request);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id){
        return parent::editAction($request, $id);
    }
}
