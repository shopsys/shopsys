Custom components
- for better orientation in code use prefix ssfw - for example SsfwProductItem
- Ssfw - prefix, Product = Folder, Item = filename
- project will contain a lot of 3rd party comoponents, so we will see, which are SSFW components and which are 3rd party
- as possible use PascalCase - some components are lowercase, come are PascalCase 
-- much easier without thinking about it - use Upper first letter 
-- use lower first letter only on components which force it
-- (how to use folder names? PascalCase too?)

SSFW Storefront folder structure

api
- init 
- queries
  - brand
  - category
  - product

components
- forms
- layout
- header
- footer
- product
- order

pages
- Homepage
- Category
- Product-detail
- Login
- Registration
- Order
- Shipping-payment
- Order-finish

config/* - all plugins config files

config/global.js - global settings 

docs - docs for components, settings, pages, global mindset

public - in this folder are public files 

```javascript
http://localhost:3000/favicon.ico
```

```javascript
import CONFIG from '../config/global'

<p className="description">
    {t('English subtitle')} v {CONFIG.VERSION}
</p>

```

Adding new global config variable - edit file config/global.js and add use UPPERCASE.

```javascript
module.exports  = {
    LOCALE: 'cs',
    CURRENCY: 'CZK',
    VERSION: '0.0.10',
    NEW_VARIABLE: 'New Variable value'
 }
 ```