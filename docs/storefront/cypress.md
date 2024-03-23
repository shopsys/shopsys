# Cypress

For E2E testing, we use [Cypress](https://www.cypress.io/). Below you can read answers to some of the questions you might have.

## How to structure your cypress folder?

### e2e folder

This folder is where you should put your test suites and domain-specific helpers. These would be things related to a single part of the application you are testing, such as helper which only focus on authentication, cart, or order.

You should split your tests into domain-specific subfolders. This helps to balance the tests and make it clear what each test suite focuses on. Some examples are the aforementioned authentication, cart, or order. Other examples could be adding to cart.

-   e2e/
    -   domainSpecificFunctionality/
        -   domainSpecificFunctionality.cy.ts
        -   domainSpecificFunctionalitySomeOtherPart.cy.ts
        -   domainSpecificFunctionalitySupport.ts

### fixtures folder

Here you can put any static values and demodata you would need. This could be strings to fill in in inputs, things you would expect to find in a page, etc.

### support folder

Here you can put various global helpers, such as custom cypress commands, or similar.

### TIDs.ts

Here you should put all data test IDs used in the app. Having them in a single TS file which can be globally referenced is helpful for maintenance and keeping track of used or unused IDs.

### cypress.d.ts

Here you should put type definitions for your custom cypress commands which are defined using `Cypress.Commands.add`. This is necessary as otherwise cypress cannot infer the types.

### snapshots folder

This is where all snapshots created using `takeSnapshotAndCompare` are stored. They are stored under the provided name (the name provided as a function parameter).

### videos folder (uncommited)

This is were all videos from your tests are stored.

### screenshots folder (uncommited)

This is were all screenshots from your tests are stored. They are not the same as the snapshots, as these are generated even when running your tests in `base` mode. However, they can be used to compare your snapshots with the given test run. They are also the images based on which the snapshot diffs are generated (diffs between `snapshots` and `screenshots`).

### snapshotDiffs folder (uncommited)

This is where snapshot diffs are stored if a test fails because of visual regression.F

## How to write tests?

### General guidelines

Your tests should ideally test a small and isolated part of the application. For example, it is better to split the order process into multiple steps (adding to cart, adding a promo code, choosing transport, choosing payment, filling in personal information) and test each of them separately, rather then as a whole. This is because to test all combinations (adding products from multiple places, choosing different transports, etc.) by testing the entire order, we would have to have a very large amount of tests, where many things would be repeated unnecessarily. However, if we split them and test all variants of a partial step, we test all combinations implicitly.

To be more specific, you should group all tests for a specific part of the application in a single test suite using the `describe` method as seen below. Name it the same way your file is named.

Each test should be named in a way to describe what the test and the application should do. Below are some examples:

-   Should add a product to cart and check the cart
-   Should not be allowed to see transport options if cart is empty
-   Should login from header and then log out

In the `beforeEach` hook, you can run various preparation logic. There are also other hooks, which you can find in the cypress documentation. One of the specific things you might want to do is to reset the zustand storage by setting `app-store` as visible below. Another thing could be to visit a specific page, such as the cart page if all your tests only focus on that page.

```ts
describe('<Domain Specific Functionality> tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('should do something', () => {
        ...
    });
});
```

### Custom cypress commands

Below are some examples of custom commands. We mention only those, that should be used instead of the default cypress commands.

-   `cy.visitAndWaitForStableDOM` (instead of `cy.visit`): Use this command for visiting pages. This command makes sure that the tests wait for the DOM to be stable, ensuring that the tests do not click on non-interactive (yet visible) elements.

#### How to write a custom cypress command

If you want to add a custom cypress command using `Cypress.Commands.add`, which might be helpful if you want to define a command "the cypress way" and allow it to be chained with other commands, you need to add a similar entry in the `/support/index.ts` file. You will need to set its name and interface, together with the actual logic. In the end, you might need to return a suitable cypress object to allow for chaining.

```ts
Cypress.Commands.add('youCustomCommandName', (param1: string, param2: number) => {
    // the command logic

    // optionally return the cypress object if you want to chain it, for example by returning cy.get, or similar
    return cy.get(...);
});
```

Another thing is that you should modify `cypress.d.ts`, where you should put type definitions for your custom cypress commands which are defined using `Cypress.Commands.add`. This is necessary as otherwise cypress cannot infer the types.

### Visual regression tests

Another important part of our cypress tests is visual regression. This allows us to make a full-page screenshot of the application at any point and compare it with a base screenshot every time the tests are run. This way you make sure that the app looks the same and that your changes did not break it visually.

For this purpose, the `takeSnapshotAndCompare` helper method can be used. You can use it multiple times in each test, just remember to provide the screenshot name, which will be used to store the snapshot under `/snapshots`.

```ts
it('should do something', () => {
    ...
    // do something
    ...
    takeSnapshotAndCompare('screenshot-name');
    ...
    // do something else
    ...
    takeSnapshotAndCompare('another-screenshot-name');
});
```

Remember this can be leveraged to make sure that an action does not change the UI by comparing to the same screenshot.

```ts
it('should do something', () => {
    takeSnapshotAndCompare('screenshot-name');
    ...
    // do something that should not change the UI
    ...
    takeSnapshotAndCompare('screenshot-name');
});
```

The `takeSnapshotAndCompare` helper method does several things. First it waits for 200ms for the UI to stabilize (animations to finish, etc.), then the device pixel ratio is changed, which is neccessary to standardize tests across different devices, then it takes a screenshot, and in the end it compares the screenshot to the base snapshot.

```ts
export const takeSnapshotAndCompare = (snapshotName: string) => {
    cy.wait(200);
    cy.setDevicePixelRatio(1);
    cy.screenshot();
    cy.compareSnapshot(snapshotName);
};
```

You can set up the snapshot to take a full-page, runner, or a viewport screenshot. The most robust version is to test the full page, because then you know that the entire page is unchanged.

You can also set the comparison threshold. For example, the `0.02` threshold seen below means that 2% of the image pixels can change without the tests failing. This can be modified in any way necessary, but remember to keep a balance. The higher the threshold, the less false positives you will get, but the more differences and bugs can stay unnoticed. For example, if you have a page with order detail, where only the total price is wrong, if the page is large enough, the mistake in the price might be less than, for example, 2%. On the other hand, if you do not allow any differences (`errorThreshold: 0`), you might get some false positives, because of unnoticable differences.

```ts
compareSnapshotCommand({
    capture: 'fullPage',
    errorThreshold: 0.02,
});
```

## How to run tests?

You can run your tests both using the CLI (usually run as `cypress run`) and using the cypress interactive GUI (usually run using `cypress open`). To make sure that the test runs are consistent, use the provided make commands located in `Makefile` in the project root. These commands run the tests using a separate dedicated storefront copy (`storefront-cypress`). Furthermore, the back-end application is set to a test environment with a dedicated database. Last, but not least, running it via docker makes sure that your OS does not influence the tests, which can happen, e.g. by font smoothing, which causes differences in visual regression tests.

### How to run tests using the CLI (`cypress run`)?

There are two commands provided for you:

-   `run-acceptance-tests-base`: This command runs the tests and allows screenshot regeneration. This means that whatever your tests generate at that point will be considered the new base case. By running this, the tests will not fail because of visual differences, but might still fail because of the cypress tests failing themselves. Make sure to only run this once you are sure that your application behaves as expected. If you set the base to an invalid state, once it is fixed, your tests will start failing.
-   `run-acceptance-tests-actual`: This command runs the tests without allowing screenshot regeneration. This should be used most of the time if you want to check your application. This is also what should be used as part of CI. If this command fails because of visual differences, there will be screenshot diffs generated in a `/snapshotDiffs` folder. You can analyze them to see the differences which caused an issue.

### How to run tests using the cypress interactive GUI (`cypress open`) on Mac?

Unfortunately, you cannot just simply run cypress tests in docker and use the cypress GUI. Especially on Mac, you will have to allow the docker application to connect to a display port and stream the visual data to your screen. Allowing this is fairly straightforward and should take you just a couple of minutes. All steps you need to do are described in [this tutorial](https://sourabhbajaj.com/blog/2017/02/07/gui-applications-docker-mac/). You should only focus on the parts titled **Install XQuartz** and **Run XQuartz**. These are the only steps you will have to do. You do not have to care about getting your host machine IP, as we have prepared a general command which should cover all scenarios. After installing and setting up XQuartz, you can continue by reading the next block, which describes how to run the tests with GUI.

### How to run tests using the cypress interactive GUI (`cypress open`) on Linux or Mac + XQuartz?

If you use Linux or Mac, where you have previously installed and set-up XQuartz as described above, you have these two commands available to run cypress tests with the interactive GUI.

-   `open-acceptance-tests-base`: This command opens the cypress interactive GUI, where you can select and run tests. Similar to `run-acceptance-tests-base`, this command allows screenshot regeneration. This means that whatever your tests generate at that point will be considered the new base case. By running this, the tests will not fail because of visual differences, but might still fail because of the cypress tests failing themselves. Make sure to only run this once you are sure that your application behaves as expected. If you set the base to an invalid state, once it is fixed, your tests will start failing.
-   `open-acceptance-tests-actual`: This command opens the cypress interactive GUI, where you can select and run tests. Similarly to `run-acceptance-tests-actual`, this command runs the tests without allowing screenshot regeneration. This should be used most of the time if you want to check your application. This is also what should be used as part of CI. If this command fails because of visual differences, there will be screenshot diffs generated in a `/snapshotDiffs` folder. You can analyze them to see the differences which caused an issue.

### Extra make commands

There are some extra make commands you can use:

-   `prepare-data-for-acceptance-tests` runs just the necessary commands to prepare the BE and API for cypress tests. This includes switching BE to test mode, running database migrations, and related. It can also be helpful while debugging, as described in the [paragraph about debugging tests containing registration](#debugging-tests-containing-registration).

### Gotchas when running tests

#### Debugging tests containing registration

Our tests include scenarios where we register with a static email (which is the most comfortable way of running visual regression tests). However, this means that if you use `open-acceptance-tests-base` or `open-acceptance-tests-actual`, and run a specific test with registration multiple times, the test will fail, as you will try to register with a previously registered email. For this, there are several workarounds:

-   if you need to do quick, iterative debugging, where you run the same test multiple times, you can take that specific test and change from a static email to a generated one like shown in the diff below. This will fail your visual regression tests (if run with the `open-acceptance-tests-actual` command), but will allow you to debug. Once you understand and fix the bug, you can switch back to the static email.

```diff
- generateCustomerRegistrationData('some-static-email@shopsys.com')
+ generateCustomerRegistrationData()
```

-   if you only need to run the test with registration one more time, it might be easier for you to use the `prepare-data-for-acceptance-tests` make command. It only runs the most necessary data preparation logic, such as cleaning the database and uploading fresh demo data.

#### Screenshots containing mouse cursor when running cypress interactive GUI

Because we run the cypress interactive GUI through docker, if you leave your mouse cursor on the GUI while a screenshot for visual regression tests is being taken, it will fail the test, as the cursor will be included in the screenshot. This is a _funny_ gotcha, that might raise some eyebrows, but the easiest way to avoid this issue is to just move your cursor outside of the GUI.

As described above in the [section about running tests](#how-to-run-tests), to update your screenshots, you can run the `run-acceptance-tests-base` make command. This way, all your screenshots which have changed will be regenerated and the new values will be stored in `/snapshots`.

#### Killing the cypress interactive GUI and finishing the make commands

Though this may be obvious, when running `open-acceptance-tests-base` or `open-acceptance-tests-actual`, the make commands will not finish until you close the GUI window and kill the GUI runner. Only then will your cypress script end, storefront cypress will be killed, and regular storefront brought up.

## How to debug failed tests?

-   You can view the videos in `/videos` to see where the test got stuck
-   You can view snapshot diffs in `/snapshotDiffs` if your tests fail because of visual differences, they should help you to spot the differences
-   You can log within your tests, though this is considerably harder than the methods above, as logging is not intuitive in cypress, however, you can read more in the [official docs](https://docs.cypress.io/api/commands/log)
-   You can run the tests using the cypress interactive GUI. This is very helpful especially when dealing with complex bugs. Within the GUI, even a browser console is available. However, definitely read the [part about running your tests](#how-to-run-tests) and the [part about various gotchas you might face](#gotchas-when-running-tests).

## How to work with dynamic data?

In situations when you work with dynamic data, such as store opening hours, or created order numbers, which might be different each time you run the tests, it is good to find a way how to make this data static in order for the tests results to be consistent.

There are generally two ways to work with dynamic data which you could want to modify in order to work on a consistend UI:

### Modification of the incoming API request

This one is suitable for situations in which you have a client-side API request which you can intercept. This approach might be better, as it does not directly change the UI. For example, you can change the incoming order number to be `1234`, and test if the UI does display this number, which should be consistent with how the actual application behaves. If, on the other hand, you directly modify the UI using cypress (hardcode a heading to display `1234`), even if the logic of display the number is broken because of a bug, the UI will just show the number and your tests will not discover a bug related to data display. On the other hand, this approach with intercepting and modifying a request might be too complicated for some situations. Furthermore, it cannot be used (or in a very complicated manner) for SSR requests.

To intercept and modify an API request, you will need a code similar to the one below. There are no types provided, and the application types are by default not available in the cypress folder. Because of that, you will either have to ignore the types, or provide a pseudo support type.

**You have to call this intercept before your API call is made to correctly catch it.**

```ts
export const changeSomethingInApiResponses = () => {
    cy.intercept('POST', '/graphql/', (req) => {
        req.reply((response) => {
            if (response?.body?.data?.yourResponseObject?.someValue) {
                response.body.data.yourResponseObject.someValue = 'your value override';
            }
        });
    });
};
```

### Modification of the UI

If you cannot use intercepting because of some of the aforementioned reasons, such as the call happening on SSR, or if your data inconsistency is not caused by API requests in the first place, you can still stabilize your screenshots by manually modifying the UI. Keep in mind that this should be done as the last resort, as it effectively means that the tests are not actually testing what the user sees, but rather your hardcoded data. If, however, you find this necessary in a given scenario, you can use the provided helper method `changeElementText` to change an element's text, or copy the approach to do any similar thing.

As for the `changeElementText` method, it by default expects to be called right after the page is loaded after SSR, which is the reason why we wait for 200ms, in order to surpass the React hydration error. If you call this method in a different setting, you can save yourself 200ms for every call by setting `isRightAfterSSR` to `false`.

```ts
export const changeElementText = (selector: TIDs, newText: string, isRightAfterSSR = true) => {
    if (isRightAfterSSR) {
        cy.wait(200);
    }
    cy.getByTID([selector]).then((element) => {
        element.text(newText);
    });
};
```
