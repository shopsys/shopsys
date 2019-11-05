<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;

class BrandDataFixture extends AbstractReferenceFixture
{
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

    /** @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade */
    protected $brandFacade;

    /** @var \App\Model\Product\Brand\BrandDataFactory */
    protected $brandDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     * @param \App\Model\Product\Brand\BrandDataFactory $brandDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(BrandFacade $brandFacade, BrandDataFactoryInterface $brandDataFactory, Domain $domain)
    {
        $this->brandFacade = $brandFacade;
        $this->brandDataFactory = $brandDataFactory;
        $this->domain = $domain;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $brandData = $this->brandDataFactory->create();

        foreach ($this->getBrandNamesIndexedByBrandConstants() as $brandConstant => $brandName) {
            $brandData->name = $brandName;

            foreach ($this->domain->getAllLocales() as $locale) {
                $brandData->descriptions[$locale] = t('This is description of brand %brandName%.', ['%brandName%' => $brandData->name], 'dataFixtures', $locale);
            }

            $brand = $this->brandFacade->create($brandData);
            $this->addReference($brandConstant, $brand);
        }
    }

    /**
     * @return string[]
     */
    protected function getBrandNamesIndexedByBrandConstants()
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
