<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Flag\FlagDataFactory;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;

class FlagDataFixture extends AbstractReferenceFixture
{
    public const FLAG_PRODUCT_SALE = 'product_sale';
    public const FLAG_PRODUCT_ACTION = 'product_action';
    public const FLAG_PRODUCT_NEW = 'product_new';
    public const FLAG_PRODUCT_MADEIN_CZ = 'product_madein_cz';
    public const FLAG_PRODUCT_MADEIN_SK = 'product_madein_sk';
    public const FLAG_PRODUCT_MADEIN_DE = 'product_madein_de';

    /**
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \App\Model\Product\Flag\FlagDataFactory $flagDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly FlagFacade $flagFacade,
        private readonly FlagDataFactory $flagDataFactory,
        private readonly Domain $domain,
    ) {
    }

    /**
     * Flags are created in database migration.
     *
     * @see \App\Migrations\Version20200221155940
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->createFlag(1, self::FLAG_PRODUCT_SALE);
        $this->createFlag(2, self::FLAG_PRODUCT_ACTION);
        $this->createFlag(3, self::FLAG_PRODUCT_NEW);
        $this->createFlag(4, self::FLAG_PRODUCT_MADEIN_CZ);
        $this->createFlag(5, self::FLAG_PRODUCT_MADEIN_SK);
        $this->createFlag(6, self::FLAG_PRODUCT_MADEIN_DE);
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
        if ($referenceName !== self::FLAG_PRODUCT_ACTION) {
            return;
        }

        $flagData = $this->flagDataFactory->createFromFlag($flag);
        foreach ($this->domain->getAllLocales() as $locale) {
            $flagData->name[$locale] = t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->flagFacade->edit($flagId, $flagData);
    }
}
