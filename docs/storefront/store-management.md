# Store Management

For storing various data all over the app, we use [Zustand](https://github.com/pmndrs/zustand). Since we don't rely on some heavy storing data logic, Zustand's simple API is the perfect fit for our needs.

Zustand allows us to create multiple stores. Each store consists of `slices`, which you can understand as one part of the store.

## Store structure

Under folder `/project-base/storefront/store`, you can find all possible stores available for storing any kind of values.

These are stores which we currently use:

- **Persist Store** - Store which is persisted. That means that information saved in this store will persist after you reload a page or even close the tab/window. Simply, it will remain until it's removed. This is the ideal place to store information (`slices`) like
  - **User Consent agreement** (to not ask about the agreement of user consent each time a user opens the page)
  - **User CartUuid or ComparisonUuid** (to not lose an unauthenticated user's cart or comparison)
- **Session Store** - Store for storing temporary information. All values saved in this store will be removed after reloading the page.
