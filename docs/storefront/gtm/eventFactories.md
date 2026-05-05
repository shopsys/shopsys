# GTM Event Factories

These factories are responsible for creating and preparing GTM event objects. They can be written either as basic `get` methods, such as `getGtmCartViewEvent`, or as hooks, such as `useGtmStaticPageReadyEvent`. The difference between them is

## getGtmCartViewEvent

```ts
export const getGtmCartViewEvent = (
    currencyCode: string; // the code of the currency used on the domain
    valueWithoutVat: number, // the value of the cart without VAT
    valueWithVat: number, // the value of the cart with VAT
    products: GtmCartItemType[] | undefined, // products in cart, if available
): GtmCartViewEventType => {
    // function body not included in this code block
}
```

## getGtmContactInformationViewEvent

```ts
export const getGtmContactInformationViewEvent = (
  gtmCartInfo: GtmCartInfoType // the cart of the current user in the shape of GTM cart information
): GtmContactInformationViewEventType => {
  // function body not included in this code block
};
```

## getGtmPaymentAndTransportViewEvent

```ts
export const getGtmPaymentAndTransportViewEvent = (
    currencyCode: string; // the code of the currency used on the domain
    gtmCartInfo: GtmCartInfoType, // the cart of the current user in the shape of GTM cart information
): GtmPaymentAndTransportViewEventType => {
    // function body not included in this code block
}
```

## getGtmPaymentFailEvent

```ts
export const getGtmPaymentFailEvent = (
  orderId: string // ID of the order for which the payment has failed
): GtmPaymentFailEventType => {
  // function body not included in this code block
};
```

## getGtmCreateOrderEvent

```ts
export const getGtmCreateOrderEvent = (
  gtmCreateOrderEventOrderPart: GtmCreateOrderEventOrderPartType, // part of the GtmCreateOrderEvent object containing information about the order
  gtmCreateOrderEventUserPart: GtmUserInfoType, // part of the GtmCreateOrderEvent object containing information about the user
  isPaymentSuccessful?: boolean // boolean pointer if the payment was successful, not filled if we cannot determine the payment status
): GtmCreateOrderEventType => {
  // function body not included in this code block
};
```

## getGtmCreateOrderEventOrderPart

```ts
export const getGtmCreateOrderEventOrderPart = (
  cart: CartFragment, // the cart of the current user in the shape of basic CartFragment object
  payment: SimplePaymentFragment, // the payment method chosen by the user that will be used to pay for the order
  promoCode: string | null, // promo code used for the order, if applicable
  orderNumber: string, // identifying number of the order
  reviewConsents: GtmReviewConsentsType, // information about consents previously given by the user
  domainConfig: DomainConfigType // configuration for the current domain
): GtmCreateOrderEventOrderPartType => {
  // function body not included in this code block
};
```

## getGtmCreateOrderEventUserPart

```ts
export const getGtmCreateOrderEventUserPart = (
  user: CurrentCustomerType | null | undefined, // information about current user
  userContactInformation: ContactInformation // contact information filled by the user during order
): GtmUserInfoType => {
  // function body not included in this code block
};
```

## getGtmSendFormEvent

```ts
export const getGtmSendFormEvent = (
  form: GtmFormType // type of the form submitted by the user
): GtmSendFormEventType => {
  // function body not included in this code block
};
```

## getGtmProductClickEvent

```ts
export const getGtmProductClickEvent = (
  product: ListedProductFragment | SimpleProductFragment, // information about the product clicked by the user
  gtmProductListName: GtmProductListNameType, // name of the list from which the product was clicked
  listIndex: number, // index of the product within the list
  domainUrl: string // URL of the current domain
): GtmProductClickEventType => {
  // function body not included in this code block
};
```

## getGtmProductDetailViewEvent

```ts
export const getGtmProductDetailViewEvent = (
    product: ProductDetailFragment | MainVariantDetailFragment, // information about the product displayed on on the product detail page
    currencyCode: string; // the code of the currency used on the domain
    domainUrl: string, // URL of the current domain
): GtmProductDetailViewEventType => {
    // function body not included in this code block
}
```

## getGtmAutocompleteResultsViewEvent

```ts
export const getGtmAutocompleteResultsViewEvent = (
  searchResult: AutocompleteSearchQuery, // object with all autocomplete search results
  keyword: string // keyword for which the results were returned
): GtmAutocompleteResultsViewEventType => {
  // function body not included in this code block
};
```

## getGtmAutocompleteResultClickEvent

```ts
export const getGtmAutocompleteResultClickEvent = (
  keyword: string, // keyword for which the results were returned
  section: GtmSectionType, // type of the section of the autocomplete results on which the user clicked
  itemName: string // name of the autocomplete search results item clicked by the user
): GtmAutocompleteResultClickEventType => {
  // function body not included in this code block
};
```

## useGtmStaticPageReadyEvent

```ts
export const useGtmStaticPageReadyEvent = (
  pageType: GtmPageType, // type of the page viewed by the user
  breadcrumbs?: BreadcrumbFragment[] // breadcrumbs for the viewed page, if available
): GtmPageReadyEventType => {
  // function body not included in this code block
};
```

