# GTM Event Objects

These objects represent all GTM events. They are composed from GtmEventInterface, GtmEventType, and the content of the event. Each event has a description of all its properties.

## GtmEventInterface<EventType, EventContent>

This interface is a generic type that includes an event of type EventType and the property \_clear of boolean type. The remaining properties are of type EventContent, which is a type argument through which one can extend this generic interface.

### Event Object Properties:

```typescript
export type GtmEventInterface<EventType, EventContent> = {
    event: EventType; // the type of the event
    _clear: boolean; // lears the current data layer
} & EventContent; // additional properties of the event;
```

## GtmPageViewEventType

This type is used for page view tracking.

### Event Object Properties:

```typescript
export type GtmPageViewEventType = GtmEventInterface<
    GtmEventType.page_view, // the type of the event
    {
        language: string; // the current language of the domain
        currencyCode: string; // the code of the currency used on the domain
        consent: GtmConsentInfoType; // information about user consent
        page: GtmPageInfoType; // information about the viewed page
        user: GtmUserInfoType; // information about the user
        device: GtmDeviceTypes; // information about the user's device
        cart: GtmCartInfoType | null; // information about the user's cart, if available
        _isLoaded: boolean; // indicates if the page has finished loading
    }
>;
```

## GtmConsentUpdateEventType

This type is used for consent update tracking.

### Event Object Properties:

```typescript
export type GtmConsentUpdateEventType = GtmEventInterface<
    GtmEventType.consent_update, // the type of the event
    {
        consent: GtmConsentInfoType; // information about user consent
    }
>;
```

## GtmChangeCartItemEventType

This type is used for tracking both adding and removing cart items.

### Event Object Properties (all are inherited):

```typescript
export type GtmChangeCartItemEventType = GtmAddToCartEventType | GtmRemoveFromCartEventType;
```

## GtmAddToCartEventType

This type is used for tracking adding items to a cart. Keep in mind that adding does not need to mean the product has not been added before. It can also mean increasing product's quantity.

### Event Object Properties:

```typescript
export type GtmAddToCartEventType = GtmEventInterface<
    GtmEventType.add_to_cart, // the type of the event
    {
        ecommerce: {
            listName: GtmProductListNameType; // the name of the product list from which the product was added
            currencyCode: string; // the code of the currency used on the domain
            valueWithoutVat: number; // the value of the added products without VAT (value of the event)
            valueWithVat: number; // the value of the added products with VAT (value of the event)
            products: GtmCartItemType[] | undefined; // information about the products added to the cart, if available
        };
        cart?: GtmCartInfoType | null; // information about the user's cart, if available
    }
>;
```

## GtmRemoveFromCartEventType

This type is used for tracking removing items from a cart.

### Event Object Properties:

```typescript
export type GtmRemoveFromCartEventType = GtmEventInterface<
    GtmEventType.remove_from_cart, // the type of the event
    {
        ecommerce: {
            listName: GtmProductListNameType; // the name of the product list from which the product was removed
            currencyCode: string; // the code of the currency used on the domain
            valueWithoutVat: number; // the value of the removed products without VAT (value of the event)
            valueWithVat: number; // the value of the removed products with VAT (value of the event)
            products: GtmCartItemType[] | undefined; // information about the products removed from the cart, if available
        };
        cart?: GtmCartInfoType | null; // information about the user's cart, if available
    }
>;
```

## GtmCartViewEventType

This type is used for tracking cart view events.

### Event Object Properties:

```typescript
export type GtmCartViewEventType = GtmEventInterface<
    GtmEventType.cart_view, // the type of the event
    {
        ecommerce: {
            currencyCode: string; // the code of the currency used on the domain
            valueWithoutVat: number; // the value of the products in the cart without VAT
            valueWithVat: number; // the value of the products in the cart with VAT
            products: GtmCartItemType[] | undefined; // information about the products in the cart, if available
        };
    }
>;
```

## GtmProductListViewEventType

This type is used for tracking when a user views a list of products.

### Event Object Properties:

```typescript
export type GtmProductListViewEventType = GtmEventInterface<
    GtmEventType.product_list_view, // the type of the event
    {
        ecommerce: {
            listName: GtmProductListNameType; // the name of the product list
            products: GtmListedProductType[] | undefined; // an array of product information objects for the listed products, if available
        };
    }
>;
```

