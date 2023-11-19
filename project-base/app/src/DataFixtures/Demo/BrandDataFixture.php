<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
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

    /**
     * @var string[]
     */
    private array $uuidPool = [
        'd660b994-5765-42ec-a64a-df9660e6af21',
        '1287c26a-5da0-4e61-b17d-2a1374560b9a',
        '980d99c3-286f-4662-9b88-faaa9165cc15',
        '5c11567e-7326-4f79-bcef-562da0094312',
        '230bcb59-acfa-4305-84a2-d8af65394044',
        'e373c9fb-5200-4b28-9c6b-ec231ee08810',
        '04dd0a5e-72d7-4f5f-beb8-12ff413c9d1e',
        '8d1add90-9eb4-4757-8193-cd9670524eec',
        '374cbf2a-12d3-43c7-9a4f-08abfb6261d9',
        '97538164-b8e0-426e-ba82-6786b55ed4b5',
        '8901c304-b513-4f0f-af70-0447ab9fa707',
        'ed124d8d-2689-4cdb-b701-6f2db8659165',
        '632dd0a1-c4eb-4a71-ba6e-3a6dbedbf0be',
        'c1934e06-5957-4cab-be42-7036a89c7dae',
        '0ce9e493-f4f7-4920-a7de-0baf466481b7',
        '7ed15961-ea05-4730-8513-22f889d4d936',
        '6124d29c-604e-4ae0-8e6c-f3cdb56b6db1',
        '5c68edb5-d555-45fd-ae1d-56dbb802fb1e',
        '0d021b11-99f0-46d1-a767-14ad62cfdc17',
        '8f189fef-3e28-44c7-9c7a-61083e6888ab',
        '59e018a7-5683-4d5c-8d7d-6b2fec015c71',
        '8a8dfdac-223b-41b6-a35d-a1577d376255',
        'cec82d51-0671-4368-9ea8-a3d3d0ad1d22',
        '738ead90-3108-433d-ad6e-1ea23f68a13d',
    ];

    /**
     * @param \App\Model\Product\Brand\BrandFacade $brandFacade
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
    public function load(ObjectManager $manager): void
    {
        $brandData = $this->brandDataFactory->create();

        foreach ($this->getBrandNamesIndexedByBrandConstants() as $brandConstant => $brandName) {
            $brandData->name = $brandName;
            $brandData->uuid = array_pop($this->uuidPool);

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
    private function getBrandNamesIndexedByBrandConstants(): array
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
