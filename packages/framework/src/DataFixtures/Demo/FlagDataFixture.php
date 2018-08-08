<?php

namespace Shopsys\FrameworkBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;

class FlagDataFixture extends AbstractReferenceFixture
{
    const FLAG_NEW_PRODUCT = 'flag_new_product';
    const FLAG_TOP_PRODUCT = 'flag_top_product';
    const FLAG_ACTION_PRODUCT = 'flag_action';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade
     */
    private $flagFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactoryInterface
     */
    private $flagDataFactory;

    public function __construct(
        FlagFacade $flagFacade,
        FlagDataFactoryInterface $flagDataFactory
    ) {
        $this->flagFacade = $flagFacade;
        $this->flagDataFactory = $flagDataFactory;
    }

    public function load(ObjectManager $manager): void
    {
        $flagData = $this->flagDataFactory->create();

        $flagData->name = ['cs' => 'Novinka', 'en' => 'New'];
        $flagData->rgbColor = '#efd6ff';
        $flagData->visible = true;
        $this->createFlag($flagData, self::FLAG_NEW_PRODUCT);

        $flagData->name = ['cs' => 'Nejprodávanější', 'en' => 'TOP'];
        $flagData->rgbColor = '#d6fffa';
        $flagData->visible = true;
        $this->createFlag($flagData, self::FLAG_TOP_PRODUCT);

        $flagData->name = ['cs' => 'Akce', 'en' => 'Action'];
        $flagData->rgbColor = '#f9ffd6';
        $flagData->visible = true;
        $this->createFlag($flagData, self::FLAG_ACTION_PRODUCT);
    }

    private function createFlag(FlagData $flagData, ?string $referenceName = null): void
    {
        $flag = $this->flagFacade->create($flagData);
        if ($referenceName !== null) {
            $this->addReference($referenceName, $flag);
        }
    }
}
