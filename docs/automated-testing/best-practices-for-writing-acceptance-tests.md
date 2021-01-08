# Best Practices for Writing Acceptance Tests

## Use prepared methods from `StrictWebDriver` that are available via `AcceptanceTester`
We have prepared many useful methods like `fillFieldByName` that actually do multiple actions to improve acceptance tests dependability.
These methods find the element on the page, scroll to it, move mouse over it and then do appropriate action. 
They are intended to imitate human behaviour as much as possible.

## Separate common behaviour to page objects
Use page object that extends from `AbstractPage` for common actions.
For example: we do test login in several tests.
Without `LoginPage` its logic would be duplicated in several tests, and it would be much harder to maintain any changes.

## Use test prefix for CSS classes
Test prefix helps to differentiate common CSS classes used by frontend developers from those used for tests, so they are not accidentally removed during design changes.

## Use translations for testing text appearance
Texts on a frontend can be changed from time to time or language can be changed during development.
Such change would lead to many errors reported by acceptance tests.
We are preventing such errors by using prepared methods in `AcceptanceTester` like `seeTranslationFrontend` or `seeTranslationAdminInCss`.

## Use calculation of price by exchange rate for testing prices
Testing price is not common in acceptance tests, but it can be required from time to time.
By default, there are several tests checking right price displayed in popup window after products is added to cart.
As currency can be easily changed on a domain, we wanted to prevent error caused by such change.
`AcceptanceTester` includes two useful methods `getPriceWithVatConvertedToDomainDefaultCurrency` and `getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend` for such cases.
See `CartBoxPage` to get some inspiration.
