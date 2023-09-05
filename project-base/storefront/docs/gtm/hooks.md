# GTM Event Hooks

These hooks are responsible for handling of asynchronous GTM events according to how React's lifecycle works. They do not return anything, just handle the asynchronous event. They are usually used for events such as page view or list view.

## useGtmPaginatedProductListViewEvent

Hook used to handle viewing of a paginated products list, e.g. category detail, brand detail, or flag detail.
It is triggered every time page (and therefore products) change.

```typescript
export const useGtmPaginatedProductListViewEvent = (
    paginatedProducts: ListedProductFragmentApi[] | undefined, // array of displayed products, if loaded and available
    gtmProductListName: GtmProductListNameType, // name of the viewed paginated list
): void => {
    // function body not included in this code block
};
```

## useGtmSliderProductListViewEvent

Hook used to handle viewing of a products list inside a slider, such as promoted products on homepage.
It is triggered every time page therefore products change.

```typescript
export const useGtmSliderProductListViewEvent = (
    products: ListedProductFragmentApi[] | undefined, // array of displayed products, if loaded and available
    gtmProuctListName: GtmProductListNameType, // name of the viewed paginated list
): void => {
    // function body not included in this code block
};
```

## useGtmAutocompleteResultsViewEvent

Hook used to handle viewing of autocomplete search results. It is triggered every time the search keyword changes.

```typescript
export const useGtmAutocompleteResultsViewEvent = (
    searchResult: AutocompleteSearchQueryApi | undefined, // object containing all autocomplete search results, if loaded and available
    keyword: string, // search keyword for which the results were found
    fetching: boolean, // boolean pointer saying if the results are still loading
): void => {
    // function body not included in this code block
};
```

## useGtmPageViewEvent

Basic hook used to handle viewing of a page. It is sometimes accompanied with one of the hooks below if the page is of a special type. The parameter used for this hook can be taken from `useGtmStaticPageViewEvent` or `useGtmFriendlyPageViewEvent` based on the type of the page ('static' vs 'friendly URL').

```typescript
export const useGtmPageViewEvent = (
    gtmPageViewEvent: GtmPageViewEventType, // object containing information about the viewed page
    fetching?: boolean, // boolean pointer saying if the results are still loading
): void => {
    // function body not included in this code block
};
```

## useGtmCartViewEvent

Hook used to handle viewing of the cart page. The parameter used for this hook can be taken from `useGtmStaticPageViewEvent`.

```typescript
export const useGtmCartViewEvent = (
    gtmPageViewEvent: GtmPageViewEventType, // object containing information about the viewed page
): void => {
    // function body not included in this code block
};
```

## useGtmContactInformationPageViewEvent

Hook used to handle viewing of the contact information page. The parameter used for this hook can be taken from `useGtmStaticPageViewEvent`.

```typescript
export const useGtmContactInformationPageViewEvent = (
    gtmPageViewEvent: GtmPageViewEventType, // object containing information about the viewed page
): void => {
    // function body not included in this code block
};
```

## useGtmPaymentAndTransportPageViewEvent

Hook used to handle viewing of the transport and payment page. The parameter used for this hook can be taken from `useGtmStaticPageViewEvent`.

```typescript
export const useGtmPaymentAndTransportPageViewEvent = (
    gtmPageViewEvent: GtmPageViewEventType, // object containing information about the viewed page
): void => {
    // function body not included in this code block
};
```

## useGtmProductDetailViewEvent

Hook used to handle viewing of the product detail page.

```typescript
export const useGtmProductDetailViewEvent = (
    productDetailData: ProductDetailFragmentApi | MainVariantDetailFragmentApi, // information about the displayed product
    slug: string, // slug of the page
    fetching: boolean, // boolean pointer saying if the results are still loading
): void => {
    // function body not included in this code block
};
```
