# Finální plán: Stabilizace demodat a testů pro variabilní nastavení e‑shopu (Shopsys Platform)

Cíl: testy (včetně GQL) a demodata musí spolehlivě fungovat i při změně běžných nastavení e‑shopu. V CI bude přidán samostatný běh s alternativním profilem, aby se do main nedostaly nekompatibilní změny. Tento finální plán vznikl syntézou dokumentů plan-claude.md a plan-gpt.md.


## Co bereme z obou plánů (a proč)

- Profily nastavení pro testy (baseline + alt-pricing) přes env proměnnou
  - Jednoduché, málo invazivní, bez úprav kódu v CI (preferováno před skripty měnícími soubory).
- Jediný zdroj pravdy v demodatech (kanonická cena + DPH) a veškeré odvozeniny přes kalkulační služby
  - Eliminuje „natvrdo“ uložené kombinace cen a zvyšuje determinismus.
- Test helpery pro ceny
  - Kombinujeme přístupy: PriceTestHelper (metody pro výpočet očekávání) + PriceAssertions/PriceExpectationBuilder (normalizace na Money/minor units a jednotné aserce).
- Refaktor GQL testů: žádné pevné částky, normalizace na Money/minor units, očekávání počítaná stejnou službou jako backend
  - Zvýšení stability a snížení křehkosti na formátování/zaokrouhlení.
- CI maticový běh
  - Začneme se 2 profily (baseline, alt-pricing). Rozšíření (měny, DPH varianty, lokalizace, více domén) jako fáze 2.
- Seznam prioritních testů k úpravě
  - Převzaté konkrétní cílení urychlí dopad refaktoru.


## Co odkládáme nebo měníme (a proč)

- Nebudeme v 1. iteraci hýbat měnou ani počtem domén
  - Výrazně komplikuje výpočty a prodlužuje CI. Přidáme jako fázi 2.
- V CI nebudeme skripty měnit soubory repozitáře (sed/yq)
  - Křehké, hůř auditovatelné. Místo toho použijeme SettingsProfileApplier na začátku test bootstrappingu.
- „Hotové“ checkboxy v plánu od Claude bereme jako cíle, nikoli již splněné úkoly
  - Vše projde standardním review a implementací.


## Cílové profily nastavení (MVP)

- baseline (aktuální výchozí v repu)
- alt-pricing (odchylky typické na projektech):
  - Vstupní typ ceny: bez DPH (alt) vs s DPH (baseline) — přesně dle aktuálního projektu nadefinujeme
  - Zaokrouhlování: měřítko 0 vs 2 desetinná místa, režim HALF_UP
  - Výchozí cenová skupina: B2C vs B2B (pro doménu 1)
  - Dopravy/platby: částky a limit dopravy zdarma zap./vyp.

Přepínání přes env proměnnou TEST_SETTINGS_PROFILE=baseline|alt-pricing. Aplikace profilu proběhne před natažením demodat.


## Úpravy demodat

- Kanonické atributy: base_price_without_vat + vat_rate (u produktů, doprav, plateb)
- Odvozené hodnoty (price_with_vat, vat_amount, totals) se počítají přes oficiální kalkulační služby
- Odstranit duplikace pevných kombinací cen a ručních mezisoučtů (objednávky apod.) – při seedování dopočítat


## Testovací helpery pro ceny

- PriceTestHelper (výpočet očekávaných cen pro aktivní profil):
  - getExpectedProductPrice(Product, domainId, quantity)
  - getExpectedTransportPrice(Transport, domainId)
  - getExpectedCartTotals(Cart, domainId)
  - calculatePriceForCurrentSettings(basePriceWithoutVat, domainId)
- PriceExpectationBuilder
  - Z jedné kanonické ceny + DPH a množství sestaví očekávané částky skrze stejné kalkulační služby
- PriceAssertions
  - Aserce nad Money/minor units, sjednocené zaokrouhlení, tolerance formátu


## Refaktor testů (vč. GQL)

- Nahradit pevné částky výpočty přes PriceTestHelper/PriceExpectationBuilder
- Normalizovat GQL odpovědi na Money/minor units před porovnáním
- Omezit křehké snapshoty – kontrolovat hodnoty po normalizaci, případně struktury s matchery
- Detekční vzory (pro audit):
  - "priceWithVat", "priceWithoutVat", "vatAmount" s literály
  - Známé sazby DPH (21, 15, 10) v testech v kombinaci s cenami
  - Money::fromString('...') s literály