## GtmProductClickEventType

This type is used for tracking when a user clicks on a product and is redirected to product detail page.

### Event Object Properties:

```typescript
export type GtmProductClickEventType = GtmEventInterface<
    GtmEventType.product_click, // the type of the event
    {
        ecommerce: {
            listName: GtmProductListNameType; // the name of the product list where the product was clicked
            products: GtmListedProductType[] | undefined; // an array containing a single object of product information object for the clicked product, if available
        };
    }
>;
```

## GtmProductDetailViewEventType

This type is used for tracking when a user views the details of a product.

### Event Object Properties:

```typescript
export type GtmProductDetailViewEventType = GtmEventInterface<
    GtmEventType.product_detail_view, // the type of the event
    {
        ecommerce: {
            currencyCode: string; // the code of the currency used on the domain
            valueWithoutVat: number; // the price of the viewed product without VAT (value of the event)
            valueWithVat: number; // the price of the viewed product with VAT (value of the event)
            products: GtmProductInterface[] | undefined; // an array containing a single object of product information object for the viewed product, if available
        };
    }
>;
```

## GtmPaymentAndTransportPageViewEventType

This type is used for tracking when a user views the payment and transport page.

### Event Object Properties:

```typescript
export type GtmPaymentAndTransportPageViewEventType = GtmEventInterface<
    GtmEventType.payment_and_transport_page_view, // the type of the event
    {
        ecommerce: {
            currencyCode: string; // the code of the currency used on the domain
            valueWithoutVat: number; // the total value of the products in the cart without VAT
            valueWithVat: number; // the total value of the products in the cart with VAT
            products: GtmCartItemType[] | undefined; // an array of product information objects for the products in the cart, if available
        };
    }
>;
```

## GtmAutocompleteResultsViewEventType

This type is used for tracking when a user views autocomplete search results.

### Event Object Properties:

```typescript
export type GtmAutocompleteResultsViewEventType = GtmEventInterface<
    GtmEventType.autocomplete_results_view, // the type of the event
    {
        autocompleteResults: {
            keyword: string; // the search keyword entered by the user
            results: number; // the number of search results displayed to the user
            sections: { [key in GtmSectionType]: number }; // an object that maps each section of search results to the number of results in that particular section
        };
    }
>;
```

## GtmAutocompleteResultClickEventType

This type is used for tracking clicks on autocomplete results.

### Event Object Properties:

```typescript
export type GtmAutocompleteResultClickEventType = GtmEventInterface<
    GtmEventType.autocomplete_result_click, // the type of the event
    {
        autocompleteResultClick: {
            section: GtmSectionType; // the section of the autocomplete results where the result was clicked
            itemName: string; // the name of the item clicked on
            keyword: string; // the search keyword used to generate the autocomplete results
        };
    }
>;
```

## GtmTransportChangeEventType

This type is used for tracking changes to the transport option during checkout.

### Event Object Properties:

```typescript
export type GtmTransportChangeEventType = GtmEventInterface<
    GtmEventType.transport_change, // the type of the event
    {
        ecommerce: {
            currencyCode: string; // the code of the currency used on the domain
            valueWithoutVat: number; // the value of the products in the cart without VAT
            valueWithVat: number; // the value of the products in the cart with VAT
            promoCodes: string[] | undefined; // an array of promo codes applied to the order, if any
            paymentType: string | undefined; // the payment type selected by the user, if any
            transportPriceWithoutVat: number; // the price of the transport without VAT
            transportPriceWithVat: number; // the price of the transport with VAT
            transportType: string; // the type of transport selected by the user
            transportDetail: string; // any additional details about the transport selected by the user
            transportExtra: string[]; // an array of any extra transport-related information provided by the user
            products: GtmCartItemType[]; // information about the products in the cart
        };
    }
>;
```

## GtmContactInformationPageViewEventType

This type is used for tracking when a user views the contact information page during checkout.

### Event Object Properties:

