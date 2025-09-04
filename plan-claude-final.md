# Finální plán: Stabilizace testů a demodat pro různá nastavení e-shopu

## Shrnutí problému

Současné testy v Shopsys Platformě obsahují natvrdo zakódované ceny a hodnoty, které při změnách nastavení e-shopu (měna, DPH sazby, lokalizace, cenová strategie) způsobují selhání testů. Podobně demodata obsahují duplicitní uložené kombinace cen místo dynamických výpočtů.

## Konsolidované řešení

Kombinujeme nejlepší prvky obou přístupů:
- **Jeden zdroj pravdy** pro ceny v demodatech (z plan-gpt)  
- **Profily nastavení** pro testování různých konfigurací (z plan-gpt)
- **PriceTestHelper** utility pro stabilní GQL testy (z plan-claude)
- **GitHub Actions matici** pro automatizované testování (kombinace obou)

## Cílová nastavení pro testování

Budeme pracovat s následujícími profily:

### Profil "baseline" (současný default)
- Měna: EUR na první doméně
- Locale: EN na první doméně  
- Vstupní typ ceny: včetně DPH
- Zaokrouhlování: 2 desetinná místa

### Profil "alt-pricing" (alternativní běžné nastavení)
- Měna: CZK na první doméně
- Locale: CS na první doméně
- Vstupní typ ceny: bez DPH
- Zaokrouhlování: haléře (0 desetinných míst pro konečné částky)

## Implementační strategie

### 1. Audit a analýza demodat (0.5 dne)

**Cíl**: Vytvořit kompletní seznam všech míst v demodatech, která potřebují úpravu pro podporu profilů nastavení.

**Konkrétní činnosti**:
- **Audit DataFixtures** - systematické prohledání všech fixture souborů
- **Detekce problematických vzorů** pomocí grep příkazů
- **Vytvoření strukturovaného seznamu** s konkrétními místy a navrhovanými řešeními

**Výstup**: Dokument `demodata-audit-results.md` s kompletním přehledem potřebných změn

### 2. Refaktoring demodat (1 den)

**Princip**: Jeden zdroj pravdy + dynamické výpočty (implementace podle auditu)

```php
// Místo současného duplicitního ukládání
$product->setPriceWithoutVat('1000');
$product->setPriceWithVat('1200'); // duplicitní!

// Nový přístup - pouze kanonická hodnota
$product->setBasePriceWithoutVat('1000');
// priceWithVat se počítá dynamicky podle aktivní sazby DPH
```

**Konkrétní změny** (podle výsledků auditu):
- Upravit `ProductDataFixture` - ukládat pouze `basePriceWithoutVat`
- Upravit `TransportDataFixture` a `PaymentDataFixture` stejně
- Všechny `priceWithVat`, `vatAmount` počítat přes aplikační kalkulační služby
- Odstranit duplicitní kombinace cen z fixtures

### 3. SettingsProfileApplier služba (0.5 dne)

```php
class SettingsProfileApplier
{
    public function applyProfile(string $profileName, int $domainId = 1): void
    {
        switch ($profileName) {
            case 'alt-pricing':
                $this->setCurrency($domainId, 'CZK');
                $this->setLocale($domainId, 'cs');
                $this->setVatRate('VAT_HIGH', 21);
                $this->setPriceInputType(PricingSetting::PRICE_TYPE_WITH_VAT);
                break;
            case 'baseline':
            default:
                // current defaults
                break;
        }
    }
}
```

### 4. PriceTestHelper utility třídy (1 den)

**Kombinace obou přístupů** - využije Money/minor units + dynamické výpočty:

```php
class PriceTestHelper 
{
    /**
     * Porovná ceny v GQL odpovědi s normalizací na minor units
     */
    public function assertPriceEquals(array $expectedPrice, array $actualGqlPrice): void
    {
        $expectedMoney = Money::fromString($expectedPrice['priceWithVat']);
        $actualMoney = Money::fromString($actualGqlPrice['priceWithVat']);
        
        $this->assertEquals(
            $expectedMoney->getMinorUnits(),
            $actualMoney->getMinorUnits()
        );
    }
    
    /**
     * Spočítá očekávané ceny podle aktuálního profilu nastavení
     */
    public function getExpectedProductPrice(Product $product, int $domainId, int $quantity = 1): array
    {
        // Používá stejné kalkulační služby jako aplikace
        $basePrice = $this->basePriceCalculation->calculateRoundedBasePrice(/*...*/);
        return [
            'priceWithVat' => $this->moneyFormatterHelper->formatWithMaxFractionDigits($basePrice->getPriceWithVat()),
            'priceWithoutVat' => $this->moneyFormatterHelper->formatWithMaxFractionDigits($basePrice->getPriceWithoutVat()),
            'vatAmount' => $this->moneyFormatterHelper->formatWithMaxFractionDigits($basePrice->getVatAmount()),
        ];
    }
}
```

