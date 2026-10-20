# Ukládání analytických dat e-shopu – implementační plán

## Přehled

Shopsys chce mít na jednom místě statistická data o e-shopu (Jira [SSP-4480](https://shopsys.atlassian.net/browse/SSP-4480)): provozní data, která vznikají přímo v e-shopu (hledané fráze, kliky z našeptávače, později oblíbené, porovnání), a agregovaná data třetích stran (Google Analytics, Search Console, Google Ads, Meta, Heureka, Zboží, Sklik). Data se mají zobrazovat v administraci konkrétního e-shopu a mají být dostupné AI agentům přes MCP.

Plán vychází z výkopu s Lukášem Fibigerem (19. 10. 2026) a navazující diskuse. Navazující tickety: [SSP-4481](https://shopsys.atlassian.net/browse/SSP-4481) Statistika vyhledávání (první konzument), [SSP-4482](https://shopsys.atlassian.net/browse/SSP-4482) Správa synonym.

Dokument je analýza a soubor rozhodnutí, jak k analytickým datům přistupovat jako celek. Fáze na konci jsou pak jen důsledek těchto rozhodnutí; první z nich pokrývají SSP-4481.

## Analýza a rozhodnutí

Rozhodnutí v kostce:

- **Úložiště je Postgres**, samostatný server v cloudu Shopsys, **dvě databáze per projekt** (produkční a vývojová). Žádná sdílená multitenantní DB, žádné BigQuery.
- **Žádné API.** E-shop i konektory zapisují a čtou přímo do DB přes vlastní DB role s úzkými právy. Společným jmenovatelem e-shopu a konektorů je jen databáze.
- **E-shop zapisuje asynchronně** přes Symfony Messenger. Výpadek analytické DB nikdy nesmí ovlivnit e-shop.
- **Provozní data sbírá e-shop server-side** (resolvery Frontend API), ne přes GTM. GTM a GA zůstávají pro marketingové nástroje jako dnes.
- **Data vznikající v e-shopu zapisuje platforma** (balíček `shopsys/analytics`) do svých tabulek. **Data třetích stran stahuje samostatný worker** mimo platformu: repozitář Lukáše Fibigera, běžící jako kontejner spouštěný cronem přímo na analytickém serveru vedle DB, každý konektor s vlastní tabulkou. Platforma o tabulkách konektorů nic neví, dokud je nepotřebuje.
- **Admin a MCP čtou z denních rollupů**, ne z raw dat. První konzument v adminu jsou vlastní data o hledání (SSP-4481, SSP-4482); data konektorů zpřístupní nejdřív čtecí MCP.

### Zvažované varianty

| Otázka | Rozhodnutí | Zamítnuté varianty | Důvod |
|---|---|---|---|
| Úložiště | Postgres | BigQuery; ClickHouse | BigQuery účtuje per dotaz, admin grid a agenti tvoří nepředvídatelný účet (zkušenost: dashboard na homepage klienta = 30 tis. Kč měsíčně) a sekundové latence; MCP validátor SQL je postgresový; tým Postgres umí. ClickHouse až kdyby jeden projekt přerostl desítky TB, výměna úložiště za DBAL connection. |
| Umístění serveru | samostatný analytický server v cloudu Shopsys | druhá databáze na produkčním Postgres serveru projektu | Postgres neumí dělit CPU, RAM ani spojení mezi databázemi, rollupy a dotazy agentů by soupeřily s objednávkami; disk roste jinak; externí přístup k produkčnímu serveru nechceme. Lokálně a na review prostředích je druhá DB v kontejneru projektu v pořádku, je to jen DSN. |
| Členění dat | databáze per projekt | sdílená multitenantní DB s `tenant_id` a row-level security | Každý projekt chce ukládat něco jiného a schéma je jeho kód; izolace klientů a budoucí přímý přístup klienta jsou jen DB role; žádná RLS ani sdílené schéma. Cross-client pohled pro vedení se složí nad více DB (Grafana). |
| Rozhraní k DB | přímý přístup přes DBAL connection a DB role | HTTP ingest/read API | API nepřidává bezpečnost (API klíč je stejné tajemství jako heslo), jen vrstvu navíc; izolaci řeší DB per projekt. API lze přidat později, až bude zapisovat někdo jiný než PHP. |
| Sběr provozních dat | server-side v resolverech Frontend API, kliky first-party mutací | GTM → server-side GTM → DB | GTM je jen trubka, cesta přes něj je blokovatelná (blokátory, consent); server zná frázi i počty výsledků se 100% pokrytím. GA zůstává jako jeden ze zdrojů, ne úložiště. |
| Zápis z e-shopu | asynchronně přes Messenger, dávkový consumer | synchronní `INSERT` v requestu | Statistika nesmí nikdy položit e-shop; při výpadku DB zprávy čekají ve frontě. |
| Čtení v adminu a MCP | denní rollupy, raw jen pro rollup a detail | dotazy nad raw tabulkami | Výkon nezávislý na objemu raw dat; chrání server před scénářem „velký klient a tisíc agentů“. |
| Vlastník schématu | každý zapisovatel vlastní své tabulky: platforma tabulky plněné e-shopem (verzované kroky aplikované commandem), worker tabulky konektorů | tabulky zakládá devops; Doctrine Migrations nad druhou connection; jedno schéma vlastněné platformou pro všechny | Definice se mění s kódem toho, kdo zapisuje; devops zakládá jen DB a role. DBAL `Schema` neumí partitioning, `shopsys/migrations` je stavěný na jednu connection. |
| Konektory třetích stran | samostatný worker mimo platformu, kontejner spouštěný cronem na analytickém serveru (podrobně níže; **změna rozhodnutí 27. 10. 2026**, 21. 10. byly zvoleny konektory v platformě) | cron moduly v platformě s volitelným balíčkem per zdroj; skripty volané z projektu | Konektory píše marketing, ne vývoj platformy, a potřebuje je nasazovat bez release cyklu platformy; SDK třetích stran (Google Ads API vydává verzi každé ~3 měsíce a starou vypíná po roce) se aktualizuje jednou místo na každém projektu. Podrobně v tabulce níže. |
| Tvar tabulek konektorů | každý konektor vlastní tabulku s vlastními sloupci, jen minimum konvencí | jedna unifikovaná tabulka metrik „na výšku“ (`day, source, entity, metric, value`) | Unifikace řešila jen problém konektorů v platformě (nová metrika = změna struktury na N projektech); u centrálního workeru, který si tabulky zakládá sám, problém není. Pojmenované sloupce jsou pro agenta přes MCP čitelnější než EAV. Unifikace by se stejně dlouhodobě neudržela. |
| Přihlašovací údaje ke zdrojům | CI/CD proměnné repozitáře workeru, nasazené na server jako env soubor kontejneru, vyplňuje marketing per projekt a zdroj | env proměnné projektu (worker se k nim nedostane); šifrovaně v analytické DB zapsané z administrace | Nejjednodušší pro jednotky projektů s agenturními přístupy. Uložení z administrace (OAuth souhlas klienta) je správný koncový stav pro klientské účty, ale je to samostatná pozdější fáze; nikdy ne plaintext v tabulce. |
| Grafana | mimo scope, pro agenta další nezávislý zdroj | Grafana jako proxy pro byznysová data | Grafana je nástroj pro infrastrukturní metriky; spojení zdrojů dělá agent. |
| Existující nástroje | pro hledání a synonyma vlastní řešení nad Elasticsearch; pro stahování vlastní worker s rozhodovacím pravidlem; pro chování mimo hledání a doporučování vyhodnotit až po SSP-4481 | OpenSearch User Behavior Insights, Typesense (analytika hledání); Airbyte, Keboola (stahování); Matomo, PostHog (chování návštěvníků); Gorse (doporučování); Prometheus | UBI a Typesense znamenají výměnu vyhledávače kvůli dvěma tabulkám; Elastic Behavioral Analytics je od 9.0 deprecated. Airbyte je viabilní headless ingest, ale pro 2–3 zdroje dražší provozně než vlastní worker; při růstu k 5+ zdrojům zvážit (spike před druhým konektorem). Keboola je placená per klient, stejná námitka jako Luigi's Box. Matomo (server-side tracking, reporting API) a Gorse (doporučovací engine krmený našimi eventy) jsou kandidáti pro pozdější oblasti, oba potřebují eventy, které teprve začneme sbírat. Prometheus je na metriky s nízkou kardinalitou, ne na byznysová data. „Zbavit se externích nástrojů“ platí pro klienta, ne pro nás: nástroj schovaný za naší administrací je s cílem v souladu. |

### Konektory třetích stran: worker vs. platforma

| | Samostatný worker (zvoleno 27. 10. 2026) | Konektory v platformě (zvoleno 21. 10., zamítnuto 27. 10.) |
|---|---|---|
| Kdo konektory píše a nasazuje | marketing (Lukáš Fibiger) ve vlastním repozitáři; nasazení = git push, CI postaví image do registru a cron na serveru ho při dalším běhu použije; žádné tagy, žádné composer release, žádné CI standardy platformy | vývoj platformy musí každý skript zabalit do balíčku, protáhnout CI platformy (phpstan, coding standards, verze PHP) a vydat |
| Tempo změn | nezávislé na releasech platformy, nový zdroj „hned“ | přes PR do balíčku, k projektům s upgradem platformy |
| Upgrade SDK třetích stran | jednou, v jednom repozitáři | composer update a deploy na každém projektu zvlášť, na jeho release cyklu |
| Vlastník tabulek konektorů | worker, zakládá si je sám; platforma je nezná, dokud je nepotřebuje | platforma, verzované kroky schématu per balíček |
| Co projekt ví o obsahu své DB | jen svoje tabulky; cizí tabulky objeví přes MCP nebo až při implementaci konkrétní admin obrazovky | všechno |
| Rozšíření pro konkrétního klienta | řádek v konfiguraci workeru, nebo projektový konektor v project-base zapisující do téže DB | přepsání nebo přidání třídy v project-base |
| Přihlašovací údaje | CI/CD proměnné repozitáře workeru → env soubor kontejneru na serveru | env proměnné projektu |
| Nový projekt | devops založí DB a role, marketing přidá projekt do konfigurace workeru a vyplní přístupy | env proměnné v deployi projektu |
| Infrastruktura | cron a Docker na analytickém serveru vedle Postgresu, žádný další server | žádná, běží v cronu projektu |
| Monitoring | vlastní logování a Sentry projekt workeru, tabulka `connector_run` v DB projektu | `CronModuleRun`, Sentry monitoring cronů |
| Lokální vývoj a testy | repozitář workeru proti lokální analytické DB | Docker projektu |

Historie rozhodnutí: 21. 10. byly zvoleny konektory v platformě kvůli jednomu vlastníkovi schématu, dat i admin UI (vzor feed balíčků). 27. 10. rozhodnutí obráceno po oponentuře marketingu: (1) konektory píše marketing, který do platformy psát nebude a potřebuje reagovat na změny zdrojů bez release cyklu; (2) SDK třetích stran se mění každých pár měsíců a aktualizace na N projektech je neúnosná. Námitka „dva vlastníci schématu“ padla tím, že se vzdáváme jednotného schématu: worker si tabulky konektorů zakládá a vlastní sám a platforma je čte, až když je potřebuje (nejdřív přes MCP). Varianta „projekt volá Fibiho skripty“ byla zamítnuta, protože otázka „jak se projekt ke skriptům dostane“ je právě verzování a vydávání, které marketing dělat nebude. Co z platformy zůstává: data vznikající v e-shopu (hledání, objednávky, atribuce zdroje objednávky) zapisuje platforma sama, protože čte svoji DB.

## Současný stav

- Platforma nemá žádné logování hledaných frází ani jiných behaviorálních dat. Jediná statistika v administraci je dashboard (`packages/framework/src/Model/Statistics/`), počítaný živě SQL nad hlavní DB.
- Sledování chování je výhradně klientské přes GTM dataLayer (`project-base/storefront/gtm/`): eventy `ec.autocomplete_results_view`, `ec.autocomplete_result_click` (obsahují `keyword`), `ec.product_click` s `listIndex` (1-based, `mappers/mapGtmListedProductType.ts`), `ec.add_to_wishlist`, `ec.add_to_comparsion` a další. Stránka výsledků hledání nemá frázi v dataLayer vůbec (jen `product_list_view` se jménem listu `search results`). Žádné server-side měření (sGTM, Measurement Protocol) neexistuje.
- Luigi's Box (`packages/luigis-box`) je zapínán per doména přes `LUIGIS_BOX_ENABLED_DOMAIN_IDS`; analytiku hledání drží jen ve svém systému.
- MCP server (`packages/mcp`) vystavuje read-only SQL nad hlavní DB přes **druhou Doctrine connection `mcp`** (`project-base/app/config/packages/doctrine.yaml`), s vlastní read-only rolí (`McpReadOnlyUserManager`), `statement_timeout` (`McpSessionSettingsDriver`) a validátorem SQL nad whitelistem tabulek a sloupců (`SqlQueryValidator`). Dostupnost se řídí přítomností env proměnných (`McpAvailabilityChecker`).
- Každý projekt má vlastní RabbitMQ vhost a Messenger transporty (`project-base/app/config/packages/messenger.yaml`) a cron instance v Kubernetes (`project-base/app/deploy/deploy-project.sh`, `CRON_INSTANCES`). Cron moduly se registrují tagem `shopsys.cron` se Sentry monitoringem (`project-base/app/config/cron.yaml`).
- Žádný centrální server Shopsys neexistuje, každý projekt je samostatné nasazení. Jediná vnější integrace je Data Bridge (`DATA_BRIDGE_*`).
- Composer neobsahuje klienty pro GA, GSC, Google Ads ani Meta. Elasticsearch definice nemají žádný synonym filtr.

## Cílový stav

- Projekt má v konfiguraci třetí Doctrine connection `analytics` (vzor `mcp`) směřující na vlastní databázi na analytickém Postgres serveru (produkce na produkční DB projektu, devel prostředí na vývojovou). Když env proměnné chybí, analytika je vypnutá a aplikace běží beze změny.
- Balíček `packages/analytics` (`Shopsys\AnalyticsBundle`) dodává: definici schématu a command pro jeho založení, Messenger zprávu a handler pro asynchronní zápis, writer s dávkovým upsertem, cron moduly pro rollupy a retenci, DBAL data source pro admin grid.
- Hledání a našeptávač logují každý dotaz jedním řádkem per request (doména, fráze, typ, čas, počty výsledků per typ entity), kliky z výsledků se logují s pozicí. Boti se nelogují. Admin sekce Marketing zobrazuje přehled frází podle SSP-4481.
- Data třetích stran stahuje samostatný worker (repozitář marketingu, kontejner spouštěný cronem na analytickém serveru) do tabulek, které si sám zakládá v DB každého projektu, s minimem konvencí (`day`, `domain_id`, `product_id` = naše ID produktu, přirozený primární klíč, upsert). Přihlašovací údaje ke zdrojům jsou CI/CD proměnné repozitáře workeru.
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
- `packages/plugin-interface/src/Cron/IteratedCronModuleInterface.php` a `packages/framework/src/Model/Feed/FeedCronModule.php` – iterovaný cron modul, vzor pro interní agregace v platformě (denní agregáty objednávek), které musí být po dnech a vejít se do `timeout_iterated_cron_sec` (240 s, `project-base/app/config/cron.yaml`).
- Google Ads API vydává novou verzi zhruba každé tři měsíce a starou vypíná po roce; kód konektoru tedy vyžaduje pravidelnou údržbu nezávisle na platformě.
- Materialized view v Postgresu nejde obnovovat po dnech, proto rollupy řeší `INSERT … SELECT … ON CONFLICT DO UPDATE` nad klouzavým oknem.
- DBAL `Schema` objekty neumí `PARTITION BY` ani založení partition; partitioning potřebuje vlastní SQL vrstvu. Schéma **platformy** potřebuje verzování od začátku (`CREATE TABLE IF NOT EXISTS` neřeší přidání sloupce v další verzi); worker si vystačí s idempotentním DDL, protože každou jeho tabulku vlastní jediný konektor.
- Postgres odmítne založit novou range partition, pokud `DEFAULT` partition obsahuje řádky spadající do jejího rozsahu; dozakládání partition musí řádky z `DEFAULT` nejdřív přesunout.
- `messenger.yaml` má `failure_transport: failed` = `doctrine://default`, tedy tabulka v **hlavní produkční DB**; bez výjimky pro analytický transport by vyčerpané retry spadly do produkční DB. RabbitMQ při dosažení memory/disk watermarku blokuje všechny publishery vhostu, tedy i objednávky.
- Postgres image spouští `/docker-entrypoint-initdb.d` jen při prvním initu `PGDATA`; vývojáři s existujícími daty a review prostředí by DB nedostali. Správná cesta je `shopsys:database:create` (`CreateDatabaseCommand`), který volají targety `db-create` / `test-db-create`; migrace běží v `db-migrations` a při deployi v `build-deploy-part-2-db-dependent`.
- Lokální consumer běží z `project-base/app/docker/php-fpm/consumer-entrypoint.sh` s pevným výčtem transportů, v K8s z `DEFAULT_CONSUMERS` v `deploy-project.sh`.
- Env proměnné bez defaultu v `parameters.yaml` shodí kontejner `EnvNotFoundException` (vzor defaultů: `packages/luigis-box/src/Resources/config/parameters.yaml`).
- Od Postgresu 15 nemá schéma `public` implicitní právo `CREATE` pro ostatní role.
- Role pro admin sekci je konstanta v `AdminRoleConstant` a zařazení do role group; existující administrátoři ji bez migrace nedostanou.

## Co neděláme

- BigQuery ani jiný sloupcový sklad. Pokud by jeden projekt přerostl desítky TB nebo potřeboval ad-hoc analytiku nad raw eventy, je další krok ClickHouse jako výměna úložiště za DBAL connection.
- Sdílená multitenantní databáze, `tenant_id`, row-level security.
- HTTP API pro zápis nebo čtení. Oddělení klientů řeší DB per projekt a DB role.
- Server-side GTM, Measurement Protocol, změny ve stávajících GTM eventech.
- Grafana jako proxy pro byznysová data. Grafana zůstává zdrojem pro infrastrukturní metriky a případný interní cross-client pohled nad analytickými DB.
- Správa synonym (SSP-4482) – samostatný plán, netýká se analytické DB (jen z ní předvyplňuje fráze).
- Správa konektorů a přihlašovacích údajů klientem v administraci (OAuth souhlas, šifrované tokeny v analytické DB projektu) – pozdější fáze, až klienti budou napojovat vlastní účty; teď CI/CD proměnné workeru.
- Admin obrazovky nad daty konektorů (přehled kanálů, GSC, produkt na srovnávačích) – až po SSP-4481 a po zpřístupnění dat přes MCP; struktura tabulek konektorů platformu do té doby nezajímá.
- Chování návštěvníků mimo hledání (oblíbené, porovnání, funnel) a doporučování produktů – před vlastními eventovými tabulkami vyhodnotit Matomo, resp. Gorse (viz tabulka variant). Dispatcher eventů v platformě zůstává obecný, aby se k němu dal později připojit druhý odběratel.
- Atribuce zdroje objednávky (UTM, `gclid`, kanál uložené k objednávce) – nutná pro výpočet návratnosti kanálů (spend z konektorů proti objednávkám e-shopu); platforma ji dnes neukládá. Samostatný ticket, první „interní konektor“ platformy po SSP-4481.

## Přístup k implementaci

Práce v platformě probíhá v balíčku `packages/analytics` (package-first), v `project-base` jen konfigurace (doctrine, messenger, cron, env, deploy). Větev `rv/analytics-data-storage` nad `20.0`. Fáze 1–3 pokrývají SSP-4480 a datovou i UI část SSP-4481 a tvoří jeden PR nebo dva stacked PR (balíček + logování; rollup + admin). Fáze 4 je samostatný repozitář workeru mimo monorepo, fáze 5 samostatný PR v monorepu. Fáze 4 na fázích 1–3 nezávisí, může běžet souběžně, jakmile existuje DB z fáze 0a a běhové prostředí z fáze 0b.

Umístění DB je jen DSN: lokálně a na review prostředích je analytická DB druhou databází v kontejneru `postgres` projektu, devel prostředí v cloudu používá vývojovou DB projektu na analytickém serveru, produkce produkční. Kód je stejný.

Závislosti fází: 0a → 1 → 2 → 3 (platforma, SSP-4481); 0b → 4 (worker, nezávisle na 1–3); 5 po 3, plně ověřitelná až po 4.

### Kdo co dělá

| Role | Odpovídá za |
|---|---|
| Vývoj platformy | balíček `shopsys/analytics` a vše v monorepu (fáze 1–3, 5); repozitář workeru: kostra, šablona konektoru, `Dockerfile`, `.gitlab-ci.yml`, testy, README (fáze 4 §1); dokumentace v `docs/` |
| Devops | analytický server, Postgres, zálohy, obnova, monitoring disku a spojení; založení DB a rolí per projekt podle runbooku; Docker, cron, registr, deploy klíč a env soubor na serveru; Sentry projekt workeru a alarm na stáří posledního běhu; noční kopie tabulek konektorů do devel DB |
| Marketing (Lukáš Fibiger) | konektory ve workeru (kód, testy nad nahranými odpověďmi), přidání projektu do `projects.yaml`, vyplnění přístupů ke zdrojům v CI/CD proměnných, reakce na Sentry alerty workeru |
| PO / obchod | potvrzení retence a sémantiky statistik (SSP-4481), smlouva o zpracování dat per klient, rozhodnutí o servisním účtu Google (klientův vs. sdílený Shopsys), získání přístupů od klientů |

Zásady pro schéma:

- Každý zapisovatel vlastní své tabulky **ve vlastním schématu Postgresu**: platforma ve schématu `platform`, worker ve schématu `connector`. Role `_app` má `search_path = platform, public`, role `_connector` `search_path = connector, public`, takže oba zapisují nekvalifikovanými názvy do svého schématu. Vlastnictví je vidět z názvu, `ALTER DEFAULT PRIVILEGES IN SCHEMA` je jednoznačné, názvy tabulek nekolidují, MCP blacklist a noční kopie do devel DB pracují se schématem jako celkem. Nikdo nemění cizí tabulky.
- Minimum konvencí napříč tabulkami, kvůli agentovi přes MCP a budoucímu spojování: denní granularita ve sloupci `day date`, doména v `domain_id int`, produkt v `product_id int` jako naše ID produktu (tak, jak ho posílají feedy), kategorie v `category_id int`, přirozený primární klíč (den + doména + entita), zápis idempotentním upsertem, `COMMENT ON TABLE` a `COMMENT ON COLUMN` u každé tabulky (čte je MCP). Výjimka: raw eventové tabulky platformy mají surrogate `id bigserial` a jsou append-only. `day` u tabulek konektorů je den v zóně zdroje (GA4 property, účet Ads); pokud se liší od zóny domény, je to v komentáři sloupce. Žádné názvy produktů ani kategorií, jen ID; názvy se dohydratují z hlavní DB.
- Tabulky s raw eventy platformy jsou **append-only** a partitionované podle času události, s `DEFAULT` partition jako záchrannou sítí. `search_log` je partitionovaný dvouúrovňově, `PARTITION BY LIST (type)` a pod ním `PARTITION BY RANGE (searched_at)` po měsících, aby retence našeptávače a odeslaného hledání byla čistý `DROP TABLE` partition bez `DELETE`. Žádná odvozená logika (sjednocení psací session apod.) se nedělá při zápisu, vše až v rollupu, který je idempotentní a dá se přepočítat.
- Schéma platformy je verzované: tabulka `schema_version` a posloupnost očíslovaných kroků v balíčku, které command aplikuje idempotentně. Projekt přidává vlastní kroky stejným mechanismem (tagovaná služba kroku). Migrace schématu musí být bezpečná při běžícím consumeru (jen aditivní `ALTER`, žádné přejmenování sloupců).
- Co má známou strukturu, má vlastní tabulku. Generická JSONB tabulka pro neznámé eventy vznikne až s prvním reálným eventem, ne dopředu.
- Osobní údaje: neukládá se IP ani identifikátor uživatele, jen hash `userIdentifier` s denní solí (den v UTC; session přes půlnoc se rozpadne, což je přijatelné). Fráze mohou být osobní údaj: při normalizaci se fráze odpovídající vzoru e-mailu, telefonního čísla nebo čísla karty nahradí zástupným tokenem (`[email]`, `[phone]`, `[card]`) ještě před zápisem, takže raw log ani rollup je neobsahují. Právo na výmaz není realizovatelné (bez identifikátoru není co najít) a nemusí být, protože data nejsou přiřaditelná k osobě. Retence raw dat nejvýše 13 měsíců (potvrdit s PO, viz SSP-4481), u našeptávače kratší. Raw `search_log` a `search_click_log` se přes MCP nevystavují, jen rollupy. Shopsys hostuje data klientů na svém serveru, což vyžaduje smlouvu o zpracování per klient a záznam o činnosti zpracování (vlastník PO/obchod, mimo kód).

---

## Fáze 0a: Analytický server a lokální prostředí

### Přehled
Rozhodnutí mimo kód plus příprava serveru a lokálního prostředí, aby fáze 1 šla vyvíjet. Vlastník: devops (server, runbook), vývoj platformy (lokální DB, dokumentace).

### Potřebné změny

1. Analytický Postgres server běží v cloud infrastruktuře Shopsys jako další PostgreSQL server, který založí a spravuje devops (rozhodnuto 20. 10. 2026, finální po dohodě s marketingem 26. 10. 2026). Pro každý projekt dvě databáze, `<projekt>_analytics` (produkce) a `<projekt>_analytics_devel` (vývojové prostředí v cloudu), v každé schémata `platform` a `connector` a tři role: `<projekt>_app` (vlastník schématu `platform`, `search_path = platform, public`), `<projekt>_connector` (vlastník schématu `connector`, `search_path = connector, public`), `<projekt>_readonly` (`SELECT` na obě schémata, pro MCP a případný přímý přístup klienta). Zakládá je devops na vyžádání, při založení projektu nebo kdykoliv později za běhu. Tabulky devops nezakládá: tabulky platformy vytvoří command `analytics:schema:migrate` (fáze 1), tabulky konektorů worker (fáze 4).
2. Runbook pro devops: SQL pro založení obou DB, schémat a rolí; `ALTER DEFAULT PRIVILEGES FOR ROLE <projekt>_app IN SCHEMA platform GRANT SELECT …` pro `_connector` a `_readonly` a obdobně `FOR ROLE <projekt>_connector IN SCHEMA connector` pro `_app` a `_readonly`; `pg_hba`: `_app` a `_readonly` jen z IP rozsahů clusterů projektů a VPN, `_connector` jen z `localhost`; TLS povinné (`sslmode=verify-full` v DSN projektů, certifikát serveru v runbooku); limity spojení per role, `statement_timeout` pro `_readonly`; rotace hesel; zálohy `pg_dump` per DB s retencí a pravidelným testem obnovy (raw data e-shopu nejdou dopočítat zpětně, data konektorů ano v okně 30 dní); monitoring disku a `max_connections`; verze Postgresu a plán upgradu; kdo má root.
3. Lokálně: `shopsys:database:create` (`CreateDatabaseCommand`, volaný z `db-create` / `test-db-create`) založí i databázi z connection `analytics`, pokud je analytika zapnutá; žádný init skript Postgres image (běží jen při prvním initu `PGDATA`, review prostředí ho nemají). `.env`, `.env.dev`, `.env.test` dostanou `ANALYTICS_DATABASE_HOST/PORT/NAME/USER/PASSWORD` ukazující na kontejner `postgres` projektu; schémata `platform` a `connector` v lokální DB založí `analytics:schema:migrate`.
4. Dokumentace: `docs/integration/analytics-storage.md` s architekturou (tento plán zhuštěně), konvencemi tabulek, výčtem env proměnných (vzor `docs/ai/mcp/mcp-server.md`), postupem napojení projektu a odkazem na README workeru; zařazení do `docs/navigation.yml`.

### Kritéria úspěchu

#### Automatická verifikace:
- [ ] `phing db-create` založí `shopsys_analytics`, `phing test-db-create` založí `shopsys_analytics_test`
- [ ] `php bin/console doctrine:query:sql --connection=analytics 'SELECT 1'` projde

#### Ruční verifikace:
- [x] Hosting je rozhodnut: samostatný PostgreSQL server v cloudu Shopsys spravovaný devops, dvě DB a tři role per projekt na vyžádání
- [ ] Runbook pro devops existuje a první projektová databáze je založená
- [ ] záloha a obnova jedné projektové DB vyzkoušena
- [ ] spojení z clusteru projektu na server jde jen přes TLS

---

## Fáze 0b: Běhové prostředí workeru

### Přehled
Infrastruktura pro fázi 4, nezávislá na fázích 1–3. Vlastník: devops.

### Potřebné změny

1. Worker běží na tomtéž analytickém serveru jako Postgres, jako kontejner spouštěný hostovským cronem (`docker run` s limitem CPU a paměti a vlastním volume pro logy). Devops připraví Docker, cron, přístup k registru a Sentry projekt workeru.
2. Nasazení: CI repozitáře workeru po pushi do `main` postaví image, otaguje ho `sha` i `latest` a nahraje do registru; cron před každým během stáhne tag z env souboru (`WORKER_IMAGE_TAG`, default `latest`). Rollback = devops přepne tag v env souboru na předchozí `sha`.
3. Přístupy: zdrojem pravdy jsou CI/CD proměnné repozitáře workeru (vyplňuje marketing). CI je po pushi zapíše do env souboru kontejneru na serveru přes deploy klíč omezený na jeden adresář (`rrsync`, bez shellu). Jiný přístup CI na server neexistuje. Konvence názvů proměnných: `<PROJEKT>_ANALYTICS_DSN`, `<PROJEKT>_ANALYTICS_DEVEL_DSN`, `<PROJEKT>_GA4_SERVICE_ACCOUNT_JSON`, `<PROJEKT>_GOOGLE_ADS_*`; výčet v README workeru. Rotace: změna proměnné v GitLabu a push.
4. Worker se k DB připojuje přes `localhost` rolí `_connector`, ze sítě není otevřená.
5. Hlídání: Sentry z workeru (alert do kanálu marketingu a vývoje) a alarm devopsu, pokud poslední úspěšný běh workeru je starší než dva dny (`connector_run` nebo soubor s časem posledního běhu na serveru).
6. Noční kopie schématu `connector` z produkční do vývojové DB každého projektu (`pg_dump --schema=connector | pg_restore`), aby devel mělo reálná data konektorů bez dvojího volání API třetích stran.

### Kritéria úspěchu

#### Ruční verifikace:
- [ ] push do `main` repozitáře workeru vede bez zásahu k novému image v registru a k aktualizovanému env souboru na serveru
- [ ] cron spustí kontejner, log a Sentry ukazují běh
- [ ] rollback přepnutím tagu vyzkoušen
- [ ] devel DB prvního projektu obsahuje schéma `connector` zkopírované z produkce

---

## Fáze 1: Balíček `shopsys/analytics` – connection, schéma, asynchronní zápis

### Přehled
Základ, na kterém stojí vše další: connection, tabulky, zápis přes Messenger. Po této fázi do DB nic nezapisuje, jen existuje infrastruktura.

### Potřebné změny

#### 1. Balíček
- `packages/analytics/` podle vzoru `packages/mcp` (composer.json `shopsys/analytics`, `Shopsys\AnalyticsBundle`, `src/Resources/config/services.yaml`, phpstan `extension.neon`, tests). Registrace stejně, jako byl přidán `packages/mcp`: root `composer.json` (`autoload`, `autoload-dev`, `replace`), `project-base/app/composer.json`, `project-base/app/config/bundles.php`, split workflow v `.github/workflows/`.
- `Component/Availability/AnalyticsAvailabilityChecker` – `isAvailable()` podle `ANALYTICS_DATABASE_HOST`, `ANALYTICS_DATABASE_NAME`, `ANALYTICS_DATABASE_USER` a `ANALYTICS_DATABASE_PASSWORD`; všechny mají prázdný default v `parameters.yaml` balíčku (vzor `packages/luigis-box`), aby chybějící env nepoložily kontejner.

#### 2. Connection
- `project-base/app/config/packages/doctrine.yaml`: connection `analytics` (vzor `mcp`), pouze DBAL, bez ORM mappingu, s `sslmode` v DSN (lokálně `prefer`, v cloudu `verify-full`).
- `project-base/app/deploy/deploy-project.sh`: předání `ANALYTICS_DATABASE_*` do K8s.
- `AnalyticsConnectionProvider` – vrací `Doctrine\DBAL\Connection` pro connection `analytics`; všechny služby balíčku jdou přes něj, aby se dala connection v testech podstrčit.

#### 3. Schéma
- `Component/Schema/AnalyticsSchemaMigration/*` – očíslované kroky (`Version001CreateSearchLog`, …), každý krok je idempotentní PHP třída vracející SQL příkazy. Ruční SQL je tu záměrně: DBAL `Schema` neumí partitioning a schéma je malé. Tabulka `platform.schema_version` drží aplikované kroky. První krok založí schémata `platform` a `connector`, pokud neexistují (lokálně je nezakládá devops).
- `Command/AnalyticsSchemaMigrateCommand` (`analytics:schema:migrate`) – aplikuje chybějící kroky. Spouští se z `phing` targetů `db-migrations` / `test-db-migrations` (tím i při deployi v `build-deploy-part-2-db-dependent` a v `db-demo` / `test-db-demo`), ne z `db-create`. Zvažovaná alternativa Doctrine Migrations nad druhou connection byla zamítnuta kvůli složitosti konfigurace `shopsys/migrations` pro více connection; `DatabaseSchemaPreparedSubscriber` z `packages/mcp` neběží při continuous deployi, proto target.
- `Component/Schema/PartitionManager` – založení měsíční partition, `DEFAULT` partition a drop partition. Založení partition běží v transakci: zámek, `DELETE … RETURNING` řádků spadajících do nového rozsahu z `DEFAULT` partition, `CREATE TABLE … PARTITION OF`, `INSERT` přesunutých řádků. Partitions na další měsíc zakládá jen retenční cron (ne command).
- Tabulky této fáze (vše ve schématu `platform`):

| Tabulka | Sloupce | Poznámka |
|---|---|---|
| `schema_version` | `version int PK`, `applied_at timestamptz` | |
| `search_log` | `id bigserial`, `searched_at timestamptz`, `domain_id int`, `type` (`autocomplete`/`search`), `phrase text`, `phrase_normalized text`, `visitor_hash text`, `product_count int`, `category_count int`, `article_count int`, `brand_count int`, `provider` (`elasticsearch`/`luigis_box`) | append-only; `PARTITION BY LIST (type)`, pod ním `PARTITION BY RANGE (searched_at)` po měsících + `DEFAULT`; index `(domain_id, searched_at)`, `(domain_id, phrase_normalized)`, `(visitor_hash, searched_at)` pro sjednocení session v rollupu |
| `search_click_log` | `id bigserial`, `clicked_at timestamptz`, `domain_id int`, `type`, `phrase text`, `phrase_normalized text`, `visitor_hash text`, `product_id int`, `position int` | append-only; partition po měsících + `DEFAULT`; index `(domain_id, product_id)`, `(domain_id, phrase_normalized, clicked_at)`, `(visitor_hash, clicked_at)` |
| `search_phrase_daily` | `domain_id int`, `day date` (v zóně domény), `phrase_normalized text`, `phrase text` (nejčastější raw varianta dne), `type`, `search_count int`, `avg_product_count numeric`, `zero_product_result_count int`, `avg_total_count numeric`, `click_count int`, `last_searched_at timestamptz` | PK `(domain_id, day, phrase_normalized, type)`; plní fáze 3 |

#### 4. Asynchronní zápis
- `Messenger/AnalyticsEventMessage` (serializovatelný DTO: tabulka + řádek) a `Messenger/AnalyticsEventMessageHandler`.
- `project-base/app/config/packages/messenger.yaml`: transport `analytics_transport` (vzor `article_export_transport`), routing zprávy, `retry_strategy` s dlouhými prodlevami, aby výpadek DB trvající hodiny jen zpozdil zápis. **`failure_transport: false`** pro tento transport: statistika se po vyčerpání retry zahodí, nikdy nespadne do tabulky `messenger_messages` v hlavní produkční DB. Fronta má strop (`x-max-length` nebo `x-message-ttl` v argumentech fronty), aby vícedenní výpadek analytiky nenaplnil RabbitMQ až k watermarku, který blokuje publishery všech transportů vhostu včetně objednávek. Ztráta statistiky při dlouhém výpadku je přijatelná. Alert na hloubku fronty u devopsu.
- `Component/Writer/AnalyticsWriter` – dávkový `INSERT` přes DBAL; handler sbírá řádky a flushuje po N zprávách nebo po uplynutí intervalu (Messenger `BatchHandlerInterface`, vzor `ProductRecalculationMessageHandler`). Řádek bez odpovídající měsíční partition spadne do `DEFAULT` partition, writer to zaloguje jako warning a zavolá `PartitionManager`, který partition dozaloží a řádky z `DEFAULT` přesune.
- `Component/Dispatcher/AnalyticsEventDispatcher` – jediný vstupní bod pro aplikaci; když `AnalyticsAvailabilityChecker::isAvailable()` vrací `false`, zprávu zahodí. Výjimky při dispatchi loguje a polyká, zápis nikdy nesmí shodit request. Rozhraní je obecné (typ eventu + payload), aby se později dal připojit druhý odběratel (doporučování, Matomo).
- Consumer `messenger:consume analytics_transport` přidaný do `DEFAULT_CONSUMERS` v `deploy-project.sh` i do `project-base/app/docker/php-fpm/consumer-entrypoint.sh` (lokální běh).

#### 5. Retence
- `Cron/AnalyticsRetentionCronModule` (instance `service`, denně) – čistý drop partitions: `search_log` typu `autocomplete` starší než `shopsys.analytics.autocomplete_retention_months` (default 3, řádky našeptávače tvoří většinu objemu a po rollupu nejsou potřeba), `search_log` typu `search` a `search_click_log` starší než `shopsys.analytics.retention_months` (default 13); `DELETE` řádků `search_phrase_daily` starších 13 měsíců; založení partitions na další měsíc pro všechny partitionované tabulky.

### Kritéria úspěchu

#### Automatická verifikace:
- [ ] `php bin/console analytics:schema:migrate` je idempotentní (druhý běh nic nemění) a nový krok se aplikuje nad starší verzí schématu
- [ ] funkční test: dispatch zprávy → řádek v `platform.search_log` v test analytické DB
- [ ] funkční test: řádek s časem mimo existující partitions skončí v `DEFAULT` partition, writer partition dozaloží a řádky přesune; `DEFAULT` je poté prázdná
- [ ] funkční test: bez env proměnných dispatcher nic neposílá a neháže výjimku
- [ ] funkční test retence: partition našeptávače starší 3 měsíců je dropnuta, partition odeslaného hledání téhož stáří zůstává
- [ ] `phing standards`, `phpstan`, `tests` projdou

#### Ruční verifikace:
- [ ] zastavená analytická DB nezpůsobí chybu requestu, zprávy čekají v RabbitMQ a po startu DB se zapíší
- [ ] při vyčerpání retry se zprávy zahodí, tabulka `messenger_messages` v hlavní DB zůstává prázdná, fronta nepřekročí strop a ostatní transporty nejsou dotčeny

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
- GraphQL mutation `logSearchClick(input: { type, phrase, productUuid, position, userIdentifier })` v balíčku (`Resources/config/graphql-types/`), název camelCase jako ostatní mutace. Resolver validuje vstup (fráze max. 255 znaků a normalizovaná stejně jako při hledání včetně maskování osobních údajů, `position` 1–1000, `productUuid` formát) a předá dispatcheru; návrat `true`. Rate limit per `userIdentifier` **i per IP** (vzor `CreateProductReviewMutation` a `frontend_api_password_recovery_ip` v `rate_limiter.yaml`), protože jde o neautentizovaný zápis a UUID si útočník generuje. Existence produktu se neověřuje při zápisu, ale v rollupu joinem na ID produktů z hlavní DB; kliky na neexistující produkty se nezapočtou.
- Storefront: v `onGtmProductClickEventHandler.ts` a `onGtmAutocompleteResultClickEventHandler.ts` (resp. v jejich volajících) zavolat mutaci přes `fetch` s `keepalive: true`, aby přežila navigaci. Na stránce výsledků hledání je fráze k dispozici z URL parametru, do dataLayer se kvůli tomu nepřidává.
- Klik se do raw logu ukládá k frázi tak, jak byla napsaná v okamžiku kliku. Přiřazení ke sjednocené session řeší rollup.

#### 4. Konfigurace a dokumentace
- Parametry balíčku v `Resources/config/parameters.yaml`, popis v docs stránce z fáze 0a.

### Kritéria úspěchu

#### Automatická verifikace:
- [ ] funkční testy Frontend API: request se čtyřmi search fieldy vytvoří jeden řádek v `search_log` se správnými počty a typem; request jen s `productsSearch` má ostatní počty `NULL`
- [ ] funkční test: `productsSearch` s filtrem nebo kurzorem nevytvoří řádek
- [ ] funkční test: request s User-Agentem crawleru nevytvoří řádek
- [ ] funkční test mutace `logSearchClick` včetně validace vstupu a rate limitu per identifikátor i per IP
- [ ] unit test maskování osobních údajů ve frázi (e-mail, telefon, číslo karty)
- [ ] storefront: `tsc`, `biome`, existující Cypress testy projdou; v Cypress prostředí je analytika zapnutá nad `shopsys_analytics_test`, takže mutace skutečně zapisuje

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
- `DbalQueryBuilderDataSource` implementující `DataSourceInterface` nad `Doctrine\DBAL\Query\QueryBuilder` (stránkování, řazení, počet řádků) v `packages/framework/src/Component/Grid/`, protože je obecně použitelný a balíček `analytics` nemá zavádět grid komponentu.

#### 3. Admin
- `Controller/Admin/SearchStatisticsController` (nová role v `AdminRoleConstant`, menu položka v sekci Marketing vedle Autocomplete favorites), grid se sloupci fráze, doména, počet vyhledání, průměr výsledků, CTR, naposledy vyhledáno; filtry období, „jen bez výsledků“, produkt; detail řádku s kliknutými produkty.
- Migrace, která novou roli přidá do existujících role groups administrátorů, plus upgrade note; jinak ji administrátoři existujících projektů neuvidí.
- Když analytika není dostupná (`AnalyticsAvailabilityChecker`), menu položka se nezobrazí.
- Dnešní den je v gridu označen jako neúplný (rollup běží hodinově), včetně času posledního úspěšného běhu rollupu z `CronModuleRun` (`packages/framework/src/Component/Cron/`).
- Překlady (cs, en, sk), texty podle konvencí administrace.

#### 4. Demo data
- `analytics:demo-data:generate --days=90` vygeneruje raw hledání a kliky nad demo produkty a spustí rollup; volá se z `db-demo` / `test-db-demo`, pokud je analytika zapnutá. Review prostředí tak mají naplněný grid pro PO a testery a SSP-4481 jde akceptovat.

### Kritéria úspěchu

#### Automatická verifikace:
- [ ] funkční test rollupu: známá raw data → očekávané řádky v `search_phrase_daily`; druhý běh nic nezmění
- [ ] funkční test sjednocení session: tři navazující autocomplete dotazy → jedno hledání s poslední frází; po pauze nad limit nebo po změně prefixu → nová session; klik během psaní se započte k poslední frázi session
- [ ] funkční test: hledání kolem půlnoci v zóně domény spadne do správného `day`
- [ ] funkční test: klik na neexistující produkt se do rollupu nezapočte
- [ ] funkční test repository (období, filtr bez výsledků, filtr podle produktu)
- [ ] smoke test admin stránky
- [ ] `phing db-demo` naplní analytickou DB vygenerovanými 90 dny

#### Ruční verifikace:
- [ ] grid odpovídá akceptačním kritériím SSP-4481 včetně detailu fráze
- [ ] grid nad vygenerovanými 90 dny odpovídá do 200 ms (čte jen rollup)
- [ ] administrátor existujícího projektu po upgradu sekci vidí

---

## Fáze 4: Worker s konektory třetích stran

### Přehled
Samostatný repozitář mimo monorepo (návrh názvu `shopsys/analytics-worker`), vlastněný marketingem: malá PHP CLI aplikace, Docker image spouštěný cronem na analytickém serveru vedle Postgresu. Jednou denně stáhne agregáty z API třetích stran a upsertuje je do DB projektů. Společným jmenovatelem s platformou je jen databáze a konvence z „Zásad pro schéma“. První konektor: zdroje návštěv z Google Analytics 4 (GA4 Data API); druhý: existující skripty marketingu na Google Ads přepsané do kostry.

Vývoj platformy dodá kostru, šablonu konektoru, testy, CI a README; marketing pak přidává a udržuje konektory. Pro marketing je celá flow jen push do `main`.

### Potřebné změny

#### 1. Repozitář a kostra (vlastník: vývoj platformy)
- Struktura:

```
analytics-worker/
├── bin/run                       # bin/run [--project=brenno] [--connector=ga4] [--from=…] [--to=…] [--dry-run]
├── bin/status                    # poslední connector_run per projekt a konektor + počty řádků
├── src/
│   ├── Connector/ConnectorInterface.php
│   ├── Connector/Ga4TrafficSourceConnector.php
│   ├── Connector/GoogleAdsCampaignConnector.php
│   ├── Runner.php                # smyčka přes projekty × konektory × okno dnů, chyby izoluje per konektor
│   ├── Writer.php                # DBAL upsert podle primárního klíče, COMMENT ON při založení tabulky
│   └── ProjectConfig.php
├── templates/ExampleConnector.php      # šablona ke zkopírování
├── tests/Connector/*Test.php           # nad nahranými odpověďmi API (fixtures/*.json)
├── config/projects.yaml                # projekty, zapnuté konektory, nesecret parametry
├── docker-compose.yml                  # lokální Postgres pro vývoj konektoru
├── Dockerfile
├── .gitlab-ci.yml
└── README.md
```

- `ConnectorInterface`: `name()`, `createTableSql()` (včetně `COMMENT ON TABLE/COLUMN`), `fetch(ProjectConfig $project, int $domainId, DateRange $range): iterable<array<string, mixed>>`. Rozsah místo jednoho dne, aby konektor volal API jednou za okno, ne 30× (kvóty GA4 Data API). Runner volá denně okno posledních 7 dní a jednou týdně 30 dní (Google Ads mění konverze až 90 dní zpětně, to řeší konektor Ads sám delším oknem).
- Každý konektor si při prvním běhu založí svoji tabulku ve schématu `connector` (`CREATE TABLE IF NOT EXISTS`), změnu sloupců řeší sám (`ALTER TABLE … ADD COLUMN IF NOT EXISTS`). Platforma tabulky konektorů nezná a nemění.
- Tabulka `connector.connector_run` (`project`, `domain_id`, `connector`, `day`, `started_at`, `finished_at`, `status`, `message`) zakládaná a plněná workerem, retence 13 měsíců; chyba jednoho konektoru nezastaví ostatní; souhrn běhu do logu a do Sentry projektu workeru.
- Konfigurace `config/projects.yaml`: per projekt DSN produkční analytické DB (jako odkaz na env), seznam zapnutých konektorů a jejich nesecret parametry (ID GA property, mapování hostname na `domain_id`). Worker zapisuje jen do produkční DB; devel DB plní noční kopie schématu `connector` (fáze 0b), takže API třetích stran se nevolá dvakrát.
- Standardy repozitáře jsou volné (vlastní verze PHP, bez phpstan a coding standards platformy), ale CI gating je jediná pojistka, protože marketing pushuje přímo do `main`: `composer install`, unit testy, integrační test proti Postgres service, build image, push do registru s tagy `sha` a `latest`. Při červené CI se image nepublikuje a cron dál běží s posledním funkčním. Vývoj dělá periodickou revizi repozitáře (např. měsíčně), ne review každého pushe.

#### 2. Flow pro marketing (vlastník: vývoj platformy, obsah README)
Cíl: marketér podle README od nuly přidá konektor i projekt bez dotazu na vývoj. README obsahuje:

1. **Nový konektor:** zkopíruj `templates/ExampleConnector.php` do `src/Connector/`, přejmenuj, doplň `name()`, `createTableSql()` a `fetch()`, přidej test s nahranou odpovědí API do `tests/Connector/` a `fixtures/`, zaregistruj konektor v `config/connectors.php`, zapni ho u projektu v `projects.yaml`. Konvence tabulek (`day`, `domain_id`, `product_id`, přirozený PK, komentáře) jsou v README jako checklist.
2. **Lokální běh:** `docker compose up -d` (Postgres s demo DB), `bin/run --project=demo --connector=ga4 --from=2026-10-20 --to=2026-10-26 --dry-run` vypíše řádky bez zápisu, bez `--dry-run` zapíše do lokální DB; `bin/status` ukáže, co se zapsalo. Přístupy ke zdrojům pro lokální běh jsou v `.env.local` (v `.gitignore`).
3. **Nový projekt:** požádat devops ticketem o DB a role (šablona ticketu v README), přidat projekt do `projects.yaml`, vyplnit CI/CD proměnné v GitLabu podle konvence názvů z fáze 0b, získat od klienta přístup ke zdroji (postup pro GA4: klient přidá servisní účet jako čtenáře property; postup pro Ads: přístup přes MCC), push.
4. **Nasazení:** push do `main`, CI zelená = image v registru a env soubor na serveru aktualizovaný; další noční běh použije novou verzi. CI červená = nic se nenasadí, cron běží s poslední funkční verzí; oprava = další push.
5. **Když něco selže:** Sentry alert workeru chodí do kanálu marketingu a vývoje; `connector_run.status = failed` s `message`; chyba jednoho konektoru nezastaví ostatní. Marketing opravuje konektor, devops řeší server a DB, vývoj kostru.
6. **Ověření, že data dorazila:** `bin/status` lokálně proti produkční DB přes VPN rolí `_readonly` (pg_hba to povoluje z VPN), nebo Adminer na analytickém serveru za VPN s rolí `_readonly` (devops), později dotaz přes MCP projektu (fáze 5).

#### 3. Konektory (vlastník: marketing, první dva s vývojem)
- `Ga4TrafficSourceConnector` – `google/analytics-data`, servisní účet; dotaz na GA4 Data API s dimenzemi zdroj / médium / kanál. Tabulka `connector.ga4_traffic_source_daily` (`day`, `domain_id`, `source`, `medium`, `channel_group`, `sessions`, `users`, `new_users`, `orders`, `revenue`; PK `(day, domain_id, source, medium)`; `day` v zóně GA property). Klient přidá servisní účet jako čtenáře své GA property (servisní účet klientův nebo sdílený Shopsys, rozhodne obchod před prvním nasazením).
- `GoogleAdsCampaignConnector` – přepis existujících skriptů marketingu; tabulka `connector.google_ads_campaign_daily` (`day`, `domain_id`, `campaign_id`, `campaign_name`, `impressions`, `clicks`, `cost`, `conversions`, `conversion_value`; `day` v zóně účtu Ads).
- Další konektory podle pořadí hodnota/pracnost: GSC (dotazy a stránky), Sklik, Heureka (výkonnostní report přes statickou URL), Meta, Zboží. U Heureky a Zboží nejdřív ověřit, co oficiálně jde stáhnout; ceny konkurence a trendy produktů z veřejného API nejsou.
- Rozhodovací pravidlo: pokud seznam zdrojů poroste k pěti a víc, udělat před dalším konektorem spike s Airbyte jako headless ingestem do týchž DB.

#### 4. Nasazení a přístupy
- Podle fáze 0b: hostovský cron na analytickém serveru spustí kontejner s tagem z env souboru; přístupy jsou CI/CD proměnné repozitáře, na server je zapisuje CI přes omezený deploy klíč; nikdy v kódu ani v `projects.yaml`.
- Role `_connector` vlastní schéma `connector` a díky default privileges čte schéma `platform` jen tehdy, když to konektor potřebuje (např. mapování produktů); připojuje se přes `localhost`.

### Kritéria úspěchu

#### Automatická verifikace:
- [ ] unit test konektoru nad nahranou odpovědí GA4 API → očekávané řádky
- [ ] integrační test v CI: `bin/run --from=… --to=…` proti Postgres service založí tabulku s komentáři, zapíše řádky a `connector_run`; druhý běh nic nezmění; chyba jednoho konektoru nezastaví druhý
- [ ] CI po pushi do `main` postaví image s tagy `sha` a `latest`; při červených testech image nepublikuje

#### Ruční verifikace:
- [ ] noční běh na analytickém serveru naplní `connector.ga4_traffic_source_daily` prvního projektu za včerejšek; ráno je totéž ve vývojové DB
- [ ] role `_readonly` vidí tabulky konektorů (default privileges fungují)
- [ ] marketér podle README od nuly přidá testovací konektor a projekt bez dotazu na vývoj, ověří data přes `bin/status`

---

## Fáze 5: MCP nad analytickou DB

### Přehled
Tool v projektovém MCP serveru pro validované read-only SQL nad analytickou DB, aby agent mohl spojovat data e-shopu, analytiky a dalších zdrojů.

### Potřebné změny

- Druhá read-only connection `analytics_mcp` s rolí `<projekt>_readonly` a stejnými session nastaveními jako `McpSessionSettingsDriver` (read-only transakce, `statement_timeout`).
- Schéma pro `SqlQueryValidator` se čte z `information_schema` analytické DB pro schémata `platform` a `connector` s blacklistem (ne z atributů entit, entity nemáme; tabulky konektorů platforma nezná dopředu). Blacklist od začátku: raw `platform.search_log` a `platform.search_click_log` (fráze mohou být osobní údaj), vystaveny jsou rollupy a celé schéma `connector`. Omezení na úrovni sloupců se doplní, až bude potřeba.
- Tooly `getAnalyticsSchema` a `executeAnalyticsSql` v `packages/mcp` (nebo `packages/analytics` s volitelnou závislostí na `shopsys/mcp`), aktivní jen při dostupnosti obou funkcí. `getAnalyticsSchema` vrací i komentáře tabulek a sloupců (`COMMENT ON`), které konvence vyžadují u každé tabulky, aby agent rozuměl datům konektorů bez dokumentace.
- Plně ověřitelné až po fázi 4 (tabulky workeru); nad daty platformy jde ověřit po fázi 3.
- Dokumentace pro uživatele MCP: doporučit dotazy nad `*_daily` tabulkami; limit řádků a `statement_timeout` platí jako u hlavní DB.

### Kritéria úspěchu

#### Automatická verifikace:
- [ ] testy validátoru: dotazy na povolené tabulky projdou, na jiné jsou odmítnuty, `INSERT` je odmítnut
- [ ] funkční test toolu vrací řádky z `search_phrase_daily` a odmítne dotaz na `search_log`

#### Ruční verifikace:
- [ ] Claude přes MCP projektu odpoví na „které fráze bez výsledků byly minulý týden nejčastější“

---

## Testovací strategie

### Unit testy
- normalizace fráze, výpočet `visitor_hash`, detekce crawlerů, validace vstupu mutace; v repozitáři workeru mapování odpovědí API na řádky tabulek.

### Funkční testy
- testovací analytická DB (`shopsys_analytics_test`, založená v `test-db-create`, schéma v `test-db-migrations`), schema migrate, dispatch → řádek, resolvery Frontend API včetně kolektoru, rollup včetně sjednocení session a timezone, repository, admin smoke test, MCP tooly. Testy závislé na druhé doméně značit `#[Group('multidomain')]`.
- Funkční testy platformy rollbackují transakci jen na `default` connection; testy analytiky proto truncatují své tabulky v `setUp` / `tearDown` přes helper v balíčku.
- Messenger v test env: `analytics_transport` je v `config/packages/test/messenger.yaml` nastaven na `sync://`, handler tak běží v rámci testu (`BatchHandlerTrait` při volání mimo worker flushuje okamžitě). Flush kolektoru na `kernel.terminate` test vyvolá explicitně.

### Ruční testování
- výpadek analytické DB během provozu, Luigi's Box doména, klik ze stránky výsledků i z našeptávače, retence (umělá stará partition).

## Výkon

- Zápis jde přes Messenger a dávky, request na e-shopu přidá jen publish zprávy do RabbitMQ.
- Odhad objemu raw dat: velký projekt (1 mil. návštěv měsíčně) zhruba 90 tis. řádků `search_log` denně, z toho většina našeptávač; extrémní projekt (10 mil. návštěv) zhruba 900 tis. denně. Protože se raw řádky nesjednocují při zápisu, drží se raw řádky našeptávače jen 3 měsíce (velký projekt jednotky GB, extrémní desítky GB); 13 měsíců raw by u extrémního projektu znamenalo stovky GB. Odeslaná hledání, kliky a rollupy za 13 měsíců jsou řádově menší. Dvouúrovňové měsíční partitions drží vacuum i indexy v mezích a retence je vždy drop partition.
- Admin a MCP čtou z rollupů (tisíce řádků denně), raw tabulky čte jen rollup nad oknem tří dní a detail fráze přes index.
- Analytický server sdílejí projekty mezi sebou; limit spojení per roli a `statement_timeout` na čtecích rolích brání tomu, aby jeden projekt vyčerpal server. Produkční DB e-shopu analytika nikdy nezatěžuje.

## Poznámky k migraci

- Pro existující projekty je zapnutí analytiky: založení dvou DB, schémat a rolí na serveru, doplnění `ANALYTICS_DATABASE_*` do nasazení, `analytics:schema:migrate` (běží v rámci `db-migrations`), přidání consumeru do projektového `deploy-project.sh` a `consumer-entrypoint.sh`, kontrola, že ingress a nginx propouští hlavičku `X-Original-User-Agent`; pro konektory přidání projektu a přístupů do repozitáře workeru. Bez env proměnných se v projektu nic nemění.
- Historická data nejdou dopočítat: statistika hledání začíná dnem nasazení, data konektorů dnem prvního běhu (zpětně jen tolik, kolik dovolí API zdroje).
- Upgrade notes: nový balíček `shopsys/analytics`, nové env proměnné, nový Messenger transport a consumer, nové cron moduly, nová admin role a její migrace do role groups, GraphQL mutation `logSearchClick`, přeposílání User-Agentu ze storefrontu. Worker upgrade notes nemá, je mimo platformu.

## Reference

- Jira: [SSP-4480](https://shopsys.atlassian.net/browse/SSP-4480), [SSP-4481](https://shopsys.atlassian.net/browse/SSP-4481), [SSP-4482](https://shopsys.atlassian.net/browse/SSP-4482)
- Výkop s Lukášem Fibigerem 19. 10. 2026 (nahrávka „Jak uchovávat statistická data o e-shopu“) a navazující diskuse 20.–27. 10. 2026 (varianty konektorů, dostupnost dat ve zdrojích, existující nástroje)
- `packages/mcp` – vzor druhé connection, read-only role, validátoru SQL a availability checkeru
- `project-base/storefront/gtm/` – existující GTM eventy a pozice produktů (`listIndex`)
- `docs/integration/luigis-box.md`
