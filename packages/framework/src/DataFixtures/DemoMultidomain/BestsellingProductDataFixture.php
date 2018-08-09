<?php

namespace Shopsys\FrameworkBundle\DataFixtures\DemoMultidomain;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\DataFixtures\Demo\CategoryDataFixture as DemoCategoryDataFixture;
use Shopsys\FrameworkBundle\DataFixtures\Demo\ProductDataFixture as DemoProductDataFixture;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProductFacade;

class BestsellingProductDataFixture extends AbstractReferenceFixture
{
    /** @var \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProductFacade */
    private $manualBestsellingProductFacade;

    public function __construct(
        ManualBestsellingProductFacade $manualBestsellingProductFacade
    ) {
        $this->manualBestsellingProductFacade = $manualBestsellingProductFacade;
    }

    public function load(ObjectManager $manager)
    {
        $domainId = 2;
        $this->manualBestsellingProductFacade->edit(
            $this->getReference(DemoCategoryDataFixture::CATEGORY_PHOTO),
            $domainId,
            [$this->getReference(DemoProductDataFixture::PRODUCT_PREFIX . '7')]
        );
    }
}
