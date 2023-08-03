<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Script\Script;
use Shopsys\FrameworkBundle\Model\Script\ScriptDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Script\ScriptFacade;

class ScriptDataFixture extends AbstractReferenceFixture
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Script\ScriptFacade $scriptFacade
     * @param \Shopsys\FrameworkBundle\Model\Script\ScriptDataFactory $scriptDataFactory
     */
    public function __construct(
        private readonly ScriptFacade $scriptFacade,
        private readonly ScriptDataFactoryInterface $scriptDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $scriptData = $this->scriptDataFactory->create();
        $scriptData->name = 'Demo script 1';
        $scriptData->code = '<!-- demo script -->';
        $scriptData->placement = Script::PLACEMENT_ALL_PAGES;

        $this->scriptFacade->create($scriptData);

        $scriptData->name = 'Demo script 2';
        $scriptData->code = '<!-- script to display on order sent page -->';
        $scriptData->placement = Script::PLACEMENT_ORDER_SENT_PAGE;

        $this->scriptFacade->create($scriptData);
    }
}
