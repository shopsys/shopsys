### Next.js

https://nextjs.org/learn/basics/create-nextjs-app

```plain
npx create-next-app [your_app_name] --use-npm --example "https://github.com/vercel/next-learn-starter/tree/master/learn-starter"
```

Next.js creates folders
Pros - it solves routing, SSR, static pages, it is globally used, better debug, there are a lot of plugins

### API client

URQL

https://formidable.com/open-source/urql/docs/advanced/server-side-rendering/#nextjs

```plain
npm install --save next-urql react-is urql graphql
```

Apollo Client

-   we decided to not use Apollo, package is much greater in kb
-   urql has next.js plugin and we will have more control on loading sources - cache, pre-fetch data, fetch date on build...

https://formidable.com/open-source/urql/docs/comparison/ comparison uqrl, Apollo, Relay

### Endpoint configuration

GraphQL endpoint is configurable via environment variable `PUBLIC_GRAPHQL_ENDPOINT`.
Default value is set in `.env` file and this value can be overridden with local config file `.env.local` (ignored by git),
or by setting a real environment variable (for example on CI and deployed application).

### React-i18next i18next

https://react.i18next.com/

```plain
npm install react-i18next i18next --save
npm install i18next-http-backend i18next-browser-languagedetector --save
```

Pros

-   command line export
-   using BabelEdit for translate (not tried yet https://www.codeandweb.com/babeledit)

### React-i18next translate file export

setup is in file config/i18next-parser.config.js

Command line export

```plain
i18next 'pages/**/*.{js, tsx}' --config config/i18next-parser.config.js
```

or

```plain
npm run translate
```

Translate files are created to folder public/locales/[language]/translation.json - and are pre-filled by constants from en language

### Styleguidist - document your component

https://react-styleguidist.js.org/docs/getting-started

```plain
npm install --save react-styleguidist
```

If you have troubles with dependences use this line:

```plain
npm i --save react-styleguidist --legacy-peer-dep
```

https://react-styleguidist.js.org/docs/cookbook

### React Hook Form - simple React forms validation

https://react-hook-form.com/get-started

```plain
npm install react-hook-form
```

https://react-hook-form.com/api

### Yup - simple schema validation

https://github.com/jquense/yup#readme

```plain
npm install -S yup
```

https://github.com/jquense/yup#api