```typescript
export type GtmContactInformationPageViewEventType = GtmEventInterface<
    GtmEventType.contact_information_page_view, // the type of the event
    {
        ecommerce: {
            currencyCode: string; // the code of the currency used on the domain
            valueWithoutVat: number; // the value of the products in the cart without VAT
            valueWithVat: number; // the value of the products in the cart with VAT
            promoCodes: string[] | undefined; // an array of promo codes applied to the order, if any
            products: GtmCartItemType[] | undefined; // information about the products in the cart, if available
        };
    }
>;
```

## GtmPaymentChangeEventType

This type is used for tracking changes to the payment option during checkout.

### Event Object Properties:

```typescript
export type GtmPaymentChangeEventType = GtmEventInterface<
    GtmEventType.payment_change, // the type of the event
    {
        ecommerce: {
            currencyCode: string; // the code of the currency used on the domain
            valueWithoutVat: number; // the value of the products in the cart without VAT
            valueWithVat: number; // the value of the products in the cart with VAT
            promoCodes: string[] | undefined; // an array of promo codes applied to the order, if any
            paymentType: string; // the payment type selected by the user
            paymentPriceWithoutVat: number; // the price of the payment without VAT
            paymentPriceWithVat: number; // the price of the payment with VAT
            products: GtmCartItemType[] | undefined; // information about the products in the cart, if available
        };
    }
>;
```

## GtmPaymentFailEventType

This type is used for tracking when a payment fails.

### Event Object Properties:

```typescript
export type GtmPaymentFailEventType = GtmEventInterface<
    GtmEventType.payment_fail, // the type of the event
    {
        paymentFail: {
            id: string; // the ID of the order for which the payment failed
        };
    }
>;
```

## GtmCreateOrderEventOrderPartType

This type represents the order part of the GtmCreateOrderEventType. It is stored in local storage before redirecting to payment gate, if the user has chosen to pay using a payment that requires a redirect.

### Event Object Properties:

```typescript
export type GtmCreateOrderEventOrderPartType = {
    currencyCode: string; // the code of the currency used on the domain
    id: string; // the ID of the order
    valueWithoutVat: number; // the value of the products in the order without VAT
    valueWithVat: number; // the value of the products in the order with VAT
    vatAmount: number; // the total amount of VAT paid for the order
    paymentPriceWithoutVat: number; // the price of the payment without VAT
    paymentPriceWithVat: number; // the price of the payment with VAT
    promoCodes: string[] | undefined; // any promo codes used for the order, if available
    discountAmount: number | undefined; // the total amount of discounts applied to the order, if available
    paymentType: string; // the type of payment used for the order
    reviewConsents: GtmReviewConsentsType; // user's review consents
    products: GtmCartItemType[] | undefined; // information about the products in the order, if available
};
```

## GtmPurchaseEventPaymentPartType

This type represents the payment part of the GtmCreateOrderEventType. It is never stored in local storage, but always set when an order is created according to the current information about the payment.

### Event Object Properties:

```typescript
export type GtmPurchaseEventPaymentPartType = {
    isPaymentSuccessful: boolean | undefined; // whether the payment was successful or not, if available
};
```

## GtmCreateOrderEventType

This type is used for tracking when an order is created by the user. It consists of the GtmCreateOrderEventOrderPartType, GtmPurchaseEventPaymentPartType, and GtmUserInfoType objects.

### Event Object Properties:

```typescript
export type GtmCreateOrderEventType = GtmEventInterface<
    GtmEventType.create_order, // the type of the event
    {
        ecommerce: GtmCreateOrderEventOrderPartType & GtmPurchaseEventPaymentPartType;
        user: GtmUserInfoType
    }

```

## GtmShowMessageEventType

This type is used for tracking when a message is displayed to the user.

### Event Object Properties:

```typescript
export type GtmShowMessageEventType = GtmEventInterface<
    GtmEventType.show_message, // the type of the event
    {
        eventParameters: {
            type: GtmMessageType; // the type of the message displayed
            origin: GtmMessageOriginType | undefined; // the origin of the message, if available
            detail: string | undefined; // any additional details about the message, if available
            message: string; // the message displayed to the user
        };
    }
>;
```

## GtmSendFormEventType

This type is used for tracking when a form is submitted by the user.

### Event Object Properties:

```typescript
export type GtmSendFormEventType = GtmEventInterface<
    GtmEventType.send_form, // the type of the event
    {
        eventParameters: {
            form: GtmFormType; // information about the form submitted by the user
        };
    }
>;
```
