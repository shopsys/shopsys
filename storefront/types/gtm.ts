import { BreadcrumbItemType } from './breadcrumb';

export type GtmPageType =
    | 'home'
    | 'crossroad'
    | 'category' // category - friendly URL
    | 'seo category' // prepared SEO category - friendly URL
    | 'product' // product - friendly URL
    | 'cart' // /cart
    | 'transport pay' // /transport-and-payment
    | 'shipping data' // /contact-information
    | 'purchase' // /order-confirmation
    | 'search' // /search
    | 'blog' // blog - friendly URL
    | 'article' // blog article - friendly URL
    | 'stores' // /stores
    | 'store' // store - friendly URL
    | 'flag'
    | 'brand'
    | 'text'
    | 'purchase fail'
    | 'purchase success'
    | 'about'
    | '404'
    | 'other' // fallback for new/unknown pages.
    | 'cookie consent'
    | 'contact';

export type GtmListNameType =
    | 'blog article'
    | 'brand'
    | 'category'
    | 'seo category'
    | 'flag'
    | 'accessory'
    | 'variants'
    | 'search result'
    | 'homepage promo products'
    | 'cart'
    | 'detail'
    | 'suggest';

export type GtmEventType =
    | 'page_ready' // page view event
    | 'consent.update'
    | 'ec.add_to_cart' // add to cart/increase cart item quantity
    | 'ec.remove_from_cart' // remove from cart/reduce cart item quantity
    | 'ec.cart' // cart page view event
    | 'ec.products_list'
    | 'ec.product_click'
    | 'ec.product_view'
    | 'ec.payment_shipping_info'
    | 'ec.suggest_result'
    | 'ec.suggest_click'
    | 'ec.shipping_info'
    | 'ec.shipping_data'
    | 'ec.payment_info'
    | 'ec.purchase'; // order confirmation page view event

export type GtmSectionType = 'category' | 'product' | 'brand' | 'article';

export type GtmUserType = 'visitor' | 'customer';

export type GtmDeviceTypes = 'desktop' | 'tablet' | 'mobile' | 'unknown';

export type GtmConsent = 'granted' | 'denied';

export type GtmConsentUpdateType = {
    event: GtmEventType;
    consent: GtmConsentInfoType;
};

export type GtmEcommerceEventType = {
    event: GtmEventType;
    ecommerce: unknown;
    eventTimeout?: number;
    eventCallback?: (id: string) => void;
    _clear?: boolean;
};

export type GtmPageViewEventType = {
    event: GtmEventType;
    language: string;
    currency: string;
    consent: GtmConsentInfoType;
    page: GtmPageInfoType;
    user: GtmUserInfoType;
    device: GtmDeviceTypes;
    _isLoaded: boolean;
    cart?: GtmCartInfoType | null;
    _clear: boolean;
};

export type GtmSearchEventType = {
    event: GtmEventType;
    suggestClick?: GtmSuggestClickType;
    suggestResult?: GtmSuggestResultType;
};

/** page view event info objects */

export type GtmPageInfoType = {
    type: GtmPageType;
    path: string;
    pageId: string; // random string generated for every page load
    breadcrumbs: BreadcrumbItemType[];
    category?: string[]; // name from root
    categoryId?: number[];
    categoryLevel?: number;
    articleId?: string; // for article page type (UUID used)
};

export type GtmCartInfoType = {
    urlCart: string;
    currency: string;
    value: number;
    valueWithTax: number;
    coupons: string[];
    products: GtmCartItemType[] | undefined;
};

export type GtmCartInfoEventType = {
    cart: GtmCartInfoType | null;
    isLoaded: boolean;
};

export type GtmUserInfoType = {
    type: GtmUserType;
    group?: string;
    id?: string;
    email?: string;
    emailHash?: string;
    name?: string;
    surname?: string;
    phoneNumber?: string; // phone number in intl. format (+420777123456)
    street?: string; // includes house no.
    city?: string;
    psc?: string;
    country?: string; // country code according to ISO 3166-1 alpha-2.
};

export type GtmConsentInfoType = {
    statistics: GtmConsent;
    marketing: GtmConsent;
    preferences: GtmConsent;
};

/** product data types for category list, detail, cart item and order item */

export type GtmProductInterface = {
    id: number;
    name: string;
    availability: string;
    labels: string[];
    uuid: string;
    price: number;
    priceWithTax: number;
    tax: number;
    url: string;
    sku: string;
    brand: string;
    categories: string[];
    listIndex?: number;
    collection?: string;
    coupon?: string;
    size?: string;
    color?: string;
    discount?: number;
    rating?: number;
    variant?: string;
    imageUrl?: string;
};

export type GtmListedProductType = GtmProductInterface & {
    listIndex?: number; // only catalog
};

export type GtmCartItemType = GtmProductInterface & {
    quantity: number; // only cart attribute
};

/** ecommerce event data objects */

export type GtmPurchaseType = {
    currency: string;
    id: string;
    value: number; // order price without shipping and payment price
    valueWithTax: number;
    valueTax: number;
    coupons: string[];
    discountAmount: number;
    paymentType: string;
    paymentPrice: number;
    paymentPriceWithTax: number;
    shippingPrice: number;
    shippingPriceWithTax: number;
    shippingType: string; // 'Zasilkovna'
    shippingDetail: string; // 'Downing street 10',
    shippingExtra: string[]; // ['night delivery'],
    products: GtmCartItemType[];
    reviewConsents: GtmReviewConsentsType;
};

export type GtmReviewConsentsType = {
    seznam: boolean;
    google: boolean;
    heureka: boolean;
};

export type GtmSuggestResultType = {
    keyword: string;
    results: number;
    sections: {
        products: number;
        categories: number;
    };
};

export type GtmSuggestClickType = {
    section: GtmSectionType;
    itemName: string; // which result has been clicked on
    keyword: string; // searched term
};

export type GtmChangeCartItemEventType = {
    listName: GtmListNameType;
    currency: string;
    /**
     * value of the event, depending on the context
     * for addToCart it is the value of added products
     * for removeFromCart it is the value of removed products
     */
    value: number;
    valueWithTax: number; // same as above but with tax
    products: GtmCartItemType[];
};

export type GtmProductsListEventType = {
    listName: GtmListNameType;
    products: GtmListedProductType[];
};

export type GtmProductDetailEventType = {
    currency: string;
    value: number;
    valueWithTax: number;
    products: GtmProductInterface[];
};

export type GtmShippingInfoEventType = {
    value: number; // value of all products
    valueWithTax: number; // value of all products with tax
    currency: string;
    coupons: string[];
    paymentType?: string;
    shippingPrice: number;
    shippingPriceWithTax: number;
    shippingType: string; // 'Zasilkovna'
    shippingDetail: string; // 'Downing street 10',
    shippingExtra: string[]; // ['night delivery'],
    products: GtmCartItemType[];
};

export type GtmPaymentInfoEventType = {
    value: number; // value of all products
    valueWithTax: number; // value of all products with tax
    currency: string;
    coupons: string[];
    paymentType: string;
    paymentPrice: number;
    paymentPriceWithTax: number;
    products: GtmCartItemType[];
};

export type GtmShippingInfoType = {
    shippingDetail: string;
    shippingExtra: string[];
};
