<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\FlagController as BaseFlagController;

/**
 * @property \App\Model\Product\Flag\FlagFacade $flagFacade
 * @method __construct(\App\Model\Product\Flag\FlagFacade $flagFacade, \Shopsys\FrameworkBundle\Model\Product\Flag\FlagInlineEdit $flagInlineEdit)
 */
class FlagController extends BaseFlagController
{
    /**
     * @Route("/product/flag/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @throws \RuntimeException
     */
    public function deleteAction($id)
    {
        throw new \RuntimeException('deleteAction() should never be called.');
    }
}
