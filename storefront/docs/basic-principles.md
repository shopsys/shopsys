### Custom components
- for better orientation in code use prefix shopsys - for example ShopsysProductItem
- project will contain a lot of 3rd party components, so we will see, which are Shopsys components and which are 3rd party
- as possible use PascalCase - some components are lowercase, some are PascalCase 
-- much easier without thinking about it - use Upper first letter 
-- use lower first letter only on components which force it

### Shopsys Storefront folder structure
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
import CONFIG from 'config/global'

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

### Creating a link with next.js Link and with styling
If you want to use a link with styling you have to use passHref and your code will look like something this:

```plain
<Link href="/" passHref>
    <ComponentLinkWithStyle>
        ...
    </ComponentLinkWithStyle>
</Link>
 ```

Styled-component element (in our example ComponentLinkWithStyle) will be set to `<a>` element.

### Create tabs with content switching
If you want to create unified tabs with switching its content just use these element structure:

```plain
import { Tabs, TabsContent, TabsList, TabsListItem } from 'components/basic/Tabs';

<Tabs>
    <TabsList>
        <TabsListItem>{t('Tab A Desktop')}</TabsListItem>
        <TabsListItem>{t('Tab B Desktop')}</TabsListItem>
    </TabsList>
    <TabsContent headingTextMobile={t('Tab A Mobile')}>
        Content A
    </TabsContent>
    <TabsContent headingTextMobile={t('Tab B Mobile')}>
        Content B
    </TabsContent>
</Tabs>
 ```
 Tip: you can use shorter text of Tab heading for mobile devices.

### SVG icons
**SVG icons must have these attributes:**
 - They must have define width and height in the viewBox and they must not have define width and height as a separate attribute.
 - Paths must have fill="currentColor" and they must not have color="".
We optimalize every icon with https://jakearchibald.github.io/svgomg/.
We don't use SVG icons as files but as an SVG code that is added to file IconsSvg.tsx and is used as a function.
Every svg must be define in the IconsSvgMap.tsx.

Bad defined Icon:
```plain
    <svg xmlns="http://www.w3.org/2000/svg" width="512" height="512">
        <path
            color="currentColor"
        />
    </svg>
```

Good defined Icon:
```plain
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path
            fill="currentColor"
        />
    </svg>
```

**Steps to add new svg icon:**
1. Check if your icon has viewbox and fill="currentColor".
2. Add icon to IconsSvg.tsx as a new function with source code of SVG.
3. Add icon to IconsSvgMap.tsx
4. Now your icon is ready to use.

**How to use Icons:**
For icons, we have a component Icon. The best practice to use is to create a new styled-component which is defined as an Icon component and here you can add your specific style like color, width, height, etc...

**Example:**
file.styled.ts

```plain
import Icon from 'components/basic/Icon';

export const ComponentIconStyled = styled(Icon)>`
    ${({ theme }) => css`
        height: 20px;
        width: 20px;

        color: ${theme.color.white};
    `};
`;
```plain

### Creating animated styles
If you want to create some animated dropdown or something similar you can use react-transition-group https://reactcommunity.org/react-transition-group/
