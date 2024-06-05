# Store Management

For storing various data all over the app, we use [Zustand](https://github.com/pmndrs/zustand). Since we don't rely on some heavy storing data logic, Zustand's simple API is the perfect fit for our needs.

Zustand allows us to create multiple stores. Each store consists of `slices`, which you can understand as one part of the store.

## Store structure

Under folder `/project-base/storefront/store`, you can find all possible stores available for storing any kind of values. Below, all stores are described, together with specific things for them, and when they should be used.

## Persist store

As the name suggests, this store is persisted. That means that information saved in this store will stay in your browser (specifically in your local storage) after you reload the page or even close the tab/window. Simply, it will remain there until it's removed.

### When should it be used?

Persist store should be used if you need something to work across sessions, and not be influenced by user closing the browser. This could be storing the cart UUID, various long-term identifiers, etc. Mind that persist store is not available on the server (due to local storage not being available), so if you need to store something in a persistent manner, but need it on the server as well, you should use [cookie store](#cookie-store). If you do not need to persist the data you are working with, you should look into [session store](#session-store).

### Setting the store name

It is a good practice to rename the store when you start developing your project. You should pick a very specific name, which cannot be confused with another project. This is because if you jump between multiple projects based on this codebase, and develop all of them localy, they are essentially treated as the same application (they all use the same domain). This can result in the persisted store being all messed up in your local storage. This is exactly what causes all those weird errors which simply disappear once you open the application in incognito mode in your browser.

Because of that, we suggest that the first thing you do with your persist store is a change like this:

```diff
- const PERSIST_STORE_NAME = 'shopsys-platform-persist-store';
+ const PERSIST_STORE_NAME = 'my-amazing-app-persist-store';
```

### Changes to your persist store's schema, versioning, and migrations

It is possible that you will need to make a breaking change to your schema. These could be, for example, one of the scenarios below:

-   a key is added to the schema
-   a key is removed from the schema
-   a key is renamed
-   a value for a given key changes schematically (e.g. was nullable but is not anymore, could be a number but now can only be a string, etc.)

In such situation, these steps will tell you exactly what to do:

#### 1. Increase the version of your store

```diff
export const usePersistStore = create<PersistStore>()(
    persist(
        ...
        {
            ...
-            version: 1,
+            version: 2,
```

#### 2. Add migration logic

Here you have to realize that TypeScript trusts whatever you tell it. Because of that, the type you give your `migratedPersistedState` will either help you, or really complicate your development. Some natural options are:

-   `let migratedPersistedState = { ...(persistedState as object) };` (this will enforce property checking for any property you might want to access)
-   `let migratedPersistedState = { ...(persistedState as Record<string, any>) };` (this will allow you to access any property and not check its type)
-   `let migratedPersistedState = { ...(persistedState as PersistStoreVersion2Type) };` (if you are sure the type is the store at version 2, e.g. by checking the version or the properties, you can ease your development by assigning this type)

Below you can see a version with `Record<string, any>`:

```ts
export const usePersistStore = create<PersistStore>()(
    persist(
        ...,
        {
            ...,
            migrate: (persistedState, version) => {
                const migratedPersistedState = { ...(persistedState as Record<string, any>) };

                if (version === 1) {
                    delete migratedPersistedState.oldKey;
                    migratedPersistedState.newKey = 'newValue';

                    if (migratedPersistedState.changedKey === null) {
                        migratedPersistedState.changedKey = 0;
                    } else {
                        migratedPersistedState.changedKey = 1;
                    }
                }

                return migratedPersistedState as PersistStore;
            },
        }
    ),
);
```

#### 3. Add a base version below which the store is just reset (optional)

This can be useful if the newest version is e.g. 100, and you only want to migrate the last 10 versions (which could mean visiting the app and using the store sometimes during the last year). If you want to simply reset all stores with versions lower than 90, as they are too old and migrating them would be too complicated, this is how it can be done

```ts
export const usePersistStore = create<PersistStore>()(
    persist(
        ...,
        {
            ...,
            migrate: (persistedState, version) => {
                const migratedPersistedState = { ...(persistedState as Record<string, any>) };

                if (version < 90) {
                    migratedPersistedState = {
                        ...defaultAuthLoadingState,
                        ...defaultUserState,
                        ...defaultContactInformationState,
                        ...defaultPacketeryState,
                    };
                }
                ...

                return migratedPersistedState as PersistStore;
            },
        }
    ),
);
```

#### 4. Fix the `DEFAULT_PERSIST_STORE_STATE` constant in your cypress (optional)

Remember to update the `DEFAULT_PERSIST_STORE_STATE` constant in your cypress so that it mirrors the actual default state set in your persist store.

## Cookie store

As the name suggests, this store works with cookies. That means that information saved in this store will stay in your browser (specifically in your cookies) after you reload the page or even close the tab/window. Simply, it will remain there until it's removed.

### When should it be used?

Cookie store should be used if you need something to work across sessions, and not be influenced by user closing the browser. This could be storing the cart UUID, various long-term identifiers, etc. Mind that cookie store is available on the server, which makes it especially useful if SSR is where you need your data to be accessible. If you do not need the data on the server, you should rather use [persist store](#persist-store). If you do not need to persist the data you are working with, you should look into [session store](#session-store).

### Size limitation

You might ask why even bother with persist store and local storage, when cookies seem to be more powerful. The main answer is the size limitation of cookies. By default, browsers allow cookies to be at most 4kB in size. If you put everything in your cookie store, you may reach this size very quickly, which will result in your app breaking in an unexpected way.

### Working with the state on the server

The whole magic of cookie store on the server is that it is loaded from the cookies as the first thing (inside `getServerSidePropsWrapper.ts`), then passed deeper to the SSR code where it can be worked with, and in the end returned from the `getServerSidePropsWrapper` again.

```ts
export const getServerSidePropsWrapper =
    (...) =>
    async (context: GetServerSidePropsContext) => {
        const cookiesStoreState = getCookiesStoreState(context);

        ...
        // You can work with cookiesStoreState here
        ...

        return {
            ...
            props: {
                ...
                cookiesStore: cookiesStoreState,
            },
        };
    };
```

The reason for this is that hooks cannot be used in `getServerSideProps` and the only way to access the state is to work with it as an object. Inside this state you have no access to the mutation functions (setters). You can, however, work directly with the object. This will work because the state on the client will be loaded based on what is returned from `getServerSidePropsWrapper`, and this will also be persisted in the cookie.

## Session store

As the name suggests, this store only works for the duration of the session, which can be ended by refreshing the page, or closing the browser window.

### When should it be used?

Here you should store things which do not benefit from being remembered, such as momentary global state, which can be understood as global `useState`. If you need to store data long-term and persist them, you should look into [persist store](#persist-store) or [cookie store](#cookie-store).
