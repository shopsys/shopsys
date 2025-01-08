<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Flag\FlagDataFactory;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;

class FlagDataFixture extends AbstractReferenceFixture
{
    public const string FLAG_PRODUCT_SALE = 'product_sale';
    public const string FLAG_PRODUCT_ACTION = 'product_action';
    public const string FLAG_PRODUCT_NEW = 'product_new';
    public const string FLAG_PRODUCT_MADEIN_CZ = 'product_madein_cz';
    public const string FLAG_PRODUCT_MADEIN_SK = 'product_madein_sk';
    public const string FLAG_PRODUCT_MADEIN_DE = 'product_madein_de';

    /**
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \App\Model\Product\Flag\FlagDataFactory $flagDataFactory
     */
    public function __construct(
        private readonly FlagFacade $flagFacade,
        private readonly FlagDataFactory $flagDataFactory,
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
        $this->createFlag(1, self::FLAG_PRODUCT_SALE, '#f7d631');
        $this->createFlag(2, self::FLAG_PRODUCT_ACTION, '#ee1c25');
        $this->createFlag(3, self::FLAG_PRODUCT_NEW, '#2bba51');
        $this->createFlag(4, self::FLAG_PRODUCT_MADEIN_CZ, '#3110e8');
        $this->createFlag(5, self::FLAG_PRODUCT_MADEIN_SK, '#b01c1f');
        $this->createFlag(6, self::FLAG_PRODUCT_MADEIN_DE, '#000000');
    }

    /**
     * @param int $flagId
     * @param string|null $referenceName
     * @param string|null $rgbColor
     */
    private function createFlag(int $flagId, ?string $referenceName = null, ?string $rgbColor = null): void
    {
        $flag = $this->flagFacade->getById($flagId);

        if ($referenceName !== null) {
            $this->addReference($referenceName, $flag);
        }

        $flagData = $this->flagDataFactory->createFromFlag($flag);

        if ($rgbColor !== null) {
            $flagData->rgbColor = $rgbColor;
        }

        if ($referenceName === self::FLAG_PRODUCT_ACTION) {
            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $flagData->name[$locale] = t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            }
        }

        $this->flagFacade->edit($flagId, $flagData);
    }
}
