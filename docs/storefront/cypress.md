# Cypress

For E2E testing, we use [Cypress](https://www.cypress.io/). It's available to be run in Docker or natively.

## Setup

### Run in Docker

From the root of the project run

```bash
make run-acceptance-tests
```

This will prepare both the backend and frontend for Cypress to be able to test scenarios on the correct setup.

### Run natively

You first need [setup and run](https://docs.shopsys.com/installation/installation-guide/) project. For more info about frontend setup see the section [Setup Storefront](./setup-storefront.md).

Then go to `/project-base/storefront/cypress` folder and install all dependencies.

```bash
npm i
```

Then you should be able to run Cypress itself by command

```bash
npx cypress run
```

## Tests Results

You should be able to see text results in your terminal after Cypress finishes the testing.

There are also generated videos (for all scenarios) in folder `/project-base/storefront/cypress/videos` and screenshots (for failed scenarios) in folder `/project-base/storefront/cypress/screenshots`.

## Test scenarios

Test scenarios are placed in folder `/project-base/storefront/cypress/integration`. They are split into two folders `/Functions` and `/Tests`.

**Functions** are meant for reusable actions used in test scenarios.

**Tests** are scenarios itself split into several folders according to the tested part in the Storefront application.
