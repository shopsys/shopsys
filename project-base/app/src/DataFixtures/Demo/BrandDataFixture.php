<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;

class BrandDataFixture extends AbstractReferenceFixture
{
    private const string UUID_NAMESPACE = 'd660b994-5765-42ec-a64a-df9660e6af21';

    public const BRAND_APPLE = 'brand_apple';
    public const BRAND_CANON = 'brand_canon';
    public const BRAND_LG = 'brand_lg';
    public const BRAND_PHILIPS = 'brand_philips';
    public const BRAND_SENCOR = 'brand_sencor';
    public const BRAND_A4TECH = 'brand_a4tech';
    public const BRAND_BROTHER = 'brand_brother';
    public const BRAND_VERBATIM = 'brand_verbatim';
    public const BRAND_DLINK = 'brand_dlink';
    public const BRAND_DEFENDER = 'brand_defender';
    public const BRAND_DELONGHI = 'brand_delonghi';
    public const BRAND_GENIUS = 'brand_genius';
    public const BRAND_GIGABYTE = 'brand_gigabyte';
    public const BRAND_HP = 'brand_hp';
    public const BRAND_HTC = 'brand_htc';
    public const BRAND_JURA = 'brand_jura';
    public const BRAND_LOGITECH = 'brand_logitech';
    public const BRAND_MICROSOFT = 'brand_microsoft';
    public const BRAND_SAMSUNG = 'brand_samsung';
    public const BRAND_SONY = 'brand_sony';
    public const BRAND_ORAVA = 'brand_orava';
    public const BRAND_OLYMPUS = 'brand_olympus';
    public const BRAND_HYUNDAI = 'brand_hyundai';
    public const BRAND_NIKON = 'brand_nikon';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     * @param \App\Model\Product\Brand\BrandDataFactory $brandDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly BrandFacade $brandFacade,
        private readonly BrandDataFactoryInterface $brandDataFactory,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $brandData = $this->brandDataFactory->create();

        foreach ($this->getBrandNamesIndexedByBrandConstants() as $brandConstant => $brandName) {
            $brandData->name = $brandName;
            $brandData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, $brandName)->toString();

            foreach ($this->domain->getAllLocales() as $locale) {
                $brandData->descriptions[$locale] = t(
                    'This is description of brand %brandName%.',
                    ['%brandName%' => $brandData->name],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                );
            }

            foreach ($this->domain->getAll() as $domain) {
                $brandData->seoH1s[$domain->getId()] = t(
                    '%brandName% SEO H1',
                    ['%brandName%' => $brandData->name],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $domain->getLocale(),
                );
                $brandData->seoTitles[$domain->getId()] = t(
                    '%brandName% SEO Title',
                    ['%brandName%' => $brandData->name],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $domain->getLocale(),
                );
                $brandData->seoMetaDescriptions[$domain->getId()] = t(
                    'This is SEO meta description of brand %brandName%.',
                    ['%brandName%' => $brandData->name],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $domain->getLocale(),
                );
            }

            $brand = $this->brandFacade->create($brandData);
            $this->addReference($brandConstant, $brand);
        }
    }

    /**
     * @return string[]
     */
    private function getBrandNamesIndexedByBrandConstants()
    {
        return [
            self::BRAND_APPLE => 'Apple',
            self::BRAND_CANON => 'Canon',
            self::BRAND_LG => 'LG',
            self::BRAND_PHILIPS => 'Philips',
            self::BRAND_SENCOR => 'Sencor',
            self::BRAND_A4TECH => 'A4tech',
            self::BRAND_BROTHER => 'Brother',
            self::BRAND_VERBATIM => 'Verbatim',
            self::BRAND_DLINK => 'Dlink',
            self::BRAND_DEFENDER => 'Defender',
            self::BRAND_DELONGHI => 'DeLonghi',
            self::BRAND_GENIUS => 'Genius',
            self::BRAND_GIGABYTE => 'Gigabyte',
            self::BRAND_HP => 'HP',
            self::BRAND_HTC => 'HTC',
            self::BRAND_JURA => 'JURA',
            self::BRAND_LOGITECH => 'Logitech',
            self::BRAND_MICROSOFT => 'Microsoft',
            self::BRAND_SAMSUNG => 'Samsung',
            self::BRAND_SONY => 'SONY',
            self::BRAND_ORAVA => 'Orava',
            self::BRAND_OLYMPUS => 'Olympus',
            self::BRAND_HYUNDAI => 'Hyundai',
            self::BRAND_NIKON => 'Nikon',
        ];
    }
}
