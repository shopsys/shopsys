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

### Keen slider - library-agnostic touch slider with native touch/swipe behavior and great performance

We use this package for sliders and carousels. It provides great API and performance.

https://keen-slider.io/

```plain
npm install -S keen-slider

### Smooth Scroll Polyfill 
We use it for scroll function support for all modern browsers.

https://www.npmjs.com/package/smoothscroll-polyfill

Adding to App:
```plain
import * as smoothscroll from 'smoothscroll-polyfill';

// kick off the polyfill - start using it only in browser
// add to your component these lines.
useEffect(() => {
    smoothscroll.polyfill();
}, []);
```

### Simple React Lightbox 

https://simple-react-lightbox.dev/documentation

```plain
npm install --save simple-react-lightbox
```
This package needs its own syntax with 'a' and 'img'
```plain
 <SimpleReactLightbox>
    <SRLWrapper>
        <a href="link/to/the/full/width/image.jpg">
            <img src="src/for/the/thumbnail/image.jpg" alt="Umbrella" />
        </a>
        <a href="link/to/the/full/width/image_two.jpg">
            <img src="src/for/the/thumbnail/image_two.jpg" alt="Blue sky" />
        </a>
    </SRLWrapper>
</SimpleReactLightbox>
```


```

### React tabs - An accessible and easy tab component for ReactJS
In background of styled tab parts we are using - react-tabs components.
In style file are adding elements roles.

```plain
npm install --save react-tabs
```

```plain
https://github.com/reactjs/react-tabs
```

### babel-plugin-styled-components
```plain
npm install --save-dev babel-plugin-styled-components@"<1.10.2"
```

The displayName and FileName parameters do not work correctly for newer versions of the package. Therefore, you need to install an older version. Once this bug is fixed, we will keep the version up to date.
