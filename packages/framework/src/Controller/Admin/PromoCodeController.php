<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeInlineEdit;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;

class PromoCodeController extends AdminBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade
     */
    private $promoCodeFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeInlineEdit
     */
    private $promoCodeInlineEdit;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade
     */
    private $administratorGridFacade;

    public function __construct(
        PromoCodeFacade $promoCodeFacade,
        PromoCodeInlineEdit $promoCodeInlineEdit,
        AdministratorGridFacade $administratorGridFacade
    ) {
        $this->promoCodeFacade = $promoCodeFacade;
        $this->promoCodeInlineEdit = $promoCodeInlineEdit;
        $this->administratorGridFacade = $administratorGridFacade;
    }

    public function listAction()
    {
        $administrator = $this->getUser();
        /* @var $administrator \Shopsys\FrameworkBundle\Model\Administrator\Administrator */

        $grid = $this->promoCodeInlineEdit->getGrid();

        $grid->enablePaging();

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $this->render('@ShopsysFramework/Admin/Content/PromoCode/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    public function deleteAction(int $id)
    {
        try {
            $code = $this->promoCodeFacade->getById($id)->getCode();

            $this->promoCodeFacade->deleteById($id);

            $this->getFlashMessageSender()->addSuccessFlashTwig(
                t('Promo code <strong>{{ code }}</strong> deleted.'),
                [
                    'code' => $code,
                ]
            );
        } catch (\Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeNotFoundException $ex) {
            $this->getFlashMessageSender()->addErrorFlash(t('Selected promo code doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_promocode_list');
    }
}