### 5. Refaktoring GQL testů (2 dny)

**Před** (křehký hardcoded test):
```php
$this->assertEquals(
    $this->getFormattedMoneyAmountWithVatConvertedToDomainDefaultCurrency('3498.96'),
    $data['totalPrice']['priceWithVat'],
);
```

**Po** (robustní dynamický test):
```php
$expectedPrice = $this->priceTestHelper->getExpectedProductPrice(
    $this->testingProduct,
    Domain::FIRST_DOMAIN_ID,
    1
);
$this->priceTestHelper->assertPriceEquals(
    $expectedPrice,
    $data['totalPrice']
);
```

**Soubory k úpravě** (podle plan-claude analýzy):
- `RoundingPriceInCartTest.php` - odstranit skip při různých měnách
- `ProductsFilteringTest.php` - dynamické cenové filtry  
- `ProductsFilteringOptionsTest.php` - dynamické min/max ceny
- `MultipleProductsQueryTest.php` - produktové ceny
- `OrderWithPromoCodeTest.php` - ceny objednávek se slevami
- `ApplyPromoCodeToCartTest.php` - košík se slevovými kódy

### 6. GitHub Actions rozšíření (0.5 dne)

**Kombinace obou návrhů** - matice s environment proměnnými:

```yaml
tests-unit-functional-smoke-settings-matrix:
    name: Run tests with different settings profiles  
    needs: [build-php-fpm-image, build-elasticsearch-image]
    if: |
        always() && !failure() && !cancelled() &&
        (
            github.event.pull_request.head.repo.full_name == 'shopsys/shopsys' ||
            github.ref_protected == true
        )
    runs-on: ubuntu-22.04
    strategy:
        fail-fast: false
        matrix:
            settings_profile: [baseline, alt-pricing]
    env:
        TEST_SETTINGS_PROFILE: ${{ matrix.settings_profile }}
    steps:
        - name: GIT checkout branch - ${{ github.ref }}
          uses: actions/checkout@v4
          with:
              ref: ${{ github.ref }}
        - name: Download docker-compose.yml from artifacts
          uses: actions/download-artifact@v4
          with:
              name: docker-compose
        - name: Build application with settings profile
          run: |
              docker compose pull --parallel postgres webserver elasticsearch redis php-fpm
              docker compose up -d postgres webserver elasticsearch redis php-fpm
              # Profil se aplikuje během bootstrap procesu
              docker compose exec -T php-fpm php phing -D production.confirm.action=y -D change.environment=dev environment-change test-dirs-create test-db-create frontend-api-generate-new-keys npm-install-dependencies
        - name: Run tests
          run: docker compose exec -T php-fpm php phing -D production.confirm.action=y tests
```

### 7. Integrace do aplikace (0.5 dne)

**Bootstrap test environment**:
```php
// V TestKernel nebo test bootstrap
if ($profile = $_ENV['TEST_SETTINGS_PROFILE'] ?? null) {
    $settingsProfileApplier = $container->get(SettingsProfileApplier::class);
    $settingsProfileApplier->applyProfile($profile);
}
```

## Implementační plán (celkem 5.5 dne)

### Den 1: Audit a analýza demodat (0.5 dne)
- ✅ Systematické prohledání všech DataFixture souborů
- ✅ Detekce problematických vzorů s duplicitními cenami
- ✅ Identifikace hardcoded DPH sazeb a cenových hodnot
- ✅ Vytvoření strukturovaného seznamu `demodata-audit-results.md`

