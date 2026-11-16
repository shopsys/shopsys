# Ukládání analytických dat e-shopu

Analýza a soubor rozhodnutí pro [SSP-4480](https://shopsys.atlassian.net/browse/SSP-4480). Vychází z výkopu s datovým analytikem Lukášem Fibigerem (dále jen Fibi) 19. 10. 2026 a navazujících diskusí do 9. 11. 2026. Tělo popisuje cílový stav, zamítnuté varianty a důvody jsou v sekci 5.

Plán má dvě části: **A) statistika hledání** (SSP-4481, SSP-4482; jistý konzument, naše data) a **B) konektory třetích stran** (T2, T4, T6; cizí agregáty, které určitě přijdou; prvním konzumentem budou spíš agenti přes MCP, T7, než admin). Statistika hledání i konektory ukládají do analytické DB na samostatném serveru (T1), správa synonym na ni jen navazuje z gridu; A na B nezávisí. Tasky v sekci 4 jsou podklad pro tickety, plán se po jejich založení dropne.

## 1. Zadání

Shopsys chce mít na jednom místě statistická data o e-shopu: provozní data vznikající v e-shopu (hledané fráze, kliky z našeptávače, později oblíbené, porovnání) a agregovaná data třetích stran (GA4, Search Console, Google Ads, Sklik, Meta, Heureka, Zboží, …). Klienti chtějí výsledky vidět v administraci svého e-shopu, ne v dalším nástroji. Data mají být časem dostupná i AI agentům.

