# Project structure

As usual, following a project structure helps us with navigation and organization. Since we use Next.js, we must follow the [Next.js project structure](https://nextjs.org/docs/getting-started/project-structure).

Besides the Next.js structure, our Storefront consists of these parts:

-   **components**
    -   **Basic** - generic components
    -   **Blocks** - less generic components which have a specific place to be used in
    -   **Forms** - components used for building forms
    -   **Layout** - components used for building page layouts
    -   **Pages** - content for pages
-   **config** - base files for the app configuration
-   **connectors** - collections of hooks and mappers used for better work with data from the backend API
-   **cypress** - part of our flow is E2E testing for which we use Cypress, this is its codebase, docs can be found [here](./cypress.md)
-   **docker** - place for all Docker-related stuff needed to be able to run Storefront in a Docker environment
-   **graphql** - place to put the GraphQL requests, and where generated hooks and types are imported from, information about how we work with GraphQL can be found in the docs [here](./graphql.md)
-   **gtm** - GTM ([Google Tag Manager](https://support.google.com/tagmanager/answer/6102821?hl=en)) this is sort of a separated module in our Storefront repo, since its logic is very specific and closely related only to GTM, it's consisted of its own `helpers`, `hooks` and `types` as well as its own component used as an entry point for GTM usage, GTM docs can be found [here](./gtm/index.md)
-   **helpers** - all possible kinds of utilities go here
-   **hooks** - all hooks which can be found in Storefront are placed here
-   **pages** - Next.js required folder, here you can find entries for all pages accessible on the frontend
-   **public** - Next.js required folder, place for all accessible files on the frontend (`fonts`, `images`, `locales`, ...)
-   **store** - for state management, we use Zustand, this folder is a place for all the stores used in Storefront, docs [here](./store-management.md)
-   **styles** - no rocket science, just a simple place for storing CSS files
-   **types** - this can sometimes be on the edge with rocket science since you can wonder whether to put a type here or on a component level, but in this folder, only shared types used in multiple places multiple should be put
-   **urql** - URQL is a GraphQL client which we use for GraphQL requests, here you can find all related files from the`createClient` function to all the different kinds of exchanges. Docs for GraphQL are [here](./graphql.md), docs for caching [here](./caching/index.md)
-   **vitest** - for unit testing, we use Vitest, this folder is for all kinds of tests, docs [here](./unit-tests.md)