Prioritní soubory k úpravě (z plan-claude.md):
- Vysoká priorita: RoundingPriceInCartTest.php, ProductsFilteringTest.php, ProductsFilteringOptionsTest.php, MultipleProductsQueryTest.php, OrderWithPromoCodeTest.php, ApplyPromoCodeToCartTest.php
- Střední priorita: CartTotalItemsPriceTest.php, TransportPriceCalculationTest.php, PaymentTest.php


## CI: maticový běh (MVP)

- Přidat matrix se settings_profile: [baseline, alt-pricing]
- Exportovat TEST_SETTINGS_PROFILE pro job
- V bootstrapu testů aplikovat profil (SettingsProfileApplier) před natažením demodat/fixtures
- Použít existující projektové příkazy (phing/bin/console) – konkrétní příkazy doplníme dle repa

Pozn.: Plán od Claude se skripty (sed/yq) pro úpravu souborů nepoužijeme v MVP; je vhodný spíše pro fázi 2, pokud bude potřeba masivně testovat různé kombinace měn/DPH/lokalizací.


## Fáze 2 (rozšíření po MVP)

- Rozšíření matice v CI o:
  - měny (CZK/EUR) a různé sazby DPH (např. 15/21 %)
  - lokalizace (cs/en) – dopad na formátování
  - více domén současně
- Přidání jednoduchých skriptů/seed kroků, které tyto varianty zapnou, stále bez patchování souborů v repu


## Implementační postup (kroky)

1) Audit nastavení a testů (0.5–1 den)
   - Sepsat přesné parametry a služby: vstupní typ ceny, zaokrouhlování, cenové skupiny, doprava/platba, limit free shipping
   - Zmapovat testy s pevnými částkami (použít detekční vzory)

2) SettingsProfileApplier (0.5 dne)
   - Aplikace profilu podle TEST_SETTINGS_PROFILE před fixtures
   - Uložení do DB/konfigurace přes existující facády/služby

3) Testovací helpery (0.5–1 den)
   - Implementovat PriceTestHelper, PriceExpectationBuilder, PriceAssertions
   - Integrace do GraphQlTestCase (pokud existuje)

4) Refaktor prioritních testů (1–2 dny)
   - Vysoká priorita → Střední priorita
   - Odstranit hardcoded částky, nahradit výpočty a normalizované aserce

5) Úpravy demodat (0.5–1 den)
   - Zavést kanonický zdroj ceny + DPH
   - Dopravy/platby a objednávky dopočítávat

6) CI maticový běh (0.5 dne)
   - Přidat matrix baseline/alt-pricing
   - Aplikovat profil v bootstrapu před fixtures

7) Finální audit a dokumentace (0.5 dne)
   - Ověřit lokálně i v CI, dopsat krátký README/Dev notes


## Akceptační kritéria – mapování

- Vytipovat častá nastavení a upravit testy → profily + helpery + refaktor prioritních testů
- Přidat do GitHub Actions další běh testů → CI matrix baseline/alt-pricing
- Zvážit jednoduché úpravy GQL testů s cenami → normalizace Money/minor units, výpočet očekávání přes kalkulační služby
- Najít „natvrdo“ definované ceny → audit + jejich náhrada výpočty v demodatech a testech


## Metriky úspěchu (MVP)

- 100 % testů prochází v obou profilech (baseline, alt-pricing)
- 0 hardcoded cen v testech (mimo kanonické demodata)
- GQL testy s cenami používají helpery a porovnávají normalizované Money/minor units
- CI běží bez významného prodloužení (> +20 % času je signál k optimalizaci)


## Rizika a mitigace

- Zpětná kompatibilita demodat → postupné PR po částech, běhy v obou profilech
- Zpomalení CI → cache composer/npm, paralelizace, selektivní spuštění
- Chybějící jednotné kalkulační služby → znovupoužít stávající pricing služby, případně tenký wrapper v testech


## Lokální použití

- Baseline: běžné příkazy projektu (např. vendor/bin/phpunit)
- Alt profil: TEST_SETTINGS_PROFILE=alt-pricing vendor/bin/phpunit (před prvním během znovu natáhnout fixtures)


## Výstupy (deliverables)

- SettingsProfileApplier a dokumentovaný TEST_SETTINGS_PROFILE
- Refaktor demodat: kanonická cena + DPH, výpočty přes kalkulační služby
- Test helpery: PriceTestHelper, PriceExpectationBuilder, PriceAssertions
- Refaktor prioritních testů (vč. GQL) bez pevných částek
- CI pipeline s maticí baseline/alt-pricing a návodem pro lokální běh

