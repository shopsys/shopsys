# Cypress

For E2E testing we use [Cypress](https://www.cypress.io/). It's available to be run in Docker or natively.

## Setup

### Run in Docker

From root of the project run

```bash
make run-acceptance-tests
```

This will prepare both backend and frontend for Cypress to be able to test scenarios on correct setup.

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

Text results you should be able to see in your terminal after Cypress finish the testing.

There are also generated videos (for all scenarios) in folder `/project-base/storefront/cypress/videos` and screenshots (for failed scenarios) in folder `/project-base/storefront/cypress/screenshots`.

## Test scenarios

Test scenarios are places in folder `/project-base/storefront/cypress/integration`. They are splitted into two folder `/Functions` and `/Tests`.

**Functions** are meant for reusable actions used in tests scenarios.

**Tests** are scenarios itself splitted into several folders according to tested part in Storefront application.
