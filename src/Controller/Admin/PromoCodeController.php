<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Controller\Admin\PromoCodeController as BasePromoCodeController;

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
}
