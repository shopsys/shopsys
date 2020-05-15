<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;

class FlagDataFixture extends AbstractReferenceFixture
{
    public const FLAG_PRODUCT_SALE = 'product_sale';
    public const FLAG_PRODUCT_ACTION = 'product_action';
    public const FLAG_PRODUCT_SCONTO = 'product_sconto';
    public const FLAG_PRODUCT_NEW = 'product_new';
    public const FLAG_PRODUCT_MADEIN_CZ = 'product_madein_cz';
    public const FLAG_PRODUCT_MADEIN_SK = 'product_madein_sk';
    public const FLAG_PRODUCT_MADEIN_DE = 'product_madein_de';

    /**
     * @var \App\Model\Product\Flag\FlagFacade
     */
    private $flagFacade;

    /**
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     */
    public function __construct(
        FlagFacade $flagFacade
    ) {
        $this->flagFacade = $flagFacade;
    }

    /**
     * Flags are created in database migration.
     * @see \Shopsys\FrameworkBundle\Migrations\Version20200221155940
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->createFlag(1, self::FLAG_PRODUCT_SALE);
        $this->createFlag(2, self::FLAG_PRODUCT_ACTION);
        $this->createFlag(3, self::FLAG_PRODUCT_SCONTO);
        $this->createFlag(4, self::FLAG_PRODUCT_NEW);
        $this->createFlag(5, self::FLAG_PRODUCT_MADEIN_CZ);
        $this->createFlag(6, self::FLAG_PRODUCT_MADEIN_SK);
        $this->createFlag(7, self::FLAG_PRODUCT_MADEIN_DE);
    }

    /**
     * @param int $flagId
     * @param string|null $referenceName
     */
    private function createFlag(int $flagId, ?string $referenceName = null): void
    {
        $flag = $this->flagFacade->getById($flagId);
        if ($referenceName !== null) {
            $this->addReference($referenceName, $flag);
        }
    }
}
