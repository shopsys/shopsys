# Install 
Install all dependencies.
```javascript
npm install
```

After install open http://localhost:3000/ in your browser.

## Start app

Start the development server.
```javascript
npm run dev
```

Builds the app for production.
```javascript
npm run build
```

Runs the built app in production mode.
```javascript
npm start
```

Runs translation files generator.
```javascript
npm run translate
```

# Components

## Next.js
https://nextjs.org/learn/basics/create-nextjs-app
```javascript
npx create-next-app [your_app_name] --use-npm --example "https://github.com/vercel/next-learn-starter/tree/master/learn-starter"
```

Vytvoří složky
- pages - odtud se generuji stranky a není třeba pro ně vytvářet routování
- public - soubory v této složce jsou dostupné při zadání nazev_domeny-cz/nazev_souboru_v_public.jpg (bez složky public)

Plusy - řeší routování, SSR, static pages, je hojně používáno, existuje spoustu dalších pluginů a umožňuje lepší debug

## API client 
URQL

https://formidable.com/open-source/urql/docs/advanced/server-side-rendering/#nextjs

```javascript
npm install --save next-urql react-is urql graphql
```

Apollo Client - jsme se rozhodli nepoužívat, protože je to několikanásobně větší balík, oproti tomu urql má next.js plugin a budeme mít načítání dat pod větší kontrolou - cachování, možnost fetchnout data pouze při buildu apod.

https://formidable.com/open-source/urql/docs/comparison/ porovnání uqrl, Apollo, Relay

## React-i18next i18next
https://react.i18next.com/

```javascript
npm install react-i18next i18next --save
npm install i18next-http-backend i18next-browser-languagedetector --save
```
Plusy - možnost exportů v příkazové řácce a možnost použít BabelEdit (neodzkoušeno)

### React-i18next export překladových souborů
nastavení je v souboru config/i18next-parser.config.js
export pomocí příkazové řádky
```javascript
i18next 'pages/**/*.{js, tsx}' --config config/i18next-parser.config.js
```
nebo 

```javascript
npm run translate
```

Soubory jsou vytvořeny do public/locales/[jazyk]/translation.json - a jsou předvyplněny konstanty z původního jazyka



