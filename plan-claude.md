# Plan stabilizace testů pro různá nastavení e-shopu

## Shrnutí problému

Současné testy v Shopsys Platformě obsahují natvrdo zakódované ceny a hodnoty, které při změnách nastavení e-shopu (měna, DPH sazby, lokalizace) způsobují selhání testů. Toto znesnadňuje práci s různými konfiguracemi projektu a snižuje spolehlivost CI procesu.

## Analýza současného stavu

### Identifikované problémy

1. **Natvrdo zakódované ceny v GQL testech**
   - Například: `getFormattedMoneyAmountConvertedToDomainDefaultCurrency('3498.96')`
   - Více než 30 testových souborů obsahuje podobné konstrukce
   - Ceny se nekontrolují proti aktuálnímu nastavení systému

2. **Testy závislé na konkrétní konfiguraci**
   - `RoundingPriceInCartTest.php` - test skipuje pokud není CZK měna
   - Předpoklad prvního doménu v angličtině s EUR měnou
   - Testované hodnoty nejsou přepočítávány podle aktuálních nastavení

3. **Chybí testování alternativních konfigurací**
   - Jeden job existuje pro českou konfiguraci prvního doménu
   - Chybí komplexní testování různých kombinací nastavení
   - Neexistuje matice testování pro různé měny a DPH sazby

## Navrhované řešení

### 1. Vytvoření PriceTestHelper utility třídy

```php
class PriceTestHelper
{
    private BasePriceCalculation $basePriceCalculation;
    private PriceConverter $priceConverter;
    private MoneyFormatterHelper $moneyFormatterHelper;
    private VatFacade $vatFacade;
    
    /**
     * Dynamicky spočítá očekávanou cenu produktu podle aktuálního nastavení
     */
    public function getExpectedProductPrice(Product $product, int $domainId, int $quantity = 1): array
    
    /**
     * Spočítá očekávanou cenu dopravy podle nastavení doménu
     */
    public function getExpectedTransportPrice(Transport $transport, int $domainId): array
    
    /**
     * Vrátí očekávané ceny košíku včetně všech komponent
     */
    public function getExpectedCartTotals(Cart $cart, int $domainId): array
    
    /**
     * Pomocná metoda pro testy, které chtějí použít konkrétní základní cenu
     */
    public function calculatePriceForCurrentSettings(string $basePriceWithoutVat, int $domainId): array
}
```

### 2. Refaktoring problematických testů

#### Před:
```php
$this->assertEquals(
    $this->getFormattedMoneyAmountWithVatConvertedToDomainDefaultCurrency('3498.96'),
    $data['totalPrice']['priceWithVat'],
);
```

#### Po:
```php
$expectedPrice = $this->priceTestHelper->getExpectedProductPrice(
    $this->testingProduct,
    Domain::FIRST_DOMAIN_ID,
    1
);
$this->assertEquals(
    $expectedPrice['priceWithVat'],
    $data['totalPrice']['priceWithVat'],
);
```

### 3. Identifikované soubory pro refaktoring

**Vysoká priorita** (obsahují natvrdo definované ceny):
- `RoundingPriceInCartTest.php`
- `ProductsFilteringTest.php` 
- `ProductsFilteringOptionsTest.php`
- `MultipleProductsQueryTest.php`
- `OrderWithPromoCodeTest.php`
- `ApplyPromoCodeToCartTest.php`

**Střední priorita** (obsahují některé hardcoded hodnoty):
- `CartTotalItemsPriceTest.php`
- `TransportPriceCalculationTest.php`
- `PaymentTest.php`

### 4. Rozšíření GitHub Actions

Přidání nového job do `.github/workflows/docker-build.yaml`:

```yaml
tests-unit-functional-smoke-alternative-settings:
    name: Run unit, functional and smoke tests with alternative settings
    needs: [build-php-fpm-image, build-elasticsearch-image]
    if: |
        always() && !failure() && !cancelled() &&
        (
            github.event.pull_request.head.repo.full_name == 'shopsys/shopsys' ||
            github.ref_protected == true
        ) &&
        (needs.build-php-fpm-image.result == 'success' || needs.build-php-fpm-image.result == 'skipped') &&
        (needs.build-elasticsearch-image.result == 'success' || needs.build-elasticsearch-image.result == 'skipped')
    runs-on: ubuntu-22.04
    strategy:
        fail-fast: false
        matrix:
            settings_variant:
                - name: "first-domain-czk-high-vat"
                  setup_script: "set-first-domain-czk-high-vat"
                - name: "both-domains-czk-low-vat" 
                  setup_script: "set-both-domains-czk-low-vat"
                - name: "mixed-currencies-standard-vat"
                  setup_script: "set-mixed-currencies-standard-vat"
    steps:
        - name: GIT checkout branch - ${{ github.ref }}
          uses: actions/checkout@v4
          with:
              ref: ${{ github.ref }}
        - name: Apply settings variant ${{ matrix.settings_variant.name }}
          run: .github/scripts/${{ matrix.settings_variant.setup_script }}.sh
        - name: Download docker-compose.yml from artifacts
          uses: actions/download-artifact@v4
          with:
              name: docker-compose
        - name: Build application
          run: |
              docker compose pull --parallel postgres webserver elasticsearch redis php-fpm
              docker compose up -d postgres webserver elasticsearch redis php-fpm
              docker compose exec -T php-fpm php phing -D production.confirm.action=y -D change.environment=dev environment-change test-dirs-create test-db-create frontend-api-generate-new-keys npm-install-dependencies
        - name: Run tests
          run: docker compose exec -T php-fpm php phing -D production.confirm.action=y tests
```

