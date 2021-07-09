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

Build the app for production.
```javascript
npm run build
```

Run the built app in production mode.
```javascript
npm start
```

Run eslint for code
```javascript
npm run lint
```

Run eslint and fix code
```javascript
npm run lint--fix
```

Run prettier format code
```javascript
npm run format 
```

Run translation files generator.
```javascript
npm run translate
```

# Components

## Next.js
https://nextjs.org/learn/basics/create-nextjs-app
```javascript
npx create-next-app [your_app_name] --use-npm --example "https://github.com/vercel/next-learn-starter/tree/master/learn-starter"
```

Next.js creates folders
- pages - pages are generated from there, no need to create special route
- public - files in this folder are accessable after typing domain_name-cz/file_name_in_public_folder.jpg (without folder "public")

Pluses - it solves routing, SSR, static pages, it is globally used, better debug, there are a lot of plugins

## API client 
URQL

https://formidable.com/open-source/urql/docs/advanced/server-side-rendering/#nextjs

```javascript
npm install --save next-urql react-is urql graphql
```

Apollo Client 
- we decided to not use Apollo, package is much greater in kb 
- urql has next.js plugin and we will have more control on loading sources - cache, pre-fetch data, fetch date on build...

https://formidable.com/open-source/urql/docs/comparison/ comparison uqrl, Apollo, Relay

### Endpoint configuration
GraphQL endpoint is configurable via environment variable `PUBLIC_GRAPHQL_ENDPOINT`.
Default value is set in `.env` file and this value can be overridden with local config file `.env.local` (ignored by git),
or by setting a real environment variable (for example on CI and deployed application).

## React-i18next i18next
https://react.i18next.com/

```javascript
npm install react-i18next i18next --save
npm install i18next-http-backend i18next-browser-languagedetector --save
```
Pluses 
- command line export
- using BabelEdit for translate (not tried yet https://www.codeandweb.com/babeledit)

### React-i18next translate file export
setup is in file config/i18next-parser.config.js

Command line export
```javascript
i18next 'pages/**/*.{js, tsx}' --config config/i18next-parser.config.js
```
or

```javascript
npm run translate
```

Translate files are created to folder public/locales/[language]/translation.json - and are pre-filled by constants from en language



# Coding standards
## Eslint 
- can show you error on demand when you are writing your code - and I have to install editor plugin for using it (can be used on server side in any test)
- rules are defined in files:
```
- .eslintignore, .eslintrc.json
```
## Prettier 
- can format you code on save or can be fired by key shortcut - and I have to install editor plugin for using it
- rules are defined in file:
```
- .prettierrc
```
## Editorconfig 
- can say to your editor coding standards even if you dont have any plugin installed
- rules are defined in file:
```
- .editorconfig
```