## useGtmFriendlyPageReadyEvent

```ts
export const useGtmFriendlyPageReadyEvent = (
  friendlyUrlPageData: FriendlyUrlPageType | null | undefined // data for the friendly URL page
): GtmPageReadyEventType => {
  // function body not included in this code block
};
```

## getGtmPageReadyEvent

```ts
export const getGtmPageReadyEvent = (
  pageInfo: GtmPageInfoType, // information about the viewed page
  gtmCartInfo: GtmCartInfoType | null, // the cart of the current user in the shape of GTM cart information, if available
  isCartLoaded: boolean, // boolean pointer saying if the cart is loaded
  user: CurrentCustomerType | null | undefined, // information about the current user
  userContactInformation: ContactInformation, // contact information filled by the user during order
  domainConfig: DomainConfigType // config for the current domain
): GtmPageReadyEventType => {
  // function body not included in this code block
};
```

## getGtmChangeCartItemEvent

```ts
export const getGtmChangeCartItemEvent = (
    event: GtmEventType.add_to_cart | GtmEventType.remove_from_cart, // type of the event saying if we are removing or adding items to cart
    cartItem: CartItemFragment, // removed (or added) cart item
    listIndex: number | undefined, // list index from which the item was removed/added, it can be index within cart (for removing) or index within any other list (for adding)
    quantity: number, // how much was removed/added, absolute value of the delta of the previous and current quantity
    currencyCode: string; // the code of the currency used on the domain
    eventValueWithoutVat: number, // value of the event without VAT, calculated as product price without VAT * quantity
    eventValueWithVat: number, // value of the event with VAT, calculated as product price with VAT * quantity
    gtmProductListName: GtmProductListNameType, // name of the list from which the product was removed/added
    domainUrl: string, // URL of the current domain
    gtmCartInfo?: GtmCartInfoType | null, // the cart of the current user in the shape of GTM cart information, if available
): GtmChangeCartItemEventType => {
    // function body not included in this code block
}
```

## getGtmChangeProductListItemEvent

```ts
export const getGtmChangeProductListItemEvent = (
    event:
        | GtmEventType.add_to_wishlist
        | GtmEventType.remove_from_wishlist
        | GtmEventType.add_to_comparison
        | GtmEventType.remove_from_comparison, // type of the event saying if we are removing or adding items to product list
    product: ProductInterfaceType, // removed or added wishlist/comparison product
    listIndex: number | undefined, // list index from which the item was removed/added
    currencyCode: string; // the code of the currency used on the domain
    gtmProductListName: GtmProductListNameType, // name of the list from which the product was removed/added
    domainUrl: string, // URL of the current domain
    arePricesHidden: boolean, // boolean pointer saying if prices are hidden for the current user
): GtmChangeProductListItemEventType => {
    // function body not included in this code block
}
```

## getGtmPaymentChangeEvent

```ts
export const getGtmPaymentChangeEvent = (
  gtmCartInfo: GtmCartInfoType, // the cart of the current user in the shape of GTM cart information, if available
  updatedPayment: SimplePaymentFragment // payment method newly updated by the user
): GtmPaymentChangeEventType => {
  // function body not included in this code block
};
```

## getGtmTransportChangeEvent

```ts
export const getGtmTransportChangeEvent = (
  gtmCartInfo: GtmCartInfoType, // the cart of the current user in the shape of GTM cart information, if available
  updatedTransport: TransportWithAvailablePaymentsFragment, // transport method newly updated by the user
  updatedPickupPlace: ListedStoreFragment | null, // pickup place method newly updated by the user, if available
  paymentName: string | undefined // name of the selected payment method
): GtmTransportChangeEventType => {
  // function body not included in this code block
};
```

## getGtmProductListViewEvent

```ts
export const getGtmProductListViewEvent = (
  products: ListedProductFragment[], // products contained in the viewed list
  gtmProductListName: GtmProductListNameType, // name of the viewed list
  currentPage: number, // current page of the viewed list
  pageSize: number, // page size of the viewed list
  domainUrl: string // URL of the current domain
): GtmProductListViewEventType => {
  // function body not included in this code block
};
```

## getGtmShowMessageEvent

```ts
export const getGtmShowMessageEvent = (
  type: GtmMessageType, // type of the message shown to the user (e.g. error, information)
  message: string, // content of the message shown to the user
  detail: GtmMessageDetailType | string, // any additional information about the message shown to the user, can be of a predefined type GtmMessageDetailType or any arbitrary string value
  origin?: GtmMessageOriginType // origin of the message, saying which part of the website triggered it
): GtmShowMessageEventType => {
  // function body not included in this code block
};
```

## getGtmConsentUpdateEvent

```ts
export const getGtmConsentUpdateEvent = (
  updatedGtmConsentInfo: GtmConsentInfoType // newly updated consents given by the user
): GtmConsentUpdateEventType => {
  // function body not included in this code block
};
```
