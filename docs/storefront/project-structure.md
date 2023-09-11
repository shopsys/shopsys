# Project structure

As usual, following some project structure helps us with navigation and organization. Since we use Next.js we must follow the [Next.js project structure](https://nextjs.org/docs/getting-started/project-structure).

Besides the Next.js structure, our Storefront consist of these parts:

- **components**
  - **Basic** - generic components
  - **Blocks** - less generic components which have specific place to be used
  - **Forms** - components used for building forms
  - **Layout** - components used for building page layouts
  - **Pages** - content for pages
- **config** - base files for configuration the app
- **connectors** - collections of hooks and mappers used for better work with data from backend API
- **cypress** - part of our flow is E2E testing for which we use Cypress, this is it's codebase, docs can be found [here](./cypress.md)
- **docker** - place for all Docker related stuff to be able to run Storefront in Docker environment
- **graphql** - place to place GraphQL requests and place where generated hooks and types are imported from, informations about how we work with GraphQL are in the docs [here](./graphql.md)
- **gtm** - GTM ([Google Tag Manager](https://support.google.com/tagmanager/answer/6102821?hl=en)) this is sort of separated module in our Storefront repo, since its logic is very specific and closely related only to GTM, it's consisted of its own `helpers`, `hooks` and `types` as well as its own component used as entry point for GTM usage, GTM docs can be found [here](./gtm/index.md)
- **helpers** - all possible kinds of utilities go here
- **hooks** - all hooks which can be found in Storefront are placed here
- **pages** - Next.js required folder, here you can find entries for all pages accessible on the frontend
- **public** - Next.js required folder, place for all accessible files on the frontend (`fonts`, `images`, `locales`, ...)
- **store** - for state management we use Zustand, this folder is place for all the stores used in Storefront, docs [here](./store-management.md)
- **styles** - no rocket science, simple place for storing CSS files
- **types** - this can sometimes be on the edge with rockets science since you can wonder whether put a type here or on component level, but in this folder should end up only shared type for multiple places
- **urql** - urql is GraphQL client which we are using for GraphQL requests, here you can find all related files from `createClient` function to different kinds of caches exchanges, docs for GraphQL are [here](./graphql.md), docs for caching [here](./caching.md)
- **vitest** - for unit testing we use Vitest framework, this folder is nest for all kinds of tests, docs [here](./unit-tests.md)
