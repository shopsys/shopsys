# Veverka: analytická aplikace (SSP-4480)

Plán pro ukládání statistických dat o e-shopu. Popisuje cílový stav a postup prvního spuštění, ne historii rozhodnutí.

## Slovníček

Dokument používá důsledně tyto pojmy:

| pojem | význam |
|---|---|
| **Veverka** | nová analytická aplikace v PHP (Symfony), o které je tento plán; schovává data na později, stejně jako veverka oříšky |
| **platforma** | Shopsys Platform: kód, který běží v každém e-shopu (balíčky, `project-base`, storefront) |
| **Fibi** | Lukáš Fibiger, datový analytik; vlastník konektorů a hlavní uživatel adminu Veverky |
| **ČMIS** | hosting, kde běží analytický server |
| **projekt** | e-shop postavený na platformě: klientský, nebo demo e-shop platformy samotné (interně „stabil“) |
| **projektová DB** | hlavní Postgres databáze e-shopu (produkty, objednávky, …); Veverka do ní nikdy nesahá |
| **administrace platformy** | administrace e-shopu, kterou používá klient |
| **produkční Veverka** | nasazení Veverky z větve `main`; drží všechny stage všech projektů (i jejich stage devel) |
| **devel Veverka** | nasazení Veverky z větve `devel` pro vývoj a zkoušení konektorů; její stage jsou na zahození |
| **analytický server** | server u ČMIS, kde běží obě nasazení Veverky a analytický Postgres |
| **analytický Postgres** | Postgres na analytickém serveru; obsahuje konfigurační DB a datové DB |
| **konfigurační DB** | jedna databáze per nasazení Veverky: projekty a jejich stage, tokeny, přístupy k rolím datových DB, nastavení konektorů, přístupy klientů, historie běhů |
| **stage** | prostředí projektu (devel, produkce, alpha, …), stejně jako u deploye projektů; jednotka, kterou admin Veverky zakládá; má vlastní datovou DB, DB roli a token |
| **datová DB** | databáze s analytickými daty jedné stage (raw vyhledávání, data konektorů); jedna per stage, s vlastní zápisovou a čtecí DB rolí |
| **token** | API klíč stage, kterým se platforma při volání Veverky prokazuje |
| **šifrovací klíč** | symetrický klíč v env Veverky, kterým šifruje hesla rolí stage a přístupy klientů uložené v konfigurační DB; v DB ani v kódu není |
| **API Veverky** | HTTP rozhraní Veverky pro příjem dat z platformy |
| **konektor** | kód Veverky, který stáhne data jedné třetí strany (GA4, Sklik, …) do datové DB |
| **admin Veverky** | administrace Veverky pro Fibiho a vývoj |

## Big picture

Veverka běží na analytickém serveru a dělá tři věci:

1. **Přijímá data z platformy** přes API Veverky. Platforma jen zavolá API na správném místě (vyhledávání, našeptávač), nic dalšího neřeší.
2. **Stahuje data třetích stran** přes konektory (GA4, Google Ads, Sklik, Zboží, Heureka, Search Console, …). Konektory jsou kód Veverky; napíšeme je s Fibim, Fibi si je pak udržuje a přidává nové.
3. **Má admin pro Fibiho.** Založení projektu a jeho stage, zapnutí konektorů, zadání přístupů a ruční spuštění jsou klikání v adminu Veverky, ne změna kódu.

Data se v datových DB ukládají **raw**, bez agregací. Co z nich půjde vyčíst a jak, rozhodneme až nad reálnými daty z provozu velkého projektu. První čtenář je člověk přes Adminer nad datovou DB, případně MCP; GET API nebo samostatné MCP nad datovými DB přijde později.