### Den 2: Infrastruktura (1 den)
- ✅ Implementace `SettingsProfileApplier`
- ✅ Vytvoření `PriceTestHelper` s Money/minor units podporou
- ✅ Bootstrap integrace pro TEST_SETTINGS_PROFILE

### Den 3: Demodata refaktoring (1 den)
- ✅ Úprava ProductDataFixture na jednotný zdroj pravdy
- ✅ Úprava TransportDataFixture a PaymentDataFixture
- ✅ Odstranění duplicitních cenových kombinací
- ✅ Dynamické výpočty přes kalkulační služby

### Den 4-5: GQL testy refaktoring (2 dny)
- ✅ Refaktoring vysoké priority testů (6 souborů)
- ✅ Refaktoring střední priority testů  
- ✅ Odstranění všech hardcoded cenových konstant
- ✅ Implementace robustních assertů s Money porovnáním

### Den 6: CI a finalizace (0.5 dne)
- ✅ Přidání GitHub Actions matice
- ✅ Testování obou profilů lokálně i v CI
- ✅ Dokumentace a code review

## Detekční vzory pro audit

Pro nalezení problematických míst:
```bash
# Hardcoded ceny v testech
grep -r "getFormattedMoneyAmount.*[0-9]" project-base/app/tests/
grep -r "priceWithVat.*[0-9]" project-base/app/tests/
grep -r "Money::create.*[0-9]" project-base/app/tests/

# Hardcoded DPH sazby
grep -r "VAT_HIGH.*[0-9]" project-base/app/src/DataFixtures/
grep -r "21\|20\|15" project-base/app/tests/ # kontextově zkontrolovat

# Duplicitní cenové kombinace v fixtures
grep -r "setPriceWithVat\|setPriceWithoutVat" project-base/app/src/DataFixtures/
```

## Očekávané přínosy

### Krátkodobé
- ✅ Testy projdou v různých nastaveních měny, DPH, lokalizace
- ✅ Eliminace hardcoded cenových hodnot  
- ✅ Robustnější GQL testy s Money normalizací

### Dlouhodobé
- ✅ CI automaticky ověří kompatibilitu s alternativními nastavení
- ✅ Jednodušší údržba testů při změnách cenové logiky
- ✅ Rychlejší setup nových projektů s různými konfiguracemi
- ✅ Jeden zdroj pravdy pro ceny eliminuje nekonzistence

## Metriky úspěchu

- [ ] Profil `baseline` i `alt-pricing` projde v CI bez chyb
- [ ] Zero hardcoded cenových konstant v testech (kromě base values v demodatech)
- [ ] Všechny GQL testy používají Money/minor units porovnání
- [ ] Lokální přepínání profilů: `TEST_SETTINGS_PROFILE=alt-pricing phpunit`
- [ ] Demodata obsahují pouze base_price, vše ostatní je počítáno
- [ ] Dokumentace popisuje jak přidat nový profil

## Rizika a mitigace

1. **Riziko**: Rozsáhlý refaktoring může narušit stávající testy
   **Mitigace**: Postupné PR po částech, kontinuální běh CI v obou profilech

2. **Riziko**: Zpomalení CI kvůli dvojnásobnému běhu testů  
   **Mitigace**: Paralelizace, cache optimizace, fail-fast: false

3. **Riziko**: Neúplné pokrytí všech cenových scénářů
   **Mitigace**: Postupné rozšiřování profilů na základě reálných projektů

## Lokální použití

```bash
# Baseline profil (default)
docker compose exec php-fpm php phing tests

# Alternativní profil  
TEST_SETTINGS_PROFILE=alt-pricing docker compose exec php-fpm php phing tests

# Přepnout profil a znovu načíst demodata
TEST_SETTINGS_PROFILE=alt-pricing docker compose exec php-fpm php phing test-db-create demo-data
```

## Závěr

Tento konsolidovaný plán kombinuje silné stránky obou původních návrhů:
- **Jeden zdroj pravdy** pro ceny eliminuje duplicity a nekonzistence
- **Profily nastavení** umožňují testování různých konfigurací  
- **Money/minor units** přístup eliminuje float porovnávání
- **PriceTestHelper** poskytuje robustní API pro testy
- **GitHub Actions matice** zajišťuje automatické ověření

Výsledkem bude spolehlivý testovací systém, který podporuje různá nastavení e-shopu bez manuálních úprav testů.
