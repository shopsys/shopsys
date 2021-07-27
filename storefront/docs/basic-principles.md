### Custom components
- for better orientation in code use prefix ssfw - for example SsfwProductItem
- project will contain a lot of 3rd party components, so we will see, which are SSFW components and which are 3rd party
- as possible use PascalCase - some components are lowercase, some are PascalCase 
-- much easier without thinking about it - use Upper first letter 
-- use lower first letter only on components which force it

### SSFW Storefront folder structure
components/
- This folder contains both stateless and statefull components.

pages/
- This folder contains routed pages
-- pages are generated from here, no need to create special route
config/* 
- all plugins config files
- global.js - global settings 

docs/ 
- docs for components, settings, pages, global mindset (these files are included to styleguide)
- For writing documentation we use MarkDown - https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet, https://www.markdownguide.org/cheat-sheet/

- docs/styleguide/ stand alone generated html page with all docs - generate your styleguide by 
```plain
npm run styleguide-build
```

public/
- public - files in this folder are accessible after typing <domain_name>/<file_name_in_public_folder>.jpg (without folder "public")

```plain
http://127.0.0.1:3000/favicon.ico
```

```plain
import CONFIG from '../config/global'

<p className="description">
    {t('English subtitle')} v {CONFIG.VERSION}
</p>

```

### Adding new global config variable 
- edit file config/global.js and add use UPPERCASE.

```plain
module.exports  = {
    LOCALE: 'cs',
    CURRENCY: 'CZK',
    VERSION: '0.0.10',
    NEW_VARIABLE: 'New Variable value'
 }
 ```
