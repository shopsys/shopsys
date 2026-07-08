# Multi-měny na jedné doméně (multi-currency per domain)

## Context

Shopsys Platform dnes váže měnu 1:1 na doménu (DB setting `defaultDomainCurrencyId`). Cíl: umožnit na jedné doméně **více měn** definovaných **napevno v commitované konfiguraci** s přepínačem měny na storefrontu (zobrazí se jen při >1 měně). Dřívější ruční pokusy ztroskotaly na tom, že cenotvorba nemá univerzální vstup (měna se musela protahovat všemi signaturami a všemi GraphQL queries) a na per-měnové viditelnosti produktů. Řešení: request/scope-level `CurrentCurrencyProvider` (idiom `Domain::switchDomainById`) + měna přenášená HTTP hlavičkou + povinné ceny ve všech měnách (per-měnová viditelnost neexistuje).

### Schválená rozhodnutí (od uživatele)

1. **Konfigurace**: `currencies: [KÓD, ...]` **povinně** v `domains.yaml`, první položka = výchozí měna domény. DB setting `defaultDomainCurrencyId` zaniká úplně (migrace smaže, admin sekce zmizí). Build zajistí existenci měn z configu; config měny nepůjdou smazat. Globální výchozí měna (základ kurzů, `defaultCurrencyId`) zůstává v adminu.
2. **Režim produktových cen**: globální přepínač v commitovaném configu — parametr `shopsys.pricing.product_prices_multicurrency_mode` = `calculated` (default; vedlejší měny kurzem při výpočtu) / `manual` (ceny uložené per měna).
3. **Manuální režim**: ceny povinné ve **všech** měnách domény — all-or-nothing per pricing group. Žádná per-měnová viditelnost/dostupnost (výpisy, filtry, detail i košík fungují v každé měně vždy). `ProductVisibilityRepository` se NEMĚNÍ.
4. **Price VO nese volitelnou měnu** (`?Currency`), add/subtract hlídají mix měn (`CurrencyMismatchException`), GraphQL `Price.currencyCode`.
5. **Dopravy, platby, kupóny, free limit**: explicitní ceny per měna, povinné pro všechny měny domény (nikdy runtime přepočet). Price listy/special prices následují režim produktových cen.
6. **API**: hlavička `X-Currency-Code` na každém requestu; neplatná/nepovolená → tichý fallback na výchozí. Anonymní i přihlášený uživatel: cookie.
7. **Feedy**: generování parametrizované měnou; nastavení per feed (DI tag), pro které měny se generuje; default = výchozí měna domény (BC). Heureka CZK/EUR dle trhu, Mergado třeba vše, Google/Luigi's Box zatím jen výchozí.
8. **Objednávky**: snapshot kurzu do orderu; admin statistiky přepočítávají na výchozí měnu domény; admin edit počítá v měně objednávky.
9. **BC minimalizace**: default `calculated` + jednořádková config změna per doména = projekt bez multi-měn beze změn chování.
10. Po implementaci upgrade notes přes `/generate-upgrade-notes`.

### Ověřená fakta z průzkumu

- Demo výchozí měny: **D1 (en) = EUR, D2 (cs) = CZK, D3 (sk) = EUR**; EUR = globální default (rate 1), CZK rate 0.04 (`SettingValueDataFixture`, `CurrencyDataFixture`).
- Viditelnost už dnes závisí na existenci ceny (`input_price > 0` per pricing group) — all-or-nothing pravidlo ji nechává beze změny.
- ES index: `prices` je `nested` (mapping `project-base/app/src/Resources/definition/product/{1,2,3}.json`); painless skripty v `FilterQuery` matchují `pricing_group_id`.
- `Order` už snapshotuje `currencyCode` + rounding metadata; `OrderFacade::edit` už počítá ze zamrzlých rounding metadat objednávky.
- Přeexport ES po změně kurzu už dnes funguje (`CurrencyEvent::UPDATE` → `DispatchAffectedProductsSubscriber::dispatchAllProducts`).
- Storefront SSR Redis cache (`urql/fetcher.ts`) klíčuje podle pricing group — musí přibýt měna (jinak cross-currency leak na 1h TTL, včetně `SettingsQuery`).
- `GoPayPaymentMethod` už má FK na měnu; `GoPayOrderMapper` posílá `$order->getCurrencyCode()`.
- Feedy se registrují výhradně DI tagem `shopsys.feed` (compiler pass → `FeedRegistry` → `FeedConfig`); jeden `FeedCronModule` s persistencí kontinuace v Setting.
- Migrace umí číst domains.yaml (`DomainAwareInterface` + `MultidomainMigrationTrait`).
- Žádný Varnish/APQ/persisted queries v repu — cache audit čistý, jediné riziko je storefront Redis.

## Pracovní postup (požadavek uživatele)