```
  platforma (stage projektu)               analytický server (ČMIS)
  ┌─────────────────────┐   POST + token   ┌──────────────────────────────┐
  │ vyhledávání,        │ ───────────────► │ Veverka (PHP)                │
  │ našeptávač          │      "OK"        │  • API Veverky               │
  └─────────────────────┘                  │  • konektory (cron / klik)   │
                                           │  • admin Veverky             │
  třetí strany                             │             │                │
  GA4, Ads, Sklik, Zboží,  ◄───────────────┤             ▼                │
  Heureka, GSC …          API třetích stran│ analytický Postgres:         │
                                           │  konfigurační DB             │
                                           │  datová DB per stage         │
                                           └──────────────┬───────────────┘
                                                          │ SQL
                                             Adminer, později MCP / GET API
```

### Zásady

- **Platforma zůstává nezávislá.** Žádná druhá Doctrine connection, žádné eventy ani consumery. Jen HTTP klient s URL API Veverky a tokenem v konfiguraci. Volání probíhá až po odeslání odpovědi uživateli (`kernel.terminate`), s timeoutem v řádu stovek milisekund, aby nepadající Veverka nedržela PHP workery; výpadek Veverky se jen zaloguje a nikdy neovlivní e-shop.
- **Veverka je jednoduchá.** Žádný RabbitMQ. API Veverky ověří token, odpoví „OK“ a data zapíše do datové DB na konci requestu. Při výpadku datové DB se události ztratí a bereme to; statistika vyhledávání není účetnictví.
- **Projekt má X stage** (devel, produkce, alpha, …), stejně jako při deployi. Každá stage má vlastní datovou DB, zápisovou roli, čtecí roli a token; Veverka si je při založení stage vytvoří sama svou správcovskou rolí v analytickém Postgresu. Všechny stage mají stejné schéma: tabulky pro sběr vyhledávání a tabulky všech typů konektorů, které Veverka zná; prázdná tabulka nic nestojí a zapnutí konektoru je pak jen záznam v konfigurační DB.
- **Izolaci hlídá Postgres.** Role stage mají práva jen ve své datové DB a do ostatních se nemůžou ani připojit. API Veverky i konektory pracují s datovou DB vždy pod zápisovou rolí stage nalezené podle tokenu, nikdy pod správcovskou rolí; Adminer a později MCP pod čtecí rolí. Chyba ve výběru stage ani uniklý token tak nedovolí zapsat nebo číst data jiné stage. Před kompromitací Veverky samotné to nechrání: hesla rolí drží šifrovaná v konfigurační DB a šifrovací klíč má v env, takže kdo ovládne Veverku, má všechny stage.
- **Autentizace API Veverky** je token stage v hlavičce `Authorization: Bearer`. Nic složitějšího. Veverka ukládá jen hash tokenu, token se zobrazí jednou při vygenerování.
- **Dvě osy devel/produkce.** Devel a produkce Veverky je verze jejího kódu, devel a produkce projektu jsou data. Nemíchat:

  | | stage devel projektu | stage produkce projektu |
  |---|---|---|
  | **produkční Veverka** | trvalá data; sem posílá devel projektu | ostrá data; sem posílá produkce projektu |
  | **devel Veverka** | testovací stage na zahození pro vývoj konektorů | totéž s produkčními přístupy třetích stran; platforma sem neposílá |

  Obě nasazení mají vlastní konfigurační DB, správcovskou roli a datové DB; sdílí jen image, pipeline a analytický server.
- **Konektory jsou kód, jejich konfigurace jsou data.** Typ konektoru je PHP třída ve Veverce; zapnutí pro stage, přístupy, parametry (ID účtu, property, filtr kampaní) a rozvrh jsou v konfigurační DB a nastavují se v adminu Veverky.
- **Správa synonym** (SSP-4482) je nezávislá funkce administrace platformy. Klient v ní nastaví, co potřebuje; podklady si v první fázi vezme bokem (Adminer, MCP).

### Mimo rozsah první fáze

