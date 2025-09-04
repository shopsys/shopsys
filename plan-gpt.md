# Plán: Stabilizace demodat a testů pro variabilní nastavení e‑shopu (Shopsys Platform)

Cíl: Kombinace demodat a testů musí bez pádu fungovat při změně vybraných (běžně používaných) nastavení e‑shopu. Ověřovat to budeme i v CI přidaným během testů s alternativním profilem nastavení. Zvláštní pozornost věnujeme GQL testům s cenami, aby nebyly křehké a neobsahovaly tvrdé částky.


## Shrnutí řešení

- Zavedeme „profily nastavení“ (baseline a alt-pricing) a zajistíme, že demodata i testy s nimi fungují.
- Demodata převedeme na výpočetní režim: ceny se nebudou ukládat „natvrdo“ v různých kombinacích, ale budou odvozené z jedné kanonické hodnoty a sazby DPH.
- Testy (včetně GQL) nebudou porovnávat plovoucí čísla ani křehké absolutní částky, místo toho použijí Money typy / minor units a očekávání spočítané stejným kalkulačním servisem jako aplikace.
- Do GitHub Actions přidáme matici, která spustí testy jak v baseline, tak v alt-pricing profilu, aby se do main nedostaly změny, které v alternativním nastavení selžou.


## Rozsah a ne‑cíle

- V rozsahu: backend testy (PHPUnit, včetně GraphQL testů), demodata, integrační testy, jednoduché e2e kroky spojené s cenami (pokud existují). 
- Ne‑cíle 1. iterace: víceměnovost a multi‑doménové běhy v CI (zvýšily by náročnost a čas běhu). Ty lze přidat následně jako další profily.


## Cílová (běžná) nastavení, na která se zaměříme

Budeme pracovat s dvojicí profilů, které pokrývají typické rozdíly mezi projekty:

- baseline (aktuální default v repu)
- alt-pricing (alternativní, „nejčasté“ odchylky na projektech)

Přepínané volby v alt-pricing (příklad, konkrétní parametry a názvy vyčteme z kódu):
- Vstupní typ ceny: s DPH vs. bez DPH (input price type)
- Zaokrouhlování a přesnost: 0 vs. 2 desetinná místa; strategie HALF_UP
- Výchozí cenová skupina: B2C vs. B2B (např. jiná cenová skupina pro doménu)
- Limit dopravy zdarma: zapnuto/vypnuto; různé částky dopravy/platby

Záměrně v 1. kroku nepřepínáme: měnu a počet domén (lze přidat jako další profily později).


## Změny v demodatech

Základní princip: jeden zdroj pravdy pro cenu + DPH, vše ostatní se dopočítá podle profilu.

- Přidat jednotný vstup pro výrobky a služby: base_price_without_vat + vat_rate. 
- Při seedování (fixtures) počítat price_with_vat, vat_amount, totals atd. přes aplikační kalkulační služby (ne ručně). 
- Stejné pravidlo aplikovat na dopravy/platby a případné cenové pravidla/slevy.
- Odstranit z demodat duplicitní uložení kombinací cen (např. současně „bez DPH“ i „s DPH“ natvrdo). 
- Pokud demodata obsahují mezisoučty (např. objednávky) natvrdo, nahradit je výpočtem během seedování.

Výsledek: jeden zdroj a deterministické výpočty pod kontrolou profilu nastavení.


## Změny v testech

- Zavedeme „PriceAssertions“ a „PriceExpectationBuilder“ (testovací helpery):
  - Porovnání cen se provádí přes Money/Minor Units a s jednotným zaokrouhlením.
  - Očekávané hodnoty se generují kalkulační službou (stejnou jako používá aplikace), a to pro aktivní profil nastavení.
  - Žádné přímé porovnání floatů; zákaz „magických“ konstant, které se mění se zaokrouhlením/DPH.
- V integračních a GQL testech budeme očekávání budovat z demodat (ID produktu, množství, sazba DPH) a výsledek kontrolovat přes helpery. 
- Tam, kde to dává smysl, nahradíme „rovnost“ struktur chytrými matchery (např. kontrola existence polí a hodnot po normalizaci), ne tvrdé snapshoty s absolutními částkami.


## Jednoduché úpravy GQL testů s cenami

- Normalizace odpovědi: parsovat cenu na Money v minor units (např. integer v haléřích) před porovnáním.
- Vyhnout se asertům na přesný string s 2 desetinnými místy; místo toho porovnat částku + měnu po aplikaci stejného zaokrouhlení, které používá backend.
- U složených částek (subtotal, total, shipping) počítat očekávání pomocí kalkulační služby, ne ručně.
- Zkontrolovat, zda někde v resolverech/testech nekolují „natvrdo dané“ ceny (např. doprava za 99), a přepnout je na výpočet z demodat/konfigurace.

Příklad detekčních vzorů (k auditům v repu):
- Řetězce s cenami v testech: `"price":\s*\"?\d+` nebo `"priceWithVat"`, `"priceWithoutVat"`, `"vatAmount"`
- „Známé“ sazby DPH natvrdo: `21`, `15`, `10` v souvislosti s cenami
- Konstrukce typu `Money::fromString('...')` s literály v testech


## Integrace profilů do běhu testů

Existují dvě cesty; pro 1. iteraci preferujeme A (rychlejší):

A) Profil přes environment proměnnou (doporučeno pro první krok)
- Zavedeme proměnnou např. `TEST_SETTINGS_PROFILE` s hodnotami `baseline` | `alt-pricing`.
- Při bootstrapu testů spustíme „SettingsProfileApplier“, který přes aplikační nastavení (facade pro nastavení, doménu a pricing) uloží parametry profilu (do DB/configu) před seedováním demodat a během nich.
- Výhoda: žádné nové Symfony environmenty, minimální zásah do konfigurace.

