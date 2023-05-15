<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;

class FlagDataFixture extends AbstractReferenceFixture
{
    public const FLAG_NEW_PRODUCT = 'flag_new_product';
    public const FLAG_TOP_PRODUCT = 'flag_top_product';
    public const FLAG_ACTION_PRODUCT = 'flag_action';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactoryInterface $flagDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly FlagFacade $flagFacade,
        private readonly FlagDataFactoryInterface $flagDataFactory,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $flagData = $this->flagDataFactory->create();

        foreach ($this->domain->getAllLocales() as $locale) {
            $flagData->name[$locale] = t('New [noun]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $flagData->rgbColor = '#efd6ff';
        $flagData->visible = true;
        $this->createFlag($flagData, self::FLAG_NEW_PRODUCT);

        foreach ($this->domain->getAllLocales() as $locale) {
            $flagData->name[$locale] = t('TOP', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $flagData->rgbColor = '#d6fffa';
        $flagData->visible = true;
        $this->createFlag($flagData, self::FLAG_TOP_PRODUCT);

        foreach ($this->domain->getAllLocales() as $locale) {
            $flagData->name[$locale] = t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $flagData->rgbColor = '#f9ffd6';
        $flagData->visible = true;
        $this->createFlag($flagData, self::FLAG_ACTION_PRODUCT);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData $flagData
     * @param string|null $referenceName
     */
    private function createFlag(FlagData $flagData, $referenceName = null)
    {
        $flag = $this->flagFacade->create($flagData);
        if ($referenceName !== null) {
            $this->addReference($referenceName, $flag);
        }
    }
}
