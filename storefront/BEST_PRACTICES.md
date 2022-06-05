## Upgrade notes from SSFW team
- this file contains information about various breaking changes introduced by the SSFW team
- it is possible that some of the changes that are easy to implement, or are of a smaller nature may be skipped from these notes
- each change note contains 
  - information about the original US
  - information about the original MR
  - reason behind the changes (may be skipped for changes with obvious reasons, such as customer section)
  - most significant changes
  - tips on how to implement them
  - section about seemingly unconnected changes that was brought as a part of the MR (may be skipped if there were no such changes)

### Intorduction of the rules of hooks ES lint rule
- [FWCC-831](https://shopsys.atlassian.net/browse/FWCC-831)
- [FWCC-831 implemented rules of hooks](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/515) 
- the reasons these changes were introduced:
  - hooks are a complex topic to gather and it may be problematic for new developers working on JS SF
  - violation of the rules of hooks were becoming more and more often which could result in run-time errors
  - having a static check is going to improve the standards of the code
- most significant changes 
  - all connector methods were renamed from "getSomething" to "useSomething" (e.g. getOrderDetail to useOrderDetail)
  - usePagination hooks now uses useMemo hook internally
  - additional test package was added to allow for testing of hooks
  - useGetInternationalizedStaticUrls was renamed to getInternationalizedStaticUrls as it is not a hook and was moved from hooks folder to utils folder
- tips on how to implement these changes
  - make sure that every custom hook's name starts with a "use" prefix
    - custom hooks are all methods that call other hooks internally, and to which the rules of hooks apply
  - make sure that no hook is called conditionally (after or inside an if/else block, after early return)

### Customer section of the website
- [FWCC-439](https://shopsys.atlassian.net/browse/FWCC-439)
- [FWCC-439 - customer profile ](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/435)
- most significant changes
  - customer page was added with links to other parts of the customer section
  - edit profile page was added together with sections for
    - password change
    - delivery address modifiactions
    - personal data change
- other changes
  - PageGuard component was introduced
    - it helps with unauthorized access redirect on client
    - was created because in come cases router redirect in the component was throwing a runtime error, as it can only be used on the client-side
    - if you want this change, check for all the router.push redirects in your components, delete them, and use the page guard wrapper instead
    - the component can be easily nested to introduce multiple redirect rules
  - CustomerTypeEnum was unified to respect DRY
    - this meant changes in many places but will be more scalable and robust in the future
  - UserDataRefresher was refactored
    - because the refresher was failing to refresh the user data in the most important situation (when the user data were modified) it had to be refactored
    - now the user contact information are updated more often (as many update conditions were removed) but it works in all the known cases
    - to save some performance, the two flows that were previously put in one useEffect hook were now split into two, so changes to dependencies does not trigger code that does not rely on those dependencies
  - login/logout mechanism now uses simple handlers instead of hooks
    - because of a bug that did not allow the user to log out, the login/logout mechanism was refactored to simple handler methods
    - all changes happened only in useAuth hook and on the outside everything is the same
    - this approach should be easier to understand and to extend
  - isCompanyUser property was renamed to companyUser
    - this was done as the datapoint is called companyUser on the API
    - this should ease mapping and working with the property inside API calls/methods
  - ControllerRenderProps and TFunction typings were fixed
    - incorrect typings for ControllerRenderProps and TFunction were displaying annoying errors in some IDEs
    - even though this was not a compilation error, it was refactored so now the errors are not displayed, which should ease debugging

### Using picture tag instead of NextImage in Image component
- [FWCC-322](https://shopsys.atlassian.net/browse/FWCC-322)
- [FWCC-322 - Responsive picture element](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/538/)
- the reasons these changes were introduced:
  - we need simple generic way to display images in different sizes on different devices (as defined in images.yaml)
- most significant changes
  - the `ImageSizeFragment` now contains `additionalSizes` property with an array of all other sizes of the image
  - a new `ImageType` was introduced, this type contains array of all possible sizes of the image
  - all types that contain image now use `ImageType` instead of `ImageSizeType` (all possible sizes instead of only one)
  - the Image component now accepts `ImageType` instead of `ImageSizeType` and also new required parameter `type` which defines the specific size to be used (according to specification in images.yaml)
  - the Image component uses the html `<picture />` tag with sources instead of NextImage component

### Mobile banner for home page
- [FWCC-757](https://shopsys.atlassian.net/browse/FWCC-757)
- [FWCC-757 - Banner images - mobile vs desktop](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/539/diffs)
    - the reasons these changes were introduced:
      - to allow us to use different images for desktop and mobile in home page slider
    - most significant changes
      - the definition of images in `images.yaml` has been changed to use only one (default) size for web and mobile devices and to implement the `additionalSizes` field
      - the BannerSlider component now uses the `useGetWindowSize` hook and the `getBannersSliderItemImage` method that decides, which image should be used
    - tips on how to implement them
      - you can change the breakpoint when mobile/desktop variant is to be used by changing the `desktopVariant` parameter of `getBannersSliderItemImage` method

### Transport and Payment cart mutations
- [FWCC-843](https://shopsys.atlassian.net/browse/FWCC-843)
- most significant changes
  - changes of transport and payment (together with related fields, such as personal pickup place identifier and GoPay SWIFT) are handled in separate mutations (ChangeTransportInCart and ChangePaymentInCart)
  - cart slice of redux has been completely removed with cart UUID being moved to the user slice
  - all information about cart is now loaded from useCurrentCart hook
  - each mutation has a handler method that can be easily extended for future needs (GTM, logging)
    - the reason behind this decision is that the logic for same operation was often scattered around, breaking DRY
    - it was also hard to pinpoint the exact place where an event could be extended
    - simple handler methods with async/await handling are easier to understand and work with
    - in all of the hooks (useApplyPromoCodeToCart, useChangeTransportInCart, usePaymentInCart, useRemovePromoCodeFromCart) there is a handler method with a comment where the event can be extended
    ```js
        const applyPromoCodeHandler = async (newPromoCode: string, messages: { success: string; error: string }) => {
        const applyPromoCodeResult = await applyPromoCodeToCart({ input: { promoCode: newPromoCode, cartUuid } });

        // EXTEND PROMO CODE MODIFICATIONS HERE
        console.log('Is applyPromoCodeResult null?', applyPromoCodeResult === null)

        if (applyPromoCodeResult.error !== undefined) {
            const { userError } = getUserFriendlyErrors(applyPromoCodeResult.error, t);
            if (userError?.validation?.promoCode !== undefined) {
                showErrorMessage(userError.validation.promoCode.message);
            } else {
                showErrorMessage(messages.error);
            }

            return null;
        }

        showSuccessMessage(messages.success);

        return applyPromoCodeResult.data?.ApplyPromoCodeToCart;
    };
    ```
  - cart state utility hooks and methods were removed as they were not needed anymore
  - inputs for cart queries/mutations now use input objects that use types generated based on API schema
    - instead of having 2 arguments for the mutation (foo, bar) we now only use input, that has the separate arguments inside
    ```
    mutation FooMutation($input: FooMutationInput!) {
      Foo(input: $input) {
        ...
      }
    }
    ```

    instead of

        ```
    mutation FooMutation($foo: String!, bar: String!) {
      Foo(input: {foo: $foo, bar: $bar}) {
        ...
      }
    }
    ```
    - this allows us to modify input types without having to touch the GQL definitions
- other changes
  - AddToCart result now doesn't iherit from Cart, but implements Cart as its property
    - the reason behind this decision is that the GQL cache did not catch updates to cart fragment when AddToCart result extended cart
    - by using composition instead of inheritance the issue was solved
  - URQL devtools exchange has been added which allows for better debugging of the current state of the GQL cache and various operations performed on it
  - client can be now injected into initServerSideProps if it is needed outside of the method
    - this is required if we want to call a query/mutation inside getServerSideProps but outside of initServerSideProps
    - we can easily initialize the client before, call the required operation and then inject the client into initServerSideProps method to still the same GQL cache
- tips on how to implement these changes
  - if you need the current cart in a component, use the useCurrentCart hook, which also includes transport, payment, promo code, pickup place identifier, and GoPay bank swift
  - if you have a custom data point in the current cart implementation in redux, you can either use its value directly from GQL if the value comes from the API, or you can keep it in redux as a single value and then use it in the useCurrentCart hook to propagate it further

### Add to cart and remove from cart handlers refactoring
- [FWCC-845](https://shopsys.atlassian.net/browse/FWCC-845)
- [FWCC-845 added handlers for adding to and removing from cart ](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/543)
- most significant changes
  - adding to cart and removing from cart is now done using extendable handlers
  - AddToCartPopup is mapped using a simple mapper and its state is stored in a useState hook
  - other messages and errors regarding adding to cart are handled directly inside the handler method
- other changes
  - AddToCart result now also returns the added cart item so we do not have to perfrom search on the result to find the added item

### Setting up the stylelint linter
- [FWCC-901](https://shopsys.atlassian.net/browse/FWCC-901)
- [FWCC-901 Stylelint](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/548)
- the reasons these changes were introduced:
    - type checking of the CSS notation within styled-components to keep the CSS code clean and valid
- most significant changes
    - the stylelint package was added to the project and CI config
- tips on how to implement these changes
    - run `npm run stylelint` locally and fix reported issues

### User data queries
- [FWCC-843](https://shopsys.atlassian.net/browse/FWCC-894)
- [FWCC-439 - User data & user contact information refactoring](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/544)
- most significant changes
  - `UserDataRefresher` was removed - now we store only data of a not-logged-in user in Redux, so we don't need to update store from API
  - user data are read from Redux (for an anonymous user) or directly from API (for a signed user) (uses new `useCurrentUserContactInformation` hook), it helps us to keep the data always up-to-date thanks to the GraphQl cache
  - `isUserLoggedIn` was removed from Redux UserSlice, use `useCurrentUserData` hook instead - the property is now based on data from the API which helps us to avoid incorrect values and issues with updating this values in the Redux store
- other changes
  - `useCurrentCustomerUser` was renamed to `useCurrentCustomerContactInformationQuery`
- tips on how to implement these changes
  - if you need to extend the contact information, extend the `ContactInformationFormType` type in `form.ts`, the `mapCurrentCustomerContactInformationApiData` mapper in `CurrentCustomerUser.ts` (for API data) and the `contactInformationSlice` in Redux (for anonymous user)
  - if you need to extend the user data, extend the `CurrentCustomerType` type and the `mapCurrentCustomerApiData` mapper in `CurrentCustomer.ts`

### Introduction of the eslint react-hooks/exhaustive-deps rule
- [FWCC-909](https://shopsys.atlassian.net/browse/FWCC-909)
- [FWCC-909 - add eslint rule react-hooks/exhaustive-deps](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/553/diffs)
- the reasons these changes were introduced
    - the new rule helps to keep the deps of React hooks (useEffect, useCallback and useMemo) being set correctly which prevents bugs and unexpected behavior
- most significant changes
    - the `eslint react-hooks/exhaustive-deps` eslint rule was configured and reported bugs were fixed
    - new `useEffectOnce` hook was introduced. It should be used when you want to call the code only once on the first render
- other changes
    - in the process of fixing reported bugs it was necessary to change some code, some useMemo and useCallback hooks were added
- tips on how to implement them
    - run `npm run lint` and fix all newly reported bugs

### Build storefront from Alpine Linux 
- [FWCC-819](https://shopsys.atlassian.net/browse/FWCC-819)
- [FWCC-819 - Build storefront container from Alpine Linux](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/563/diffs)
    - to achieve smaller image resulting in slightly faster builds and reduction of network traffic and storage necessary
    - you have to update your local docker-compose files with changes introduced in `docker-*.yaml.dist` files
    - you need to rebuild and recreate your storefront docker containers
        - `docker-compose up -d --force-recreate --build storefront` should do the trick

### How to work with language constants

- introduced by [TES-353](https://shopsys.atlassian.net/browse/TES-353)
    - local language constants (locales) used in `.ts(x)` files and defined by FE in `./public/locales/${language}/common.json` can be overwritten by user constants via user administration
    - user constants are fetched from remote endpoint, merged with local constants and cached locally
- new NPM package `next-translate` installed:
  - provides `loadLocaleFrom` function for fetching and applying remote language files
  - use new standardized plural rules system ([CS](https://unicode-org.github.io/cldr-staging/charts/37/supplemental/language_plural_rules.html#cs), [EN](https://unicode-org.github.io/cldr-staging/charts/37/supplemental/language_plural_rules.html#en) example)
- **breaking changes:**
  - old plurals:
    - `"(0)[bodů];(1)[bod];(2-4)[body];(5-inf)[bodů];"`
  - will be (see [CS rules](https://unicode-org.github.io/cldr-staging/charts/37/supplemental/language_plural_rules.html#cs)):
    - `"{{count}} points_0": "0 bodů"`
    - `"{{count}} points_one": "1 bod"`
    - `"{{count}} points_few": "{{count}} body"`
    - `"{{count}} points_many": "{{count}} bodů"`
    - so your key for `t()` function will be `"{{count}} points"`
  - for minor changes in `<Trans />` component see [Trans Component](https://github.com/vinissimus/next-translate#trans-component) in docs
  - for Typescript typing use `Translate` type instead of `TFunction`

### Server vs Client helpers
- [FWCC-912](https://shopsys.atlassian.net/browse/FWCC-912)
- [FWCC-912 - Refaktor conditions server vs client](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/556/diffs)
    - the reasons these changes were introduced:
      - to have simple helpers that anyone can use to check if they can use server- specific or client-specific code (such as DOM, etc.)
    - most significant changes
      - the new `isServer` helper was added to check if you are on server
      - the new `canUseDom` helper was added to check if you are in browser and can work with DOM
      - these helpers were implemented on all places with conditions like `document !== undefined` etc.
    - tips on how to implement them
      - find all the places where you use the `window` or `document` in the conditions and replace them with adequate helpers
### Cypress folder structure
- [FWCC-892](https://shopsys.atlassian.net/browse/FWCC-892)
- [FWCC-892 - cypress change folder structure](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/572)
- the reason why this change was introduced
  - the developer can run a specific scenario without having to skip other screnarios in 1 file
  - better clarity of tests screnarios
  - esier test debugging
- most significant changes
  - folrder TestScrenaios was renamed to Tests
  - file CartTests.js was deleted and was replace separate files
- how to add new tests
  - create test screnarios in separate files in the appropirate folder

### Sorting of typescript imports
- [FWCC-941](https://shopsys.atlassian.net/browse/FWCC-941)
- [FWCC-941 - sort imports with prettier](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/579/diffs)
    - the reasons these changes were introduced:
      - developers don't want to care of imports, this should be done automatically
    - most significant changes
      - `sort-imports` was removed from eslint
      - import sorting plugin was added to prettier
    - tips on how to implement them
  - run `npm run format` to fix all the imports automatically

### Adding promo code to OrderSummary
- [FWCC-838](https://shopsys.atlassian.net/browse/FWCC-838)
- [FWCC-838 added promo code to order summary](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/546)
- most significant changes
  - promo code section was added to the OrderSummary component, so it's now visible in 2nd and 3rd order step
- other changes
  - styled components in the OrderSummary folder were renamed to contain the "Styled" suffix

### Returning 404 error page
- [FWCC-923](https://shopsys.atlassian.net/browse/FWCC-923)
- [FWCC-923 - return correct HTTP error codes](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/574/diffs)
    - the reasons these changes were introduced:
        - if the resource was not found, we should return a 404 error code for that resource
    - most significant changes
        - `[...all.tsx]` now returns correct 404 error code if the resource was not found
    - important note
        - we don't use the default NextJS 404 page mechanism, because it doesn't allow us to run the server code on the error page and we're not able to load data and translations correctly
        - this should be improved in the future
    - what to do
        - if you want to return the 404 error code on any page, then set `context.res.statusCode = 404;` in the `getServerSideProps` and render the correct component in the page

### Handle unauthenticated request to protected routes
- [FWCC-923](https://shopsys.atlassian.net/browse/FWCC-923)
- [FWCC-923 - return correct HTTP error codes](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/574/diffs)
    - the reasons these changes were introduced:
        - the unauthenticated user when trying to access a protected route should be redirected to the login page
    - most significant changes
        - the `initServerSideProps` has new prop `authenticationRequired` that can be used to check if the route is protected
        - when the route is protected and the user is not authenticated, the user is redirected to the login page
    - tips how to implement it
        - if you want to protect a route, then set `authenticationRequired: true` in the call of `initServerSideProps`
        - you don't need to use the `PageGuard` component anymore to protect routes for unauthenticated users

### API errors translation
- [FWCC-920](https://shopsys.atlassian.net/browse/FWCC-920)
- [FWCC-920 - API error handling](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/560/diffs)
- the reasons these changes were introduced:
    - we want to know what type of error is returned from the API, and we also want to be able to create different frontend scenarios based on the type of error
- most significant changes
    - most of the user errors returned from the API was recreated to use the UserErrorWithCodeInterface
    - the user error code (string) is returned in the error extension under the `userCode` key
    - the http error code (int) can be returned in the error extension under the `code` key
    - a "map" was created on the frontend to translate the error codes to the corresponding error messages
- tips on how to implement them
    - when you use some custom errors, make sure they implement the `UserErrorWithCodeInterface`
    - extend the `ApplicationErrors` constant in `storefront/helpers/errors/applicationErrors.ts` with codes from your custom errors
    - then extend the `storefront/helpers/errors/errorMessageMapper.ts` to set the correct error message for each error code

### Filtering by slider parameters
- [FWCC-853](https://shopsys.atlassian.net/browse/FWCC-853)
- [FWCC-853 - filtering by slider parameters](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/592/diffs)
- Backend:
  - The API for parameters input in the product filter has changed (`ParameterFilter.types.yaml`, i.e. the input you sent to API to filter the products you want to receive):
    - you can provide `minimalValue` and `maximalValue` instead of the array of values for the slider parameters
  - The API for parameters choices in product filter has changed (`productFilterOptions`, i.e. the output you get from API to display the filter):
    - `ParameterFilterOption.types.yaml` was removed
    - new `ParameterFilterOptionInterface.types.yaml` and new `AppParameterFilterOptionDecorator.types.yaml` implementing the interface were created
    - there are three implementations extending the decorator:
      - `ParameterCheckboxFilterOption.types.yaml` with an array of values
      - `ParameterColorFilterOption.types.yaml` with an array of values that contain `rgbHex` color information
      - `ParameterSliderFilterOption.types.yaml` with the minimal and maximal float values
      - thanks to that, there is no need for the `ParameterFilterOption.type` field as the type can be recognized using generic `__typename`
    - if there are any custom changes in `ParameterFilterOption.types.yaml` in your project, you need to transfer them into the new structure:
      - common fields for all parameters belong to the interface and decorator
      - color/slider/checkbox specific fields belong to the particular implementation
    - if there are any custom changes in `ParameterValueFilterOption.types.yaml` in your project that are related to the color parameters, you should move them into `ParameterValueColorFilterOption.types.yaml`
- Frontend:
  - the `ParametersType` was split to 3 independent types
  - the `FilterOptionsParameterTypeEnum` enum was removed, `__typename` is now used to differentiate between parameter types
  - the `RangeSlider` component was refactored to be more generic and reusable
  - new `SliderFilter` component was added and implemented to the `Filter` component
  - the form types and form handlers was extended to work with `minimalValue` and `maximalValue`
  - the `SelectedParameters` and the `Parameters` components was adapted for the new parameter type