- Agregace a rollupy nad vyhledáváním.
- Logování kliků na výsledky vyhledávání (samostatný task T8).
- Zobrazení vyhledávacích frází v administraci platformy.
- Čtecí API nebo MCP nad datovými DB.
- Atribuce objednávek ke zdrojům (UTM), export objednávek pro BI.
- Správa konektorů klienty.
- Rotace šifrovacího klíče Veverky.

## Tasky

Každý task má řádek *Blokováno*; task bez blokace může začít hned.

### T1 Analytický server a Postgres (devops + vývoj)

Blokováno: nic.

- Server u ČMIS s Postgres 18 a rozšiřitelným diskem.
- Zálohy mimo server s vyzkoušenou obnovou.
- HTTPS před oběma nasazeními Veverky (nginx nebo Traefik, certifikáty řeší proxy, Veverka běží jen na HTTP uvnitř serveru).
- Sentry, SMTP relay pro upozornění, standardní monitoring serveru.
- Analytický Postgres přístupný jen z localhostu a z míst, odkud se bude číst (Adminer, později MCP).
- API Veverky dostupné z clusterů projektů; admin Veverky a Adminer jen přes VPN nebo IP allowlist.
- Přístup GitLab runneru na server pro deploy; pipeline samotná je v T2.

Hotovo když: bez jediného řádku Veverky lze předat: každé nasazení má v analytickém Postgresu vlastní správcovskou roli s právem zakládat DB a role, správcovská role jednoho nasazení se nepřipojí do DB druhého, proxy odpovídá na obou hostnamech a runner se na server přihlásí.

Poznámky:

- Záloha musí zahrnovat i role Postgresu (`pg_dumpall --globals-only`). Šifrovací klíč se zálohuje zvlášť (správce hesel), nikdy spolu s dumpem; bez něj jsou po obnově hesla rolí a přístupy klientů v konfigurační DB k ničemu.
- Odhad růstu disku: raw vyhledávání velkého projektu je řádově 25 tisíc řádků denně, většina z našeptávače.
- Na proxy limit velikosti těla a rate limit per token; per IP jen jako hrubá pojistka.
- Oddělení nasazení v analytickém Postgresu (vlastní správcovská role a prefix názvů DB, nebo druhý Postgres) rozhodne devops.

### T2 Kostra Veverky (vývoj)

Blokováno: T1, ale jen kvůli deployi na konci; vývoj může začít hned.

- Nový repozitář s větvemi `devel` a `main`, Symfony aplikace s konfigurační DB a přihlášením do adminu Veverky (Fibi, vývoj).
- Deploy pipeline s devops: `git push` do GitLabu → pipeline → analytický server; větev `devel` nasadí devel Veverku, `main` produkční, stejně jako u projektů.
- Entity *projekt* a *stage*.
- Založení stage v adminu vytvoří datovou DB, zápisovou a čtecí roli s právy jen do ní a spustí migrace od nuly.
- Správa stage: regenerace tokenu a hesel rolí, zobrazení přihlašovacích údajů čtecí role pro Adminer, smazání stage i s datovou DB a rolemi.
- Migrace datových DB: jedna sada ve zdrojácích Veverky (na začátku jen tabulky vyhledávání, každý typ konektoru přidá svoje), pipeline je spouští nad všemi stage.

Hotovo když: pipeline nasadí obě Veverky, v adminu Veverky založím projekt se stage a v analytickém Postgresu vznikne její datová DB s tabulkami, do které se role jiné stage nepřipojí.

Poznámky:

- Runner migrací nad N databázemi není v Doctrine Migrations standard a bude to největší položka T2. Každá datová DB si drží vlastní verzi, migrace běží po deployi a před přepnutím na novou verzi, selhání na jedné stage ostatní neblokuje, DDL běží pod zápisovou rolí, která tabulky vlastní.
- Izolace rolí v Postgresu: `REVOKE CONNECT FROM PUBLIC` na datové DB, odebrat práva na schéma `public`, zápisová role s `CONNECT` jen do své DB, čtecí role jen `SELECT` přes `ALTER DEFAULT PRIVILEGES` i na budoucí tabulky, s `statement_timeout`, read-only transakcemi a limitem spojení jako u MCP.
- `CREATE DATABASE` neběží v transakci, takže založení stage musí být idempotentní a admin musí umět nedokončenou stage dokončit nebo uklidit.
- Hesla rolí jsou v konfigurační DB šifrovaná stejným šifrovacím klíčem jako přístupy klientů.
- CI Veverky (standardy, testy konektorů nad uloženými odpověďmi API, test zakládání stage nad jednorázovým Postgresem) je nice-to-have.

### T3 API Veverky pro příjem dat o vyhledávání (vývoj)

Blokováno: T2.

- Endpoint pro zápis události vyhledávání: doména, typ (hledání / našeptávač), fráze, počet výsledků, čas, náhodné ID session z prohlížeče.
- Ověří token, odpoví „OK“ a na konci requestu zapíše do datové DB stage pod její zápisovou rolí.
- Dokumentace API Veverky v README s jedním příkladem `curl`.

Hotovo když: `curl` s tokenem stage vytvoří řádek v její datové DB.

Poznámky:

- Nevalidní payload a chyby zápisu se jen logují, platforma nikdy nečeká na datovou DB.
- Doctrine neumí měnit přihlašovací údaje za běhu, Veverka potřebuje vlastní správu spojení per stage. Spojení otevřít až po odeslání odpovědi a po zápisu zavřít, aby počet spojení nerostl s počtem stage.
- Nice-to-have: e-mail, když ze stage nepřišel žádný zápis déle než den; zapíná se per stage, u devel stage defaultně vypnuté.

### T4 Volání API Veverky z platformy (vývoj, SSP-4481)

Blokováno: nic, vyvíjí se proti mocku; ověření doopravdy až po T3.

- Malý HTTP klient (pár tříd bez vazby na zbytek platformy, aby se daly převzít do projektu na jakékoli verzi); URL API Veverky a token z env proměnných projektu.
- Napojení na vyhledávání a našeptávač ve Frontend API: volání v `kernel.terminate` s timeoutem a logováním chyb.
- Storefront posílá v inputu vyhledávání náhodné ID session.
- Lokálně a v CI běží místo Veverky mock HTTP server v docker-compose projektu.

Hotovo když: lokálně hledání na storefrontu dorazí do mocku a na review prostředí s tokenem stage devel vznikají řádky v její datové DB.

Poznámky:

- Žádný samostatný balíček: musel by žít mimo monorepo. Místo toho kód držet tak jednoduchý, aby šel převzít na jakýkoli projekt kopií souborů.
- Bez URL a tokenu se nic nevolá a payload se jen zaloguje na úrovni debug.
- ID session je jen v paměti prohlížeče, bez cookies a bez identifikace uživatele; žije do dalšího načtení stránky, takže „jedno hledání“ znamená jedno načtení stránky, ne návštěvu. Je to jediná změna storefrontu v první fázi; bez ní by se nedaly spojit dotazy našeptávače z jednoho hledání.
- Jeden HTTP request s více GraphQL dotazy musí vytvořit více událostí.
- Mock: Veverka je privátní, proto v docker-compose (lokál, GitLab CI, GitHub Actions) veřejný image (např. WireMock) se stubem „POST → 200 OK“ a `.env` projektu na něj míří jako výchozí hodnota. Vývojář vidí payloady v jeho žurnálu, funkční test se ho zeptá, zda request dorazil. Devel a produkce přepíší URL a token v CI proměnných na svou stage.

### T5 Konektory (vývoj kostra, Fibi konektory)

Blokováno: T2.

