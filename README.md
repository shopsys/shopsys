# Monorepo

This document provides basic information about development in monorepo to make the work with packages and project-base repository as easy as possible.

## 1. Problem
Because there is an intention for more extensive changes to framework architecture,
there will be more situations where the developer is trying to reflect the same change into several separated packages.
In the current situation, a developer had to implement this change individually in the separated repository of each package.
This approach would be inefficient and at the same time, the repeated process always brings increased errors rate.

## 2. Solution - Monorepo
Monorepo approach provides some environment for management of packages from one specific repository - monorepo repository. 

## 3. Creation of monorepo - build
Monorepo je vytvořeno za pomocí nástroje monorepo-tools. Vývojáři neupravují přímo jednotlivé oddělené repositáře ale
veškerá práce probíha jen již v rámci monorepa.

Monorepo obsahuje:
* všech větvě hlavního repositáře project-base (aktuálně obsahuje stabilní verzi shopsys frameworku)
* balíčky (jejich master verze)

Součástí monorepa je i historie jednotlivých repositářu.

## 4. Update of separated repositories by monorepo - split
Úprava provedená v monorepu se neprojeví automaticky i v puvodních repositářich, ze kterých je monorepo postaveno. K převedení
změn z monorepa i do přislušných oddělených repositářu slouží funkce split, kterou disponuje nástroj monorepo-tools.
V nejbližší dobe nebude potreba splitovat (tj aktualizovat puvodni oddelene repositare) a provedene zmeny budou ponechany
jen v samotnem monorepo repositari.

## 5. Monorepo - infrastructure
Aby bylo mozne monorepo pouzivat jako standartni aplikaci, je monorepo repositar krome spojeni puvodnich
repositaru doplnen i o infrastrukturu.

* **docker/** - šablony pro konfiguraci dockeru v monorepu.

* **build.xml** - pro použití v monorepou vznikli nějaké nové targety a u některých stávajících je upraveno chování takovym zpusobem, aby svoje
akce spoušteli nad všemi balíčky monorepa

* **composer.json** - obsahuje závislosti vyžadované jednotlivými balíčky a shopsys frameworkem. Není generován automaticky a tedy při
přidání/úpravě závislosti v composer.json konkrétního balíčku je nutno tuto změnu reflektovat i do composer.json v rootu monorepa. V monorepu jsou využívané verze balíčku přímo z packages/ a nedochází k instalaci těchto balíčku do vendoru.
Výjimkou je balíček coding-standards, který se nadále instaluje do vendoru, protože aktuální verze balíčku v packages není
frameworkem podporována. V případě přidání nového balíčku do packages/ nautoloadujeme tento balíček i v composer.json

* **parameters_monorepo.yml** - Overridování globálních proměnných shopsys frameworku aby bylo možné spustit shopsys framework z nadřazené složky

## 6. Development in monorepo
Při vývoji v monorepu je nutno zajistit, aby provedené změny zachovali funkčnost balíčku i mimo monorepa - např v případě,
že v project-base (aktuálně stabil shopsys frameworku) v rámci monorepa budu využívat proměnnou, kterou mám definovanou
jen v rootu monorepa, bude monorepo funkční aplikace. Samotný project-base ale nebude funkční aplikací (používanou proměnnou
definuji jen mimo project-base) a po splitnutí
změn do repositáře shopsys/project-base bude repositář shopsys/project-base obsahovat nefunkční verzi aplikace.

Je vhodné mít na mysli, že souborová struktura shopsys-frameworku, která je standartně umístěná v rootu projektu
je v monorepu umístěna ve složce project-base/. To se projeví např při tvorbě domains_urls.yml, který se standartně
provede spuštěním cp app/config/domains_urls.yml.dist app/config/domains_urls.yml v monorepu je ale tento příkaz
upraven na cp project-base/app/config/domains_urls.yml.dist project-base/app/config/domains_urls.yml.
Tento stav platí pro všechny příkazy, které nějak pracují se soubory z původního repositáře shopsys/shopsys
(např ./web => ./project-base/web)