Navazující tickety: [SSP-4481](https://shopsys.atlassian.net/browse/SSP-4481) Statistika vyhledávání (první konzument), [SSP-4482](https://shopsys.atlassian.net/browse/SSP-4482) Správa synonym. Všechno se staví a ověřuje nejdřív na Stabilu, interním projektu Shopsysu; kritéria „Hotovo když“ se vztahují k němu.

Podklad od PO (listopad 2026) počítá se zdroji: marketingové nástroje (Heureka, Zboží, Google Ads, Sklik, GA, Search Console, Meta, Bing Webmaster Tools, Ecomail), e-mailing s chybami doručení, dopravci (kde je objednávka), zpětná vazba zákazníků (recenze Heureka, Zboží, Google, chatovací nástroje, sociální sítě). Nic z toho do hlavní DB e-shopu nepatří; to je důvod pro samostatné úložiště bez ohledu na to, kdy vznikne první konektor.

## 2. Rozhodnutí

1. **Úložiště je Postgres na samostatném serveru** u stejného poskytovatele jako cloud Shopsys (ČMIS), spravovaný devopsem: **dvě databáze per projekt** (produkční a vývojová), v každé role per zapisovatel: `<projekt>_app` pro platformu a `<projekt>_worker` pro worker (ve vývojové DB s příponou `_devel`), každá vlastní své tabulky, druhou jen čte a má `CONNECT` jen na svou DB; vše ve schématu `public`. Důvod je rozsah dat z podkladu PO, ne zátěž hledání. Platforma přistupuje vlastní DBAL connection, umístění DB je pro ni jen DSN. Žádné BigQuery, multitenantní DB ani API; oddělené schéma vznikne jen pro pohledy MCP (T7).
2. **Data z e-shopu zapisuje platforma** v balíčku `shopsys/analytics`: server-side v resolverech hledání, asynchronně přes Messenger, admin čte denní rollupy. Výpadek analytiky nikdy nesmí ovlivnit e-shop. Po vlastní ose sbíráme jen to, co e-shop potřebuje pro vlastní funkci (dnes hledání pro admin); chování pro marketing (zobrazení produktu, košík, nákup) zůstává v GTM a GA4 a do DB přijde konektorem.
3. **Data třetích stran stahuje Fibiho worker**: samostatný repozitář mimo platformu, naplánovaná GitLab pipeline. Každý konektor si zakládá a vlastní svou tabulku; s platformou sdílí jen databázi a pár konvencí.
4. **Jisté je zobrazení statistiky hledání a synonym v adminu** (SSP-4481, SSP-4482) a to, že konektory přijdou. Jejich data zpřístupníme nejdřív agentům přes MCP (T7); co přesně a kdy, se rozhodne nad prvními daty.
5. **Vlastníci:** vývoj (balíček, kostra workeru, pipeline workeru a její proměnné), devops (server, zakládání projektových DB, `pg_hba`, runner), Fibi (konektory). Provoz stejně: alerty balíčku (consumer neběží, roste `analytics_failed`) vývoji, alerty serveru devopsu, selhání pipeline workeru Fibimu a vývoji.

## 3. Architektura

```
 Projekt (K8s)                                     Analytický server (ČMIS, mimo K8s)
 ┌───────────────────────────────────────────────┐   ┌──────────────────────────────────────┐
 │ storefront → mutace logSearchClick            │   │ Postgres                             │
 │ Frontend API: event z resolverů hledání       │   │  <projekt>_analytics                 │
 │   → balíček shopsys/analytics                 │   │    search_log, search_autocomplete_log│
 │   → Messenger (RabbitMQ projektu) ─── INSERT ─┼───┼─►  search_click_log, *_daily (rollupy)│
 │   cron: rollup, retence           ◄── SELECT ─┼───┼──  ga4_*, google_ads_*, connector_run│
 │ admin: Marketing → Statistika vyhledávání     │   │  <projekt>_analytics_devel           │
 └───────────────────────────────────────────────┘   └──────────────────▲───────────────────┘
                                                                        │ INSERT (TLS)
 Fibiho repozitář ── push do main ── GitLab scheduled pipeline (noc) ───┘
```

- **Zápis z e-shopu:** resolver vyvolá event → listener posbírá údaje → na konci requestu jedna zpráva do Messengeru → consumer zapíše do analytické DB. Výpadek DB přežijí zprávy v RabbitMQ projektu, po vyčerpání retry skončí v ohraničené frontě `analytics_failed` tamtéž s ručním replayem, nikdy v hlavní DB. Fronty mají strop: dlouhý výpadek statistiku ořeže, e-shop nezpomalí.
- **Čtení v adminu:** jen z rollupů, raw data čte jen rollup. Názvy produktů se dohydratují z hlavní DB podle ID.
- **Worker:** dvě noční pipeline, z `main` do produkční DB, z `devel` do vývojové (detail T2). Devel čte devel zdroj, kde existuje (GA4 property jen pro devel web má část projektů), jinak produkční (Ads a Sklik devel nemají).
- **Umístění DB:** v provozu vždy na analytickém serveru: produkce projektu píše do `<projekt>_analytics` rolí `<projekt>_app`, devel prostředí projektu do `<projekt>_analytics_devel` rolí `<projekt>_app_devel`; lokálně, na review a v testech je analytická DB druhou databází v kontejneru `postgres` projektu (detail T3). Projekty hostované mimo cloud Shopsys se připojí stejně přes TLS, pokud mají stabilní IP; konektory pro ně jsou, jen pokud workeru otevřou přístup ke zdrojům.

## 4. Tasky

- **Část A:** T3 → T5 (SSP-4481); SSP-4482 souběžně. T1 startuje hned a bude hotové dřív než T5.
- **Část B:** T1 a T4 → T2 → T6, T7 po T5 a T6 (T2 ověřuje pipeline už s kostrou z T4). Konektory nečekají na konzumenta: jsou první, co bude MCP číst; otázky, které zodpovídají, a pořadí jsou v sekci 5. Do vzniku T2 Fibi vyvíjí lokálně proti `docker-compose` Postgresu.

Tasky zatím nezakládat.

### T1 – Analytický Postgres server a postup pro založení projektové DB (devops)

- **Cíl:** samostatný PostgreSQL server; pro každý projekt `<projekt>_analytics` s rolemi `<projekt>_app` (platforma) a `<projekt>_worker` (worker) a `<projekt>_analytics_devel` s `<projekt>_app_devel` a `<projekt>_worker_devel`; každá role má `CONNECT` jen na svou DB, vlastní své tabulky a tabulky druhé role jen čte.
- **Udělat:**
  - Server s Postgresem 18 (stejná major verze jako `postgres:18-alpine` v commitnutých šablonách `docker/conf/*.dist`, CI a review, aby `unaccent` a chování `public` byly všude stejné). Konfigurace jako kód (Ansible) není součástí, lze doplnit ex post. Devops nabízí na start 200 GB disku s možností přidávat, alarm na 70 %.
  - SQL skript pro založení projektu: `CREATE DATABASE` výslovně s UTF-8 a pevnou kolací (ne z `template1`; `lower` v `analytics_normalize` na kolaci závisí); dvě role per DB (`_app`, `_worker`) s `GRANT CREATE ON SCHEMA public` (od PG 15 ho `public` nemá implicitně), `REVOKE CONNECT … FROM PUBLIC` a `GRANT CONNECT` jen rolím své DB (role platí pro celý server, ne pro jednu DB; bez revoke se připojí kamkoli), `ALTER DEFAULT PRIVILEGES FOR ROLE … GRANT SELECT ON TABLES` navzájem, aby každá role vlastnila své tabulky a druhá je jen četla; `CREATE EXTENSION unaccent` vlastníkem DB; limity per roli (`CONNECTION LIMIT`, `statement_timeout`, `idle_in_transaction_session_timeout`, `temp_file_limit`); `pg_hba` per databáze a roli jen z clusterů projektů a z IP serveru s GitLabem (runnery na Proxmoxu jdou ven přes ni, clustery mají stabilní egress IP); kam uložit heslo. Tabulky devops nezakládá: platformy vytvoří `analytics:schema:migrate`, konektorů worker. Skript počítá s pozdějším přidáním čtecí role a schématu pohledů pro MCP (T7).
  - Provoz: TLS (`sslmode=verify-full`), rotace hesel, monitoring disku a spojení. Zálohy mimo server s vyzkoušenou obnovou; rollupy jsou jediná kopie historie (raw po 45 dnech zmizí), takže se hodí obnova k okamžiku před chybou; jak konkrétně a zda to zajišťuje hosting ČMIS, rozhodne devops. Jeden cluster = společný major upgrade (`pg_upgrade --link --jobs`, nejdřív na kopii, odstávku oznámit, admin a MCP jsou zákaznicky viditelné); `autovacuum_max_workers` nad výchozí 3 při desítkách DB.
  - Dokumentace `docs/integration/analytics-storage.md` (architektura, konvence, env, napojení projektu, odkaz na README workeru), zařadit v `docs/integration/navigation.yml`.
- **Hotovo když:** DB Stabilu (produkční i devel) existují podle skriptu, zálohování běží a obnova je vyzkoušená, Stabil se připojí jen přes TLS.

### T2 – Běhové prostředí workeru (vývoj s devopsem)

- **Cíl:** worker běží jako dvě naplánované GitLab pipeline svého repozitáře (`main` → produkční DB, `devel` → vývojová) a dosáhne na analytický server.
- **Udělat:** dvě noční pipeline (`STAGE=production` z `main`, `STAGE=devel` z `devel`), job `run` s `parallel:matrix` per projekt a `environment: name: $PROJECT/$STAGE`; `main` chráněný zelenou CI, `devel` se po merge drží na `main`. Přístupy jako CI/CD proměnné per environment stejně jako u projektů: `ANALYTICS_DSN` na `<projekt>/production` a `<projekt>/devel`, `GA4_SERVICE_ACCOUNT_JSON`, `GOOGLE_ADS_*` na `<projekt>/*`; DSN workeru nese roli `<projekt>_worker`, resp. `<projekt>_worker_devel` (T1), takže se devel pipeline k produkci nepřipojí ani úpravou DSN. Hlídání: Sentry projekt workeru, upozornění GitLabu na selhanou pipeline, Adminer za VPN. Žádný image, cron ani env soubor na serveru.
- Noční běh krok za krokem:
  1. Push spustí jen testy; data se při pushi nestahují.
  2. V noci pipeline vezme aktuální kód větve, přečte `projects.yaml` a pro každý projekt spustí `bin/run --project=<projekt>`.
  3. GitLab jobu podle environmentu dosadí `ANALYTICS_DSN` a přístupy ke zdrojům jako env proměnné, stejně jako projekt čte `DATABASE_PASSWORD`; nic na disku, po jobu zmizí.
  4. Konektory zavolají API zdrojů za okno 7 dní. To je pomalá část (sekundy až minuty na konektor), ne zápis.
  5. Writer se přes TLS připojí k Postgresu a upsertuje v dávkách (tisíce řádků na `INSERT`, transakce per konektor), zapíše `connector_run`, provede retenční `DELETE`. I desítky tisíc řádků za noc jsou jednotky sekund; při milionech by přešel na `COPY`.
  6. Selhání jednoho konektoru nezastaví ostatní; pipeline skončí červeně a GitLab i Sentry upozorní.
  - Proč v noci: zdroje mají denní data hotová se zpožděním (GA4 do druhého dne, Ads konverze dny), okno 7 dní včerejšek dotáhne a předchozí dny opraví; stahování při každém pushi by ukusovalo kvóty API klientů. Ruční spuštění pipeline je pro „chci to vidět teď“ a první naplnění.
- **Hotovo když:** obě pipeline doběhnou proti produkční a devel DB Stabilu, každá se svým DSN; selhání přijde jako upozornění; revert v `main` změní výsledek dalšího běhu.

### T3 – Balíček `shopsys/analytics`: infrastruktura pro zápis (vývoj)

- **Cíl:** projekt má infrastrukturu pro zápis do své analytické DB (connection, schema runner, asynchronní zápis, retence), ověřenou na testovací tabulce; výpadek analytiky nesmí ovlivnit e-shop. Konkrétní tabulky zakládá až T5.
- **Balíček a connection:** `packages/analytics` podle vzoru `packages/mcp` (registrace, split workflow, DBAL connection, availability checker), závislý na `shopsys/framework`, `shopsys/frontend-api` (vzor `shopsys/luigis-box`) a `shopsys/administration` kvůli admin sekci (jako `mcp`). Vzor je částečný: connection `mcp` je jiná role na téže DB; celý balíček po T5 (admin, GraphQL, rollup, Messenger) bude velikostí srovnatelný s `mcp`. Kód v balíčku, konfigurace v project-base jako u `mcp`: connection `analytics` v `doctrine.yaml` (bez ORM, `server_version`, `sslmode`), env `ANALYTICS_DATABASE_HOST/PORT/NAME/USER/PASSWORD` v `deploy-project.sh` a `.env*`, prázdné defaulty v `parameters.yaml` balíčku; `AnalyticsAvailabilityChecker` podle env, bez env se nic neděje; `AnalyticsConnectionProvider` pro testy. Lokálně založí DB `shopsys:database:create` (z `db-create` / `test-db-create`), init skript Postgres image ne (běží jen při prvním initu `PGDATA`). Vlastní connection je cena za přenositelnost přes DSN: testy nemají transakční rollback (truncate) a migrace přes `shopsys/migrations` bez úpravy nejdou (viz Schéma).
- **Schéma:** očíslované idempotentní SQL kroky v PHP a tabulka `schema_version`; jen aditivní `ALTER`, nové sloupce nullable nebo s defaultem (rollback release nesmí rozbít starý writer). Před schématem půldenní ověření, zda jde `shopsys/migrations` parametrizovat connection (dnes je vázané na výchozí EntityManager); jinak vlastní lehký runner nad raw SQL. Command `analytics:schema:migrate` z targetů `db-migrations` / `test-db-migrations`, při deployi nefatální (nedostupná DB = warning do Sentry, release pokračuje).
- **Bez partitioningu v první iteraci:** obyčejné tabulky s PK `event_id` a indexem `(domain_id, <čas>)`, retence nočním `DELETE` po dnech v dávkách; při 25 tis. řádků denně a 45 dnech retence (do 2 mil. řádků) partitioning nic nepřináší a přidá se až podle měření jako aditivní krok (důvody v tabulce variant).
- **Asynchronní zápis:** `AnalyticsEventDispatcher` jako jediný vstup (typ + payload), při vypnuté analytice zahodí, výjimky loguje a polyká. Transport `analytics_transport` (vzor `article_export_transport`) s dlouhými retry a vlastním failure transportem `analytics_failed`: fronta na tomtéž RabbitMQ se stropem a TTL 7 dní, tedy standardní mechanismus projektu (`messenger:failed:retry --transport=analytics_failed` nad AMQP funguje bez ID, `failed:show` ne); postup po výpadku: po obnovení DB `failed:retry`, pak ruční `analytics:rollup --from=<první den výpadku>` (T5), pokud výpadek přesáhl okno rollupu; pojistka proti přepsání menším výsledkem tu nebrání, replay data jen přidává. Zprávy starší než TTL fronty jsou ztracené, proto alert na hloubku `analytics_failed` dřív, než TTL vyprší. Globální `failed` (Doctrine v hlavní DB) by při výpadku dostal statisíce payloadů; `failure_transport: false` nestačí, `FrameworkExtension::registerMessengerConfiguration` bere `false` jako nenastaveno a přiřadí globální `failed`. Strop nebo TTL i na delay frontách (RabbitMQ při watermarku blokuje celý vhost včetně objednávek), alert na hloubku fronty, krátké `connect_timeout` a `read_timeout`. `event_id` i čas události vznikají při dispatchi a jdou ve zprávě (čas jako epoch v mikrosekundách), payload nese `version` a handler toleruje předchozí verzi po dobu TTL front. Obyčejný handler, jeden `INSERT … ON CONFLICT (event_id) DO NOTHING` per zpráva (při desetinách zprávy za sekundu nemá dávkování co ušetřit); při chybě spojení `close()` connection, aby se consumer po failoveru nezacyklil. Publish z `kernel.terminate` pod PHP-FPM uživatele nezdrží; při výpadku brokeru drží FPM workera jen krátký timeout a výjimky jdou do Sentry s throttlingem; circuit breaker až kdyby se to ukázalo jako problém. Consumer do `DEFAULT_CONSUMERS` a `consumer-entrypoint.sh`.
- **Retence:** denní cron nad registrovanými tabulkami: `DELETE` řádků starších než retence tabulky, po dnech a v dávkách. Hodnoty určuje task, který tabulku zakládá (hledání v T5: raw 45 dní, rollupy 13 měsíců); tabulky konektorů maže worker sám (T4).
- **Testy:** DB `shopsys_analytics_test` (`test-db-create`, `test-db-migrations`), `analytics_transport` v test env jako `sync://`, testovací tabulka a testový typ události jen v test schématu, truncate v `setUp`/`tearDown` (rollback platí jen pro `default` connection), multidoménové testy `#[Group('multidomain')]`.
- **Hotovo když:**
  - Connection a schéma: migrace idempotentní a aplikuje nový krok nad starší verzí; bez env se nic neděje; `phing db-create` / `test-db-create` založí DB a `doctrine:query:sql --connection=analytics 'SELECT 1'` projde.
  - Zápis: dispatch testové události → řádek, opakované doručení → stále jeden; zastavená DB nezpůsobí chybu requestu a consumer po jejím startu pokračuje bez restartu; po vyčerpání retry končí zprávy v `analytics_failed` a `failed:retry` je zapíše, ostatní transporty nedotčeny; test kontejneru hlídá, že failure transport je `analytics_failed`.
  - Retence: smaže jen řádky starší než retence testovací tabulky. Ručně: výpadek DB za provozu.

### T4 – Kostra repozitáře workeru a flow pro Fibiho (vývoj)

- **Cíl:** Fibi podle README od nuly přidá konektor i projekt a nasadí ho pushem do `main`.

```
analytics-worker/
├── bin/run                  # [--project] [--connector] [--from] [--to] [--dry-run]
├── bin/status               # poslední connector_run per projekt a konektor + počty řádků
├── src/Connector/ConnectorInterface.php    # konkrétní konektory přidává T6
├── src/Runner.php           # projekty × konektory × okno dnů, chyby izoluje per konektor
├── src/Writer.php           # DBAL upsert podle PK, COMMENT ON při založení tabulky
├── templates/ExampleConnector.php, tests/Connector/*Test.php (fixtures/*.json)
├── config/connectors.php    # registr konektorů
├── config/projects.yaml     # projekty, zapnuté konektory, zdroje per stage, nesecret parametry
├── docker-compose.yml       # lokální Postgres pro vývoj konektoru
├── .gitlab-ci.yml           # testy na MR/push + noční job `run` (viz T2)
└── README.md
```

- `ConnectorInterface`: `name()`, `createTableSql()` (s komentáři), `fetch(ProjectConfig, int $domainId, DateRange): iterable<array>`; rozsah místo dne kvůli kvótám. Runner stahuje noční okno 7 dní; zdroj, který data mění dál do minulosti (Ads konverze až 90 dní), si okno prodlouží v konektoru.
- Konektor si tabulku založí při prvním běhu (`CREATE TABLE IF NOT EXISTS`, `ADD COLUMN IF NOT EXISTS`) a drží verzi schématu v `connector_schema_version`, aby šlo poznat, kde `ALTER` selhal uprostřed desítek DB; `(not set)`, `(other)` a prázdné hodnoty v dimenzích se mapují na pevnou hodnotu (žádné `NULL` v PK). `connector_run` (`domain_id`, `connector`, `day_from`, `day_to`, `started_at`, `finished_at`, `status`, `coverage numeric` nullable, `message`): řádek per běh, konektor a doménu (projekt je dán databází); retence 13 měsíců. Retence tabulek konektorů 25 měsíců (meziroční srovnání), přepsatelná per konektor v `projects.yaml`, maže worker `DELETE` na konci běhu. Writer zapisuje do jednoho DSN; kam, rozhoduje větev pipeline.
- PHP, standardy volné. `main` chráněný: merge jen se zelenou CI (`composer install`, unit testy, integrační test proti Postgres service); nový konektor přes MR s review vývoje; změny writeru, retence a DDL by měl vidět vývoj vždy, protože se další noc projeví u všech klientů. Jak to vynutit, je detail procesu: `CODEOWNERS` to umí jen v GitLab Premium, kterou nemáme, takže to bude spíš dohoda s Fibim, že na tyto soubory přidá vývoj do MR. Rollback = revert.
- README, flow pro Fibiho: (1) nový konektor ze šablony, test s nahranou odpovědí API, registrace v `connectors.php`, zapnutí v `projects.yaml`, checklist konvencí; (2) lokální běh `docker compose up -d`, `bin/run … --dry-run`, `bin/status`, přístupy v `.env.local`; (3) vyzkoušení na develu pushem do `devel`, výsledek v Adminer; (4) nový projekt: ticket devopsu na DB a environmenty, řádek v `projects.yaml`, proměnné podle konvence, přístup od klienta (GA4: servisní účet jako čtenář property; Google Ads: připojení účtu klienta pod manažerský účet MCC, Google Ads Manager Account, pokud ho Fibi nebo agentura má, jinak přístup k účtu přímo); (5) nasazení MR z `devel` do `main`, další noční běh jede s novou verzí, po merge srovnat `devel`; (6) když něco selže: upozornění GitLabu a Sentry, `connector_run.status = failed`, ostatní konektory běží dál; Fibi opravuje konektor, devops server, vývoj kostru.
- **Hotovo když:** unit test konektoru nad nahranou odpovědí; integrační test v CI založí tabulku s komentáři, zapíše řádky a `connector_run`, druhý běh nic nezmění, chyba jednoho konektoru nezastaví druhý; červená CI zablokuje merge; Fibi podle README přidá testovací konektor a projekt bez dotazu na vývoj a vývojář bez znalosti workeru podle README spustí lokální běh (README má stačit na převzetí).

### T5 – SSP-4481 Statistika vyhledávání (vývoj; závisí na T3, jehož infrastrukturu naplní prvními tabulkami)

Zadání a akceptační kritéria jsou v [SSP-4481](https://shopsys.atlassian.net/browse/SSP-4481); tady jen to, co z analýzy vyplynulo nad jejich rámec. Nepotřebuje worker, konektory ani MCP.

**Jak napojit**
- Frontend API: jediný event `SearchResultsResolvedEvent` (typ entity, počet, fráze, `domain_id`, `isAutocomplete`, `sessionId`, provider) z resolverů `productsSearch`, `categoriesSearch`, `articlesSearch`, `brandSearch` a nepovinné `sessionId: Uuid` v `SearchInput`; malá změna, může jít samostatným PR. Funguje i s Luigi's Box (balíček LB jen implementuje provider rozhraní, query třídy zůstávají).
- Balíček: request-scoped `SearchLogCollector`, subscriber na `kernel.terminate` sestaví jeden řádek per request do `search_log` nebo `search_autocomplete_log` (nedotazovaný typ `NULL`) a předá dispatcheru. Mutace `logSearchClick(input: { type, phrase, productUuid, position, sessionId, userIdentifier })` v GraphQL typech balíčku (DI extension jako u `ShopsysLuigisBoxExtension`), validace, převod `productUuid` na `product_id` dotazem do hlavní DB (ověří i existenci; neexistující = zahodit); storefront ji volá vedle GTM click handlerů přes `fetch` s `keepalive: true`.
- Tabulky (kroky schématu tohoto tasku; raw se drží 45 dní, rollupy 13 měsíců):

| Tabulka | Sloupce | Poznámka |
|---|---|---|
| `search_log` (odeslané hledání) a `search_autocomplete_log` (našeptávač), stejná struktura | `event_id uuid`, `searched_at`, `domain_id`, `phrase`, `session_id uuid` (nullable), `product_count`, `category_count`, `article_count`, `brand_count`, `provider` (`elasticsearch`/`luigis_box`) | dvě tabulky místo jedné se sloupcem typu (našeptávač má desetinásobný objem a sjednocuje se per session), PK `event_id`; index `(domain_id, searched_at)`; bez indexu na `session_id`; fráze tak, jak přišla |
| `search_click_log` | `event_id uuid`, `clicked_at`, `domain_id`, `type` (`autocomplete`/`search`), `phrase`, `session_id uuid` (nullable), `product_id`, `position` | PK `event_id`; index `(domain_id, clicked_at)` |
| `search_phrase_daily` | `domain_id`, `day` (zóna domény), `phrase_normalized`, `phrase` (nejčastější varianta dne), per typ (`autocomplete_*`, `search_*`): `count`, `click_count`, `clicked_count` (hledání s alespoň jedním klikem), `sum_product_count`, `zero_product_count`, `sum_total_count`, `zero_total_count`; `last_searched_day` (bez přesného času, kvůli osobním údajům ve frázích) | PK `(domain_id, day, phrase_normalized)`; čítače per typ, nikde se nesčítají; `sum_total_count` = produkty + kategorie + články + značky, `zero_total_count` = bez jakéhokoli výsledku; součty místo průměrů |
| `search_phrase_period` | `domain_id`, `period_days` (7/30/90), `phrase_normalized`, `phrase` (z posledního dne), tytéž čítače, `last_searched_day` | PK `(domain_id, period_days, phrase_normalized)`; přepočítává se denně; grid čte jen odsud |
| `search_click_product_daily` | `domain_id`, `day`, `product_id`, `phrase_normalized`, `click_count`, `sum_position` | PK `(domain_id, day, product_id, phrase_normalized)`; filtr „podle produktu“ a detail fráze bez čtení raw |

- Rollup: command `analytics:rollup --from=<den> [--to=<den>]`; cron denně po půlnoci domény nad posledními 3 uzavřenými dny (dnešek nikdo nečte, hodinový rollup přijde až s konzumentem), per `(domain_id, day)` v transakci pod `pg_advisory_xact_lock(domain_id, day)` (`day` jako počet dní od epochy, funkce bere dva `int`) jako `DELETE` a `INSERT` (ne `ON CONFLICT DO UPDATE`, nechal by řádky frází, které po doručení zbytku session už neexistují); sjednocení session `DISTINCT ON (COALESCE(session_id, event_id)) … ORDER BY searched_at DESC` (`session_id` je nullable a `DISTINCT ON` bere všechna `NULL` jako stejná, takže hledání bez session musí mít vlastní klíč); `search_phrase_period` z uzavřených dní přes dočasnou tabulku a prohození (stovky tisíc řádků, bez bloatu; délku změřit na demo datech). Normalizace až tady funkcí `analytics_normalize(text)` (trim, `lower`, `unaccent`, bílé znaky, interpunkce na koncích), stejnou používá admin filtr; `unaccent()` je `STABLE`, v indexu jen přes `IMMUTABLE` wrapper s explicitním slovníkem; změna normalizace je BC break pro rollupy starší než retence raw, proto `normalize_version` ve `schema_version`. Command odmítne den mimo retenci raw a den starší než včerejšek nepřepíše řádově menším výsledkem bez `--force` (raw po výpadku consumeru mohla dorazit neúplná).
- Admin: `SearchStatisticsRepository` (DBAL jen nad rollupy) a facade s dohydratací názvů z `ProductFacade`; `Datagrid` z `AdministrationBundle` s novým DBAL adaptérem vedle `Orm` a `Array` (neexistuje; je to nová funkce `shopsys/administration` s vlastním PR, testy a upgrade note, odhadovat zvlášť), `DatagridFactory` z controlleru; `SearchStatisticsController` v sekci Marketing vedle `AutocompleteController`, role přes `RoleProviderInterface` s migrací do role groups, menu skryté při vypnuté analytice, překlady cs, en, sk; `analytics:demo-data:generate --days=90` z `db-demo` / `test-db-demo` s realistickou kardinalitou.

**Zjištění a rozhodnutí nad rámec AK**
- Psací session určuje storefront, ne heuristika „stejný prefix do pár vteřin“ z AK: `AutocompleteSearch.tsx` vygeneruje UUID při začátku psaní jen v paměti, nové po smazání textu, pauze nebo odeslání, totéž pošle v `logSearchClick`. Request bez `sessionId` (SSR, přímé volání) je vlastní session. Psaní přes půlnoc domény se rozdělí na dvě hledání ve dvou dnech; zanedbatelné, přijato.
- Celý request se neloguje, pokud `productsSearch` má filtr nebo kurzor jiný než první stránka (`SearchQuery` posílá `after` i `filter` vždy, stránkování jde `SearchProductsQuery`); jinak by řádky s `product_count = NULL` zkreslily „bez výsledků“. Kritérium „jedno hledání v prohlížeči = jeden řádek“: ověřit ssrExchange a urql refetch.
- Boti a IP: storefront při SSR posílá jen `X-Forwarded-Proto`; doplnit `X-Original-User-Agent` a `X-Forwarded-For` do `createClient.ts` (dnes je jen v `authApi.ts`; změna se týká všech SSR dotazů, ne jen hledání, samostatná položka storefrontu), jinak `getClientIp()` vrátí IP podu storefrontu (`trusted_proxies` 10.0.0.0/8) a rate limit zaškrtí všechna SSR hledání; obě hlavičky jen z důvěryhodné proxy. Rate limit per IP a `userIdentifier` v kolektoru i mutaci (klíče jen v Redisu s TTL minuty, do DB nejdou), nad limitem se řádek tiše zahodí; je to hrubé zmírnění (sdílená NAT IP ho vyčerpá legitimně), pro interní statistiku přijatelné.
- Pozice kliku: `listIndex` existuje jen u kliku ze stránky výsledků (`getGtmProductClickEvent`), u našeptávače ho storefront spočítá jako index v sekci produktů (1-based); u výsledků je pozice absolutní napříč načtenými stránkami. V odhadu storefrontu s tím počítat.
- Párování kliků: z našeptávače přes `session_id` k poslední frázi session („ipho“ → klik → „iphone“); ze stránky výsledků k frázi z URL (po odeslání vzniká nový `sessionId`), jen s hledáním téže domény a dne, jinak do vlastního dne s frází. Vědomě přibližné; přesnější `searchId` ze storefrontu je možné rozšíření, kdyby CTR neseděl. CTR per typ = `*_clicked_count / *_count` (hledání s klikem / hledání), ne kliky / hledání.
- Grid: našeptávač a odeslané hledání jsou zvláštní sloupce, nesčítají se (hledání potvrzené Enterem by bylo dvakrát); doplnit do AK. Období 7/30/90 jsou uzavřené dny do včerejška, grid ukazuje „data do <včera>“ a čas posledního rollupu z `CronModuleRun`. Filtr „bez výsledků“ = `search_count > 0 AND search_zero_product_count = search_count`, vědomě podle produktů (fráze s nulou produktů a třemi kategoriemi je pro synonyma bez výsledku); přísnější varianta přes `*_zero_total_count`. Smazané produkty se vyřadí při dohydrataci.
- Retence: AK říká „max. 13 měsíců“; raw 45 dní, rollupy 13 měsíců. Historická data nejdou dopočítat, statistika začíná dnem nasazení.
- Právní posouzení oprávněného zájmu (sekce Osobní údaje, včetně retence) pověřencem nebo právníkem: jediný externí blokátor části A a jeho výsledek může změnit návrh (souhlas by storefront musel vymáhat), proto vlastníka určit před startem T5 a výsledek mít před implementací storefrontové části.
- Upgrade notes: nový balíček, env proměnné, Messenger transport a consumer, cron moduly, admin role s migrací do role groups, mutace `logSearchClick`, přeposílání hlaviček ze storefrontu.

**Hotovo když** (nad rámec AK)
- Jedno hledání v prohlížeči (SSR + hydratace) → jeden řádek; filtr nebo kurzor za první stránku, crawler, klik na neexistující produkt → žádný řádek; request nad limitem per IP za minutu (parametr, při limitu 60 tedy 61.) → žádný řádek; hlavičky `X-Forwarded-For` a `X-Original-User-Agent` mimo důvěryhodnou proxy se ignorují; Luigi's Box doména loguje stejně; `tsc`, `biome`, Cypress s analytikou nad `shopsys_analytics_test`.
- Rollup: druhý běh nad stejnými daty nic nezmění; rozpůlená session → po dalším běhu jeden správný řádek; součty v `search_phrase_period` odpovídají denním řádkům; session test (tři dotazy se stejným `session_id` → jedno hledání s poslední frází, klik → poslední fráze; tři dotazy bez `session_id` → tři hledání); půlnoc v zóně domény.
- Admin: `db-demo` naplní 90 dnů, odezva gridu do 200 ms, administrátor Stabilu sekci po nasazení vidí; právní posouzení potvrzeno.

### T6 – První konektory (Fibi s vývojem)

- **Cíl:** v produkční DB Stabilu jsou denní data z prvních konektorů a odpovídají na jednu konkrétní otázku, kterou před startem určí Fibi (vlastník) podle toho, jaké zdroje Stabil má. Které konektory to budou a co odpoví, je na dohodě s Fibim; tabulka v sekci 5 dává kandidáty a pořadí pro portfolio, Stabil ale nemá všechny zdroje (kampaně na něm nejspíš neběží).
- **Udělat:** konektory podle dohody s Fibim; u GA4 `Ga4TrafficSourceConnector` a `Ga4ItemConnector` (`google/analytics-data`, servisní účet) → `ga4_traffic_source_daily` a `ga4_item_daily` (příloha), u Ads `GoogleAdsCampaignConnector` z Fibiho skriptů → `google_ads_campaign_daily` (`day`, `domain_id`, `campaign_id`, `campaign_name`, `impressions`, `clicks`, `cost`, `conversions`, `conversion_value`). Přístupy v CI/CD proměnných, projekt v `projects.yaml`. Úplnost `ga4_item_daily` nelze předpokládat (thresholding, řádek `(other)` u vysoké kardinality `itemId`): konektor porovná součet per `itemId` s totálem bez dimenze a uloží podíl do `connector_run.coverage`, aby šel sledovat v čase; před implementací pustit na property Stabilu jeden dotaz Data API s dimenzí `itemId` a podívat se, zda odpověď v metadatech hlásí `subjectToThresholding` (GA4 vynechává řádky s malým počtem uživatelů, když má property zapnuté Google signals); podle toho víme, jak úplná data per produkt budou a zda má smysl thresholding na property vypnout. Bezplatný BigQuery export GA4 (limit 1 mil. událostí denně) pro velké projekty nepřichází v úvahu; kvóty Data API (200 tis. tokenů denně) při nočním běhu stačí.
- Další zdroje v pořadí ze sekce 5: Sklik (XML-RPC, report bez stránkování se zkracuje na 5 000 řádků, kontrolovat počet), GSC (jediný zdroj, kde čas hraje, viz sekce 5), Heureka a Zboží (oficiální cestu ověřit před zařazením: Heureka report je popsaný jen třetími stranami, Zboží statistiky jdou přes Sklik/Fénix API), pak Meta, Bing Webmaster Tools, Ecomail, recenze.
- **Hotovo když:** pipeline z `devel` naplní tabulky zvolených konektorů za včerejšek v devel DB Stabilu a pipeline z `main` v jeho produkční DB; na dohodnutou otázku jde odpovědět dotazem nad těmito tabulkami.

### T7 – Napojení analytické DB do MCP (vývoj, první konzument dat konektorů)

- Stejným konceptem, jakým MCP od PR #4984 chrání hlavní DB: schéma s pohledy nad vybranými tabulkami a čtecí role (`default_transaction_read_only`) se `search_path` do něj; tahle role a schéma vznikají až tady. Co se vystaví, se rozhodne nad prvními daty z T6; jisté je, že raw logy hledání ne a že pohledy vystaví agregáty (zda i `phrase`, viz otevřené otázky). Omezení: hlavní DB je jiná databáze, takže agent spojí `ga4_item_daily` s cenou, skladem nebo kategorií sám přes `product_id` ze dvou MCP zdrojů.

### SSP-4482 – Správa synonym (vývoj, mimo analytickou DB)
- Nativní `synonym_graph` filtr v Elasticsearch, správa skupin per doména v adminu, reindex na pozadí, sekce skrytá pro domény s Luigi's Boxem. Jediná vazba na analytiku: z gridu SSP-4481 jde z fráze bez výsledků založit skupinu synonym s předvyplněnou frází.

## 5. Proč takto

### Co konektory přinášejí nad data e-shopu

Konektory nestahují nic, co by dnes neexistovalo; všechno je vidět v GA4, Ads, Skliku a Search Console. Hodnota je jinde: data leží vedle dat e-shopu a jdou spojit přes `product_id`, `domain_id` a den, klient je vidí v adminu bez přihlášení do pěti nástrojů a agent je přečte přes MCP. E-shop ví, **co** se stalo (objednávky, ceny, sklad, zákazníci, odběratelé, vlastní recenze, nově hledání), ale nic o tom, **odkud návštěva přišla, co stála a co se dělo před nákupem**: historii košíků nemá (`carts` drží aktuální stav) a zobrazení produktů jsou jen v nginx access logu, bez vazby na session a nákup.

| Zdroj | Co dává (denně) | Máme v e-shopu? | Otázka, kterou bez toho nezodpovíme | Spojení s daty e-shopu | Konzument |
|---|---|---|---|---|---|
| GA4 | návštěvy a uživatelé per zdroj/médium/kanál; nákupy a tržba per zdroj (atribuce GA4); zobrazení, košík, nákupy per produkt | ne (objednávky bez zdroje; košík jen aktuální stav; zobrazení jen v access logu) | odkud návštěvy chodí; konverzní poměr per kanál; funnel produktu zobrazení → košík → nákup | `product_id`, `domain_id`, den: funnel vedle ceny, skladu a kliků z hledání | admin (detail produktu), Fibi, agent |
| Google Ads, Sklik | náklady, imprese, kliky, konverze per kampaň | ne | náklad na objednávku a ROAS per kanál (spend proti nákupům per zdroj z GA4) | den, doména; per kanál přes GA4 zdroj/médium | Fibi, marketér klienta, agent |
| Search Console | dotazy na Googlu, stránka, imprese, kliky, pozice | ne (naše fráze jsou to, co lidé hledají u nás) | na které dotazy se zobrazujeme a kde; co lidé hledají na Googlu a u nás nenajdou (synonyma, obsah) | `phrase_normalized` proti GSC dotazům; URL → kategorie/produkt | Fibi (SEO), později admin vedle statistiky hledání |
| Heureka, Zboží | kliky, náklady, konverze, pozice per produkt; hodnocení obchodu | ne (feed posíláme, zpět nic nečteme) | cena za klik a za objednávku per produkt ze srovnávačů | `product_id` (ITEM_ID = naše ID), den | Fibi, admin (detail produktu) |
| Meta Ads | náklady, imprese, kliky per kampaň | ne | totéž co Ads pro sociální kanál | den, doména | Fibi |
| Ecomail a podobné | odeslané, otevřené, prokliknuté per kampaň | jen seznam odběratelů | výkon kampaní proti objednávkám z e-mailu | den, doména; zdroj `newsletter/email` v GA4 | Fibi; nízká priorita |
| Recenze (Heureka) | hodnocení a texty recenzí obchodu | vlastní recenze produktů ano, obchodu ne | spokojenost z externího zdroje | doména | nízká priorita |

Pořadí: GA4 a Ads/Sklik první (teprve dohromady odpoví na „náklad na objednávku per kanál“, GA4 sám dá funnel produktu), GSC třetí (jediný průnik s naší statistikou hledání a jediný zdroj, který historii zahazuje, proto ho nenechat dlouho za ostatními), Heureka a Zboží čtvrté, Meta, e-mailing a recenze na žádost. Pořadí určuje Fibiho zájem a existující skripty. Sloupec „Konzument“ říká, pro koho data jsou; admin obrazovky nad daty konektorů v tomto plánu nejsou, první čtení je MCP (T7).

### Zvažované varianty

| Otázka | Rozhodnutí | Zamítnuto | Důvod |
|---|---|---|---|
| Úložiště | Postgres | BigQuery; ClickHouse | BigQuery účtuje per dotaz, admin grid a agenti tvoří nepředvídatelný účet (dashboard na homepage klienta = 30 tis. Kč měsíčně) a sekundové latence; tým Postgres umí. ClickHouse až při desítkách TB na projekt. |
| Umístění | samostatný analytický server | druhá DB na produkčním Postgresu projektu (oponentura 6. 11.); tabulky v hlavní DB na `default` connection | Data z podkladu PO do hlavní DB nepatří; Postgres neumí dělit CPU, RAM ani spojení mezi databázemi a externí přístup workeru k produkčnímu serveru nechceme. Tabulky v hlavní DB by měly rollback testů a `shopsys/migrations` zadarmo, ale raw tabulky by šly do dumpů a ORM toolingu a přesun by byl změna kódu. Záloha do budoucna: kdyby se externí server ukázal jako nevhodný, je návrat k druhé DB vedle projektové jen změna DSN a `pg_dump`, protože platforma má vlastní connection; je to možné zjednodušení, ne plán. |
| Členění | databáze per projekt | multitenantní DB s `tenant_id` a RLS | Každý projekt ukládá něco jiného; izolace klientů je jen DB; cross-client pohled se složí nad více DB. |
| Rozhraní | přímý přístup do DB | HTTP ingest/read API | API klíč je stejné tajemství jako heslo, API jen přidává vrstvu; lze přidat později. |
| Role a schémata | role per zapisovatel a DB (`_app`, `_worker`, ve vývojové DB s `_devel`), vlastnictví tabulek = role, vzájemný `SELECT` přes default privileges, vše v `public` | jedna sdílená role (do 9. 11.); schémata per zapisovatel; `_readonly` role | Dvě role stojí jeden `ALTER DEFAULT PRIVILEGES` v T1 a brání tomu, aby chyba workeru smazala tabulky platformy (oponentura 6. 11.); schémata by přidala `search_path` per roli bez dalšího přínosu. Čtení pro MCP až s T7 podle konceptu pohledů. |
| Sběr provozních dat | server-side v resolverech, kliky first-party mutací | GTM → sGTM → DB; GA4 Data API → worker → DB | Hranici určuje konzument: hledání konzumuje admin, funkce platformy, která musí fungovat na každém projektu nezávisle na GTM a GA4 klienta; potřebuje úplný dlouhý ocas frází (GA4 prahuje, stahuje do `(other)`, dodává se zpožděním) a počty výsledků zná jen server. Zobrazení, košík a nákup žádná funkce e-shopu nepotřebuje a v GA4 a Ads musí být stejně. Vedlejší přínos: nezávislost na blokátorech a consentu. GTM event hledání zůstává pro marketing. |
| Zápis z e-shopu | asynchronně přes Messenger | synchronní `INSERT` v requestu | Statistika nesmí nikdy položit e-shop. |
| Umístění brokeru | RabbitMQ projektu, consumer v projektu | RabbitMQ na analytickém serveru, projekt jen publikuje | Jeden consumer pro všechny verze platformy = kontrakt zpráv (zamítnuté API přes AMQP); další sdílená stavová služba, consumer s vlastním deployem, publikování přes internet v requestu blokuje FPM při výpadku. Úspora jeden malý pod. Transport je jen DSN, lze změnit později (5. 11.). |
| Čtení v adminu | denní rollupy | dotazy nad raw | Výkon nezávislý na objemu raw dat, chrání server před „velký klient a tisíc agentů“. |
| Konektory | Fibiho worker mimo platformu (srovnání v podsekci „Konektory: worker vs. platforma“) | cron moduly v platformě; skripty volané z projektu | Konektory píše Fibi a nasazuje bez release cyklu; SDK třetích stran (Ads API: nová verze každé ~3 měsíce) se aktualizuje jednou, ne na N projektech. |
| Běh workeru | naplánované GitLab pipeline z `main` a `devel`, `parallel:matrix` per projekt | kontejner s cronem na analytickém serveru; K8s CronJob | Přístupy, logy, historie a upozornění jsou v GitLabu, nasazení je merge a rollback revert; odpadá Docker, cron, env soubor a přepínání tagů na DB hostu; worker nesoupeří s Postgresem o CPU. Stačí `pg_hba` pro IP serveru s GitLabem. |
| Devel DB | pipeline z `devel` zapisuje do devel DB; zdroj devel GA4 property, kde existuje, jinak produkční účet | jedna pipeline zapisuje do obou DSN (do 5. 11.); noční `pg_dump` z produkce | Devel role se k produkci nepřipojí, rozpracovaný konektor si Fibi prohlédne v devel DB před MR; dvojí volání API je zanedbatelné; potvrzeno Fibim. Kopie přes `pg_dump` řeší kolize s běžícími zápisy. |
| Retence raw dat | 45 dní pro všechny raw tabulky, rollupy 13 měsíců | raw 13 měsíců (našeptávač 3) podle horní hranice v AK (do 6. 11.) | Raw slouží jen rollupu, session a replayi; historii nesou rollupy a AK říká „max. 13 měsíců“. Dlouhá raw retence hnala složitost (stovky GB, PITR) bez konzumenta; při 45 dnech jsou to stovky MB. |
| Partitioning raw tabulek | bez partitioningu, dávkový `DELETE` po dnech; partitioning jako pozdější aditivní krok podle měření | týdenní partitions se správcem partitions (do 9. 11.); `DEFAULT` partition; `LIST(type)` → `RANGE`; TimescaleDB | Při 25 tis. řádků denně a 45 dnech je raw do 2 mil. řádků, `DELETE` stačí; partitioning by přinesl správce partitions, hlídání, alerty a testy (oponentura 6. 11.) a přidat ho jde později bez migrace historie. Timescale by musel být i lokálně a na review. |
| Identifikace psací session | náhodné `sessionId` ze storefrontu (jen v paměti) | hash z `userIdentifier` se solí a heuristika (5 s, prefix) v rollupu | Storefront hranice psaní zná přesně; odpadá sůl, nulovací cron, heuristika i problém s mazáním textu; žádný perzistentní identifikátor. Cena: jedno nepovinné pole v API. |
| Raw data vs. jen agregáty | raw eventy (45 dní) + rollup | jen čítače per fráze a den v Redisu; jedna klientská událost na konec psací session | AK vyžaduje sjednocení session s poslední frází a klik k frázi v okamžiku kliku, což čítače neumí; klientská událost je blokovatelná a podvrhnutelná a počty výsledků zná server. Zjednodušení jen změnou AK s PO. |
| Úložiště statistiky hledání | Postgres | Elasticsearch projektu (index per doména, ILM, `terms` agregace) | ES je na projektu cache, kterou je zvykem přeindexovat; sjednocení session by bylo při zápisu; horší pro agenty a spojování s konektory. |
| Tvar tabulek konektorů | tabulka per konektor s vlastními sloupci | unifikovaná tabulka metrik „na výšku“ | Unifikace řešila jen konektory v platformě; pojmenované sloupce jsou pro agenta čitelnější; unifikace by se neudržela. |
| Přihlašovací údaje | CI/CD proměnné repozitáře workeru | env projektu; uložení z administrace | Nejjednodušší pro jednotky projektů s agenturními přístupy. OAuth z administrace je správný koncový stav pro klientské účty, ale pozdější fáze. |
| Existující nástroje | vlastní řešení | OpenSearch UBI, Typesense; Airbyte, Keboola; Matomo, PostHog; Gorse; Prometheus | UBI a Typesense = výměna vyhledávače; Airbyte je těžká platforma s licencí ELv2 a connection per projekt × zdroj; Keboola placená per klient; Matomo a Gorse kandidáti pro chování mimo hledání a doporučování; Prometheus není na byznysová data. |

### Konektory: worker vs. platforma

Původní volba z 21. 10. byly konektory v platformě (jeden vlastník schématu, dat i admin UI, vzor feed balíčků). Po Fibiho oponentuře 27. 10. obráceno:

| | Fibiho worker (zvoleno) | Konektory v platformě (zamítnuto) |
|---|---|---|
| Kdo píše a nasazuje | Fibi, merge do `main`, noční pipeline; žádné release ani CI standardy platformy | vývoj musí každý skript zabalit, protáhnout CI platformy a vydat |
| Upgrade SDK | jednou | composer update a deploy na každém projektu |
| Vlastník tabulek | worker; platforma je nezná, dokud je nepotřebuje | platforma |
| Nový projekt | devops založí DB, Fibi přidá projekt a přístupy | env projektu |
| Infrastruktura | runner GitLabu s přístupem na server | žádná navíc |

Námitka „dva vlastníci schématu“ padla tím, že jednotné schéma není. „Projekt volá Fibiho skripty“ padlo na verzování, které Fibi dělat nebude. Z platformy zůstává zápis dat vznikajících v e-shopu. Sdílený PHP balíček s kontraktem workeru nedává smysl: rozhraní konektoru používá jen worker a monorepo balíček by ho vázal na release platformy; jediný kontrakt je SQL.

### Objem dat a co musí server zvládnout

Změřeno na čtyřech produkčních projektech z nginx access logů v Elasticsearch přes Grafanu (30 dní, bez botů; `nginx.url` nese název GraphQL operace `/graphql/AutocompleteSearchQuery` a `/graphql/SearchQuery`, SSR hledání se počítá z GET stránky hledání per doménu; starší storefront posílá `/graphql/` bez operace a změřit nejde).

| | dva z největších projektů (2 a 3,7 mil. lidských requestů denně) | střední projekt (0,3 mil. denně) |
|---|---|---|
| našeptávač | 17–19 tis. požadavků denně, špička 29 tis. | 130 |
| odeslané hledání | 3–8 tis. denně | 200 |
| raw řádků denně bez kliků | zhruba 25 tis. | zhruba 300 |
| raw za 45 dní | do 2 mil. řádků, stovky MB | zanedbatelné |
| unikátní odeslané fráze denně | zhruba 3,9 tis. (špička 5,2 tis., čtyři domény) | desítky |
| boti | 5–14 % requestů | |

Objem je hluboko pod tím, co by Postgres pocítil; u velkých projektů je poměr našeptávač : hledání 2:1 až 7:1 podle toho, zda je stránka hledání SSR nebo klientská (u středního projektu byl pod 1:1); `search_phrase_period` drží všechny fráze (100–200 tis. řádků za 90 dní per projekt); vyloučení botů je nutné. Kardinalitu našeptávače po sjednocení session ověřit na demo datech a prvním projektu.

Data konektorů (odhad podle granularity, ověřit po T6): zdroje návštěv a kampaně desítky až stovky řádků denně per doména; per produkt (GA4 `ga4_item_daily`, Heureka, Zboží) tisíce až desítky tisíc; Search Console dotaz × stránka desítky tisíc. Tabulky per produkt a GSC tedy na velkém projektu přerostou logy hledání, dohromady jednotky GB per velký projekt za rok; zápis je noční dávkový upsert, čtení přes přirozený PK.

**Server a volba Postgresu.** Při 50 projektech do 1 TB po dvou letech, noční zápis v milionech řádků, přes den čtení rollupů a dotazy agentů nad tabulkami s PK, desítky až nízké stovky spojení. Pro vanilla Postgres pohodová zóna (dávkový zápis stovky tisíc řádků za sekundu, tabulky do desítek milionů řádků s PK v milisekundách, jednotky TB běžný provoz). Postgres by přestal stačit až u ad-hoc analytiky přes stovky milionů řádků bez indexu; i tam je první obrana `statement_timeout` a pohledy pro MCP, ne jiné úložiště. ClickHouse až při desítkách TB na projekt nebo převaze analytických dotazů přes celé tabulky; díky DSN by to byla výměna úložiště, ne přepis platformy. Sizing T1: 200 GB na start stačí na první rok, do 1 TB se roste přidáváním disku; RAM podle PK indexů aktivních projektů; `max_connections` 300–500 (per projekt 3–5 spojení: FPM se k analytické DB v requestech storefrontu nepřipojuje, jen admin, consumer, cron a noční worker).

## 6. Zásady

**Schéma a konvence**
- Kdo tabulku založil, ten ji vlastní a mění: platforma `search_*`, worker prefix zdroje (`ga4_*`, `google_ads_*`) a `connector_run`; vlastnictví je i právo v DB (role per zapisovatel), druhá strana jen čte.
- Společné sloupce: `day date`, `domain_id int`, `product_id int` (naše ID, jak ho posílají feedy a GTM), `category_id int`; přirozený PK a idempotentní upsert, pořadí sloupců PK podle nejčastějšího čtení (u tabulek per produkt `(domain_id, product_id, day)`, aby historie jednoho produktu nešla přes celý rozsah dnů; u tabulek per den `(domain_id, day, …)`); `COMMENT ON TABLE/COLUMN` všude. Jen ID, nikdy názvy. `*_at` jsou `timestamptz` v UTC; `day` je v zóně toho, kdo reportuje (rollupy platformy zóna domény, konektory zóna zdroje), zóna v komentáři sloupce; zóna GA4 property a účtu Ads má odpovídat zóně domény, ověřit při napojení.
- Raw tabulky platformy jsou append-only s identitou `event_id` (UUID v7 z dispatche) a `ON CONFLICT DO NOTHING`; žádná odvozená logika při zápisu, vše v idempotentním rollupu. Rollupy ukládají součty, ne průměry. Schéma platformy verzuje `schema_version`, konektorů `connector_schema_version`.

**Osobní údaje**
- Neukládá se IP ani identifikátor návštěvníka; `userIdentifier` ze `SearchInput` se neukládá ani nehashuje. Jediný identifikátor je `session_id`, náhodné UUID jen v paměti po dobu jednoho psaní; řádky z různých psaní nejde propojit.
- Fráze se nemaskují: osobní údaj ve vyhledávání je okrajový případ, řeší ho retence (raw 45 dní; v rollupech fráze bez session a jen s dnem, ne přesným časem) a to, že raw tabulky nejsou vystavené.
- Sběr nezávisí na consentu návrhem, ne jeho ignorováním: v zařízení se nic neukládá ani nečte, v datech není IP ani identifikátor přes dvě psaní, jde o log provozu vlastní funkce e-shopu pro její správu, tedy oprávněný zájem jako access log nginx. Klíče rate limitu (IP, `userIdentifier`) žijí jen v Redisu s krátkým TTL. Data konektorů jsou agregáty, consent řeší GTM a Google.
- Hranice: identifikátor přesahující jedno psaní (např. `userIdentifier` kvůli „kolik lidí“) by spadl pod kategorii statistics a storefront by musel odesílání podmínit souhlasem. Dnes storefront consent jen sbírá a předává do dataLayeru, nic podle něj neřídí; vymáhání je v GTM kontejneru per projekt.
- Právní stránka je jinak mimo plán; že stejná data tečou do GA, náš status zpracovatele neřeší.

**Co neděláme**
- Server-side GTM, Measurement Protocol, změny stávajících GTM eventů; Grafana jako proxy pro byznysová data.
- Vlastní atribuce zdroje objednávky (UTM, `gclid`; po dohodě s Fibim: „neděláme si ambice postavit stejně dobrý data-driven model atribuce jako má GA“). Objednávky per zdroj se berou z GA4 konektoru, přesné párování přes `transaction_id` v BigQuery exportu jako rozšíření konektoru u projektů pod limitem bezplatného exportu (T6); vlastní UTM jen kdyby ho potřebovala funkce platformy.
- Obecný náhled na analytickou DB v adminu: interně stačí Adminer za VPN, klientovi konkrétní obrazovka nebo MCP. Správa konektorů a přístupů klientem v administraci; admin obrazovky nad daty konektorů před MCP; chování mimo hledání a doporučování (nejdřív Matomo, resp. Gorse).

**Governance workeru**
- Chyba workeru se další noc projeví u všech klientů, proto vlastní role a tabulky (T1), review změn writeru, retence a DDL vývojem (T4) a zálohování s obnovou k okamžiku (T1).
- Repozitář workeru drží DSN a přístupy všech klientů, je to cíl útoku; proto maskované proměnné per environment (T2).
- `X-Original-User-Agent` jde podvrhnout; slouží jen k vyloučení botů z logu.

## 7. Otevřené otázky

Kromě právního posouzení (výsledek před implementací storefrontu v T5) neblokují část A; zodpovědět před T1, resp. T6:

- **Náklady:** cena serveru a zálohování; devops je má mít před T1.
- **Mapování `domain_id` ↔ GA4 property / účet Ads** v `projects.yaml` duplikuje `domains.yaml`; kdo ho udržuje při přidání domény.
- **Kvalita dat z veřejného API:** `productsSearch` může volat kdokoli; filtr podle User-Agentu je obejitelný. Přijaté riziko zmírněné rate limitem; podepsaná hlavička storefront → API je možná pozdější úprava.
- **Mazání jednorázových frází z rollupů:** řádky `search_phrase_daily` s počtem 1 po 90 dnech mazat? Zmenší dlouhý ocas s osobními údaji i tabulky, ale jednorázové fráze z gridu zmizí. Rozhodnout s PO; souvisí s tím, zda pohledy pro MCP vystaví `phrase`.
- **Luigi's Box domény:** naše čísla se budou lišit od LB dashboardu; zobrazovat s vysvětlením. LB Reporting API (ověřené zamítnutí: `no_results_queries` jen za 30 dní) by rozbilo konzistenci s ostatními doménami.
- **Vývojový zdroj dat pro Fibiho:** GA4 devel property u projektů, které ji mají; Ads a Sklik devel nemají, vyvíjí se proti produkčnímu účtu jen pro čtení. Nový konektor se ladí nejdřív na Stabilu (první projekt v `projects.yaml`); ověřit, které zdroje Stabil má.
- **Právní posouzení:** kdo ho dělá (pověřenec, právník) a jestli 45 dní raw stačí i jemu.
- **Zástup za Fibiho a vlastník kostry workeru:** kdo převezme konektory při breaking change API zdroje a kdo ve vývoji kostru vlastní jménem.

## Příloha: jak vypadá tabulka konektoru (příklad GA4)

Konkrétní ukázka pro první konektor, aby bylo vidět, co v analytické DB reálně leží. Vychází z Fibiho požadavků („kolik přišlo včera uživatelů ze kterého zdroje“, „kolik bylo za den vhozených produktů do košíku“, data na úrovni e-shopu, kategorie a produktu po dnech) a z toho, co storefront dnes posílá do GTM a co umí GA4 Data API.

**Odkud se berou identifikátory**
- GTM kontejner je podle Fibiho praxe jeden per projekt (stejné `GTM_ID` na develu i produkci; platforma umí i per doménu přes `GTM_ID_1..3`, používá to jeden projekt kvůli oprávněním per doménu). Proměnná `Environment` v GTM odvozuje z hostname stage a jazyk a podle toho směruje data do příslušné GA4 property (produkce, devel, kde existuje) a dalších značek. Konektor proto nic nefiltruje; mapování property → `domain_id` a stage je v `projects.yaml` workeru.
- Storefront posílá v GTM eventech produkt jako `id` (naše ID produktu), `sku` (katalogové číslo), `name`, `brand`, `categories`, ceny; objednávku jako `id` (číslo objednávky), `value`, `currency` (`mapGtmProductInterface.ts`, `getGtmCreateOrderEvent.ts`). Feedy posílají totéž ID: Heureka, Zboží a Mergado `ITEM_ID`, Google `g:id` jsou `product.id` (ověřeno v `*FeedItemFactory`), katalogové číslo je zvlášť. Varianty: feedy jdou z `getAllSellableQueryBuilder`, obsahují jednotlivé varianty a hlavní varianty ne, `ITEMGROUP_ID` je ID hlavní varianty; GTM posílá ID konkrétního produktu (v košíku a objednávce varianta). `product_id` v tabulkách konektorů je proto na úrovni varianty a souhrn za hlavní variantu se dělá přes `product.main_variant_id` z hlavní DB (dohydratace v platformě, nebo zrcadlená dimenze produktu, viz T7). Předpoklad: GTM kontejner mapuje GA4 `item_id` na naše `id`; ověřit při napojení projektu, jinak konektor překládá `sku` na ID přes hlavní DB.
- GA4 Data API: dimenze `date`, `sessionSource`, `sessionMedium`, `sessionDefaultChannelGroup`, `itemId`, `itemName`; metriky `sessions`, `totalUsers`, `newUsers`, `engagedSessions`, `ecommercePurchases`, `purchaseRevenue`, `itemsViewed`, `itemsAddedToCart`, `itemsPurchased`, `itemRevenue`. Den je v zóně GA4 property.

**Tabulka 1: zdroje návštěv** (Fibi: „uživatelé ze kterého zdroje“, základ pro náklad na objednávku per kanál proti spendu z Ads a Skliku)

```sql
CREATE TABLE IF NOT EXISTS ga4_traffic_source_daily (
    day                 date           NOT NULL,
    domain_id           int            NOT NULL,
    session_source      text           NOT NULL,  -- google, seznam, facebook, (direct), heureka.cz, newsletter
    session_medium      text           NOT NULL,  -- organic, cpc, paid, (none), referral, email
    channel_group       text           NOT NULL,  -- GA4 default channel group
    sessions            int            NOT NULL,
    total_users         int            NOT NULL,
    new_users           int            NOT NULL,
    engaged_sessions    int            NOT NULL,
    purchases           int            NOT NULL,  -- ecommercePurchases, podle GA4 atribuce (data-driven)
    purchase_revenue    numeric(14,2)  NOT NULL,  -- v měně property
    PRIMARY KEY (domain_id, day, session_source, session_medium, channel_group)  -- stejné google/cpc může být Paid Search, Paid Shopping i Cross-network
);
COMMENT ON TABLE  ga4_traffic_source_daily IS 'GA4 Data API: návštěvy a nákupy per den a zdroj/médium/kanál. Den v zóně GA4 property (má odpovídat zóně domény). Nákupy podle GA4 atribuce, ne podle objednávek e-shopu. total_users a new_users nejsou sčitatelné přes řádky (jeden uživatel je ve více zdrojích).';
COMMENT ON COLUMN ga4_traffic_source_daily.purchase_revenue IS 'purchaseRevenue, měna property, podle atribuce GA4; neodpovídá přesně tržbám e-shopu (consent, blokátory).';
```

| day | domain_id | session_source | session_medium | channel_group | sessions | total_users | new_users | engaged_sessions | purchases | purchase_revenue |
|---|---|---|---|---|---|---|---|---|---|---|
| 2026-11-03 | 1 | google | organic | Organic Search | 1842 | 1610 | 905 | 1207 | 31 | 58 940.00 |
| 2026-11-03 | 1 | google | cpc | Paid Search | 611 | 574 | 402 | 390 | 14 | 27 310.00 |
| 2026-11-03 | 1 | seznam | organic | Organic Search | 388 | 351 | 190 | 240 | 6 | 9 120.00 |
| 2026-11-03 | 1 | seznam | cpc | Paid Search | 203 | 191 | 150 | 118 | 4 | 6 480.00 |
| 2026-11-03 | 1 | facebook | paid | Paid Social | 157 | 152 | 131 | 71 | 2 | 2 990.00 |
| 2026-11-03 | 1 | heureka.cz | referral | Referral | 96 | 90 | 61 | 70 | 5 | 11 250.00 |
| 2026-11-03 | 1 | newsletter | email | Email | 74 | 70 | 3 | 52 | 3 | 4 870.00 |
| 2026-11-03 | 1 | (direct) | (none) | Direct | 520 | 470 | 180 | 310 | 9 | 15 600.00 |
| 2026-11-03 | 2 | google | organic | Organic Search | 212 | 190 | 101 | 140 | 3 | 210.40 |

Druhá doména má jinou měnu (EUR), proto `purchase_revenue` v měně property a převod až při čtení.

**Tabulka 2: produkty** (Fibi: „kolik bylo za daný den vhozených produktů do košíku“, podklad pro detail produktu v adminu a pro spojení s našimi daty o hledání a kliku)

```sql
CREATE TABLE IF NOT EXISTS ga4_item_daily (
    day                 date           NOT NULL,
    domain_id           int            NOT NULL,
    product_id          int            NOT NULL,  -- naše ID produktu (GA4 item_id = GTM id); GA4 vrací itemId jako text, nečíselné hodnoty ((not set), SKU při špatném mapování) konektor zahodí a spočítá do connector_run.message
    items_viewed        int            NOT NULL,
    items_added_to_cart int            NOT NULL,
    items_purchased     int            NOT NULL,
    item_revenue        numeric(14,2)  NOT NULL,
    PRIMARY KEY (domain_id, product_id, day)  -- historie jednoho produktu bez skenu přes dny
);
COMMENT ON TABLE ga4_item_daily IS 'GA4 Data API: e-commerce události per den a produkt (itemId = naše ID produktu). Zobrazení, přidání do košíku, nákupy, tržba za položku. Není úplný funnel: řádky (other) a nečíselná itemId chybí, podíl pokrytí je v connector_run.';
```

| day | domain_id | product_id | items_viewed | items_added_to_cart | items_purchased | item_revenue |
|---|---|---|---|---|---|---|
| 2026-11-03 | 1 | 22 | 412 | 37 | 9 | 26 910.00 |
| 2026-11-03 | 1 | 59 | 298 | 21 | 4 | 3 596.00 |
| 2026-11-03 | 1 | 103 | 1 240 | 96 | 18 | 8 982.00 |
| 2026-11-03 | 1 | 7 | 155 | 2 | 0 | 0.00 |
| 2026-11-03 | 2 | 22 | 48 | 3 | 1 | 109.00 |

Názvy a kategorie produktů se v tabulce nedrží, admin je dohydratuje podle `product_id` z hlavní DB; agregace per kategorie (Fibiho „úroveň kategorie“) se dělá přes kategorie produktu z hlavní DB (dohydratace, nebo zrcadlená dimenze, viz T7), ne další tabulkou.

**Stav běhu** (`connector_run`, plní worker)

| domain_id | connector | day_from | day_to | started_at | finished_at | status | coverage | message |
|---|---|---|---|---|---|---| --- |---|
| 1 | ga4_traffic_source | 2026-10-28 | 2026-11-03 | 2026-11-04 03:10:12+00 | 2026-11-04 03:10:19+00 | ok | – | 61 rows |
| 2 | ga4_traffic_source | 2026-10-28 | 2026-11-03 | 2026-11-04 03:10:19+00 | 2026-11-04 03:10:22+00 | ok | – | 9 rows |
| 1 | ga4_item | 2026-10-28 | 2026-11-03 | 2026-11-04 03:10:22+00 | 2026-11-04 03:10:41+00 | ok | 0.93 | 8 902 rows, 12 skipped (non-numeric itemId) |
| 1 | google_ads_campaign | 2026-10-28 | 2026-11-03 | 2026-11-04 03:10:41+00 | 2026-11-04 03:10:43+00 | failed | – | Google Ads API: AUTHENTICATION_ERROR (refresh token expired) |

**Co z Fibiho seznamu tyto dvě tabulky pokrývají a co ne**
- Pokrývají: uživatelé per zdroj za den, nákupy a tržba per zdroj podle GA4 atribuce, zobrazení, košík a nákupy per produkt.
- Stejný vzor pro další zdroje: `google_ads_campaign_daily` a `sklik_campaign_daily` (den, kampaň, imprese, kliky, náklady, konverze), `gsc_query_daily` (den, dotaz, stránka, imprese, kliky, pozice), `meta_ads_daily` (den, kampaň, případně produkt), `heureka_product_daily` a `zbozi_product_daily` (den, produkt, kliky, náklady, objednávky, pozice).
- Nepokrývají a z API nejdou: chyby 404/500 a penalizace z GSC (není API; chyby máme v logu serveru), Core Web Vitals (CrUX API zvlášť), ceny konkurence a trendující produkty v kategoriích z Heureky (není veřejné API).
- Náklad na objednávku per kanál se počítá z GA4 `purchases` proti spendu z Ads a Skliku; čísla jsou podle atribuce GA4 a kvůli consentu a blokátorům nižší než objednávky e-shopu. Vlastní atribuce v platformě se nedělá (viz „Co neděláme“).

