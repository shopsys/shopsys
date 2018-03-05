<?php

namespace Shopsys\FrameworkBundle\DataFixtures\Base;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;

class EnabledModuleDataFixture extends AbstractReferenceFixture
{
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $moduleFacade = $this->get(ModuleFacade::class);
        /* @var $moduleFacade \Shopsys\FrameworkBundle\Model\Module\ModuleFacade */
        $moduleFacade->setEnabled(ModuleList::PRODUCT_FILTER_COUNTS, true);
        $moduleFacade->setEnabled(ModuleList::PRODUCT_STOCK_CALCULATIONS, true);
    }
}