B) Oddělené Symfony prostředí (např. `test_alt`)
- Připravit `config/packages/test_alt/*.yaml` s přepisy parametrů.
- Spouštět testy s `APP_ENV=test_alt` a separátní cache. 
- Větší zásah, ale čistá separace konfigurace.

Pro začátek implementujeme A a v CI budeme profil přepínat přes env var.


## CI: GitHub Actions – druhý běh testů v alt profilu

Záměr: Přidat matrix a spouštět testy v obou profilech. Příklad (orientační, upraví se podle lokálního CI skriptu):

```yaml
name: CI

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        settings_profile: [baseline, alt-pricing]
    env:
      TEST_SETTINGS_PROFILE: ${{ matrix.settings_profile }}
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-php@v4
        with:
          php-version: '8.2'
          extensions: mbstring, intl
      - name: Install deps
        run: |
          composer install --no-interaction --prefer-dist
      - name: Prepare DB, cache, fixtures
        run: |
          # zde se v bootstrapu použije TEST_SETTINGS_PROFILE k aplikaci nastavení
          bin/console doctrine:database:create --if-not-exists --env=test
          bin/console doctrine:schema:update --force --env=test
          bin/console shopsys:fixtures:load --env=test --no-interaction
      - name: Run tests
        run: |
          vendor/bin/phpunit --colors=never
```

Pozn.: Konkrétní příkazy (fixtures, schema) upravíme podle skutečných nástrojů v projektu (phing, make, bin/console, atd.). Důležité je, aby profil nastavení byl aplikován před načtením demodat.


## Postup zavedení (kroky)

1) Audit nastavení a testů (0.5–1 den)
   - Sepsat přesné parametry a služby, které řídí: typ vstupní ceny, zaokrouhlování, cenové skupiny, doprava/platba, limit dopravy zdarma.
   - Najít křehké testy a místa s „natvrdo“ cenami (viz detekční vzory výše).

2) SettingsProfileApplier (0.5 dne)
   - Implementovat službu/spouštěcí skript pro aplikaci profilu před seedováním a během test bootstrupu.
   - Varianta A: profil přes env var; jednotné místo, kde se nastavení uloží (DB/konfigurace).

3) Úpravy demodat (0.5–1 den)
   - Zavést jednotný kanonický zdroj ceny a sazby DPH.
   - Převést aktuální „natvrdo“ uložené kombinace na výpočty přes kalkulační služby.

4) Test helpery (0.5 dne)
   - PriceAssertions (porovnání Money/minor units, normalizace GQL odpovědi)
   - PriceExpectationBuilder (počítá očekávání přes kalkulační služby)

5) Refaktor GQL a integračních testů s cenami (1–2 dny)
   - Vyměnit tvrdé částky za použité helpery.
   - Redukovat křehké snapshoty, zavést tolerantní matchery tam, kde je to vhodné.

6) CI maticový běh (0.5 dne)
   - Přidat matrix, zkrátit čas instalace (cache composer/vendor), přidat krok seedování s aplikovaným profilem.

7) Finální audit a úklid (0.5 dne)
   - Zkontrolovat, že oba profily prochází lokálně i v CI, a že testy nepadají při přepnutí.


## Ověření a měřitelnost

- Oba CI běhy (baseline i alt-pricing) prochází zeleně.
- Lokálně lze spustit `TEST_SETTINGS_PROFILE=alt-pricing` a testy projdou bez úprav.
- V kódu testů nejsou vidět pevně zadrátované ceny (vyjma základních demodat, ze kterých se vše počítá).
- GQL testy s cenami používají helpery a porovnávají normalizované Money/minor units.


## Mapování na akceptační kritéria

- „Vytipovat si nastavení... a upravit testy...“
  - Profily + audit + helpery + refaktor testů splňují.
- „Přidat do GitHub Actions další běh testů...“
  - CI matrix s `settings_profile` a seedováním podle profilu.
- „Podívat se na GQL testy s cenami a zvážit jednoduchou úpravu...“
  - Normalizace na Money/minor units, helpery, eliminace tvrdých částek.
- „Zda neexistují ceny natvrdo...“
  - Audit testů a demodat, nahrazení výpočty.


## Rizika a mitigace

- Riziko: Zpětná kompatibilita demodat – úpravy mohou ovlivnit existující testy
  - Mitigace: Probíhající CI v obou profilech, postupné PR po částech.
- Riziko: Zpomalení CI
  - Mitigace: Cache composer/npm, databázové cache, paralelizace jobů.
- Riziko: Chybějící jednotný kalkulační servis
  - Mitigace: Znovupoužít existující pricing služby; kde chybí, doplnit tenkou vrstvu wrapperu v testech.


## Lokální použití (pro vývojáře)

- Spuštění v baseline: standardní příkaz projektu (např. `vendor/bin/phpunit`).
- Spuštění v alt profilu:
  - `TEST_SETTINGS_PROFILE=alt-pricing vendor/bin/phpunit`
  - Před prvním během: znovu natáhnout fixtures, aby odrážely profil.


## Odhad pracnosti (MVP)

- 3–5 dnů čistého času dle velikosti úprav v testech.
- Iterace 2 (rozšíření o měnu/multi‑doménu) +2–3 dny.


## Výstupy (deliverables)

- Refaktor demodat: jednotný zdroj pravdy pro ceny + výpočty.
- Test helpery: PriceAssertions, PriceExpectationBuilder.
- Upravené GQL a integrační testy s cenami (bez tvrdých částek).
- CI pipeline s maticí profilů a dokumentací, jak lokálně spouštět oba profily.


