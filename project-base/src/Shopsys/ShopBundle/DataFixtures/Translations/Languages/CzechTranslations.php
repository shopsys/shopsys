<?php

declare(strict_types = 1);

namespace Shopsys\ShopBundle\DataFixtures\Translations\Languages;

use Shopsys\ShopBundle\DataFixtures\Demo\AvailabilityDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\CategoryDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\CountryDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\FlagDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\PaymentDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\TransportDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\UnitDataFixture;
use Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslationInterface;
use Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslations;

class CzechTranslations implements DataFixturesTranslationInterface
{
    /**
     * @var array
     */
    private $translations = [];

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return 'cs';
    }

    /**
     * @return array
     */
    public function getTranslations(): array
    {
        if (empty($this->translations)) {
            $this->initTranslations();
        }

        return $this->translations;
    }

    protected function initTranslations(): void
    {
        $this->initAvailabilityTranslations();
        $this->initBrandTranslations();
        $this->initCategoryTranslations();
        $this->initCountryTranslations();
        $this->initFlagTranslations();
        $this->initPaymentTranslations();
        $this->initTransportTranslations();
        $this->initUnitTranslations();
    }

    private function initAvailabilityTranslations(): void
    {
        $translationsAvailability = [
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME => [
                AvailabilityDataFixture::AVAILABILITY_IN_STOCK => 'Skladem',
                AvailabilityDataFixture::AVAILABILITY_PREPARING => 'Připravujeme',
                AvailabilityDataFixture::AVAILABILITY_ON_REQUEST => 'Na dotaz',
                AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK => 'Nedostupné',
            ],
        ];

        $this->translations[DataFixturesTranslations::TRANSLATED_ENTITY_AVAILABILITY] = $translationsAvailability;
    }

    private function initBrandTranslations(): void
    {
        $translationsBrand = [
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION => 'Toto je popis značky %s.',
        ];

        $this->translations[DataFixturesTranslations::TRANSLATED_ENTITY_BRAND] = $translationsBrand;
    }

    private function initCategoryTranslations(): void
    {
        $translationsCategory = [];

        $translationsCategory[DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME] = [
            CategoryDataFixture::CATEGORY_ELECTRONICS => 'Elektro',
            CategoryDataFixture::CATEGORY_TV => 'Televize, audio',
            CategoryDataFixture::CATEGORY_PHOTO => 'Fotoaparáty',
            CategoryDataFixture::CATEGORY_PRINTERS => 'Tiskárny',
            CategoryDataFixture::CATEGORY_PC => 'Počítače & příslušenství',
            CategoryDataFixture::CATEGORY_PHONES => 'Mobilní telefony',
            CategoryDataFixture::CATEGORY_COFFEE => 'Kávovary',
            CategoryDataFixture::CATEGORY_BOOKS => 'Knihy',
            CategoryDataFixture::CATEGORY_TOYS => 'Hračky a další',
            CategoryDataFixture::CATEGORY_GARDEN_TOOLS => 'Zahradní náčiní',
            CategoryDataFixture::CATEGORY_FOOD => 'Jídlo',
        ];

        $translationsCategory[DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION] = [
            CategoryDataFixture::CATEGORY_ELECTRONICS => 'Naše elektronika zahrnuje zařízení určeno pro zábavu (televize s plochou obrazovkou, DVD přehrávače, DVD filmy, iPody, '
                . 'PC hry, auta na dálkové ovládání, atd.), pro komunikaci (telefony, mobilní telefony, notebooky, atd.) '
                . 'a pro kancelář (např., stolní počítače, tiskárny, skartovačky, atd.).',
            CategoryDataFixture::CATEGORY_TV => 'Televize nebo TV je telekomunikační zařízení, které se používá pro přenos zvuku s pohyblivými obrázky v monochromatickém '
                . '(černo-bílém), nebo barevném provedení, a ve dvouch nebo ve třech rozměrech.',
            CategoryDataFixture::CATEGORY_PHOTO => 'Fotoaparát je optické zařízení určeno pro nahrávání a zachytávaní obrazu, který může být uložen lokálně, '
                . 'přenášen na jiné umístění, nebo obojí.',
            CategoryDataFixture::CATEGORY_PRINTERS => 'Tiskárna je periferní zařízení, které umožňuje trvale přenést '
                . 'grafický a textový obsah na papír nebo podobné médium '
                . 'a to ve formě, která je srozumitelná i pro člověka.',
            CategoryDataFixture::CATEGORY_PC => 'Osobní počítač (PC) je zařízení s využitím pro různé účely, kterého velikost '
                . 'široké možnosti použití, prodejní cena, umožňují využití i pro jednotlivce a může být ovládán přímo '
                . 'koncovým uživatelem.',
            CategoryDataFixture::CATEGORY_PHONES => 'Telefon je komunikační zařízení, které umožňuje dvěma nebo více uživatelů provádět konverzaci '
                . 'a to i v případě, že jsou od sebe příliš vzdálení na to, aby mohli komunikovat přímo. Telefon převádí zvuk, '
                . 'typicky právě lidský hlas, na elektronické signály, které jsou vhodné pro přenos prostřednictvím '
                . 'kabelů nebo jiného přenosného média, a to i na velké vzdálenosti. Tyto signály jsou nakonec '
                . 'přehrány ve zvukové podobě koncovému uživateli.',
            CategoryDataFixture::CATEGORY_COFFEE => 'Kávovary jsou spotřebiče, které jsou určeny na vaření kávy. '
                . 'Existuje mnoho druhů kávovarů, v mnoha provedeních, které využívají různé principy přípravy kávy, '
                . 've většině případů jsou kávová zrna umístěny do papírového nebo kovového filtru uvnitř trychtýře. '
                . 'Pod tímto trychtýřem je skleněná nebo keramická konvice.',
            CategoryDataFixture::CATEGORY_BOOKS => 'Kniha je svazek psaných, tištěných, ilustrovaných, nebo prázdných listů, '
                . 'a je může být tvořena z papíru, atramentu, pergamenu, nebo z dalších materiálů, které jsou dokupy slepeny '
                . 'na jedné straně. Každý arch v knize je listem a současně na každé straně listu je jedna stránka.',
            CategoryDataFixture::CATEGORY_TOYS => 'Hračka je předmět, který může být využitý pro zábavu a hraní. '
                . 'S hračkami si hrají především děti a zvířatka. Hry s využitím hraček slouží často i pro '
                . 'učení dětí zábavnou formou pro život ve společnosti. Na výrobu hraček se používají různé materiály.',
            CategoryDataFixture::CATEGORY_GARDEN_TOOLS => 'Zahradní nářadí je jedním z mnoha nástrojů pro zahradu a '
                . 'zahradnictví a překrývá se s řadou nástrojů pro zemědělství a zahradnictví. Zahradní nářadí může '
                . 'být také ruční nářadí a elektrické nářadí.',
            CategoryDataFixture::CATEGORY_FOOD => 'Jídlo je jakákoliv látka spotřebovaná k zajištění nutriční podpory '
                . 'těla. Obvykle je rostlinného nebo živočišného původu a obsahuje základní živiny, jako jsou tuky, '
                . 'bílkoviny, vitamíny nebo minerály. Látka je přijímána organismem a buňkami organismu spotřebována, '
                . 's účelem zajištění energie.',
        ];

        $this->translations[DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY] = $translationsCategory;
    }

    private function initCountryTranslations(): void
    {
        $translationsCountry = [
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME => [
                CountryDataFixture::COUNTRY_CZECH_REPUBLIC => 'Česká republika',
                CountryDataFixture::COUNTRY_SLOVAKIA => 'Slovenská republika',
            ],
        ];

        $this->translations[DataFixturesTranslations::TRANSLATED_ENTITY_COUNTRY] = $translationsCountry;
    }

    private function initFlagTranslations(): void
    {
        $translationsFlag = [
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME => [
                FlagDataFixture::FLAG_NEW_PRODUCT => 'Novinka',
                FlagDataFixture::FLAG_TOP_PRODUCT => 'Nejprodávanější',
                FlagDataFixture::FLAG_ACTION_PRODUCT => 'Akce',
            ],
        ];

        $this->translations[DataFixturesTranslations::TRANSLATED_ENTITY_FLAG] = $translationsFlag;
    }

    private function initPaymentTranslations(): void
    {
        $translationsPayment = [];

        $translationsPayment[DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME] = [
            PaymentDataFixture::PAYMENT_CARD => 'Kreditní kartou',
            PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY => 'Dobírka',
            PaymentDataFixture::PAYMENT_CASH => 'Hotově',
        ];

        $translationsPayment[DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION] = [
            PaymentDataFixture::PAYMENT_CARD => 'Rychle, levně a spolehlivě!',
            PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY => '',
            PaymentDataFixture::PAYMENT_CASH => '',
        ];

        $translationsPayment[DataFixturesTranslations::TRANSLATED_ATTRIBUTE_INSTRUCTIONS] = [
            PaymentDataFixture::PAYMENT_CARD => '<b>Zvolili jste platbu kreditní kartou. Prosím proveďte ji do dvou pracovních dnů.</b>',
            PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY => '',
            PaymentDataFixture::PAYMENT_CASH => '',

        ];
        $this->translations[DataFixturesTranslations::TRANSLATED_ENTITY_PAYMENT] = $translationsPayment;
    }

    private function initTransportTranslations(): void
    {
        $translationsTransport = [];

        $translationsTransport[DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME] = [
            TransportDataFixture::TRANSPORT_CZECH_POST => 'Česká pošta - balík do ruky',
            TransportDataFixture::TRANSPORT_PPL => 'PPL',
            TransportDataFixture::TRANSPORT_PERSONAL => 'Osobní převzetí',
        ];

        $translationsTransport[DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION] = [
            TransportDataFixture::TRANSPORT_CZECH_POST => '',
            TransportDataFixture::TRANSPORT_PPL => '',
            TransportDataFixture::TRANSPORT_PERSONAL => 'Uvítá Vás milý personál!',
        ];

        $translationsTransport[DataFixturesTranslations::TRANSLATED_ATTRIBUTE_INSTRUCTIONS] = [
            TransportDataFixture::TRANSPORT_CZECH_POST => '',
            TransportDataFixture::TRANSPORT_PPL => '',
            TransportDataFixture::TRANSPORT_PERSONAL => 'Těšíme se na Vaši návštěvu.',
        ];

        $this->translations[DataFixturesTranslations::TRANSLATED_ENTITY_TRANSPORT] = $translationsTransport;
    }

    private function initUnitTranslations(): void
    {
        $translationsUnit = [
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME => [
                UnitDataFixture::UNIT_CUBIC_METERS => 'm³',
                UnitDataFixture::UNIT_PIECES => 'ks',
            ],
        ];

        $this->translations[DataFixturesTranslations::TRANSLATED_ENTITY_UNIT] = $translationsUnit;
    }
}