- **Krok 0**: Tento kompletní plán uložit do repa jako `docs/plans/multi-currency-per-domain.md` a commitnout jako první commit.
- Implementovat po fázích; **každou fázi commitnout zvlášť** se **stručnou jednořádkovou commit message**, **bez Co-Authored-By/Claude-Session patičky** (výslovný požadavek — přebíjí výchozí pravidlo).
- Po každé fázi zelený build (`standards`, `phpstan`, relevantní testy).

## Pořadí commitů (dependency graph)

```
Krok 0: plán → docs/plans/multi-currency-per-domain.md      [commit 1]
P0 config domén → P1 základy (Price VO, mode, provider) → P2 migrace+bootstrap+CurrencyFacade (1 commit)
  → P3 entity+výpočty+order processing (3 sub-commity: produkty | checkout peníze | objednávky+cart)
    → P4 admin formy                → P5 Elasticsearch (nezávislé na P4)
      → A frontend-api (A1–A6)  → S storefront (S1–S7)
        → F feedy → G demo data → H testy dopolish → I docs + upgrade notes (/generate-upgrade-notes)
```

---

## Fáze P0: Konfigurace domén — klíč `currencies`

- `DomainsConfigDefinition` (`packages/framework/src/Component/Domain/Config/`): povinný `arrayNode('currencies')` — neprázdný seznam unikátních 3-písmenných uppercase ISO kódů (`isRequired` + `requiresAtLeastOneElement` + validate/thenInvalid)
- `DomainConfig`: nový poslední ctor parametr `array $currencyCodes = []` (default kvůli testům); metody `getCurrencyCodes(): string[]`, `getDefaultCurrencyCode(): string` (první; `NoCurrenciesConfiguredForDomainException` při prázdném), `hasCurrencyCode(string): bool`
- `DomainsConfigLoader::processDomainConfigArray()` předává klíč
- Demo `domains.yaml`: **D1 `currencies: [EUR, CZK]`** (EUR = dnešní default první!), **D2 `[CZK]`**, **D3 `[EUR]`**
- Testovací yaml konfigy domén napříč repem musí klíč dostat (`grep -r "domains.yaml" packages/*/tests project-base/app/tests`)

## Fáze P1: Základy — Price VO, mode provider, CurrentCurrencyProvider

- `Price` (final, `Model/Pricing/Price.php`): ctor `(Money, Money, ?Currency $currency = null)`; `getCurrency()`; `add/subtract` → privátní `resolveResultCurrency()` (mismatch kódů → nová `Model/Pricing/Exception/CurrencyMismatchException`; null strana adoptuje měnu druhé); `multiply/inverse` propagují; `zero()` neutrální
- `PriceInterface::getCurrency(): ?Currency`; `ProductPrice` deleguje na vnitřní price
- Enum `ProductPricesMulticurrencyModeEnum` (Shopsys `AbstractEnum` — použít skill `create-enum`; MODE_CALCULATED/MODE_MANUAL) + `ProductPricesMulticurrencyModeProvider` (ctor string, validace proti enumu, `isManualMode()/isCalculatedMode()`)
- framework `parameters_common.yaml`: `shopsys.pricing.product_prices_multicurrency_mode: 'calculated'` + binding v `services.yaml`
- **`CurrentCurrencyProvider`** (`Model/Pricing/Currency/`): `setCurrentCurrencyCode(?string)` (null = reset), `getCurrentCurrencyCode()`, `getCurrentCurrencyOfDomain(int): Currency` (explicitní kód pokud nastaven A `DomainConfig::hasCurrencyCode`, jinak `getByCode(getDefaultCurrencyCode())`), `getCurrentCurrencyOfCurrentDomain()`; `reset(): void` + tag `kernel.reset`

## Fáze P2: Migrace DB + bootstrap měn + přepnutí zdroje výchozí měny (JEDEN commit)

**Jedna migrace** (`packages/framework/src/Migrations/`, `DomainAwareInterface` + `MultidomainMigrationTrait`). Pořadí kroků nosné — vše, co čte `setting_values.defaultDomainCurrencyId`, běží PŘED smazáním:
1. INSERT chybějících měn z configu (name=code, rate=1, integer rounding, 2 fraction digits)
2. `orders.currency_exchange_rate NUMERIC(20,6) NOT NULL DEFAULT 1` + backfill `ROUND(c_ord.exchange_rate / c_def.exchange_rate, 6)` přes join na setting_values; pak DROP DEFAULT
3. `product_manual_input_prices.currency_id`: ADD → backfill přes `pricing_groups.domain_id` × setting → NOT NULL → PK `(product_id, pricing_group_id, currency_id)` → FK CASCADE + index. **Bez řádků pro vedlejší měny**
4. `transport_prices.currency_id`: backfill default; unique `(max_weight, domain_id, transport_id, currency_id)`; **bootstrap konvertovaných kopií pro ostatní měny domény** (jednorázově, ať checkout funguje; runtime nekonvertuje)
5. `payment_prices`: totéž, PK `(payment_id, domain_id, currency_id)` + bootstrap
6. `promo_code_limits` (ověřit název tabulky): `currency_id`, PK `(promo_code_id, currency_id, from_price)`; bootstrap — `from_price` konvertovat vždy, `discount` jen u `discount_type='nominal'`
7. Nová `free_transport_and_payment_price_limits (domain_id, currency_id, price, PK(domain_id, currency_id))` + INSERT ze settingu + bootstrap
8. `price_list_product_prices.currency_id`: backfill z domény price listu; unique `(price_list_id, product_id, currency_id)`; bez bootstrapu
9. `carts.currency_code VARCHAR(3) DEFAULT NULL`
10. NAKONEC `DELETE FROM setting_values WHERE name IN ('defaultDomainCurrencyId','freeTransportAndPaymentPriceLimit')`

