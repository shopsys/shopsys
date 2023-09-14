# GTM Event Hooks

These hooks are responsible for handling asynchronous GTM events according to how React's lifecycle works. They do not return anything, just handle the asynchronous event. They are usually used for events such as page views or list views.

## useGtmPaginatedProductListViewEvent

Hook used to handle viewing of a paginated products list, e.g. category detail, brand detail, or flag detail.
It is triggered every time the page (and therefore products) change.

```ts
export const useGtmPaginatedProductListViewEvent = (
  paginatedProducts: ListedProductFragmentApi[] | undefined, // array of displayed products, if loaded and available
  gtmProductListName: GtmProductListNameType // name of the viewed paginated list
): void => {
  // function body not included in this code block
};
```

## useGtmSliderProductListViewEvent

The hook is used to handle the viewing of a product list inside a slider, such as promoted products on the homepage.
It is triggered every time the page, therefore products change.

```ts
export const useGtmSliderProductListViewEvent = (
  products: ListedProductFragmentApi[] | undefined, // array of displayed products, if loaded and available
  gtmProuctListName: GtmProductListNameType // name of the viewed paginated list
): void => {
  // function body not included in this code block
};
```

## useGtmAutocompleteResultsViewEvent

Hook used to handle the viewing of autocomplete search results. It is triggered every time the search keyword changes.

```ts
export const useGtmAutocompleteResultsViewEvent = (
  searchResult: AutocompleteSearchQueryApi | undefined, // object containing all autocomplete search results, if loaded and available
  keyword: string, // search keyword for which the results were found
  fetching: boolean // boolean pointer saying if the results are still loading
): void => {
  // function body not included in this code block
};
```

## useGtmPageViewEvent

Basic hook used to handle viewing of a page. It is sometimes accompanied by one of the hooks below if the page is of a special type. The parameter used for this hook can be taken from `useGtmStaticPageViewEvent` or `useGtmFriendlyPageViewEvent` based on the page type ('static' vs. 'friendly URL').

```ts
export const useGtmPageViewEvent = (
  gtmPageViewEvent: GtmPageViewEventType, // object containing information about the viewed page
  fetching?: boolean // boolean pointer saying if the results are still loading
): void => {
  // function body not included in this code block
};
```

## useGtmCartViewEvent

Hook used to handle the viewing of the cart page. The parameter used for this hook can be taken from `useGtmStaticPageViewEvent`.

```ts
export const useGtmCartViewEvent = (
  gtmPageViewEvent: GtmPageViewEventType // object containing information about the viewed page
): void => {
  // function body not included in this code block
};
```

## useGtmContactInformationPageViewEvent

Hook used to handle viewing of the contact information page. The parameter used for this hook can be taken from `useGtmStaticPageViewEvent`.

```ts
export const useGtmContactInformationPageViewEvent = (
  gtmPageViewEvent: GtmPageViewEventType // object containing information about the viewed page
): void => {
  // function body not included in this code block
};
```

## useGtmPaymentAndTransportPageViewEvent

Hook used to handle the viewing of the transport and payment page. The parameter used for this hook can be taken from `useGtmStaticPageViewEvent`.

```ts
export const useGtmPaymentAndTransportPageViewEvent = (
  gtmPageViewEvent: GtmPageViewEventType // object containing information about the viewed page
): void => {
  // function body not included in this code block
};
```

## useGtmProductDetailViewEvent

Hook used to handle the viewing of the product detail page.

```ts
export const useGtmProductDetailViewEvent = (
  productDetailData: ProductDetailFragmentApi | MainVariantDetailFragmentApi, // information about the displayed product
  slug: string, // slug of the page
  fetching: boolean // boolean pointer saying if the results are still loading
): void => {
  // function body not included in this code block
};
```
