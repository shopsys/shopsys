<?php

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslations;

class FlagDataFixture extends AbstractReferenceFixture
{
    public const FLAG_NEW_PRODUCT = 'flag_new_product';
    public const FLAG_TOP_PRODUCT = 'flag_top_product';
    public const FLAG_ACTION_PRODUCT = 'flag_action';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade
     */
    protected $flagFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactoryInterface
     */
    protected $flagDataFactory;

    /**
     * @var \Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslations
     */
    protected $dataFixturesTranslations;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactoryInterface $flagDataFactory
     * @param \Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslations $dataFixturesTranslations
     */
    public function __construct(
        FlagFacade $flagFacade,
        FlagDataFactoryInterface $flagDataFactory,
        DataFixturesTranslations $dataFixturesTranslations
    ) {
        $this->flagFacade = $flagFacade;
        $this->flagDataFactory = $flagDataFactory;
        $this->dataFixturesTranslations = $dataFixturesTranslations;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $flagData = $this->flagDataFactory->create();

        $flagData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_FLAG,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::FLAG_NEW_PRODUCT
        );
        $flagData->rgbColor = '#efd6ff';
        $flagData->visible = true;
        $this->createFlag($flagData, self::FLAG_NEW_PRODUCT);

        $flagData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_FLAG,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::FLAG_TOP_PRODUCT
        );
        $flagData->rgbColor = '#d6fffa';
        $flagData->visible = true;
        $this->createFlag($flagData, self::FLAG_TOP_PRODUCT);

        $flagData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_FLAG,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::FLAG_ACTION_PRODUCT
        );
        $flagData->rgbColor = '#f9ffd6';
        $flagData->visible = true;
        $this->createFlag($flagData, self::FLAG_ACTION_PRODUCT);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData $flagData
     * @param string|null $referenceName
     */
    protected function createFlag(FlagData $flagData, $referenceName = null)
    {
        $flag = $this->flagFacade->create($flagData);
        if ($referenceName !== null) {
            $this->addReference($referenceName, $flag);
        }
    }
}