- Rozhraní konektoru `run(stage, konfigurace, od, do)`, které zapíše řádky do datové DB stage; každý typ konektoru definuje svoje tabulky.
- V adminu Veverky zapnutí konektoru pro stage, formulář s přístupy a parametry, tlačítko „spustit teď“ a historie běhů.
- Běhy vykonává cron proces, noční běh plánuje sám, selhání pošle e-mail.
- První konektory podle priority Fibiho: GA4, Google Ads, Sklik, Zboží, Heureka (oficiální API), Search Console.

Hotovo když: Fibi v adminu Veverky zapne konektor pro stage, spustí ho na klik a vidí data v její datové DB.

Poznámky:

- Běh je idempotentní nad oknem od–do (smazat a vložit) a noční běh přepisuje posledních N dní, protože třetí strany data zpětně opravují.
- „Spustit teď“ zapíše požadavek do konfigurační DB a cron ho každou minutu sebere; zámek brání souběhu téhož konektoru nad stejnou stage. Na historii v adminu se nikdo dívat nebude, proto e-mail.
- Tajemství Veverky (OAuth klient Google, developer token Ads, šifrovací klíč, přístup k analytickému Postgresu) jsou v env; přístupy klientů k třetím stranám per stage v konfigurační DB, šifrované, zadávané v adminu. V kódu není nikdy nic.
- Workflow úprav konektoru: větev → merge request s review od vývoje → `devel` → vyzkoušet na devel Veverce na testovací stage → `main`. Třetí strany mění API, takže tohle bude běžný provoz, ne výjimka.

### T6 Nasazení na první velký projekt (vývoj + devops)

Blokováno: T3, T4.

- Nezávisle na verzi platformy, na které projekt je: podle vzoru ze stabilu se do projektu ručně přenese klient, napojení na vyhledávání, konfigurace env a úprava storefrontu pro ID session. Projekt se kvůli tomu nepovyšuje.
- Zapnout na stage devel, pak produkce.

Hotovo když: produkční stage měsíc posílá data bez dopadu na e-shop.

Poznámky:

- Produkce až po právním posouzení sběru frází.
- Sledovat objem dat, zátěž API Veverky a chyby. Po měsíci provozu rozhodnout o retenci, případných agregacích a dalším postupu (čtení dat v administraci platformy, MCP).

### T7 Správa synonym (vývoj, SSP-4482)

Blokováno: nic.

Správa synonym v administraci platformy podle zadání SSP-4482, bez vazby na Veverku.

### T8 Logování kliků na výsledky vyhledávání (vývoj, později)

Blokováno: vyhodnocení T6.

Klik se děje v prohlížeči, takže storefront musí zavolat novou mutaci Frontend API (fráze, produkt, pozice, ID session) a platforma ji předá do API Veverky.

Poznámky:

- Bez kliků nemáme CTR fráze ani informaci, který produkt z výsledků lidé vybírají; pro první fázi to stačí bez nich.

## První spuštění

Konektory na začátku nepotřebujeme, první cíl je sběr dat o vyhledávání z platformy samotné:

1. Devops postaví analytický server a nasadí obě Veverky (T1, T2).
2. V adminu produkční Veverky založíme projekt „Shopsys Platform“ (stabil) a jeho stage devel a produkce (později i alpha). Veverka pro každou stage vytvoří datovou DB, roli a tabulky pro vyhledávání a vygeneruje token. Konektory zůstanou vypnuté.
3. V platformě nastavíme do CI proměnných každé stage URL API Veverky a token její stage (T4). Deploy.
4. Hledání na platformě pošle po odeslání odpovědi POST na API Veverky, ta podle tokenu najde stage a zapíše řádek do její datové DB. Ověříme Adminerem.
5. První velký projekt (T6): v adminu Veverky projekt se stage devel a produkce, tokeny do GitLab proměnných projektu, nejdřív devel, pak produkce.

T4 se dá vyvíjet hned proti mocku, ověřit doopravdy jde až po T1 až T3. Konektory (T5) do toho nevstupují a můžou přijít kdykoli potom.
