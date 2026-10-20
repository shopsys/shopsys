# Ukládání analytických dat e-shopu – implementační plán

## Přehled

Shopsys chce mít na jednom místě statistická data o e-shopu (Jira [SSP-4480](https://shopsys.atlassian.net/browse/SSP-4480)): provozní data, která vznikají přímo v e-shopu (hledané fráze, kliky z našeptávače, později oblíbené, porovnání), a agregovaná data třetích stran (Google Analytics, Search Console, Google Ads, Meta, Heureka, Zboží, Sklik). Data se mají zobrazovat v administraci konkrétního e-shopu a mají být dostupné AI agentům přes MCP.

Plán vychází z výkopu s Lukášem Fibigerem (19. 10. 2026) a navazující diskuse. Navazující tickety: [SSP-4481](https://shopsys.atlassian.net/browse/SSP-4481) Statistika vyhledávání (první konzument), [SSP-4482](https://shopsys.atlassian.net/browse/SSP-4482) Správa synonym.

Rozhodnutí v kostce:

- **Úložiště je Postgres**, samostatný server v cloudu Shopsys, **jedna databáze per projekt**. Žádná sdílená multitenantní DB, žádné BigQuery.
- **Žádné API.** E-shop i externí konektory zapisují a čtou přímo do DB přes vlastní DB role s úzkými právy.
- **E-shop zapisuje asynchronně** přes Symfony Messenger. Výpadek analytické DB nikdy nesmí ovlivnit e-shop.
- **Provozní data sbírá e-shop server-side** (resolvery Frontend API), ne přes GTM. GTM a GA zůstávají pro marketingové nástroje jako dnes.
- **Schéma vlastní platforma** (balíček `shopsys/analytics`), konektory třetích stran jen plní předem definované tabulky.
- **Admin a MCP čtou z denních rollupů**, ne z raw dat.
- **Konektory třetích stran běží jako cron kontejner** vedle DB v cloudu, ne na lokálních strojích.

## Současný stav

- Platforma nemá žádné logování hledaných frází ani jiných behaviorálních dat. Jediná statistika v administraci je dashboard (`packages/framework/src/Model/Statistics/`), počítaný živě SQL nad hlavní DB.
- Sledování chování je výhradně klientské přes GTM dataLayer (`project-base/storefront/gtm/`): eventy `ec.autocomplete_results_view`, `ec.autocomplete_result_click` (obsahují `keyword`), `ec.product_click` s `listIndex` (1-based, `mappers/mapGtmListedProductType.ts`), `ec.add_to_wishlist`, `ec.add_to_comparsion` a další. Stránka výsledků hledání nemá frázi v dataLayer vůbec (jen `product_list_view` se jménem listu `search results`). Žádné server-side měření (sGTM, Measurement Protocol) neexistuje.
- Luigi's Box (`packages/luigis-box`) je zapínán per doména přes `LUIGIS_BOX_ENABLED_DOMAIN_IDS`; analytiku hledání drží jen ve svém systému.
- MCP server (`packages/mcp`) vystavuje read-only SQL nad hlavní DB přes **druhou Doctrine connection `mcp`** (`project-base/app/config/packages/doctrine.yaml`), s vlastní read-only rolí (`McpReadOnlyUserManager`), `statement_timeout` (`McpSessionSettingsDriver`) a validátorem SQL nad whitelistem tabulek a sloupců (`SqlQueryValidator`). Dostupnost se řídí přítomností env proměnných (`McpAvailabilityChecker`).
- Každý projekt má vlastní RabbitMQ vhost a Messenger transporty (`project-base/app/config/packages/messenger.yaml`) a cron instance v Kubernetes (`project-base/app/deploy/deploy-project.sh`, `CRON_INSTANCES`). Cron moduly se registrují tagem `shopsys.cron` se Sentry monitoringem (`project-base/app/config/cron.yaml`).
- Žádný centrální server Shopsys neexistuje, každý projekt je samostatné nasazení. Jediná vnější integrace je Data Bridge (`DATA_BRIDGE_*`).
- Composer neobsahuje klienty pro GA, GSC, Google Ads ani Meta. Elasticsearch definice nemají žádný synonym filtr.

## Cílový stav

- Projekt má v konfiguraci třetí Doctrine connection `analytics` (vzor `mcp`) směřující na vlastní databázi na analytickém Postgres serveru. Když env proměnné chybí, analytika je vypnutá a aplikace běží beze změny.
- Balíček `packages/analytics` (`Shopsys\AnalyticsBundle`) dodává: definici schématu a command pro jeho založení, Messenger zprávu a handler pro asynchronní zápis, writer s dávkovým upsertem, cron moduly pro rollupy a retenci, DBAL data source pro admin grid.
- Hledání a našeptávač logují každý dotaz jedním řádkem per request (doména, fráze, typ, čas, počty výsledků per typ entity), kliky z výsledků se logují s pozicí. Boti se nelogují. Admin sekce Marketing zobrazuje přehled frází podle SSP-4481.
- Tabulky pro data třetích stran (`traffic_source_daily` a další) jsou součástí verzovaného schématu platformy. Plní je samostatný worker `shopsys/analytics-worker` běžící jako Kubernetes CronJob s přístupem do DB přes roli s právy jen na tyto tabulky.
- MCP server projektu nabízí tool pro validované read-only SQL nad analytickou DB.

### Klíčová zjištění

- `packages/mcp/src/Component/Availability/McpAvailabilityChecker.php` – vzor pro „funkce je zapnutá, jen když jsou env proměnné“.
- `project-base/app/config/packages/doctrine.yaml:16` – connection `mcp` ukazuje, jak přidat connection s vlastními `env()` přihlašovacími údaji; `server_version` je nutné uvést, aby se DIC nepřipojoval k DB při kompilaci.
- `project-base/app/deploy/deploy-project.sh:35` – env proměnné se do K8s předávají výčtem (`MCP_DATABASE_USER`), analogicky přibudou `ANALYTICS_DATABASE_*`.
- Hledání i našeptávač jsou čtyři nezávislé GraphQL fieldy v jednom requestu (`productsSearch`, `categoriesSearch`, `articlesSearch`, `brandSearch` v `packages/frontend-api/src/Resources/config/graphql-types/Query/QueryDecorator.types.yaml`). Každý resolver (`Products/Search/ProductSearchQuery.php`, `Category/Search/CategoriesSearchQuery.php`, `Article/Search/ArticlesSearchQuery.php`, …) zná jen svůj počet výsledků; `SearchResultsProviderResolver` pouze vybírá providera (Elasticsearch / Luigi's Box) podle entity. Jeden řádek logu s počty za všechny typy proto vyžaduje request-scoped kolektor.
- `SearchInput` (`ModelType/Search/Input/SearchInputDecorator.types.yaml`) už nese povinné `userIdentifier: Uuid!` (storefront ho generuje do cookie a posílá při každém hledání, dnes ho používá jen Luigi's Box) a `isAutocomplete: Boolean!`. Je to perzistentní identifikátor návštěvníka, ne session, proto se hashuje s denní solí.
- `productsSearch` se volá znovu při každé změně filtru a stránkování; bez ošetření by každé kliknutí na filtr vytvořilo nový řádek hledání.
- `packages/framework/src/Model/Product/Recalculation/ProductRecalculationMessageHandler.php` – vzor dávkového handleru (`BatchHandlerInterface`).
- `packages/frontend-api/src/Model/Mutation/ProductReview/CreateProductReviewMutation.php` a `project-base/app/config/packages/rate_limiter.yaml` – vzor rate limitu veřejné mutace.
- `project-base/storefront/urql/createClient.ts:46` – storefront při SSR posílá do Frontend API jen `X-Forwarded-Proto`, User-Agent klienta se nepřeposílá; server tedy dnes neumí při SSR rozeznat bota.
- `project-base/app/config/domains.yaml` – každá doména má vlastní `timezone`; den v rollupu se musí počítat v zóně domény.
- `project-base/storefront/gtm/handlers/onGtmProductClickEventHandler.ts` a `onGtmAutocompleteResultClickEventHandler.ts` – místa, kde storefront zná kliknutý produkt a jeho pozici; vedle GTM eventu odtud půjde first-party zápis do e-shopu.
- `packages/framework/src/Component/Grid/DataSourceInterface.php` – admin grid stojí nad ORM (`QueryBuilderDataSource`) nebo polem (`ArrayWithPaginationDataSource`); pro DBAL dotaz nad analytickou connection je potřeba nový data source.
- `packages/framework/src/Controller/Admin/AutocompleteController.php` – sekce Autocomplete favorites, vedle níž má podle SSP-4481 vzniknout Statistika vyhledávání.
- Materialized view v Postgresu nejde obnovovat po dnech, proto rollupy řeší `INSERT … SELECT … ON CONFLICT DO UPDATE` nad klouzavým oknem.
- DBAL `Schema` objekty neumí `PARTITION BY` ani založení partition; partitioning potřebuje vlastní SQL vrstvu. `CREATE TABLE IF NOT EXISTS` neřeší přidání sloupce v další verzi, schéma potřebuje verzování od začátku.

## Co neděláme

- BigQuery ani jiný sloupcový sklad. Pokud by jeden projekt přerostl desítky TB nebo potřeboval ad-hoc analytiku nad raw eventy, je další krok ClickHouse jako výměna úložiště za DBAL connection.
- Sdílená multitenantní databáze, `tenant_id`, row-level security.
- HTTP API pro zápis nebo čtení. Oddělení klientů řeší DB per projekt a DB role.
- Server-side GTM, Measurement Protocol, změny ve stávajících GTM eventech.
- Grafana jako proxy pro byznysová data. Grafana zůstává zdrojem pro infrastrukturní metriky a případný interní cross-client pohled nad analytickými DB.
- Správa synonym (SSP-4482) – samostatný plán, netýká se analytické DB (jen z ní předvyplňuje fráze).
- Správa konektorů klientem v administraci (zadání GA property apod.) – zatím konfigurace u nasazení workeru.

## Přístup k implementaci

Práce probíhá v balíčku `packages/analytics` (package-first), v `project-base` jen konfigurace (doctrine, messenger, cron, env, deploy). Větev `rv/analytics-data-storage` nad `20.0`. Fáze 1–3 pokrývají SSP-4480 a datovou i UI část SSP-4481 a tvoří jeden PR nebo dva stacked PR (balíček + logování; rollup + admin). Fáze 4 je samostatný repozitář, fáze 5 samostatný PR.

Umístění DB je jen DSN: lokálně a na review prostředích je analytická DB druhou databází v kontejneru `postgres` projektu, v produkci na centrálním serveru. Kód je stejný.

Zásady pro schéma:

- Tabulky s raw eventy jsou **append-only** a partitionované po měsících podle času události, s `DEFAULT` partition jako záchrannou sítí. Retence je `DROP TABLE` partition, ne `DELETE`. Žádná odvozená logika (sjednocení psací session apod.) se nedělá při zápisu, vše až v rollupu, který je idempotentní a dá se přepočítat.
- Schéma je verzované: tabulka `schema_version` a posloupnost očíslovaných kroků v balíčku, které command aplikuje idempotentně. Worker (fáze 4) před zápisem ověří minimální verzi.
- Analytická DB ukládá jen stabilní identifikátory (`domain_id`, `product_id`, `category_id`), nikdy názvy. Názvy se dohydratují z hlavní DB v PHP.
- Co má známou strukturu, má vlastní tabulku. Generická JSONB tabulka pro neznámé eventy vznikne až s prvním reálným eventem, ne dopředu.
- Fráze mohou obsahovat osobní údaje. Neukládá se IP ani identifikátor uživatele, jen hash `userIdentifier` s denní solí; retence raw dat nejvýše 13 měsíců (potvrdit s PO, viz SSP-4481), u našeptávače kratší.

---

## Fáze 0: Hosting a lokální prostředí

### Přehled
Rozhodnutí mimo kód plus příprava lokálního prostředí, aby fáze 1 šla vyvíjet.

### Potřebné změny

1. Analytický Postgres server běží v cloud infrastruktuře Shopsys jako další PostgreSQL server, který založí a spravuje devops (rozhodnuto 20. 10. 2026). Databázi a role pro projekt (`<projekt>_analytics`, role `<projekt>_app` s právem `CREATE` v této databázi, `<projekt>_connector`, `<projekt>_readonly`) zakládá devops na vyžádání, při založení projektu nebo kdykoliv později za běhu. Tabulky devops nezakládá, vytvoří si je aplikace commandem `analytics:schema:migrate` (fáze 1), protože jejich definici vlastní balíček a mění se s verzí kódu. Sepsat pro devops krátký runbook: SQL pro založení DB a rolí, `ALTER DEFAULT PRIVILEGES FOR ROLE <projekt>_app` (jinak role `_connector` a `_readonly` nemají práva na tabulky, které aplikace založí později), `pg_hba` pravidla, limity spojení per role, a přidání projektu do konfigurace workeru (fáze 4).
2. Lokálně: init skript v `project-base/app/docker/postgres/` založí při startu kontejneru databázi `shopsys_analytics` (Postgres image spouští `/docker-entrypoint-initdb.d/*.sh`). `.env`, `.env.dev`, `.env.test` dostanou `ANALYTICS_DATABASE_HOST/PORT/NAME/USER/PASSWORD` ukazující na tentýž kontejner.
3. Dokumentace: `docs/` stránka „Analytics storage“ s architekturou (tento plán zhuštěně) a postupem napojení projektu.

### Kritéria úspěchu

#### Automatická verifikace:
- [ ] `docker compose up` založí databázi `shopsys_analytics`
- [ ] `php bin/console doctrine:query:sql --connection=analytics 'SELECT 1'` projde

#### Ruční verifikace:
- [x] Hosting je rozhodnut: samostatný PostgreSQL server v cloudu Shopsys spravovaný devops, DB a role per projekt na vyžádání
- [ ] Runbook pro devops existuje a první projektová databáze je založená

---

## Fáze 1: Balíček `shopsys/analytics` – connection, schéma, asynchronní zápis

### Přehled
Základ, na kterém stojí vše další: connection, tabulky, zápis přes Messenger. Po této fázi do DB nic nezapisuje, jen existuje infrastruktura.

### Potřebné změny

#### 1. Balíček
- `packages/analytics/` podle vzoru `packages/mcp` (composer.json `shopsys/analytics`, `Shopsys\AnalyticsBundle`, `src/Resources/config/services.yaml`, phpstan `extension.neon`, tests). Registrace v root `composer.json` (autoload), `packages_registry.yaml`, monorepo konfiguraci a `project-base/app/config/bundles.php`.
- `Component/Availability/AnalyticsAvailabilityChecker` – `isAvailable()` podle `ANALYTICS_DATABASE_HOST` a `ANALYTICS_DATABASE_PASSWORD`.

#### 2. Connection
- `project-base/app/config/packages/doctrine.yaml`: connection `analytics` (vzor `mcp`), pouze DBAL, bez ORM mappingu.
- `project-base/app/deploy/deploy-project.sh`: předání `ANALYTICS_DATABASE_*` do K8s.
- `AnalyticsConnectionProvider` – vrací `Doctrine\DBAL\Connection` pro connection `analytics`; všechny služby balíčku jdou přes něj, aby se dala connection v testech podstrčit.

#### 3. Schéma
- `Component/Schema/AnalyticsSchemaMigration/*` – očíslované kroky (`Version001CreateSearchLog`, …), každý krok je idempotentní PHP třída vracející SQL příkazy. Ruční SQL je tu záměrně: DBAL `Schema` neumí partitioning a schéma je malé. Tabulka `schema_version` drží aplikované kroky.
- `Command/AnalyticsSchemaMigrateCommand` (`analytics:schema:migrate`) – aplikuje chybějící kroky a založí partitions pro aktuální a následující měsíc. Spouští se při deployi za migracemi hlavní DB a jako součást `phing` targetů `db-create` / `test-db-create`. Zvažovaná alternativa Doctrine Migrations nad druhou connection byla zamítnuta kvůli složitosti konfigurace `shopsys/migrations` pro více connection.
- `Component/Schema/PartitionManager` – založení měsíční partition, `DEFAULT` partition a drop partition; používá ho command, writer (záchrana při chybějící partition) i retence.
- Tabulky této fáze:

| Tabulka | Sloupce | Poznámka |
|---|---|---|
| `schema_version` | `version int PK`, `applied_at timestamptz` | |
| `search_log` | `id bigserial`, `searched_at timestamptz`, `domain_id int`, `type` (`autocomplete`/`search`), `phrase text`, `phrase_normalized text`, `visitor_hash text`, `product_count int`, `category_count int`, `article_count int`, `brand_count int`, `provider` (`elasticsearch`/`luigis_box`) | append-only; partition po měsících podle `searched_at` + `DEFAULT`; index `(domain_id, searched_at)`, `(domain_id, phrase_normalized)`, `(visitor_hash, searched_at)` pro sjednocení session v rollupu |
| `search_click_log` | `id bigserial`, `clicked_at timestamptz`, `domain_id int`, `type`, `phrase text`, `phrase_normalized text`, `visitor_hash text`, `product_id int`, `position int` | append-only; partition po měsících + `DEFAULT`; index `(domain_id, product_id)`, `(domain_id, phrase_normalized, clicked_at)`, `(visitor_hash, clicked_at)` |
| `search_phrase_daily` | `domain_id int`, `day date` (v zóně domény), `phrase_normalized text`, `phrase text` (nejčastější raw varianta dne), `type`, `search_count int`, `avg_product_count numeric`, `zero_product_result_count int`, `avg_total_count numeric`, `click_count int`, `last_searched_at timestamptz` | PK `(domain_id, day, phrase_normalized, type)`; plní fáze 3 |

#### 4. Asynchronní zápis
- `Messenger/AnalyticsEventMessage` (serializovatelný DTO: tabulka + řádek) a `Messenger/AnalyticsEventMessageHandler`.
- `project-base/app/config/packages/messenger.yaml`: transport `analytics_transport` (vzor `article_export_transport`), routing zprávy, `retry_strategy` s dlouhými prodlevami, aby výpadek DB trvající hodiny jen zpozdil zápis.
- `Component/Writer/AnalyticsWriter` – dávkový `INSERT` přes DBAL; handler sbírá řádky a flushuje po N zprávách nebo po uplynutí intervalu (Messenger `BatchHandlerInterface`, vzor `ProductRecalculationMessageHandler`). Řádek bez odpovídající měsíční partition spadne do `DEFAULT` partition, writer to zaloguje jako warning a zavolá `PartitionManager`, aby partition dozaložil.
- `Component/Dispatcher/AnalyticsEventDispatcher` – jediný vstupní bod pro aplikaci; když `AnalyticsAvailabilityChecker::isAvailable()` vrací `false`, zprávu zahodí. Výjimky při dispatchi loguje a polyká, zápis nikdy nesmí shodit request.
- Consumer `messenger:consume analytics_transport` přidaný do deployment konfigurace konzumentů.

#### 5. Retence
- `Cron/AnalyticsRetentionCronModule` (instance `service`, denně) – dropne partitions `search_click_log` starší než `shopsys.analytics.retention_months` (default 13) a `search_log` starší než `shopsys.analytics.raw_search_retention_months` (default 3, řádky našeptávače tvoří většinu objemu a po rollupu nejsou potřeba), smaže řádky `search_phrase_daily` starší 13 měsíců, založí partitions na další měsíc. Řádky typu `search` v `search_log` zůstávají 13 měsíců (vlastní partitionování podle `type` není nutné, kratší retence raw hledání se řeší `DELETE … WHERE type = 'autocomplete'` nad starými partitions před jejich dropem).

### Kritéria úspěchu

#### Automatická verifikace:
- [ ] `php bin/console analytics:schema:migrate` je idempotentní (druhý běh nic nemění) a nový krok se aplikuje nad starší verzí schématu
- [ ] funkční test: dispatch zprávy → řádek v `search_log` v test analytické DB
- [ ] funkční test: řádek s časem mimo existující partitions skončí v `DEFAULT` partition a writer partition dozaloží
- [ ] funkční test: bez env proměnných dispatcher nic neposílá a neháže výjimku
- [ ] `phing standards`, `phpstan`, `tests` projdou

#### Ruční verifikace:
- [ ] zastavená analytická DB nezpůsobí chybu requestu, zprávy čekají v RabbitMQ a po startu DB se zapíší

---

## Fáze 2: Logování hledání a kliků (datová část SSP-4481)

### Přehled
E-shop začne plnit `search_log` a `search_click_log`. Hledání se loguje na serveru, kliky posílá storefront first-party mutací.

### Potřebné změny

#### 1. Server-side log hledání přes request-scoped kolektor
- `Model/Search/SearchLogCollector` (request-scoped služba, reset mezi requesty přes `kernel.reset`) – každý ze čtyř search resolverů do něj zapíše svůj typ entity a počet výsledků spolu s frází, `domain_id`, `isAutocomplete` a `userIdentifier` ze `SearchInput`. Provider (`elasticsearch` / `luigis_box`) hlásí `SearchResultsProviderResolver`.
- `EventSubscriber/SearchLogFlushSubscriber` na `kernel.terminate` – pokud kolektor něco má, sestaví **jeden** řádek `search_log` s počty za všechny typy (typ, který v requestu nebyl dotazován, má `NULL`) a předá ho dispatcheru. Běží až po odeslání odpovědi, request na e-shopu nezdržuje.
- Loguje se jen „nové hledání“: `productsSearch` s filtrem nebo s kurzorem stránkování (`after`) se do kolektoru nehlásí. Tím odpadnou duplicitní řádky při klikání na filtry a stránkování.
- `visitor_hash`: SHA-256 z `userIdentifier` a denní soli (`shopsys.analytics.visitor_salt_secret` + datum), aby šel v rollupu rozlišit běh psaní jednoho návštěvníka, ale nešel zpětně spojit s cookie.
- Boti: storefront při SSR začne přeposílat User-Agent klienta do Frontend API (úprava `urql/createClient.ts`, hlavička `X-Original-User-Agent`), kolektor requesty s User-Agentem známých crawlerů ignoruje. Detekce přes malou knihovnu (např. `jaybizzle/crawler-detect`) nebo seznam regulárních výrazů v parametru; rozhodnout při implementaci podle velikosti závislosti.

#### 2. Sjednocení „psací session“ našeptávače se neřeší při zápisu
- Raw tabulka je append-only, každý autocomplete dotaz je jeden řádek. Pravidla ze SSP-4481 (navazující prefix do několika sekund = jedna session, smazání textu nebo pauza = nová session, odeslané hledání vždy samostatně) aplikuje až rollup ve fázi 3 window funkcemi nad `(visitor_hash, searched_at)`. Důvod: handler běží asynchronně a v dávkách, při více consumerech není zaručené pořadí a sdílený stav v Redisu by se rozjížděl; rollup je naproti tomu idempotentní a pravidla lze později změnit a přepočítat.

#### 3. Kliky
- GraphQL mutation `logSearchClick(input: { type, phrase, productUuid, position, userIdentifier })` v balíčku (`Resources/config/graphql-types/`), název camelCase jako ostatní mutace. Resolver validuje vstup (fráze max. 255 znaků, `position` 1–1000, `productUuid` formát) a předá dispatcheru; návrat `true`. Rate limit per `userIdentifier` (vzor `CreateProductReviewMutation`, `rate_limiter.yaml`), protože jde o neautentizovaný zápis.
- Storefront: v `onGtmProductClickEventHandler.ts` a `onGtmAutocompleteResultClickEventHandler.ts` (resp. v jejich volajících) zavolat mutaci přes `fetch` s `keepalive: true`, aby přežila navigaci. Na stránce výsledků hledání je fráze k dispozici z URL parametru, do dataLayer se kvůli tomu nepřidává.
- Klik se do raw logu ukládá k frázi tak, jak byla napsaná v okamžiku kliku. Přiřazení ke sjednocené session řeší rollup.

#### 4. Konfigurace a dokumentace
- Parametry balíčku v `Resources/config/parameters.yaml`, popis v docs stránce z fáze 0.

### Kritéria úspěchu

#### Automatická verifikace:
- [ ] funkční testy Frontend API: request se čtyřmi search fieldy vytvoří jeden řádek v `search_log` se správnými počty a typem; request jen s `productsSearch` má ostatní počty `NULL`
- [ ] funkční test: `productsSearch` s filtrem nebo kurzorem nevytvoří řádek
- [ ] funkční test: request s User-Agentem crawleru nevytvoří řádek
- [ ] funkční test mutace `logSearchClick` včetně validace vstupu a rate limitu
- [ ] storefront: `tsc`, `biome`, existující Cypress testy projdou

#### Ruční verifikace:
- [ ] na lokálním e-shopu hledání a klik z našeptávače i ze stránky výsledků skončí v DB s pozicí odpovídající `listIndex`
- [ ] Luigi's Box doména loguje stejně jako Elasticsearch doména

---

## Fáze 3: Rollup a administrace (UI část SSP-4481)

### Přehled
Denní agregace nad raw daty a nová sekce Marketing → Statistika vyhledávání.

### Potřebné změny

#### 1. Rollup
- `Cron/SearchPhraseRollupCronModule` (instance `service`, každou hodinu) – pro každou doménu jeden `INSERT INTO search_phrase_daily … SELECT … ON CONFLICT DO UPDATE` nad oknem posledních `shopsys.analytics.rollup_window_days` (default 3) dní. `day` se počítá jako `(searched_at AT TIME ZONE :domainTimezone)::date` podle `timezone` domény z `domains.yaml`. Idempotentní, opakovaný běh přepočítá totéž.
- Sjednocení psací session v SQL (CTE s window funkcemi): řádky typu `autocomplete` seřazené po `(visitor_hash, searched_at)`; nová session začíná, když mezera od předchozího řádku přesáhne `shopsys.analytics.autocomplete_session_seconds` (default 5) nebo fráze nezačíná normalizovanou předchozí frází. Session se počítá jako jedno hledání s **poslední** frází a jejími počty výsledků. Řádky typu `search` se nesjednocují.
- Sémantika sloupců (rozhodnuto 20. 10. 2026, potvrdit s PO v rámci SSP-4481):
  - `phrase` = nejčastější raw varianta normalizované fráze v daném dni (`mode() WITHIN GROUP`).
  - `avg_product_count` a `zero_product_result_count` vychází z počtu produktů, protože produkty jsou primární výsledek a kandidáty na synonyma (SSP-4482) jsou fráze bez produktů. `avg_total_count` je součet přes všechny typy pro kontext. Filtr „jen fráze bez výsledků“ v adminu používá `zero_product_result_count = search_count`.
  - Kliky: klik se přiřadí k session našeptávače téhož `visitor_hash`, do které časově spadá, tedy k její **poslední** frázi (uživatel napíše „ipho“, klikne, dopíše „iphone“ → klik se v rollupu započte k „iphone“). Klik mimo jakoukoli session (odeslané hledání) se přiřadí k frázi, s níž byl zaznamenán. Raw `search_click_log` drží frázi v okamžiku kliku pro detail v adminu.

#### 2. Čtení
- `Model/Search/SearchStatisticsRepository` – DBAL dotazy nad `search_phrase_daily` (součty za období 7/30/90 dní, filtr bez výsledků, filtr podle produktu přes `search_click_log`) a detail fráze (kliknuté produkty s pozicemi). Vrací ID produktů, názvy dohydratuje `SearchStatisticsFacade` z `ProductFacade` hlavní DB.
- `Component/Grid/DbalQueryBuilderDataSource` implementující `DataSourceInterface` nad `Doctrine\DBAL\Query\QueryBuilder` (stránkování, řazení, počet řádků). Umístění zvážit v `packages/framework/src/Component/Grid/`, pokud bude obecně užitečný.

#### 3. Admin
- `Controller/Admin/SearchStatisticsController` (role pro sekci Marketing, menu položka vedle Autocomplete favorites), grid se sloupci fráze, doména, počet vyhledání, průměr výsledků, CTR, naposledy vyhledáno; filtry období, „jen bez výsledků“, produkt; detail řádku s kliknutými produkty.
- Když analytika není dostupná (`AnalyticsAvailabilityChecker`), menu položka se nezobrazí.
- Dnešní den je v gridu označen jako neúplný (rollup běží hodinově), včetně času posledního úspěšného běhu rollupu z `CronModuleRun`.
- Překlady (cs, en, sk), texty podle konvencí administrace.

### Kritéria úspěchu

#### Automatická verifikace:
- [ ] funkční test rollupu: známá raw data → očekávané řádky v `search_phrase_daily`; druhý běh nic nezmění
- [ ] funkční test sjednocení session: tři navazující autocomplete dotazy → jedno hledání s poslední frází; po pauze nad limit nebo po změně prefixu → nová session; klik během psaní se započte k poslední frázi session
- [ ] funkční test: hledání kolem půlnoci v zóně domény spadne do správného `day`
- [ ] funkční test repository (období, filtr bez výsledků, filtr podle produktu)
- [ ] smoke test admin stránky

#### Ruční verifikace:
- [ ] grid odpovídá akceptačním kritériím SSP-4481 včetně detailu fráze
- [ ] grid na demo datech odpovídá do 200 ms (čte jen rollup)

---

## Fáze 4: Worker s konektory třetích stran

### Přehled
Samostatný repozitář `shopsys/analytics-worker`: malá PHP CLI aplikace, Docker image, Kubernetes CronJob vedle analytického serveru. Jednou denně stáhne agregáty z API třetích stran a zapíše je do DB projektů. První konektor: zdroje návštěv z Google Analytics 4 (GA4 Data API).

### Potřebné změny

#### 1. Schéma v platformě
- Do `AnalyticsSchema` (fáze 1) přibudou tabulky plněné konektory, aby z nich mohl číst admin a MCP:

| Tabulka | Sloupce |
|---|---|
| `traffic_source_daily` | `day date`, `domain_id int`, `source text`, `medium text`, `channel_group text`, `sessions int`, `users int`, `orders int`, `revenue numeric`, `extra jsonb`; PK `(day, domain_id, source, medium)` |
| `connector_run` | `connector text`, `day date`, `started_at`, `finished_at`, `status`, `message text` – stav běhu pro admin i Sentry |

#### 2. Repozitář workeru
- Struktura: `bin/run`, `src/Connector/ConnectorInterface` (`table()`, `fetch(ProjectConfig, DateTimeImmutable $day): iterable<array>`), `src/Runner` (smyčka přes projekty a konektory, okno posledních N dní kvůli zpětným změnám atribuce), `src/Writer` (DBAL upsert), `config/projects.yaml` (DSN a parametry konektorů jako odkazy na env), `Dockerfile` (PHP CLI), CI.
- Runner před zápisem do projektu přečte `schema_version` a konektor přeskočí s chybou do `connector_run`, pokud projekt nemá minimální verzi schématu, kterou konektor vyžaduje. Projekty poběží na různých verzích platformy a definici tabulek vlastní platforma.
- Seznam projektů je vědomě na dvou místech (DB a role u devops, `projects.yaml` ve workeru); runbook z fáze 0 oba kroky spojuje. Jednotná zdrojová konfigurace se zváží, až bude projektů víc než pár.
- `GoogleAnalyticsConnector` – `google/analytics-data`, servisní účet; klient přidá servisní účet jako čtenáře GA property. Mapování hostname → `domain_id` je součást konfigurace projektu.
- Chyby jednoho konektoru nezastaví ostatní; souhrn běhu do logu a Sentry; stav do `connector_run`.

#### 3. Nasazení a přístupy
- Kubernetes CronJob (denně ve 4:00) v clusteru analytického serveru, image z registru, přihlašovací údaje v Secretech jako env proměnné.
- DB role `<projekt>_connector` s `INSERT`/`UPDATE` jen na tabulky konektorů, bez `SELECT` na `search_*` (osobní údaje). `pg_hba.conf` povolí roli jen z clusteru.
- Fibiho existující skripty (Google Ads) se přepíší do kostry konektoru jako druhý konektor.

### Kritéria úspěchu

#### Automatická verifikace:
- [ ] unit testy konektoru nad nahranou odpovědí API → očekávané řádky
- [ ] integrační test: `bin/run --day=…` proti lokální analytické DB zapíše řádky a `connector_run`
- [ ] CI postaví image

#### Ruční verifikace:
- [ ] CronJob proběhne v cloudu a `traffic_source_daily` demo projektu má data za včerejšek
- [ ] role konektoru nemůže číst `search_log`

---

## Fáze 5: MCP nad analytickou DB

### Přehled
Tool v projektovém MCP serveru pro validované read-only SQL nad analytickou DB, aby agent mohl spojovat data e-shopu, analytiky a dalších zdrojů.

### Potřebné změny

- Druhá read-only connection `analytics_mcp` s rolí `<projekt>_readonly` a stejnými session nastaveními jako `McpSessionSettingsDriver` (read-only transakce, `statement_timeout`).
- Whitelist schématu analytických tabulek pro `SqlQueryValidator` (rozšíření `ExposedSchemaProvider` o zdroj z `AnalyticsSchema`, ne přes atributy entit, protože entity nemáme).
- Tooly `getAnalyticsSchema` a `executeAnalyticsSql` v `packages/mcp` (nebo `packages/analytics` s volitelnou závislostí na `shopsys/mcp`), aktivní jen při dostupnosti obou funkcí.
- Dokumentace pro uživatele MCP: doporučit dotazy nad `*_daily` tabulkami, raw tabulky mají limit řádků a timeout.

### Kritéria úspěchu

#### Automatická verifikace:
- [ ] testy validátoru: dotazy na povolené tabulky projdou, na jiné jsou odmítnuty, `INSERT` je odmítnut
- [ ] funkční test toolu vrací řádky z `search_phrase_daily`

#### Ruční verifikace:
- [ ] Claude přes MCP projektu odpoví na „které fráze bez výsledků byly minulý týden nejčastější“

---

## Testovací strategie

### Unit testy
- normalizace fráze, výpočet `visitor_hash`, detekce crawlerů, validace vstupu mutace, mapování odpovědí konektorů.

### Funkční testy
- testovací analytická DB (`shopsys_analytics_test`, založená v `test-db-create`), schema migrate, dispatch → řádek, resolvery Frontend API včetně kolektoru, rollup včetně sjednocení session a timezone, repository, admin smoke test, MCP tooly. Testy závislé na druhé doméně značit `#[Group('multidomain')]`.
- Messenger v test env: `analytics_transport` je v `config/packages/test/messenger.yaml` nastaven na `sync://`, handler tak běží v rámci testu (`BatchHandlerTrait` při volání mimo worker flushuje okamžitě). Flush kolektoru na `kernel.terminate` test vyvolá explicitně.

### Ruční testování
- výpadek analytické DB během provozu, Luigi's Box doména, klik ze stránky výsledků i z našeptávače, retence (umělá stará partition).

## Výkon

- Zápis jde přes Messenger a dávky, request na e-shopu přidá jen publish zprávy do RabbitMQ.
- Odhad objemu raw dat: velký projekt (1 mil. návštěv měsíčně) zhruba 90 tis. řádků `search_log` denně, z toho většina našeptávač; extrémní projekt (10 mil. návštěv) zhruba 900 tis. denně. Protože se raw řádky nesjednocují při zápisu, drží se raw `search_log` jen 3 měsíce (velký projekt jednotky GB, extrémní desítky GB); 13 měsíců raw by u extrémního projektu znamenalo stovky GB. Rollupy a kliky za 13 měsíců jsou řádově menší. Měsíční partitions drží vacuum i indexy v mezích.
- Admin a MCP čtou z rollupů (tisíce řádků denně), raw tabulky čte jen rollup nad oknem tří dní a detail fráze přes index.
- Analytický server sdílejí projekty mezi sebou; limit spojení per roli a `statement_timeout` na čtecích rolích brání tomu, aby jeden projekt vyčerpal server. Produkční DB e-shopu analytika nikdy nezatěžuje.

## Poznámky k migraci

- Pro existující projekty je zapnutí analytiky: založení DB a rolí na serveru, doplnění `ANALYTICS_DATABASE_*` do nasazení, `analytics:schema:migrate`, spuštění consumeru. Bez env proměnných se nic nemění.
- Upgrade notes: nový balíček, nové env proměnné, nový Messenger transport a consumer, nové cron moduly, GraphQL mutation `logSearchClick`, přeposílání User-Agentu ze storefrontu.

## Reference

- Jira: [SSP-4480](https://shopsys.atlassian.net/browse/SSP-4480), [SSP-4481](https://shopsys.atlassian.net/browse/SSP-4481), [SSP-4482](https://shopsys.atlassian.net/browse/SSP-4482)
- Výkop s Lukášem Fibigerem 19. 10. 2026 (nahrávka „Jak uchovávat statistická data o e-shopu“)
- `packages/mcp` – vzor druhé connection, read-only role, validátoru SQL a availability checkeru
- `project-base/storefront/gtm/` – existující GTM eventy a pozice produktů (`listIndex`)
- `docs/integration/luigis-box.md`
