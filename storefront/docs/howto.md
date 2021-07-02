Vlastní komponenty
- pro lepší orientaci používat prefix ssfw - tedy SsfwProductItem
- Ssfw - prefix, Product = Složka, Item = název soboru
- projekt bude obsahovat mnoho cizích komponent a takto bude jasné, které jsou interní a které externí
- pro psaní názvů PascalCase - některé komponenty jsou psány s malým některé naopak s velkým - pro zjednodušení a nemusím nad tím přemýšlet - psát všude velké na začátku a těch, které si to vyžádají malé.
-- (jak používat názvy složek? pro zjednodušení taky vše PascalCasem?)

Struktura SSFW

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

config/* - nastavení jednotlivých pluginů

config/global.js - globalni nastaveni 

docs - dokumentace ke kompontentám a nastavení

public - soubory v této složce jsou dostupné v URL 

```javascript
http://localhost:3000/favicon.ico
```

```javascript
import CONFIG from '../config/global'

<p className="description">
    {t('English subtitle')} v {CONFIG.VERSION}
</p>

```

Přidání nové globální config proměnné - editovat sobuor config/global.js a použít pro odlišení kapitálkami.

```javascript
module.exports  = {
    LOCALE: 'cs',
    CURRENCY: 'CZK',
    VERSION: '0.0.10',
    NEW_VARIABLE: 'New Variable value'
 }
 ```