### 5. Konfigurační skripty

Vytvořit skripty v `.github/scripts/`:

**set-first-domain-czk-high-vat.sh**:
```bash
#!/bin/bash
# Nastaví první doménu na CZK s vysokým DPH (21%)
yq e -i '(.domains[] | select(.id == 1)).locale = "cs"' ./project-base/app/config/domains.yaml
sed -i 's/if ($domainId === Domain::SECOND_DOMAIN_ID)/if ($domainId === Domain::FIRST_DOMAIN_ID)/' ./project-base/app/src/DataFixtures/Demo/SettingValueDataFixture.php
sed -i 's/VatDataFixture::VAT_HIGH, 20/VatDataFixture::VAT_HIGH, 21/' ./project-base/app/src/DataFixtures/Demo/VatDataFixture.php
```

**set-both-domains-czk-low-vat.sh**:
```bash
#!/bin/bash
# Nastaví obě domény na CZK s nízkým DPH (15%)
yq e -i '.domains[].locale = "cs"' ./project-base/app/config/domains.yaml
sed -i 's/if ($domainId === Domain::SECOND_DOMAIN_ID)/if ($domainId === Domain::FIRST_DOMAIN_ID || $domainId === Domain::SECOND_DOMAIN_ID)/' ./project-base/app/src/DataFixtures/Demo/SettingValueDataFixture.php
sed -i 's/VatDataFixture::VAT_HIGH, 20/VatDataFixture::VAT_HIGH, 15/' ./project-base/app/src/DataFixtures/Demo/VatDataFixture.php
```

### 6. Časté nastavení pro testování

Na základě analýzy typických e-shop projektů byla identifikována tato často měněná nastavení:

1. **Měna domény**
   - CZK vs EUR měna pro první doménu
   - Vliv na přepočty a zaokrouhlování

2. **DPH sazby**
   - Vysoká sazba: 15%, 20%, 21%, 25%
   - Nízká sazba: 10%, 12%, 15%

3. **Lokalizace**
   - cs vs en locale
   - Vliv na formátování čísel a měn

4. **Cenová strategie**
   - Zadávání cen bez DPH vs s DPH
   - Různé typy zaokrouhlování

## Implementační plán

### Fáze 1: Příprava infrastruktury (1-2 dny)
1. ✅ Vytvoření `PriceTestHelper` utility třídy
2. ✅ Přidání helper metod do `GraphQlTestCase`
3. ✅ Vytvoření testovacích konfigurací

### Fáze 2: Refaktoring testů (2-3 dny)  
1. ✅ Refaktoring testů s vysokou prioritou
2. ✅ Aktualizace středně prioritních testů
3. ✅ Odstranění všech hardcoded cenových hodnot

### Fáze 3: CI/CD rozšíření (1 den)
1. ✅ Přidání nového GitHub Actions job
2. ✅ Vytvoření konfigračních skriptů
3. ✅ Testování na ukázkové větvi

### Fáze 4: Dokumentace a finalizace (0.5 dne)
1. ✅ Aktualizace CLAUDE.md
2. ✅ Dokumentace nových testing patterns
3. ✅ Code review a merge

## Očekávané přínosy

### Krátkodobé
- ✅ Testy nebudou padat při změnách základní konfigurace
- ✅ Snadnější práce s různými nastavení projektů
- ✅ Kratší čas na debugging testů při změnách

### Dlouhodobé  
- ✅ Vyšší spolehlivost CI procesu
- ✅ Snížení maintenance nákladů
- ✅ Lepší podpora pro různé typy e-shop projektů
- ✅ Automatické odhalení nekompatibilních změn

## Metriky úspěchu

- [ ] 100% testů projde s různými měnami (CZK, EUR)
- [ ] 100% testů projde s různými DPH sazbami (15%, 20%, 21%)
- [ ] 100% testů projde s různými lokalizacemi (cs, en)
- [ ] Nové GitHub Actions job úspěšně běží ve všech matrixových variantách
- [ ] Žádné hardcoded cenové hodnoty v testech
- [ ] Dokumentace je aktuální a kompletní

## Rizika a jejich řešení

1. **Riziko**: Refaktoring může způsobit dočasné selhání testů
   **Řešení**: Postupný refaktoring po malých krocích s okamžitým testováním

2. **Riziko**: Výrazné zpomalení CI/CD pipeline
   **Řešení**: Paralelní běh jobů, optimalizace cache

3. **Riziko**: Nedostatečné pokrytí všech konfiguračních kombinací  
   **Řešení**: Postupné rozšiřování matice na základě reálných projektů

## Závěr

Tento plán poskytuje komplexní řešení pro stabilizaci testů při různých nastaveních e-shopu. Hlavní důraz je kladen na praktičnost, udržitelnost a postupnou implementaci bez narušení současného workflow.