**Bootstrap v buildu**: `CurrencyDataCreator` (vzor `DomainDataCreator`) + `CreateCurrenciesDataCommand` (`shopsys:currencies-data:create`) + phing target `currencies-data-create`; `domains-data-create` dostane depends (i `test-*` řada). Nová `CurrencyFacade::findByCode()`.

**PricingSetting**: REMOVE `DEFAULT_DOMAIN_CURRENCY` (+2 metody) a `FREE_TRANSPORT_AND_PAYMENT_PRICE_LIMIT` (+2 metody); KEEP `DEFAULT_CURRENCY`.

**CurrencyFacade**: `getDomainDefaultCurrencyByDomainId()` — **jméno zůstává**, reimplementace přes `DomainConfig::getDefaultCurrencyCode()` (tím se „samo" opraví ~30 call sites s legitimní sémantikou výchozí měny: feedy, admin twig, GiftPlan, PriceConverter…); nová `getEnabledCurrenciesByDomainId(int): Currency[]` (pořadí dle configu); REMOVE `setDomainDefaultCurrency`; `getNotAllowedToDeleteCurrencyIds()` = config měny všech domén + globální default + měny v objednávkách.

**Admin redukce**: `CurrencySettingsFormType` bez `domainDefaultCurrencies`; `CurrencyController::settingsAction` jen globální default; šablona currencySettings.

**Fixture cleanup** (musí do stejného commitu): `SettingValueDataFixture` — DELETE `setDomainDefaultCurrency()` (ř. 46, 148–156); free limit přes novou fasádu per měna (CZK 50000 raw + EUR konverze stejnou cestou jako dnes → bitově identické hodnoty).

## Fáze P3: Per-měnové entity + výpočty + order processing (3 sub-commity)

### Sub-commit 3.1 — produktové ceny
- **ProductManualInputPrice**: třetí `#[ORM\Id]` FK `$currency` (+`#[AsMcpColumn]`); ctor `(Product, PricingGroup, Currency, ?Money)`; repo `findByProductPricingGroupAndCurrency()`; `ProductInputPriceFacade::getManualInputPricesDataIndexedByDomainIdPricingGroupIdAndCurrencyCode()`; `ProductManualInputPriceFacade::refreshProductManualInputPrices` iteruje PG × měny, maže neposlané; `ProductInputPriceData::$manualInputPricesByPricingGroupIdAndCurrencyCode` (`array<int, array<string, Money|null>>`)
- **ProductPriceCalculation**: ctor += `CurrentCurrencyProvider` + `ProductPricesMulticurrencyModeProvider`; `calculateProductPriceForPricingGroup()`: manual → řádek current měny; calculated → řádek default měny + `multiply(getExchangeRateForCurrencies(default, current))` při current ≠ default; rounding VŽDY dle current měny. **Veřejná signatura `calculatePrice()` beze změny** (žádná kaskáda přes ProductFacade/cart/orders)
- `BasePriceCalculation::calculateRoundedBasePrice(..., ?Currency $currency = null)` — stampuje měnu do Price VO
- `QuantifiedProductPriceCalculation`: swap 2× default → provider (ř. 52, 159)
- **SpecialPrice**: repository metody += `Currency` (where na `pwp.currency`); facade interně manual → řádky current měny; calculated → default řádky + konverze; `SpecialPriceFactory::createWithCalculations(+Currency $targetCurrency, ?Currency $sourceCurrency = null)` — stejný kurz pro basic i special zachová uspořádání
- Ponechat domain default (vyřešeno reimplementací CurrencyFacade): `InputPriceRecalculator` (payments nově per-řádek `$paymentInputPrice->getCurrency()`), `PriceConverter`, `Twig/PriceExtension`, `GiftPlan*`

### Sub-commit 3.2 — checkout peníze
- **TransportPrice**: FK `$currency` NOT NULL CASCADE; ctor `(Transport, Money, int, ?int, Currency)`; unique += currency_id; repo/facade `getTransportPriceOnDomainByTransportAndClosestWeight(+Currency)`; `TransportPriceCalculation::calculatePrice(+Currency)`; `calculateIndependentPrice()` rounding z `$transportPrice->getCurrency()`
- **PaymentPrice**: třetí `#[ORM\Id]` `$currency`; `Payment::setPrice/getPrice/hasPriceForDomainAndCurrency(+Currency)`; `PaymentPriceCalculation::calculatePrice(Payment, PriceInterface, int, Currency, bool)` a `calculateIndependentPrice(Payment, int, Currency)` — rounding params nahrazeny měnou; `PaymentFacade::get*IndexedByDomainIdAndCurrencyCode()` renames + `updatePaymentPrices()`
- **FreeTransportAndPaymentPriceLimit**: nová entita + repo (`Model/TransportAndPayment/`), PK `(domainId, currency)`; `FreeTransportAndPaymentFacade::isActive/isFree/getRemainingAmount/isFreeTransportAndPaymentApplied(+Currency)`; `setPriceLimits()`
- **PromoCodeLimit**: `$currency` `#[ORM\Id]` FK; ctor `(string $from, string $discount, Currency)`; repo/facade `getHighestLimitByPromoCodeAndTotalPrice(+Currency)`

### Sub-commit 3.3 — objednávky + cart
- `OrderData::$currencyExchangeRate` + `Order::$currencyExchangeRate` (decimal 20,6, `#[AsMcpColumn]`, getter, `OrderDataFactory::fillFromOrder` kopíruje); nová `CurrencyFacade::getExchangeRateToDomainDefaultCurrency(Currency, int): string`
- **`OrderProcessor::process()`**: pokud `orderData->currencyCode === null` → `fillCurrencyFieldsFromCurrency(provider)` + exchangeRate — jediný hook pokryje web checkout, cart providery, CartWatcher; FE-API OrderDataFactory předvyplní (non-null vyhrává); admin edit snapshotuje z existující objednávky = měna objednávky
- Middlewary: `getDomainDefaultCurrencyByDomainId` → `currencyFacade->getByCode($orderProcessingData->orderData->currencyCode)` (`AddPaymentMiddleware`, `AddTransportMiddleware`, `ApplyPercentage/NominalPromoCodeMiddleware`, `AbstractPromoCodeMiddleware` — předává Currency do limit lookup, `FreeTransportAndPaymentInformationMiddleware`); `AddRoundingMiddleware` použije rovnou zamrzlé `orderData->currencyRoundingType` (drop lookup)
- `OrderItemsType` (admin edit náhledy ř. 80): transport/payment ceníky v měně objednávky
- **Statistiky**: `StatisticsRepository::getOrdersValueBetweenDates` → `SUM(o.total_price_with_vat * o.currency_exchange_rate)` (drop join na live kurzy; jediný konzument, grep-ověřeno)
- **GoPay**: `GoPayPaymentMethodFacade::downloadAndUpdatePaymentMethods` — smyčka přes enabled měny, reconcile per (domain, currency); **viditelnost plateb**: platba s gopay metodou v měně ≠ current se odfiltruje (v PaymentVisibilityCalculation-ekvivalentu; FE-API validace pokryta transitivně)
- **Cart watched prices**: `Cart::$currencyCode` (nullable, `#[AsMcpColumn]`); `CartWatcherFacade::getCheckedCartWithModifications` — první akce reset watched prices při změně měny (items + transport + payment → null, nastavit kód, flush); `CartWatcher` ošetří `watchedPrice === null` jako „ještě nehlídáno" (nastaví bez flagu); stejný guard v `TransportValidationFacade`/`PaymentValidationFacade` (frontend-api)

## Fáze P4: Admin formuláře + validace

- **Produkt** (`PricesByPricingGroupsType` + `ProductPricesWithVatSelectType`): calculated → jako dnes (bind `[pricingGroupId][defaultCode]` přes transformer); manual → compound per pricing group s MoneyType per měna (required: false). **Nový constraint `PricesForAllCurrenciesOrNone`** (+Validator): all-null nebo all-filled per pricing group; hláška „Enter the price for every currency of the domain, or leave all empty"
- **Transport**: `PriceWithLimitData::$pricesByCurrencyCode` + `$transportPriceIdsByCurrencyCode`; `PriceWithLimitType` compound, NotBlank per měna; `TransportInputPricesDataFactory` grupuje řádky dle maxWeight; `TransportFacade::updateTransportPrices` řádek per (limit × měna)
- **Payment**: `PriceAndVatTableByDomainsType` — VAT per doména, MoneyType per měna (NotBlank + NotNegativeMoneyAmount); `PaymentData::$pricesIndexedByDomainIdAndCurrencyCode`
- **PromoCode limity**: per-currency ladder UI (žebříček per měna; procentní discount sdílený, nominal per měna); `PromoCodeLimitTransformer` 1:1 na řádky; all-or-nothing constraint napříč žebříčky
- **Free limit**: `FreeTransportAndPaymentPriceLimitsFormType` — enabled checkbox + MoneyType per měna (NotBlank v enabled skupině); controller přes novou fasádu
- **PriceList CSV**: `PriceListCsvColumnsEnum` += `currency_code` (jen manual: povinný, validace kódu, all-or-nothing per catnum post-parse do `ImportPriceListResult`); calculated → beze změny formátu, řádky = default měna; export sloupec v manual

## Fáze P5: Elasticsearch

- Mapping `product/{1,2,3}.json`: `prices.properties.currency_code: keyword` (variant_prices dědí měnu rodiče) + `special_prices.prices.properties.currency_code`
- `ProductExportRepository::extractPrices/extractSpecialPrices`: smyčka přes `getEnabledCurrenciesByDomainId` s `try { provider->set(code); … } finally { provider->set(null); }`; entry += `currency_code`; calculated/manual vypadne z `ProductPriceCalculation` automaticky
- `FilterQuery`: ctor += `string $currencyCode`; painless `&& price['currency_code'] == params['currency_code']` (u stringů `==`, ne `===`!) ve 3 runtime fields + special_prices continue-guard; `FilterQueryFactory` ctor += provider
- `ProductFilterConfigIdsDataFactory` (ř. 69–70): rounding min/max dle current měny
- `PriceFactory::createProductPriceFromArrayByPricingGroup(+?string $currencyCode)` — match i na `currency_code`
- Re-export triggery: `CurrencyEvent::UPDATE` už pokryt; přidat `CurrencyEvent::CREATE` do `DispatchAffectedProductsSubscriber`; ověřit `ProductExportScopeConfig::SCOPE_PRICE`

## Fáze A: Frontend API

**A1. Přenos hlavičky — jediný vstupní bod**: nová `Component/Currency/CurrencyHeaderInitializer` (konstanta `HEADER_CURRENCY_CODE = 'X-Currency-Code'`; bez validace — fallback řeší provider). Volat jako první řádek v `FrontendApiController::endpointAction()` I `batchEndpointAction()` (vzor `DetectionFacade`). NE kernel listener (pálil by i mimo API), NE Overblog `PRE_EXECUTOR` (běží per operaci v batchi). **Explicitní registrace v services.yaml** (suffix `Initializer` není v auto-registration globu — stejný důvod jako `GqlContextInitializer`).

**A2. Settings schema**: `PricingSettingsQuery` — `defaultCurrencyCode` zůstává domain default (BC), nové `currentCurrencyCode` (efektivní měna), `minimumFractionDigits` nově dle CURRENT měny (dokumentovaná sémantická změna; bez hlavičky beze změny), nové `availableCurrencies: [CurrencySetting!]!` — `{code, name, minFractionDigits}` v pořadí configu (bez symbolu — entita ho nemá, Intl ho odvodí z kódu). Nový `CurrencySettingDecorator.types.yaml` (frontend-api) + `CurrencySetting.types.yaml` (project-base, vzor Price.types.yaml).

**A3. `Price.currencyCode: String!`**: `PriceDecorator.types.yaml` s `resolve: "@=query('currencyCodeByPriceQuery', value)"`; nová `Model/Resolver/Price/PriceCurrencyCodeQuery` — PriceInfo?->currencyCode → Price VO->getCurrency() → fallback provider. `PriceInfo::$currencyCode` (nullable public prop). `ProductPrice` dědí přes `inherits: ['Price']`. HiddenMoney ceny fallback pokryje.

**A4. Objednávkové ceny v měně objednávky**: nová `PriceWithCurrencyFactory::createWithCurrencyCode(PriceInterface, string)` (getByCode bezpečné — měny objednávek nesmazatelné); `OrderResolverMap::totalPrice` a `OrderItemResolverMap::unitPrice/totalPrice` wrapují s `$order->getCurrencyCode()`; `OrderDecorator.types.yaml` += `currencyCode: String!` + `exchangeRate: String!` (starý CZK order při zvoleném EUR nesmí hlásit EUR!)

**A5. Sweep hardcoded defaultů** (grep-ověřeno, jen 3 hity): `PricingSettingsQuery:26` (A2), `PriceQuery:77` (`calculateIndependentPaymentPrice` → provider; order-branch ř. 47–58 wrap měnou objednávky — ChangePaymentInOrder), `OrderDataFactory:55` (→ provider; **update `@method` anotace v project-base `FrontendApi/Model/Order/OrderDataFactory`**). Dále: `PriceFacade::createProductPriceFromArrayForCurrentCustomer` — filtr ES entries dle current currency (BC-tolerantní: entry bez `currency_code` projde); `SpecialPriceApiFactory::findRelevantSpecialPrice` — stejný filtr (jinak cross-currency porovnání!); sdílený helper (pozor na services glob — suffix `Filter` mimo glob, registrovat explicitně či pojmenovat `...FilterHelper`).

**A6. Schema snapshot**: `make generate-schema` — čistě aditivní diff (Price.currencyCode, ProductPrice.currencyCode, PricingSetting.currentCurrencyCode + availableCurrencies, CurrencySetting, Order.currencyCode + exchangeRate).

## Fáze S: Storefront

**S1. Cookie** — `utils/currency/currencyCookie.ts` (vzor auth cookies): název `currencyCode-{domainId}` přes `getCookieName`, maxAge 1 rok, get/set s optional SSR context. **Cookie-only, žádný Zustand** (localStorage není čitelný na serveru → SSR mismatch; jediný ne-React konzument je urql klient; multi-tab konzistence přes broadcast). Efektivní měna pro UI = `settings.pricing.currentCurrencyCode`.

**S2. urql klient** — `createClient.ts`: `export const CURRENCY_CODE_HEADER = 'X-Currency-Code'`; číst cookie (SSR z `context.req`, client z `document.cookie` přes cookies-next) a podmíněně přidat do `fetchOptions.headers`. Pokrývá SSR prefetch i všechny client operace (header na úrovni klienta).

**S3. SSR Redis cache klíč** — `fetcher.ts`: `currencyBucket` z hlavičky do klíče **VŠECH** kešovaných queries (i plain `@redisCache` — `SettingsQuery` nese currentCurrencyCode/digits!); „bez cookie" a „cookie=default" = 2 buckety se stejným obsahem (přijatelná duplikace). Update komentáře v `graphql/requests/directives.graphql`.

**S4. CurrencySwitcher** — nová `components/Layout/Header/CurrencySwitcher/CurrencySwitcher.tsx`: čte `useSettingsQuery` (availableCurrencies + currentCurrencyCode); **render null při ≤1 měně**; switch: `setCurrencyCodeToCookies` + `dispatchBroadcastChannel('reloadPage', domainId)` + `router.reload()` (vzor logout — graphcache nekóduje hlavičky, reload = čerstvé SSR HTML bez flickeru, prázdná cache, košík v nové měně). Umístění: `Header.tsx` (desktop, vedle `DeferredMenuIconic`) + `MobileMenuContent.tsx`. TIDs: `header_currency_switcher`, `header_currency_switcher_option_` + `cypress/tids.ts`. Překlady en/cs/sk.

**S5. GraphQL dokumenty**: `PricingSettingFragment` += currentCurrencyCode + availableCurrencies; `PriceFragment` += currencyCode (propaguje do všech 15 dokumentů vč. order fragmentů); `ProductPriceFragment` + `ListedProductPriceFragment` += currencyCode (inline, nested basicPrice netřeba); `OrderDetailFragment` + `ListedOrderFragment` += currencyCode; `cacheExchange.ts` keys += `CurrencySetting: keyCode`; `pnpm run gql`.

**S6. Formátování cen**: `useFormatPrice` — nový options param `currencyCode?`; default = `currentCurrencyCode ?? domainConfig.currencyCode`; digits pro cizí měnu z `availableCurrencies` (miss → undefined → Intl default, pokrývá objednávky v mezitím vypnutých měnách); `formatPrice.ts` přijme `number | undefined`. **Komponenty s per-price měnou** (order-scoped, audit 24 konzumentů): OrderDetailBasicInfo, OrderDetailOrderItem, Orders/OrderItem, OrderedItems/OrderedItem, OrderItemProductPrice, OrderItemDiscountCard, ComplaintDetailComplaintItem, PersonalDataDetailContent, OrderConfirmationSummary. Zbytek (produkty, košík, doprava/platba, filtr) dědí current měnu automaticky. Pravidlo do docs: nikdy nerenderovat ceny z `domainConfig.currencyCode` (build-time fallback).

**S7. GTM + SEO sweep**: nový hook `useCurrentCurrencyCode` (`settings ?? domainConfig`); `getGtmMappedCart`, `getGtmCreateOrderEvent` (z `order.currencyCode` — snapshot-correct), `getGtmPageReadyEvent`, `getGtmCreateWatchDogEvent`, `useGtmProductDetailViewEvent`, `ProductMetadata.tsx` (schema.org priceCurrency). Vlastní commit — degraduje jen analytiku, ale GA revenue by jinak měla špatnou měnu.

## Fáze F: Feedy per měna

**Konfigurace = rozšíření DI tagu `shopsys.feed`** o atribut `currencies`:
- vynechán → `[výchozí měna domény]` (BC); `'all'` → všechny měny domény; `'EUR,CZK'` → průnik; `{ 1: [EUR, CZK] }` → per doména (neznámý kód → `FeedCurrencyNotEnabledOnDomainException`)
- `RegisterProductFeedConfigsCompilerPass` → `FeedRegistry::registerFeed(+$currenciesConfig)` → `FeedConfig::getCurrenciesConfig()`; nová `FeedCurrencyResolver::resolveCurrencyCodes(FeedConfig, DomainConfig): string[]` (výchozí první)

**Generování**:
- `FeedPathProvider`: výchozí měna = dnešní název (BC pro agregátory); vedlejší → suffix `_czk`
- `FeedExport`: ctor += `CurrentCurrencyProvider` + `string $currencyCode`; scope **uvnitř `generateBatch()`** s `finally` resetem (mezi batchi běží `ServicesResetter` + `em->clear()`)
- `FeedExportFactory::create(+?string $currencyCode)`; `FeedFacade::generateFeed(+?string)` (null = všechny nakonfigurované), nová `getCurrencyCodesForFeed()`; `getFeedUrl/Filepath/Timestamp(+?string)`
- `FeedExportCreationDataQueue`: položky (FeedModule × měna) + `getCurrentCurrencyCode()` + `isCurrentLastCurrencyOfFeedModule()`
- `FeedCronModule`: **KRITICKÉ — unschedule až u poslední měny feedu** (jinak sleep/wake tiše přeskočí zbylé měny); sleep/wakeUp persistují měnu — nová `Setting::FEED_CURRENCY_CODE_TO_CONTINUE` + seed migrace; log s měnou
- `FeedController` + grid: řádek per (feed, doména, měna), label `(CZK)` jen při >1; route generate `/{currencyCode?}`

**Item factories** (swap default → provider): `GoogleFeedItemFactory`, `MergadoFeedItemFactory` (ř. 36), `LuigisBoxProductFeedItemFactory` (ř. 106–109). **Heureka + Zboží bez změny** (tečou přes ProductPriceCalculation — ověřeno). `heureka-delivery` (id + stock only) a article/brand/category-luigis-box (bez cen) beze změny.

**Demo** `project-base/app/config/feed.yaml`: mergado `currencies: 'all'`, heureka `currencies: { 1: [EUR, CZK] }`.

## Fáze G: Demo data + fixtures

- `TransportDataFixture::setPriceForAllDomains` + `PaymentDataFixture` (ř. ~110/216): per-currency smyčka přes `getCurrencyCodes()`; výchozí měna bitově identická s dneškem (stejná konverzní cesta) → Cypress snapshoty přežijí
- `PromoCodeDataFixture`: limity per měna (fromPrice vždy, nominal discount per měna, procenta sdílená); call sites dostanou domainId
- `ProductDataFixture` + `ProductDemoDataSetter::setPriceForAllPricingGroups` (ř. 95): shape `[pricingGroupId][currencyCode]` (jen default měna — calculated režim); `PriceListDataFixture`: set `currency` = default
- `GoPayDataFixture` (ř. 158): per-currency metody
- `OrderDataFixture`: +1–2 demo objednávky v CZK na D1 (`fillCurrencyFieldsFromCurrency` + exchange rate) — ukázka + reference pro testy; `CartDataFixture`: currencyCode volitelně
- Performance fixtures: `OrderDataFixture` ř. 179 (CZK na D1) nyní validní — jen ověřit

## Fáze H: Testy

**Nové unit** (packages/framework/tests/Unit): `PriceTest` (mismatch/propagace/zero), `CurrentCurrencyProviderTest` (fallback/validace/reset), `ProductPriceCalculationTest` (calculated konverze + cílový rounding; manual čtení řádku), `DomainsConfigDefinitionTest` (chybějící klíč/prázdný/duplicity/formát), `FeedCurrencyResolverTest`, `FeedPathProviderTest`, `FeedCronModuleTest` (expanze, unschedule po poslední měně, sleep/wake persistence), `BasePriceCalculationTest` (+currency param).

**Nové funkční** (project-base): `ProductPriceSecondaryCurrencyTest` (oba režimy; manual přes test param), `ProductExportRepositoryTest` (entry per PG × měna), `OrderSecondaryCurrencyTest` (snapshot kód+kurz+rounding), `ProductManualInputPricesValidationTest` (all-or-nothing), `CurrencyDeletionGuardTest`, `CreateCurrenciesByConfigCommandTest` (idempotence), `PaymentPriceCalculationTest` + rozšíření `TransportPriceCalculationTest` (CZK scope).

**Nové API funkční** (FrontendApiBundle; hlavička přes `HTTP_X_CURRENCY_CODE` server param): `PricingSettingCurrencyTest` (bez hlavičky/vedlejší/garbage/nepovolená → fallback bez errors; batch smoke), `ProductPriceCurrencyTest` (currencyCode + konvertované částky, listing přes ES), `CartCurrencyTest` (totals v měně; stejný cart uuid bez hlavičky → default = bezstavovost; promo discount), `CreateOrderCurrencyTest` (mutation response + entita: currencyCode, exchangeRate; re-fetch s jinou hlavičkou → ceny stále v měně objednávky).

**Storefront**: Vitest `formatPrice.test.ts` (zero/Free, digits, undefined→Intl default), `createClient.test.tsx` (header z cookie / bez cookie), fetcher cache-key test (různé měny → různé Redis klíče). Cypress `e2e/currency/currencySwitcher.cy.ts`: switch → reload → € ceny ve výpisu/košíku/dopravě, cookie persistence, zachování košíku, skrytí na single-currency doméně; TID selektory, snapshot registrace.

**Známé rozbité testy k úpravě** (grep-ověřeno): `MiddlewareTestCase:88` (stub swap + nový helper `createCurrentCurrencyProvider()` → opraví 12 middleware testů), `TransportPriceCalculationTest:72`, `ApplyNominalPromoCodeMiddlewareTest:150`, feed testy google:100/mergado:169/luigis-box:99, `FeedCronModuleTest`, `PriceExtensionTest:205`, ES testy (`FilterQueryTest`, `ProductOnCurrentDomainElasticFacadeCountDataTest`, `ProductElasticsearchConverterTest`), FrontendApiBundle price/cart/order/transport/payment testy + `GraphQlTestCase::getConvertedPriceToDomainDefaultCurrency` (+ nový sibling helper pro vedlejší měnu), Smoke+Performance `AllFeedsTest` (iterace × měna, assert `_czk` suffix).

## Fáze I: Dokumentace + upgrade notes

- Docs: `docs/introduction/how-to-set-up-domains-and-locales.md` (klíč `currencies`), `docs/model/product-feeds.md` (multi-currency feedy), `docs/model/elasticsearch.md` (prices per měna), `docs/model/how-to-work-with-money.md` (Price VO + CurrencyMismatchException), `docs/introduction/order-processing.md`, `docs/introduction/basic-and-demo-data-during-application-installation.md`, `docs/introduction/console-commands-for-application-management-phing-targets.md`, frontend-api článek o volbě měny (hlavička + cookie do cookie-consent seznamu)
- Upgrade notes: snippet `upgrade-notes/backend_YYYYMMDD_HHMMSS.md` (+ storefront_*) dle `_template.md` — **vygenerovat přes `/generate-upgrade-notes`**

## Kompletní seznam BC breaků (podklad pro upgrade notes)

1. `domains.yaml` — povinný klíč `currencies` (první = dosavadní výchozí měna!); PŘED spuštěním migrací
2. `DomainConfig::__construct` +param; Definition/Loader změněny
3. `PriceInterface::getCurrency()`; `Price::__construct(+?Currency)`; add/subtract → `CurrencyMismatchException`
4. REMOVED: `PricingSetting::DEFAULT_DOMAIN_CURRENCY` + 4 metody, `CurrencyFacade::setDomainDefaultCurrency`; `getDomainDefaultCurrencyByDomainId` čte config (stejná sémantika)
5. DB: smazané setting rows; nové PK/sloupce (product_manual_input_prices, transport_prices, payment_prices, promo_code_limits, price_list_product_prices); nové `free_transport_and_payment_price_limits`, `orders.currency_exchange_rate`, `carts.currency_code`; bootstrap konverze JEDNORÁZOVĚ — admin zkontroluje
6. Entity ctors vyžadují Currency (5 entit + factories)
7. Signatury: BasePriceCalculation, Transport/PaymentPriceCalculation, Payment get/setPrice, FreeTransportAndPaymentFacade (4), TransportPriceRepository/Facade, PromoCodeLimitRepository/Facade, SpecialPriceRepository/Factory, PriceFactory, FilterQuery ctor, PaymentFacade renames
8. Data objekty: ProductInputPriceData, PaymentData, PriceWithLimitData reshape
9. OrderProcessor snapshot chování; custom middlewary čtou měnu z OrderData
10. ES mapping změna → **plný reindex povinný**; custom FilterQuery rozšíření přidají currency match
11. Admin formy per měna; PriceList CSV `currency_code` (manual)
12. Nový phing target `currencies-data-create` (projekty s vlastním build.xml zrcadlí)
13. Nový parametr `shopsys.pricing.product_prices_multicurrency_mode`
14. Feed vrstva: signatury FeedExport/Factory/Facade/PathProvider/CronModule + item factories; `FeedCronModule` ctor
15. FE-API: ctor změny (PricingSettingsQuery, PriceQuery, PriceFacade, OrderDataFactory, resolver mapy, FrontendApiController); `PricingSetting.minimumFractionDigits` nově dle current měny (bez hlavičky beze změny); nová cookie `currencyCode-{domainId}`
16. Storefront: `useFormatPrice` signatura options; fetcher cache klíč

## Verification

- Po každé fázi: `docker compose exec php-fpm php phing standards phpstan` + relevantní testy (`tests-unit`, `tests-functional`, FE-API)
- Po GraphQL změnách: `make generate-schema`; storefront `pnpm run check--fix` + `pnpm run test`
- ES změny: `elasticsearch-index-recreate` + export v rámci `build-demo-dev-quick`
- Funkčně (demo D1 EUR+CZK): přepínač v hlavičce, ceny výpis/detail/filtr/košík v CZK, objednávka v CZK (snapshot + kurz), admin order detail/statistiky, feed soubory (`mergado_1.xml` + `mergado_1_czk.xml`, heureka obě měny), admin formy (transport/payment/promo/free limit per měna), currency settings bez per-domain selectu
- Na závěr **střídmě** e2e přes `agent-browser` CLI (žere kontext): přepnutí měny, cena na detailu, košík, checkout v CZK
- Nakonec `/generate-upgrade-notes`
