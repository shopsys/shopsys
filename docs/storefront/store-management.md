# Store Management

For storing various data all over the app we use [Zustand](https://github.com/pmndrs/zustand). Since we don't rely on some heavy storing data logic Zustand simple API is perfect fit for our needs.

Zustand allows us to create multiple stores. Each store consists of `slices`. Which you can understand as one part of the store.

## Store structure

Under folder `/project-base/storefront/store` you can find all possible stores available for storing any kind of values.

These are stores which we currently use:

-   **Persist Store** - Store which is persisted. That means that information saved in this store will persist after you reload a page or even close the tab/window. Simply, it will remain until it's being removed. This is ideal place to store informations (`slices`) like
    -   **User Consent agreement** (to not ask about agreement of User Consent each time user opens the page)
    -   **User CartUuid or ComparisonUuid** (to not lose unlogged user Cart or Comparison)
-   **Session Store** - Store to store temporary information. All values saved in this store will be removed after reloading the page.
