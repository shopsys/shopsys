export type Maybe<T> = T | null;
export type InputMaybe<T> = Maybe<T>;
export type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
export type MakeOptional<T, K extends keyof T> = Omit<T, K> & { [SubKey in K]?: Maybe<T[SubKey]> };
export type MakeMaybe<T, K extends keyof T> = Omit<T, K> & { [SubKey in K]: Maybe<T[SubKey]> };
export type MakeEmpty<T extends { [key: string]: unknown }, K extends keyof T> = { [_ in K]?: never };
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
/** All built-in and custom scalars, mapped to their actual values */
export type Scalars = {
    ID: { input: string; output: string };
    String: { input: string; output: string };
    Boolean: { input: boolean; output: boolean };
    Int: { input: number; output: number };
    Float: { input: number; output: number };
    /** Represents and encapsulates an ISO-8601 encoded UTC date-time value */
    DateTime: { input: any; output: any };
    /** Represents and encapsulates monetary value */
    Money: { input: string; output: string };
    /** Represents and encapsulates a string for password */
    Password: { input: any; output: any };
    /** Represents and encapsulates an ISO-8601 encoded UTC date-time value */
    Uuid: { input: string; output: string };
};
export type AddProductResultApi = {
    __typename?: 'AddProductResult';
    addedQuantity: Scalars['Int']['output'];
    cartItem: CartItemApi;
    isNew: Scalars['Boolean']['output'];
    notOnStockQuantity: Scalars['Int']['output'];
};

export type AddToCartInputApi = {
    /** Cart identifier, new cart will be created if not provided and customer is not logged in */
    cartUuid: InputMaybe<Scalars['Uuid']['input']>;
    /**
     * True if quantity should be set no matter the current state of the cart. False
     * if quantity should be added to the already existing same item in the cart
     */
    isAbsoluteQuantity: InputMaybe<Scalars['Boolean']['input']>;
    /** Product UUID */
    productUuid: Scalars['Uuid']['input'];
    /** Item quantity */
    quantity: Scalars['Int']['input'];
};

export type AddToCartResultApi = {
    __typename?: 'AddToCartResult';
    addProductResult: AddProductResultApi;
    cart: CartApi;
};

/** Represents a singe additional image size */
export type AdditionalSizeApi = {
    __typename?: 'AdditionalSize';
    /** Height in pixels defined in images.yaml */
    height: Maybe<Scalars['Int']['output']>;
    /** Recommended media query defined in images.yaml */
    media: Scalars['String']['output'];
    /** URL address of image */
    url: Scalars['String']['output'];
    /** Width in pixels defined in images.yaml */
    width: Maybe<Scalars['Int']['output']>;
};

export type AdvertApi = {
    /** Restricted categories of the advert (the advert is shown in these categories only) */
    categories: Array<CategoryApi>;
    /** Name of advert */
    name: Scalars['String']['output'];
    /** Position of advert */
    positionName: Scalars['String']['output'];
    /** Type of advert */
    type: Scalars['String']['output'];
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

export type AdvertCodeApi = AdvertApi & {
    __typename?: 'AdvertCode';
    /** Restricted categories of the advert (the advert is shown in these categories only) */
    categories: Array<CategoryApi>;
    /** Advert code */
    code: Scalars['String']['output'];
    /** Name of advert */
    name: Scalars['String']['output'];
    /** Position of advert */
    positionName: Scalars['String']['output'];
    /** Type of advert */
    type: Scalars['String']['output'];
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

export type AdvertImageApi = AdvertApi & {
    __typename?: 'AdvertImage';
    /** Restricted categories of the advert (the advert is shown in these categories only) */
    categories: Array<CategoryApi>;
    /** Advert images */
    images: Array<ImageApi>;
    /** Advert link */
    link: Maybe<Scalars['String']['output']>;
    /** Adverts first image by params */
    mainImage: Maybe<ImageApi>;
    /** Name of advert */
    name: Scalars['String']['output'];
    /** Position of advert */
    positionName: Scalars['String']['output'];
    /** Type of advert */
    type: Scalars['String']['output'];
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

export type AdvertImageImagesArgsApi = {
    size?: InputMaybe<Scalars['String']['input']>;
    sizes?: InputMaybe<Array<Scalars['String']['input']>>;
    type?: InputMaybe<Scalars['String']['input']>;
};

export type AdvertImageMainImageArgsApi = {
    size?: InputMaybe<Scalars['String']['input']>;
    type?: InputMaybe<Scalars['String']['input']>;
};

export type AdvertPositionApi = {
    __typename?: 'AdvertPosition';
    /** Desription of advert position */
    description: Scalars['String']['output'];
    /** Position of advert */
    positionName: Scalars['String']['output'];
};

export type ApplyPromoCodeToCartInputApi = {
    /** Cart identifier or null if customer is logged in */
    cartUuid: InputMaybe<Scalars['Uuid']['input']>;
    /** Promo code to be used after checkout */
    promoCode: Scalars['String']['input'];
};

/** A connection to a list of items. */
export type ArticleConnectionApi = {
    __typename?: 'ArticleConnection';
    /** Information to aid in pagination. */
    edges: Maybe<Array<Maybe<ArticleEdgeApi>>>;
    /** Information to aid in pagination. */
    pageInfo: PageInfoApi;
    /** Total number of articles */
    totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type ArticleEdgeApi = {
    __typename?: 'ArticleEdge';
    /** A cursor for use in pagination. */
    cursor: Scalars['String']['output'];
    /** The item at the end of the edge. */
    node: Maybe<NotBlogArticleInterfaceApi>;
};

/** Represents entity that is considered to be an article on the eshop */
export type ArticleInterfaceApi = {
    breadcrumb: Array<LinkApi>;
    name: Scalars['String']['output'];
    seoH1: Maybe<Scalars['String']['output']>;
    seoMetaDescription: Maybe<Scalars['String']['output']>;
    seoTitle: Maybe<Scalars['String']['output']>;
    slug: Scalars['String']['output'];
    text: Maybe<Scalars['String']['output']>;
    uuid: Scalars['Uuid']['output'];
};

export type ArticleLinkApi = NotBlogArticleInterfaceApi & {
    __typename?: 'ArticleLink';
    /** Creation date time of the article link */
    createdAt: Scalars['DateTime']['output'];
    /** If the the article should be open in a new tab */
    external: Scalars['Boolean']['output'];
    /** Name of article link, used as anchor text */
    name: Scalars['String']['output'];
    /** Placement of the article link */
    placement: Scalars['String']['output'];
    /** Destination url of article link */
    url: Scalars['String']['output'];
    /** UUID of the article link */
    uuid: Scalars['Uuid']['output'];
};

/** Possible placements of an article (used as an input for 'articles' query) */
export enum ArticlePlacementTypeEnumApi {
    /** Articles in 1st footer column */
    Footer1Api = 'footer1',
    /** Articles in 2nd footer column */
    Footer2Api = 'footer2',
    /** Articles in 3rd footer column */
    Footer3Api = 'footer3',
    /** Articles in 4th footer column */
    Footer4Api = 'footer4',
    /** Articles without specific placement */
    NoneApi = 'none',
    /** Articles in top menu */
    TopMenuApi = 'topMenu',
}

export type ArticleSiteApi = ArticleInterfaceApi &
    BreadcrumbApi &
    NotBlogArticleInterfaceApi &
    SlugApi & {
        __typename?: 'ArticleSite';
        /** Hierarchy of the current element in relation to the structure */
        breadcrumb: Array<LinkApi>;
        /** Date and time of the article creation */
        createdAt: Scalars['DateTime']['output'];
        /** If the the article should be open in a new tab */
        external: Scalars['Boolean']['output'];
        /** Name of article */
        name: Scalars['String']['output'];
        /** Placement of article */
        placement: Scalars['String']['output'];
        /** Seo first level heading of article */
        seoH1: Maybe<Scalars['String']['output']>;
        /** Seo meta description of article */
        seoMetaDescription: Maybe<Scalars['String']['output']>;
        /** Seo title of article */
        seoTitle: Maybe<Scalars['String']['output']>;
        /** Article URL slug */
        slug: Scalars['String']['output'];
        /** Text of article */
        text: Maybe<Scalars['String']['output']>;
        /** UUID */
        uuid: Scalars['Uuid']['output'];
    };

/** Represents an availability */
export type AvailabilityApi = {
    __typename?: 'Availability';
    /** Localized availability name (domain dependent) */
    name: Scalars['String']['output'];
    /** Availability status in a format suitable for usage in the code */
    status: AvailabilityStatusEnumApi;
};

/** Product Availability statuses */
export enum AvailabilityStatusEnumApi {
    /** Product availability status in stock */
    InStockApi = 'InStock',
    /** Product availability status out of stock */
    OutOfStockApi = 'OutOfStock',
}

export type BlogArticleApi = ArticleInterfaceApi &
    BreadcrumbApi &
    SlugApi & {
        __typename?: 'BlogArticle';
        /** The list of the blog article blog categories */
        blogCategories: Array<BlogCategoryApi>;
        /** Hierarchy of the current element in relation to the structure */
        breadcrumb: Array<LinkApi>;
        /** Date and time of the blog article creation */
        createdAt: Scalars['DateTime']['output'];
        /** ID of category */
        id: Scalars['Int']['output'];
        /** Blog article images */
        images: Array<ImageApi>;
        /** The blog article absolute URL */
        link: Scalars['String']['output'];
        /** Blog article image by params */
        mainImage: Maybe<ImageApi>;
        /** The blog article title */
        name: Scalars['String']['output'];
        /** The blog article perex */
        perex: Maybe<Scalars['String']['output']>;
        /** Date and time of the blog article publishing */
        publishDate: Scalars['DateTime']['output'];
        /** The blog article SEO H1 heading */
        seoH1: Maybe<Scalars['String']['output']>;
        /** The blog article SEO meta description */
        seoMetaDescription: Maybe<Scalars['String']['output']>;
        /** The blog article SEO title */
        seoTitle: Maybe<Scalars['String']['output']>;
        /** The blog article URL slug */
        slug: Scalars['String']['output'];
        /** The blog article text */
        text: Maybe<Scalars['String']['output']>;
        /** The blog article UUID */
        uuid: Scalars['Uuid']['output'];
        /** Indicates whether the blog article is displayed on homepage */
        visibleOnHomepage: Scalars['Boolean']['output'];
    };

export type BlogArticleImagesArgsApi = {
    sizes?: InputMaybe<Array<Scalars['String']['input']>>;
    type?: InputMaybe<Scalars['String']['input']>;
};

export type BlogArticleMainImageArgsApi = {
    size?: InputMaybe<Scalars['String']['input']>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** A connection to a list of items. */
export type BlogArticleConnectionApi = {
    __typename?: 'BlogArticleConnection';
    /** Information to aid in pagination. */
    edges: Maybe<Array<Maybe<BlogArticleEdgeApi>>>;
    /** Information to aid in pagination. */
    pageInfo: PageInfoApi;
    /** Total number of the blog articles */
    totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type BlogArticleEdgeApi = {
    __typename?: 'BlogArticleEdge';
    /** A cursor for use in pagination. */
    cursor: Scalars['String']['output'];
    /** The item at the end of the edge. */
    node: Maybe<BlogArticleApi>;
};

export type BlogCategoryApi = BreadcrumbApi &
    SlugApi & {
        __typename?: 'BlogCategory';
        /** Total count of blog articles in this category */
        articlesTotalCount: Scalars['Int']['output'];
        /** Paginated blog articles of the given blog category */
        blogArticles: BlogArticleConnectionApi;
        /** Tho whole blog categories tree (used for blog navigation rendering) */
        blogCategoriesTree: Array<BlogCategoryApi>;
        /** Hierarchy of the current element in relation to the structure */
        breadcrumb: Array<LinkApi>;
        /** The blog category children */
        children: Array<BlogCategoryApi>;
        /** The blog category description */
        description: Maybe<Scalars['String']['output']>;
        /** The blog category absolute URL */
        link: Scalars['String']['output'];
        /** The blog category name */
        name: Scalars['String']['output'];
        /** The blog category parent */
        parent: Maybe<BlogCategoryApi>;
        /** The blog category SEO H1 heading */
        seoH1: Maybe<Scalars['String']['output']>;
        /** The blog category SEO meta description */
        seoMetaDescription: Maybe<Scalars['String']['output']>;
        /** The blog category SEO title */
        seoTitle: Maybe<Scalars['String']['output']>;
        /** The blog category URL slug */
        slug: Scalars['String']['output'];
        /** The blog category UUID */
        uuid: Scalars['Uuid']['output'];
    };

export type BlogCategoryBlogArticlesArgsApi = {
    after: InputMaybe<Scalars['String']['input']>;
    before: InputMaybe<Scalars['String']['input']>;
    first: InputMaybe<Scalars['Int']['input']>;
    last: InputMaybe<Scalars['Int']['input']>;
    onlyHomepageArticles?: InputMaybe<Scalars['Boolean']['input']>;
};

/** Represents a brand */
export type BrandApi = BreadcrumbApi &
    ProductListableApi &
    SlugApi & {
        __typename?: 'Brand';
        /** Hierarchy of the current element in relation to the structure */
        breadcrumb: Array<LinkApi>;
        /** Brand description */
        description: Maybe<Scalars['String']['output']>;
        /** ID of category */
        id: Scalars['Int']['output'];
        /** Brand images */
        images: Array<ImageApi>;
        /** Brand main URL */
        link: Scalars['String']['output'];
        /** Brand image by params */
        mainImage: Maybe<ImageApi>;
        /** Brand name */
        name: Scalars['String']['output'];
        /** Paginated and ordered products of brand */
        products: ProductConnectionApi;
        /** Brand SEO H1 */
        seoH1: Maybe<Scalars['String']['output']>;
        /** Brand SEO meta description */
        seoMetaDescription: Maybe<Scalars['String']['output']>;
        /** Brand SEO title */
        seoTitle: Maybe<Scalars['String']['output']>;
        /** Brand URL slug */
        slug: Scalars['String']['output'];
        /** UUID */
        uuid: Scalars['Uuid']['output'];
    };

/** Represents a brand */
export type BrandImagesArgsApi = {
    size: InputMaybe<Scalars['String']['input']>;
    sizes?: InputMaybe<Array<Scalars['String']['input']>>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents a brand */
export type BrandMainImageArgsApi = {
    size?: InputMaybe<Scalars['String']['input']>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents a brand */
export type BrandProductsArgsApi = {
    after: InputMaybe<Scalars['String']['input']>;
    before: InputMaybe<Scalars['String']['input']>;
    brandSlug: InputMaybe<Scalars['String']['input']>;
    categorySlug: InputMaybe<Scalars['String']['input']>;
    filter: InputMaybe<ProductFilterApi>;
    first: InputMaybe<Scalars['Int']['input']>;
    flagSlug: InputMaybe<Scalars['String']['input']>;
    last: InputMaybe<Scalars['Int']['input']>;
    orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
    search: InputMaybe<Scalars['String']['input']>;
};

/** Brand filter option */
export type BrandFilterOptionApi = {
    __typename?: 'BrandFilterOption';
    /** Brand */
    brand: BrandApi;
    /** Count of products that will be filtered if this filter option is applied. */
    count: Scalars['Int']['output'];
    /**
     * If true than count parameter is number of products that will be displayed if
     * this filter option is applied, if false count parameter is number of products
     * that will be added to current products result.
     */
    isAbsolute: Scalars['Boolean']['output'];
};

/** Represents entity able to return breadcrumb */
export type BreadcrumbApi = {
    /** Hierarchy of the current element in relation to the structure */
    breadcrumb: Array<LinkApi>;
};

export type CartApi = CartInterfaceApi & {
    __typename?: 'Cart';
    /** All items in the cart */
    items: Array<CartItemApi>;
    modifications: CartModificationsResultApi;
    /** Selected payment if payment provided */
    payment: Maybe<PaymentApi>;
    /** Selected bank swift code of goPay payment bank transfer */
    paymentGoPayBankSwift: Maybe<Scalars['String']['output']>;
    /** Applied promo code if provided */
    promoCode: Maybe<Scalars['String']['output']>;
    /** Remaining amount for free transport and payment; null = transport cannot be free */
    remainingAmountWithVatForFreeTransport: Maybe<Scalars['Money']['output']>;
    /** Selected pickup place identifier if provided */
    selectedPickupPlaceIdentifier: Maybe<Scalars['String']['output']>;
    totalDiscountPrice: PriceApi;
    /** Total items price (excluding transport and payment) */
    totalItemsPrice: PriceApi;
    /** Total price including transport and payment */
    totalPrice: PriceApi;
    /** Total price (exluding discount, transport and payment) */
    totalPriceWithoutDiscountTransportAndPayment: PriceApi;
    /** Selected transport if transport provided */
    transport: Maybe<TransportApi>;
    /** UUID of the cart, null for authenticated user */
    uuid: Maybe<Scalars['Uuid']['output']>;
};

export type CartInputApi = {
    /** Cart identifier, new cart will be created if not provided and customer is not logged in */
    cartUuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type CartInterfaceApi = {
    items: Array<CartItemApi>;
    modifications: CartModificationsResultApi;
    payment: Maybe<PaymentApi>;
    paymentGoPayBankSwift: Maybe<Scalars['String']['output']>;
    promoCode: Maybe<Scalars['String']['output']>;
    remainingAmountWithVatForFreeTransport: Maybe<Scalars['Money']['output']>;
    selectedPickupPlaceIdentifier: Maybe<Scalars['String']['output']>;
    totalDiscountPrice: PriceApi;
    /** Total items price (excluding transport and payment) */
    totalItemsPrice: PriceApi;
    /** Total price including transport and payment */
    totalPrice: PriceApi;
    transport: Maybe<TransportApi>;
    uuid: Maybe<Scalars['Uuid']['output']>;
};

/** Represent one item in the cart */
export type CartItemApi = {
    __typename?: 'CartItem';
    /** Product in the cart */
    product: ProductApi;
    /** Quantity of items in the cart */
    quantity: Scalars['Int']['output'];
    /** Cart item UUID */
    uuid: Scalars['Uuid']['output'];
};

export type CartItemModificationsResultApi = {
    __typename?: 'CartItemModificationsResult';
    cartItemsWithChangedQuantity: Array<CartItemApi>;
    cartItemsWithModifiedPrice: Array<CartItemApi>;
    noLongerAvailableCartItemsDueToQuantity: Array<CartItemApi>;
    noLongerListableCartItems: Array<CartItemApi>;
};

export type CartModificationsResultApi = {
    __typename?: 'CartModificationsResult';
    itemModifications: CartItemModificationsResultApi;
    paymentModifications: CartPaymentModificationsResultApi;
    promoCodeModifications: CartPromoCodeModificationsResultApi;
    someProductWasRemovedFromEshop: Scalars['Boolean']['output'];
    transportModifications: CartTransportModificationsResultApi;
};

export type CartPaymentModificationsResultApi = {
    __typename?: 'CartPaymentModificationsResult';
    paymentPriceChanged: Scalars['Boolean']['output'];
    paymentUnavailable: Scalars['Boolean']['output'];
};

export type CartPromoCodeModificationsResultApi = {
    __typename?: 'CartPromoCodeModificationsResult';
    noLongerApplicablePromoCode: Array<Scalars['String']['output']>;
};

export type CartTransportModificationsResultApi = {
    __typename?: 'CartTransportModificationsResult';
    personalPickupStoreUnavailable: Scalars['Boolean']['output'];
    transportPriceChanged: Scalars['Boolean']['output'];
    transportUnavailable: Scalars['Boolean']['output'];
    transportWeightLimitExceeded: Scalars['Boolean']['output'];
};

/** Represents a category */
export type CategoryApi = BreadcrumbApi &
    ProductListableApi &
    SlugApi & {
        __typename?: 'Category';
        /** Best selling products */
        bestsellers: Array<ProductApi>;
        /** Hierarchy of the current element in relation to the structure */
        breadcrumb: Array<LinkApi>;
        /** All parent category names with their UUIDs */
        categoryHierarchy: Array<CategoryHierarchyItemApi>;
        /** Descendant categories */
        children: Array<CategoryApi>;
        /** Localized category description (domain dependent) */
        description: Maybe<Scalars['String']['output']>;
        /** ID of category */
        id: Scalars['Int']['output'];
        /** Category images */
        images: Array<ImageApi>;
        /** A list of categories linked to the given category */
        linkedCategories: Array<CategoryApi>;
        /** Category image by params */
        mainImage: Maybe<ImageApi>;
        /** Localized category name (domain dependent) */
        name: Scalars['String']['output'];
        /**
         * Original category URL slug (for CategorySeoMixes slug of assigned category is
         * returned, null is returned for regular category)
         */
        originalCategorySlug: Maybe<Scalars['String']['output']>;
        /** Ancestor category */
        parent: Maybe<CategoryApi>;
        /** Paginated and ordered products of category */
        products: ProductConnectionApi;
        /** An array of links of prepared category SEO mixes of a given category */
        readyCategorySeoMixLinks: Array<LinkApi>;
        /** Seo first level heading of category */
        seoH1: Maybe<Scalars['String']['output']>;
        /** Seo meta description of category */
        seoMetaDescription: Maybe<Scalars['String']['output']>;
        /** Seo title of category */
        seoTitle: Maybe<Scalars['String']['output']>;
        /** Category URL slug */
        slug: Scalars['String']['output'];
        /** UUID */
        uuid: Scalars['Uuid']['output'];
    };

/** Represents a category */
export type CategoryImagesArgsApi = {
    size: InputMaybe<Scalars['String']['input']>;
    sizes?: InputMaybe<Array<Scalars['String']['input']>>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents a category */
export type CategoryMainImageArgsApi = {
    size?: InputMaybe<Scalars['String']['input']>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents a category */
export type CategoryProductsArgsApi = {
    after: InputMaybe<Scalars['String']['input']>;
    before: InputMaybe<Scalars['String']['input']>;
    brandSlug: InputMaybe<Scalars['String']['input']>;
    categorySlug: InputMaybe<Scalars['String']['input']>;
    filter: InputMaybe<ProductFilterApi>;
    first: InputMaybe<Scalars['Int']['input']>;
    flagSlug: InputMaybe<Scalars['String']['input']>;
    last: InputMaybe<Scalars['Int']['input']>;
    orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
    search: InputMaybe<Scalars['String']['input']>;
};

/** A connection to a list of items. */
export type CategoryConnectionApi = {
    __typename?: 'CategoryConnection';
    /** Information to aid in pagination. */
    edges: Maybe<Array<Maybe<CategoryEdgeApi>>>;
    /** Information to aid in pagination. */
    pageInfo: PageInfoApi;
    /** Total number of categories */
    totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type CategoryEdgeApi = {
    __typename?: 'CategoryEdge';
    /** A cursor for use in pagination. */
    cursor: Scalars['String']['output'];
    /** The item at the end of the edge. */
    node: Maybe<CategoryApi>;
};

export type CategoryHierarchyItemApi = {
    __typename?: 'CategoryHierarchyItem';
    /** Localized category name (domain dependent) */
    name: Scalars['String']['output'];
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

export type ChangePasswordInputApi = {
    /** Customer user email. */
    email: Scalars['String']['input'];
    /** New customer user password. */
    newPassword: Scalars['Password']['input'];
    /** Current customer user password. */
    oldPassword: Scalars['Password']['input'];
};

export type ChangePaymentInCartInputApi = {
    /** Cart identifier or null if customer is logged in */
    cartUuid: InputMaybe<Scalars['Uuid']['input']>;
    /** Selected bank swift code of goPay payment bank transfer */
    paymentGoPayBankSwift: InputMaybe<Scalars['String']['input']>;
    /** UUID of a payment that should be added to the cart. If this is set to null, the payment is removed from the cart */
    paymentUuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type ChangePersonalDataInputApi = {
    /** Billing address city name (will be on the tax invoice) */
    city: Scalars['String']['input'];
    /** Determines whether the customer is a company or not. */
    companyCustomer: InputMaybe<Scalars['Boolean']['input']>;
    /** The customer’s company name (required when companyCustomer is true) */
    companyName: InputMaybe<Scalars['String']['input']>;
    /** The customer’s company identification number (required when companyCustomer is true) */
    companyNumber: InputMaybe<Scalars['String']['input']>;
    /** The customer’s company tax number (required when companyCustomer is true) */
    companyTaxNumber: InputMaybe<Scalars['String']['input']>;
    /** Billing address country code in ISO 3166-1 alpha-2 (Country will be on the tax invoice) */
    country: Scalars['String']['input'];
    /** Customer user first name */
    firstName: Scalars['String']['input'];
    /** Customer user last name */
    lastName: Scalars['String']['input'];
    /** Whether customer user should receive newsletters or not */
    newsletterSubscription: Scalars['Boolean']['input'];
    /** Billing address zip code (will be on the tax invoice) */
    postcode: Scalars['String']['input'];
    /** Billing address street name (will be on the tax invoice) */
    street: Scalars['String']['input'];
    /** The customer's telephone number */
    telephone: Scalars['String']['input'];
};

export type ChangeTransportInCartInputApi = {
    /** Cart identifier or null if customer is logged in */
    cartUuid: InputMaybe<Scalars['Uuid']['input']>;
    /** The identifier of selected personal pickup place */
    pickupPlaceIdentifier: InputMaybe<Scalars['String']['input']>;
    /** UUID of a transport that should be added to the cart. If this is set to null, the transport is removed from the cart */
    transportUuid: InputMaybe<Scalars['Uuid']['input']>;
};

/** Represents an currently logged customer user */
export type CompanyCustomerUserApi = CustomerUserApi & {
    __typename?: 'CompanyCustomerUser';
    /** Billing address city name */
    city: Scalars['String']['output'];
    /** The customer’s company name (only when customer is a company) */
    companyName: Maybe<Scalars['String']['output']>;
    /** The customer’s company identification number (only when customer is a company) */
    companyNumber: Maybe<Scalars['String']['output']>;
    /** The customer’s company tax number (only when customer is a company) */
    companyTaxNumber: Maybe<Scalars['String']['output']>;
    /** Billing address country */
    country: CountryApi;
    /** Default customer delivery addresses */
    defaultDeliveryAddress: Maybe<DeliveryAddressApi>;
    /** List of delivery addresses */
    deliveryAddresses: Array<DeliveryAddressApi>;
    /** Email address */
    email: Scalars['String']['output'];
    /** First name */
    firstName: Scalars['String']['output'];
    /** Last name */
    lastName: Scalars['String']['output'];
    /** Whether customer user receives newsletters or not */
    newsletterSubscription: Scalars['Boolean']['output'];
    /** Billing address zip code */
    postcode: Scalars['String']['output'];
    /** The name of the customer pricing group */
    pricingGroup: Scalars['String']['output'];
    /** Billing address street name */
    street: Scalars['String']['output'];
    /** Phone number */
    telephone: Maybe<Scalars['String']['output']>;
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

export type ComparisonApi = {
    __typename?: 'Comparison';
    /** List of compared products */
    products: Array<ProductApi>;
    /** Comparison identifier */
    uuid: Scalars['Uuid']['output'];
};

export type ContactInputApi = {
    /** Email address of the sender */
    email: Scalars['String']['input'];
    /** Message sent to recipient */
    message: Scalars['String']['input'];
    /** Name of the sender */
    name: Scalars['String']['input'];
};

/** Represents country */
export type CountryApi = {
    __typename?: 'Country';
    /** Country code in ISO 3166-1 alpha-2 */
    code: Scalars['String']['output'];
    /** Localized country name */
    name: Scalars['String']['output'];
};

export type CreateOrderResultApi = {
    __typename?: 'CreateOrderResult';
    cart: Maybe<CartApi>;
    order: Maybe<OrderApi>;
    orderCreated: Scalars['Boolean']['output'];
};

/** Represents an currently logged customer user */
export type CustomerUserApi = {
    /** Billing address city name */
    city: Scalars['String']['output'];
    /** Billing address country */
    country: CountryApi;
    /** Default customer delivery addresses */
    defaultDeliveryAddress: Maybe<DeliveryAddressApi>;
    /** List of delivery addresses */
    deliveryAddresses: Array<DeliveryAddressApi>;
    /** Email address */
    email: Scalars['String']['output'];
    /** First name */
    firstName: Scalars['String']['output'];
    /** Last name */
    lastName: Scalars['String']['output'];
    /** Whether customer user receives newsletters or not */
    newsletterSubscription: Scalars['Boolean']['output'];
    /** Billing address zip code */
    postcode: Scalars['String']['output'];
    /** The name of the customer pricing group */
    pricingGroup: Scalars['String']['output'];
    /** Billing address street name */
    street: Scalars['String']['output'];
    /** Phone number */
    telephone: Maybe<Scalars['String']['output']>;
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

export type DeliveryAddressApi = {
    __typename?: 'DeliveryAddress';
    /** Delivery address city name */
    city: Maybe<Scalars['String']['output']>;
    /** Delivery address company name */
    companyName: Maybe<Scalars['String']['output']>;
    /** Delivery address country */
    country: Maybe<CountryApi>;
    /** Delivery address firstname */
    firstName: Maybe<Scalars['String']['output']>;
    /** Delivery address lastname */
    lastName: Maybe<Scalars['String']['output']>;
    /** Delivery address zip code */
    postcode: Maybe<Scalars['String']['output']>;
    /** Delivery address street name */
    street: Maybe<Scalars['String']['output']>;
    /** Delivery address telephone */
    telephone: Maybe<Scalars['String']['output']>;
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

export type DeliveryAddressInputApi = {
    /** Delivery address city name */
    city: Scalars['String']['input'];
    /** Delivery address company name */
    companyName: InputMaybe<Scalars['String']['input']>;
    /** Delivery address country */
    country: Scalars['String']['input'];
    /** Delivery address first name */
    firstName: Scalars['String']['input'];
    /** Delivery address last name */
    lastName: Scalars['String']['input'];
    /** Delivery address zip code */
    postcode: Scalars['String']['input'];
    /** Delivery address street name */
    street: Scalars['String']['input'];
    /** Delivery address telephone */
    telephone: InputMaybe<Scalars['String']['input']>;
    /** UUID */
    uuid: InputMaybe<Scalars['Uuid']['input']>;
};

/** Represents a downloadable file */
export type FileApi = {
    __typename?: 'File';
    /** Clickable text for a hyperlink */
    anchorText: Scalars['String']['output'];
    /** Url to download the file */
    url: Scalars['String']['output'];
};

/** Represents a flag */
export type FlagApi = BreadcrumbApi &
    ProductListableApi &
    SlugApi & {
        __typename?: 'Flag';
        /** Hierarchy of the current element in relation to the structure */
        breadcrumb: Array<LinkApi>;
        /** Categories containing at least one product with flag */
        categories: Array<CategoryApi>;
        /** Localized flag name (domain dependent) */
        name: Scalars['String']['output'];
        /** Paginated and ordered products of flag */
        products: ProductConnectionApi;
        /** Flag color in rgb format */
        rgbColor: Scalars['String']['output'];
        /** URL slug of flag */
        slug: Scalars['String']['output'];
        /** UUID */
        uuid: Scalars['Uuid']['output'];
    };

/** Represents a flag */
export type FlagCategoriesArgsApi = {
    productFilter: InputMaybe<ProductFilterApi>;
};

/** Represents a flag */
export type FlagProductsArgsApi = {
    after: InputMaybe<Scalars['String']['input']>;
    before: InputMaybe<Scalars['String']['input']>;
    brandSlug: InputMaybe<Scalars['String']['input']>;
    categorySlug: InputMaybe<Scalars['String']['input']>;
    filter: InputMaybe<ProductFilterApi>;
    first: InputMaybe<Scalars['Int']['input']>;
    flagSlug: InputMaybe<Scalars['String']['input']>;
    last: InputMaybe<Scalars['Int']['input']>;
    orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
    search: InputMaybe<Scalars['String']['input']>;
};

/** Flag filter option */
export type FlagFilterOptionApi = {
    __typename?: 'FlagFilterOption';
    /** Count of products that will be filtered if this filter option is applied. */
    count: Scalars['Int']['output'];
    /** Flag */
    flag: FlagApi;
    /**
     * If true than count parameter is number of products that will be displayed if
     * this filter option is applied, if false count parameter is number of products
     * that will be added to current products result.
     */
    isAbsolute: Scalars['Boolean']['output'];
    /** Indicator whether the option is already selected (used for "ready category seo mixes") */
    isSelected: Scalars['Boolean']['output'];
};

export type GoPayBankSwiftApi = {
    __typename?: 'GoPayBankSwift';
    /** large image url */
    imageLargeUrl: Scalars['String']['output'];
    /** normal image url */
    imageNormalUrl: Scalars['String']['output'];
    isOnline: Scalars['Boolean']['output'];
    /** Bank name */
    name: Scalars['String']['output'];
    /** Swift code */
    swift: Scalars['String']['output'];
};

export type GoPayCreatePaymentSetupApi = {
    __typename?: 'GoPayCreatePaymentSetup';
    /** url of gopay embedJs file */
    embedJs: Scalars['String']['output'];
    /** redirect URL to payment gateway */
    gatewayUrl: Scalars['String']['output'];
    /** payment transaction identifier */
    goPayId: Scalars['String']['output'];
};

export type GoPayPaymentMethodApi = {
    __typename?: 'GoPayPaymentMethod';
    /** Identifier of payment method */
    identifier: Scalars['String']['output'];
    /** URL to large size image of payment method */
    imageLargeUrl: Scalars['String']['output'];
    /** URL to normal size image of payment method */
    imageNormalUrl: Scalars['String']['output'];
    /** Name of payment method */
    name: Scalars['String']['output'];
    /** Group of payment methods */
    paymentGroup: Scalars['String']['output'];
};

/** Represents an image */
export type ImageApi = {
    __typename?: 'Image';
    /** Image name for ALT attribute */
    name: Maybe<Scalars['String']['output']>;
    /** Position of image in list */
    position: Maybe<Scalars['Int']['output']>;
    sizes: Array<ImageSizeApi>;
    /** Image type */
    type: Maybe<Scalars['String']['output']>;
};

/** Represents a single image size */
export type ImageSizeApi = {
    __typename?: 'ImageSize';
    /** Additional sizes for different screen types */
    additionalSizes: Array<AdditionalSizeApi>;
    /** Height in pixels defined in images.yaml */
    height: Maybe<Scalars['Int']['output']>;
    /** Image size defined in images.yaml */
    size: Scalars['String']['output'];
    /** URL address of image */
    url: Scalars['String']['output'];
    /** Width in pixels defined in images.yaml */
    width: Maybe<Scalars['Int']['output']>;
};

/** Represents a single user translation of language constant */
export type LanguageConstantApi = {
    __typename?: 'LanguageConstant';
    /** Translation key */
    key: Scalars['String']['output'];
    /** User translation */
    translation: Scalars['String']['output'];
};

/** Represents an internal link */
export type LinkApi = {
    __typename?: 'Link';
    /** Clickable text for a hyperlink */
    name: Scalars['String']['output'];
    /** Target URL slug */
    slug: Scalars['String']['output'];
};

export type LoginInputApi = {
    /** Uuid of the cart that should be merged to the cart of the user */
    cartUuid: InputMaybe<Scalars['Uuid']['input']>;
    /** The user email. */
    email: Scalars['String']['input'];
    /** The user password. */
    password: Scalars['Password']['input'];
};

export type LoginResultApi = {
    __typename?: 'LoginResult';
    showCartMergeInfo: Scalars['Boolean']['output'];
    tokens: TokenApi;
};

/** Represents a product */
export type MainVariantApi = BreadcrumbApi &
    ProductApi &
    SlugApi & {
        __typename?: 'MainVariant';
        accessories: Array<ProductApi>;
        availability: AvailabilityApi;
        /** Number of the stores where the product is available */
        availableStoresCount: Scalars['Int']['output'];
        /** Brand of product */
        brand: Maybe<BrandApi>;
        /** Hierarchy of the current element in relation to the structure */
        breadcrumb: Array<LinkApi>;
        /** Product catalog number */
        catalogNumber: Scalars['String']['output'];
        /** List of categories */
        categories: Array<CategoryApi>;
        description: Maybe<Scalars['String']['output']>;
        /** EAN */
        ean: Maybe<Scalars['String']['output']>;
        /** Number of the stores where the product is exposed */
        exposedStoresCount: Scalars['Int']['output'];
        /** List of downloadable files */
        files: Array<FileApi>;
        /** List of flags */
        flags: Array<FlagApi>;
        /** The full name of the product, which consists of a prefix, name, and a suffix */
        fullName: Scalars['String']['output'];
        /** Distinguishes if the product can be pre-ordered */
        hasPreorder: Scalars['Boolean']['output'];
        /** Product id */
        id: Scalars['Int']['output'];
        /** Product images */
        images: Array<ImageApi>;
        isMainVariant: Scalars['Boolean']['output'];
        isSellingDenied: Scalars['Boolean']['output'];
        isUsingStock: Scalars['Boolean']['output'];
        /** Product link */
        link: Scalars['String']['output'];
        /** Product image by params */
        mainImage: Maybe<ImageApi>;
        /** Localized product name (domain dependent) */
        name: Scalars['String']['output'];
        /** Name prefix */
        namePrefix: Maybe<Scalars['String']['output']>;
        /** Name suffix */
        nameSuffix: Maybe<Scalars['String']['output']>;
        orderingPriority: Scalars['Int']['output'];
        parameters: Array<ParameterApi>;
        /** Product part number */
        partNumber: Maybe<Scalars['String']['output']>;
        /** Product price */
        price: ProductPriceApi;
        productVideos: Array<VideoTokenApi>;
        /** List of related products */
        relatedProducts: Array<ProductApi>;
        /** Seo first level heading of product */
        seoH1: Maybe<Scalars['String']['output']>;
        /** Seo meta description of product */
        seoMetaDescription: Maybe<Scalars['String']['output']>;
        /** Seo title of product */
        seoTitle: Maybe<Scalars['String']['output']>;
        /** Localized product short description (domain dependent) */
        shortDescription: Maybe<Scalars['String']['output']>;
        /** Product URL slug */
        slug: Scalars['String']['output'];
        /** Count of quantity on stock */
        stockQuantity: Scalars['Int']['output'];
        /** List of availabilities in individual stores */
        storeAvailabilities: Array<StoreAvailabilityApi>;
        unit: UnitApi;
        /** List of product's unique selling propositions */
        usps: Array<Scalars['String']['output']>;
        /** UUID */
        uuid: Scalars['Uuid']['output'];
        variants: Array<VariantApi>;
    };

/** Represents a product */
export type MainVariantImagesArgsApi = {
    size: InputMaybe<Scalars['String']['input']>;
    sizes?: InputMaybe<Array<Scalars['String']['input']>>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents a product */
export type MainVariantMainImageArgsApi = {
    size?: InputMaybe<Scalars['String']['input']>;
    type?: InputMaybe<Scalars['String']['input']>;
};

export type MutationApi = {
    __typename?: 'Mutation';
    /** Add product to cart for future checkout */
    AddToCart: AddToCartResultApi;
    /** Apply new promo code for the future checkout */
    ApplyPromoCodeToCart: CartApi;
    /** Changes customer user password */
    ChangePassword: CustomerUserApi;
    /** Add a payment to the cart, or remove a payment from the cart */
    ChangePaymentInCart: CartApi;
    /** Changes customer user personal data */
    ChangePersonalData: CustomerUserApi;
    /** Add a transport to the cart, or remove a transport from the cart */
    ChangeTransportInCart: CartApi;
    /** check payment status of order after callback from payment service */
    CheckPaymentStatus: Scalars['Boolean']['output'];
    /** Send message to the site owner */
    Contact: Scalars['Boolean']['output'];
    /** Creates complete order with products and addresses */
    CreateOrder: CreateOrderResultApi;
    /** Delete delivery address by Uuid */
    DeleteDeliveryAddress: Array<DeliveryAddressApi>;
    /** Edit delivery address by Uuid */
    EditDeliveryAddress: Array<DeliveryAddressApi>;
    /** Login customer user */
    Login: LoginResultApi;
    /** Logout user */
    Logout: Scalars['Boolean']['output'];
    /** Subscribe for e-mail newsletter */
    NewsletterSubscribe: Scalars['Boolean']['output'];
    /**
     * Pay order(create payment transaction in payment gateway) and get payment setup
     * data for redirect or creating JS payment gateway layer
     */
    PayOrder: PaymentSetupCreationDataApi;
    /** Recover password using hash required from RequestPasswordRecovery */
    RecoverPassword: LoginResultApi;
    /** Refreshes access and refresh tokens */
    RefreshTokens: TokenApi;
    /** Register new customer user */
    Register: LoginResultApi;
    /** Remove product from cart */
    RemoveFromCart: CartApi;
    /** Remove already used promo code from cart */
    RemovePromoCodeFromCart: CartApi;
    /** Request password recovery - email with hash will be sent */
    RequestPasswordRecovery: Scalars['String']['output'];
    /** Request access to personal data */
    RequestPersonalDataAccess: PersonalDataPageApi;
    /** Set default delivery address by Uuid */
    SetDefaultDeliveryAddress: CustomerUserApi;
    /** Add product to Comparison and create if not exists. */
    addProductToComparison: ComparisonApi;
    /** Add product to wishlist and create if not exists. */
    addProductToWishlist: WishlistApi;
    /** Remove all products from Comparison and remove it. */
    cleanComparison: Scalars['String']['output'];
    /** Remove all products from wishlist and remove it. */
    cleanWishlist: Maybe<WishlistApi>;
    /** Remove product from Comparison and if is Comparison empty remove it. */
    removeProductFromComparison: Maybe<ComparisonApi>;
    /** Remove product from wishlist and if is wishlist empty remove it. */
    removeProductFromWishlist: Maybe<WishlistApi>;
};

export type MutationAddToCartArgsApi = {
    input: AddToCartInputApi;
};

export type MutationApplyPromoCodeToCartArgsApi = {
    input: ApplyPromoCodeToCartInputApi;
};

export type MutationChangePasswordArgsApi = {
    input: ChangePasswordInputApi;
};

export type MutationChangePaymentInCartArgsApi = {
    input: ChangePaymentInCartInputApi;
};

export type MutationChangePersonalDataArgsApi = {
    input: ChangePersonalDataInputApi;
};

export type MutationChangeTransportInCartArgsApi = {
    input: ChangeTransportInCartInputApi;
};

export type MutationCheckPaymentStatusArgsApi = {
    orderUuid: Scalars['Uuid']['input'];
};

export type MutationContactArgsApi = {
    input: ContactInputApi;
};

export type MutationCreateOrderArgsApi = {
    input: OrderInputApi;
};

export type MutationDeleteDeliveryAddressArgsApi = {
    deliveryAddressUuid: Scalars['Uuid']['input'];
};

export type MutationEditDeliveryAddressArgsApi = {
    input: DeliveryAddressInputApi;
};

export type MutationLoginArgsApi = {
    input: LoginInputApi;
};

export type MutationNewsletterSubscribeArgsApi = {
    input: NewsletterSubscriptionDataInputApi;
};

export type MutationPayOrderArgsApi = {
    orderUuid: Scalars['Uuid']['input'];
};

export type MutationRecoverPasswordArgsApi = {
    input: RecoverPasswordInputApi;
};

export type MutationRefreshTokensArgsApi = {
    input: RefreshTokenInputApi;
};

export type MutationRegisterArgsApi = {
    input: RegistrationDataInputApi;
};

export type MutationRemoveFromCartArgsApi = {
    input: RemoveFromCartInputApi;
};

export type MutationRemovePromoCodeFromCartArgsApi = {
    input: RemovePromoCodeFromCartInputApi;
};

export type MutationRequestPasswordRecoveryArgsApi = {
    email: Scalars['String']['input'];
};

export type MutationRequestPersonalDataAccessArgsApi = {
    input: PersonalDataAccessRequestInputApi;
};

export type MutationSetDefaultDeliveryAddressArgsApi = {
    deliveryAddressUuid: Scalars['Uuid']['input'];
};

export type MutationAddProductToComparisonArgsApi = {
    comparisonUuid: InputMaybe<Scalars['Uuid']['input']>;
    productUuid: Scalars['Uuid']['input'];
};

export type MutationAddProductToWishlistArgsApi = {
    productUuid: Scalars['Uuid']['input'];
    wishlistUuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type MutationCleanComparisonArgsApi = {
    comparisonUuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type MutationCleanWishlistArgsApi = {
    wishlistUuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type MutationRemoveProductFromComparisonArgsApi = {
    comparisonUuid: InputMaybe<Scalars['Uuid']['input']>;
    productUuid: Scalars['Uuid']['input'];
};

export type MutationRemoveProductFromWishlistArgsApi = {
    productUuid: Scalars['Uuid']['input'];
    wishlistUuid: InputMaybe<Scalars['Uuid']['input']>;
};

/** Represents a navigation structure item */
export type NavigationItemApi = {
    __typename?: 'NavigationItem';
    /** Categories separated into columns */
    categoriesByColumns: Array<NavigationItemCategoriesByColumnsApi>;
    /** Target URL */
    link: Scalars['String']['output'];
    /** Navigation item name */
    name: Scalars['String']['output'];
};

/** Represents a single column inside the navigation item */
export type NavigationItemCategoriesByColumnsApi = {
    __typename?: 'NavigationItemCategoriesByColumns';
    /** Categories */
    categories: Array<CategoryApi>;
    /** Column number */
    columnNumber: Scalars['Int']['output'];
};

export type NewsletterSubscriberApi = {
    __typename?: 'NewsletterSubscriber';
    /** Date and time of subscription */
    createdAt: Scalars['DateTime']['output'];
    /** Subscribed email address */
    email: Scalars['String']['output'];
};

/** Represents the main input object to subscribe for e-mail newsletter */
export type NewsletterSubscriptionDataInputApi = {
    email: Scalars['String']['input'];
};

/** Represents an article that is not a blog article */
export type NotBlogArticleInterfaceApi = {
    /** creation date time of the article */
    createdAt: Scalars['DateTime']['output'];
    /** If the the article should be open in a new tab */
    external: Scalars['Boolean']['output'];
    /** name of article link */
    name: Scalars['String']['output'];
    /** placement of the article */
    placement: Scalars['String']['output'];
    /** UUID of the article link */
    uuid: Scalars['Uuid']['output'];
};

/** Represents a notification supposed to be displayed on all pages */
export type NotificationBarApi = {
    __typename?: 'NotificationBar';
    /** Notification bar images */
    images: Array<ImageApi>;
    /** Notification bar image by params */
    mainImage: Maybe<ImageApi>;
    /** Color of the notification */
    rgbColor: Scalars['String']['output'];
    /** Message of the notification */
    text: Scalars['String']['output'];
};

/** Represents a notification supposed to be displayed on all pages */
export type NotificationBarImagesArgsApi = {
    sizes?: InputMaybe<Array<Scalars['String']['input']>>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents a notification supposed to be displayed on all pages */
export type NotificationBarMainImageArgsApi = {
    size?: InputMaybe<Scalars['String']['input']>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents store opening hours */
export type OpeningHoursApi = {
    __typename?: 'OpeningHours';
    /** Current day of the week */
    dayOfWeek: Scalars['Int']['output'];
    /** Is store currently open? */
    isOpen: Scalars['Boolean']['output'];
    /** Opening hours for every day of the week (1 for Monday 7 for Sunday) */
    openingHoursOfDays: Array<OpeningHoursOfDayApi>;
};

export type OpeningHoursOfDayApi = {
    __typename?: 'OpeningHoursOfDay';
    /** Day of the week */
    dayOfWeek: Scalars['Int']['output'];
    /** First closing time */
    firstClosingTime: Maybe<Scalars['String']['output']>;
    /** First opening time */
    firstOpeningTime: Maybe<Scalars['String']['output']>;
    /** Second closing time */
    secondClosingTime: Maybe<Scalars['String']['output']>;
    /** Second opening time */
    secondOpeningTime: Maybe<Scalars['String']['output']>;
};

export type OrderApi = {
    __typename?: 'Order';
    /** Billing address city name */
    city: Scalars['String']['output'];
    /** The customer’s company name (only when ordered on the company behalf) */
    companyName: Maybe<Scalars['String']['output']>;
    /** The customer’s company identification number (only when ordered on the company behalf) */
    companyNumber: Maybe<Scalars['String']['output']>;
    /** The customer’s company tax number (only when ordered on the company behalf) */
    companyTaxNumber: Maybe<Scalars['String']['output']>;
    /** Billing address country */
    country: CountryApi;
    /** Date and time when the order was created */
    creationDate: Scalars['DateTime']['output'];
    /** City name for delivery */
    deliveryCity: Maybe<Scalars['String']['output']>;
    /** Company name for delivery */
    deliveryCompanyName: Maybe<Scalars['String']['output']>;
    /** Country for delivery */
    deliveryCountry: Maybe<CountryApi>;
    /** First name of the contact person for delivery */
    deliveryFirstName: Maybe<Scalars['String']['output']>;
    /** Last name of the contact person for delivery */
    deliveryLastName: Maybe<Scalars['String']['output']>;
    /** Zip code for delivery */
    deliveryPostcode: Maybe<Scalars['String']['output']>;
    /** Street name for delivery */
    deliveryStreet: Maybe<Scalars['String']['output']>;
    /** Contact telephone number for delivery */
    deliveryTelephone: Maybe<Scalars['String']['output']>;
    /** Indicates whether the billing address is other than a delivery address */
    differentDeliveryAddress: Scalars['Boolean']['output'];
    /** The customer's email address */
    email: Scalars['String']['output'];
    /** The customer's first name */
    firstName: Maybe<Scalars['String']['output']>;
    /** All items in the order including payment and transport */
    items: Array<OrderItemApi>;
    /** The customer's last name */
    lastName: Maybe<Scalars['String']['output']>;
    /** Other information related to the order */
    note: Maybe<Scalars['String']['output']>;
    /** Unique order number */
    number: Scalars['String']['output'];
    /** Payment method applied to the order */
    payment: PaymentApi;
    /** Selected pickup place identifier */
    pickupPlaceIdentifier: Maybe<Scalars['String']['output']>;
    /** Billing address zip code */
    postcode: Scalars['String']['output'];
    /** All product items in the order */
    productItems: Array<OrderItemApi>;
    /** Promo code (coupon) used in the order */
    promoCode: Maybe<Scalars['String']['output']>;
    /** Current status of the order */
    status: Scalars['String']['output'];
    /** Billing address street name  */
    street: Scalars['String']['output'];
    /** The customer's telephone number */
    telephone: Scalars['String']['output'];
    /** Total price of the order including transport and payment prices */
    totalPrice: PriceApi;
    /** The order tracking number */
    trackingNumber: Maybe<Scalars['String']['output']>;
    /** The order tracking link */
    trackingUrl: Maybe<Scalars['String']['output']>;
    /** Transport method applied to the order */
    transport: TransportApi;
    /** Unique url hash that can be used to  */
    urlHash: Scalars['String']['output'];
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

/** A connection to a list of items. */
export type OrderConnectionApi = {
    __typename?: 'OrderConnection';
    /** Information to aid in pagination. */
    edges: Maybe<Array<Maybe<OrderEdgeApi>>>;
    /** Information to aid in pagination. */
    pageInfo: PageInfoApi;
    /** Total number of orders */
    totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type OrderEdgeApi = {
    __typename?: 'OrderEdge';
    /** A cursor for use in pagination. */
    cursor: Scalars['String']['output'];
    /** The item at the end of the edge. */
    node: Maybe<OrderApi>;
};

/** Represents the main input object to create orders */
export type OrderInputApi = {
    /** Cart identifier used for getting carts of not logged customers */
    cartUuid: InputMaybe<Scalars['Uuid']['input']>;
    /** Billing address city name (will be on the tax invoice) */
    city: Scalars['String']['input'];
    /** The customer’s company name (required when onCompanyBehalf is true) */
    companyName: InputMaybe<Scalars['String']['input']>;
    /** The customer’s company identification number (required when onCompanyBehalf is true) */
    companyNumber: InputMaybe<Scalars['String']['input']>;
    /** The customer’s company tax number (required when onCompanyBehalf is true) */
    companyTaxNumber: InputMaybe<Scalars['String']['input']>;
    /** Billing address country code in ISO 3166-1 alpha-2 (Country will be on the tax invoice) */
    country: Scalars['String']['input'];
    /** Delivery address identifier */
    deliveryAddressUuid: InputMaybe<Scalars['Uuid']['input']>;
    /** City name for delivery (required when differentDeliveryAddress is true) */
    deliveryCity: InputMaybe<Scalars['String']['input']>;
    /** Company name for delivery */
    deliveryCompanyName: InputMaybe<Scalars['String']['input']>;
    /** Country code in ISO 3166-1 alpha-2 for delivery (required when differentDeliveryAddress is true) */
    deliveryCountry: InputMaybe<Scalars['String']['input']>;
    /** First name of the contact person for delivery (required when differentDeliveryAddress is true) */
    deliveryFirstName: InputMaybe<Scalars['String']['input']>;
    /** Last name of the contact person for delivery (required when differentDeliveryAddress is true) */
    deliveryLastName: InputMaybe<Scalars['String']['input']>;
    /** Zip code for delivery (required when differentDeliveryAddress is true) */
    deliveryPostcode: InputMaybe<Scalars['String']['input']>;
    /** Street name for delivery (required when differentDeliveryAddress is true) */
    deliveryStreet: InputMaybe<Scalars['String']['input']>;
    /** Contact telephone number for delivery */
    deliveryTelephone: InputMaybe<Scalars['String']['input']>;
    /** Determines whether to deliver products to a different address than the billing one */
    differentDeliveryAddress: Scalars['Boolean']['input'];
    /** The customer's email address */
    email: Scalars['String']['input'];
    /** The customer's first name */
    firstName: Scalars['String']['input'];
    /** The customer's last name */
    lastName: Scalars['String']['input'];
    /** Allows user to subscribe/unsubscribe newsletter. */
    newsletterSubscription: InputMaybe<Scalars['Boolean']['input']>;
    /** Other information related to the order */
    note: InputMaybe<Scalars['String']['input']>;
    /** Determines whether the order is made on the company behalf. */
    onCompanyBehalf: Scalars['Boolean']['input'];
    /** Deprecated, this field is not used, the payment is taken from the server cart instead. */
    payment: InputMaybe<PaymentInputApi>;
    /** Billing address zip code (will be on the tax invoice) */
    postcode: Scalars['String']['input'];
    /** Deprecated, this field is not used, the products are taken from the server cart instead. */
    products: InputMaybe<Array<OrderProductInputApi>>;
    /** Billing address street name (will be on the tax invoice) */
    street: Scalars['String']['input'];
    /** The customer's phone number */
    telephone: Scalars['String']['input'];
    /** Deprecated, this field is not used, the transport is taken from the server cart instead. */
    transport: InputMaybe<TransportInputApi>;
};

/** Represent one item in the order */
export type OrderItemApi = {
    __typename?: 'OrderItem';
    /** Name of the order item */
    name: Scalars['String']['output'];
    /** Quantity of order items in the order */
    quantity: Scalars['Int']['output'];
    /** Total price for the quantity of order item */
    totalPrice: PriceApi;
    /** Unit of measurement used for the order item */
    unit: Maybe<Scalars['String']['output']>;
    /** Order item price per unit */
    unitPrice: PriceApi;
    /** Applied VAT rate percentage applied to the order item */
    vatRate: Scalars['String']['output'];
};

/** Represents a product in order */
export type OrderProductInputApi = {
    /** Quantity of products */
    quantity: Scalars['Int']['input'];
    /** Product price per unit */
    unitPrice: PriceInputApi;
    /** UUID */
    uuid: Scalars['Uuid']['input'];
};

/** Information about pagination in a connection. */
export type PageInfoApi = {
    __typename?: 'PageInfo';
    /** When paginating forwards, the cursor to continue. */
    endCursor: Maybe<Scalars['String']['output']>;
    /** When paginating forwards, are there more items? */
    hasNextPage: Scalars['Boolean']['output'];
    /** When paginating backwards, are there more items? */
    hasPreviousPage: Scalars['Boolean']['output'];
    /** When paginating backwards, the cursor to continue. */
    startCursor: Maybe<Scalars['String']['output']>;
};

/** Represents a parameter */
export type ParameterApi = {
    __typename?: 'Parameter';
    /** Parameter group to which the parameter is assigned */
    group: Maybe<Scalars['String']['output']>;
    /** Parameter name */
    name: Scalars['String']['output'];
    /** Unit of the parameter */
    unit: Maybe<UnitApi>;
    /** UUID */
    uuid: Scalars['Uuid']['output'];
    values: Array<ParameterValueApi>;
    visible: Scalars['Boolean']['output'];
};

/** Parameter filter option */
export type ParameterCheckboxFilterOptionApi = ParameterFilterOptionInterfaceApi & {
    __typename?: 'ParameterCheckboxFilterOption';
    /** Indicator whether the parameter should be collapsed based on the current category setting */
    isCollapsed: Scalars['Boolean']['output'];
    /** The parameter name */
    name: Scalars['String']['output'];
    /** The parameter unit */
    unit: Maybe<UnitApi>;
    /** The parameter UUID */
    uuid: Scalars['Uuid']['output'];
    /** Filter options of parameter values */
    values: Array<ParameterValueFilterOptionApi>;
};

/** Parameter filter option */
export type ParameterColorFilterOptionApi = ParameterFilterOptionInterfaceApi & {
    __typename?: 'ParameterColorFilterOption';
    /** Indicator whether the parameter should be collapsed based on the current category setting */
    isCollapsed: Scalars['Boolean']['output'];
    /** The parameter name */
    name: Scalars['String']['output'];
    /** The parameter unit */
    unit: Maybe<UnitApi>;
    /** The parameter UUID */
    uuid: Scalars['Uuid']['output'];
    /** Filter options of parameter values */
    values: Array<ParameterValueColorFilterOptionApi>;
};

/** Represents a parameter filter */
export type ParameterFilterApi = {
    /** The parameter maximal value (for parameters with "slider" type) */
    maximalValue: InputMaybe<Scalars['Float']['input']>;
    /** The parameter minimal value (for parameters with "slider" type) */
    minimalValue: InputMaybe<Scalars['Float']['input']>;
    /** Uuid of filtered parameter */
    parameter: Scalars['Uuid']['input'];
    /** Array of uuids representing parameter values to be filtered by */
    values: Array<Scalars['Uuid']['input']>;
};

/** Represents parameter filter option */
export type ParameterFilterOptionInterfaceApi = {
    /** Indicator whether the parameter should be collapsed based on the current category setting */
    isCollapsed: Scalars['Boolean']['output'];
    /** The parameter name */
    name: Scalars['String']['output'];
    /** The parameter unit */
    unit: Maybe<UnitApi>;
    /** The parameter UUID */
    uuid: Scalars['Uuid']['output'];
};

/** Parameter filter option */
export type ParameterSliderFilterOptionApi = ParameterFilterOptionInterfaceApi & {
    __typename?: 'ParameterSliderFilterOption';
    /** Indicator whether the parameter should be collapsed based on the current category setting */
    isCollapsed: Scalars['Boolean']['output'];
    /** Can be used in filter */
    isSelectable: Scalars['Boolean']['output'];
    /** The parameter maximal value */
    maximalValue: Scalars['Float']['output'];
    /** The parameter minimal value */
    minimalValue: Scalars['Float']['output'];
    /** The parameter name */
    name: Scalars['String']['output'];
    /** The pre-selected value (used for "ready category seo mixes") */
    selectedValue: Maybe<Scalars['Float']['output']>;
    /** The parameter unit */
    unit: Maybe<UnitApi>;
    /** The parameter UUID */
    uuid: Scalars['Uuid']['output'];
};

/** Represents a parameter value */
export type ParameterValueApi = {
    __typename?: 'ParameterValue';
    /** Parameter value */
    text: Scalars['String']['output'];
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

/** Parameter value filter option */
export type ParameterValueColorFilterOptionApi = {
    __typename?: 'ParameterValueColorFilterOption';
    /** Count of products that will be filtered if this filter option is applied. */
    count: Scalars['Int']['output'];
    /**
     * If true than count parameter is number of products that will be displayed if
     * this filter option is applied, if false count parameter is number of products
     * that will be added to current products result.
     */
    isAbsolute: Scalars['Boolean']['output'];
    /** Indicator whether the option is already selected (used for "ready category seo mixes") */
    isSelected: Scalars['Boolean']['output'];
    /** RGB hex of color parameter */
    rgbHex: Maybe<Scalars['String']['output']>;
    /** Parameter value */
    text: Scalars['String']['output'];
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

/** Parameter value filter option */
export type ParameterValueFilterOptionApi = {
    __typename?: 'ParameterValueFilterOption';
    /** Count of products that will be filtered if this filter option is applied. */
    count: Scalars['Int']['output'];
    /**
     * If true than count parameter is number of products that will be displayed if
     * this filter option is applied, if false count parameter is number of products
     * that will be added to current products result.
     */
    isAbsolute: Scalars['Boolean']['output'];
    /** Indicator whether the option is already selected (used for "ready category seo mixes") */
    isSelected: Scalars['Boolean']['output'];
    /** Parameter value */
    text: Scalars['String']['output'];
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

/** Represents a payment */
export type PaymentApi = {
    __typename?: 'Payment';
    /** Localized payment description (domain dependent) */
    description: Maybe<Scalars['String']['output']>;
    /** Additional data for GoPay payment */
    goPayPaymentMethod: Maybe<GoPayPaymentMethodApi>;
    /** Payment images */
    images: Array<ImageApi>;
    /** Localized payment instruction (domain dependent) */
    instruction: Maybe<Scalars['String']['output']>;
    /** Payment image by params */
    mainImage: Maybe<ImageApi>;
    /** Payment name */
    name: Scalars['String']['output'];
    /** Payment position */
    position: Scalars['Int']['output'];
    /** Payment price */
    price: PriceApi;
    /** List of assigned transports */
    transports: Array<TransportApi>;
    /** Type of payment */
    type: Scalars['String']['output'];
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

/** Represents a payment */
export type PaymentImagesArgsApi = {
    size: InputMaybe<Scalars['String']['input']>;
    sizes?: InputMaybe<Array<Scalars['String']['input']>>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents a payment */
export type PaymentMainImageArgsApi = {
    size?: InputMaybe<Scalars['String']['input']>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents a payment */
export type PaymentPriceArgsApi = {
    cartUuid?: InputMaybe<Scalars['Uuid']['input']>;
};

/** Represents a payment in order */
export type PaymentInputApi = {
    /** Price for payment */
    price: PriceInputApi;
    /** UUID */
    uuid: Scalars['Uuid']['input'];
};

export type PaymentSetupCreationDataApi = {
    __typename?: 'PaymentSetupCreationData';
    /** Identifiers of GoPay payment method */
    goPayCreatePaymentSetup: Maybe<GoPayCreatePaymentSetupApi>;
};

export type PersonalDataApi = {
    __typename?: 'PersonalData';
    /** Customer user data */
    customerUser: Maybe<CustomerUserApi>;
    /** A link for downloading the personal data in an XML file */
    exportLink: Scalars['String']['output'];
    /** Newsletter subscription */
    newsletterSubscriber: Maybe<NewsletterSubscriberApi>;
    /** Customer orders */
    orders: Array<OrderApi>;
};

export type PersonalDataAccessRequestInputApi = {
    /** The customer's email address */
    email: Scalars['String']['input'];
    /** One of two possible types for personal data access request - display or export */
    type: InputMaybe<PersonalDataAccessRequestTypeEnumApi>;
};

/** One of two possible types for personal data access request */
export enum PersonalDataAccessRequestTypeEnumApi {
    /** Display data */
    DisplayApi = 'display',
    /** Export data */
    ExportApi = 'export',
}

export type PersonalDataPageApi = {
    __typename?: 'PersonalDataPage';
    /** The HTML content of the site where a customer can request displaying his personal data */
    displaySiteContent: Scalars['String']['output'];
    /** URL slug of display site */
    displaySiteSlug: Scalars['String']['output'];
    /** The HTML content of the site where a customer can request exporting his personal data */
    exportSiteContent: Scalars['String']['output'];
    /** URL slug of export site */
    exportSiteSlug: Scalars['String']['output'];
};

/** Represents the price */
export type PriceApi = PriceInterfaceApi & {
    __typename?: 'Price';
    /** Price with VAT */
    priceWithVat: Scalars['Money']['output'];
    /** Price without VAT */
    priceWithoutVat: Scalars['Money']['output'];
    /** Total value of VAT */
    vatAmount: Scalars['Money']['output'];
};

/** Represents the price */
export type PriceInputApi = {
    /** Price with VAT */
    priceWithVat: Scalars['Money']['input'];
    /** Price without VAT */
    priceWithoutVat: Scalars['Money']['input'];
    /** Total value of VAT */
    vatAmount: Scalars['Money']['input'];
};

/** Represents the price */
export type PriceInterfaceApi = {
    /** Price with VAT */
    priceWithVat: Scalars['Money']['output'];
    /** Price without VAT */
    priceWithoutVat: Scalars['Money']['output'];
    /** Total value of VAT */
    vatAmount: Scalars['Money']['output'];
};

/** Represents setting of pricing */
export type PricingSettingApi = {
    __typename?: 'PricingSetting';
    /** Code of the default currency used on the current domain */
    defaultCurrencyCode: Scalars['String']['output'];
    /** Minimum number of decimal places for the price on the current domain */
    minimumFractionDigits: Scalars['Int']['output'];
};

/** Represents a product */
export type ProductApi = {
    accessories: Array<ProductApi>;
    availability: AvailabilityApi;
    /** Number of the stores where the product is available */
    availableStoresCount: Scalars['Int']['output'];
    /** Brand of product */
    brand: Maybe<BrandApi>;
    /** Hierarchy of the current element in relation to the structure */
    breadcrumb: Array<LinkApi>;
    /** Product catalog number */
    catalogNumber: Scalars['String']['output'];
    /** List of categories */
    categories: Array<CategoryApi>;
    description: Maybe<Scalars['String']['output']>;
    /** EAN */
    ean: Maybe<Scalars['String']['output']>;
    /** Number of the stores where the product is exposed */
    exposedStoresCount: Scalars['Int']['output'];
    /** List of downloadable files */
    files: Array<FileApi>;
    /** List of flags */
    flags: Array<FlagApi>;
    /** The full name of the product, which consists of a prefix, name, and a suffix */
    fullName: Scalars['String']['output'];
    /** Distinguishes if the product can be pre-ordered */
    hasPreorder: Scalars['Boolean']['output'];
    /** Product id */
    id: Scalars['Int']['output'];
    /** Product images */
    images: Array<ImageApi>;
    isMainVariant: Scalars['Boolean']['output'];
    isSellingDenied: Scalars['Boolean']['output'];
    isUsingStock: Scalars['Boolean']['output'];
    /** Product link */
    link: Scalars['String']['output'];
    /** Product image by params */
    mainImage: Maybe<ImageApi>;
    /** Localized product name (domain dependent) */
    name: Scalars['String']['output'];
    /** Name prefix */
    namePrefix: Maybe<Scalars['String']['output']>;
    /** Name suffix */
    nameSuffix: Maybe<Scalars['String']['output']>;
    orderingPriority: Scalars['Int']['output'];
    parameters: Array<ParameterApi>;
    /** Product part number */
    partNumber: Maybe<Scalars['String']['output']>;
    /** Product price */
    price: ProductPriceApi;
    productVideos: Array<VideoTokenApi>;
    /** List of related products */
    relatedProducts: Array<ProductApi>;
    /** Seo first level heading of product */
    seoH1: Maybe<Scalars['String']['output']>;
    /** Seo meta description of product */
    seoMetaDescription: Maybe<Scalars['String']['output']>;
    /** Seo title of product */
    seoTitle: Maybe<Scalars['String']['output']>;
    /** Localized product short description (domain dependent) */
    shortDescription: Maybe<Scalars['String']['output']>;
    /** Product URL slug */
    slug: Scalars['String']['output'];
    /** Count of quantity on stock */
    stockQuantity: Scalars['Int']['output'];
    /** List of availabilities in individual stores */
    storeAvailabilities: Array<StoreAvailabilityApi>;
    unit: UnitApi;
    /** List of product's unique selling propositions */
    usps: Array<Scalars['String']['output']>;
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

/** Represents a product */
export type ProductImagesArgsApi = {
    size: InputMaybe<Scalars['String']['input']>;
    sizes?: InputMaybe<Array<Scalars['String']['input']>>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents a product */
export type ProductMainImageArgsApi = {
    size?: InputMaybe<Scalars['String']['input']>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** A connection to a list of items. */
export type ProductConnectionApi = {
    __typename?: 'ProductConnection';
    /**
     * The default ordering mode that is set for the given connection (e.g. in a
     * category, search page, or ready category SEO mix)
     */
    defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>;
    /** Information to aid in pagination. */
    edges: Maybe<Array<Maybe<ProductEdgeApi>>>;
    /** The current ordering mode */
    orderingMode: ProductOrderingModeEnumApi;
    /** Information to aid in pagination. */
    pageInfo: PageInfoApi;
    productFilterOptions: ProductFilterOptionsApi;
    /** Total number of products */
    totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type ProductEdgeApi = {
    __typename?: 'ProductEdge';
    /** A cursor for use in pagination. */
    cursor: Scalars['String']['output'];
    /** The item at the end of the edge. */
    node: Maybe<ProductApi>;
};

/** Represents a product filter */
export type ProductFilterApi = {
    /** Array of uuids of brands filter */
    brands: InputMaybe<Array<Scalars['Uuid']['input']>>;
    /** Array of uuids of flags filter */
    flags: InputMaybe<Array<Scalars['Uuid']['input']>>;
    /** Maximal price filter */
    maximalPrice: InputMaybe<Scalars['Money']['input']>;
    /** Minimal price filter */
    minimalPrice: InputMaybe<Scalars['Money']['input']>;
    /** Only in stock filter */
    onlyInStock: InputMaybe<Scalars['Boolean']['input']>;
    /** Parameter filter */
    parameters: InputMaybe<Array<ParameterFilterApi>>;
};

/** Represents a product filter options */
export type ProductFilterOptionsApi = {
    __typename?: 'ProductFilterOptions';
    /** Brands filter options */
    brands: Maybe<Array<BrandFilterOptionApi>>;
    /** Flags filter options */
    flags: Maybe<Array<FlagFilterOptionApi>>;
    /** Number of products in stock that will be filtered */
    inStock: Scalars['Int']['output'];
    /** Maximal price of products for filtering */
    maximalPrice: Scalars['Money']['output'];
    /** Minimal price of products for filtering */
    minimalPrice: Scalars['Money']['output'];
    /** Parameter filter options */
    parameters: Maybe<Array<ParameterFilterOptionInterfaceApi>>;
};

/** Paginated and ordered products */
export type ProductListableApi = {
    /** Paginated and ordered products */
    products: ProductConnectionApi;
};

/** Paginated and ordered products */
export type ProductListableProductsArgsApi = {
    after: InputMaybe<Scalars['String']['input']>;
    before: InputMaybe<Scalars['String']['input']>;
    brandSlug: InputMaybe<Scalars['String']['input']>;
    categorySlug: InputMaybe<Scalars['String']['input']>;
    filter: InputMaybe<ProductFilterApi>;
    first: InputMaybe<Scalars['Int']['input']>;
    flagSlug: InputMaybe<Scalars['String']['input']>;
    last: InputMaybe<Scalars['Int']['input']>;
    orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
    search: InputMaybe<Scalars['String']['input']>;
};

/** One of possible ordering modes for product */
export enum ProductOrderingModeEnumApi {
    /** Order by name ascending */
    NameAscApi = 'NAME_ASC',
    /** Order by name descending */
    NameDescApi = 'NAME_DESC',
    /** Order by price ascending */
    PriceAscApi = 'PRICE_ASC',
    /** Order by price descending */
    PriceDescApi = 'PRICE_DESC',
    /** Order by priority */
    PriorityApi = 'PRIORITY',
    /** Order by relevance */
    RelevanceApi = 'RELEVANCE',
}

/** Represents the price of the product */
export type ProductPriceApi = PriceInterfaceApi & {
    __typename?: 'ProductPrice';
    /** Determines whether it's a final price or starting price */
    isPriceFrom: Scalars['Boolean']['output'];
    /** Price with VAT */
    priceWithVat: Scalars['Money']['output'];
    /** Price without VAT */
    priceWithoutVat: Scalars['Money']['output'];
    /** Total value of VAT */
    vatAmount: Scalars['Money']['output'];
};

export type QueryApi = {
    __typename?: 'Query';
    AdvertCode: Maybe<AdvertCodeApi>;
    AdvertImage: Maybe<AdvertImageApi>;
    ArticleLink: Maybe<ArticleLinkApi>;
    ArticleSite: Maybe<ArticleSiteApi>;
    CompanyCustomerUser: Maybe<CompanyCustomerUserApi>;
    /** List of available banks for GoPay bank transfer payment */
    GoPaySwifts: Array<GoPayBankSwiftApi>;
    MainVariant: Maybe<MainVariantApi>;
    ParameterCheckboxFilterOption: Maybe<ParameterCheckboxFilterOptionApi>;
    ParameterColorFilterOption: Maybe<ParameterColorFilterOptionApi>;
    ParameterSliderFilterOption: Maybe<ParameterSliderFilterOptionApi>;
    RegularCustomerUser: Maybe<RegularCustomerUserApi>;
    RegularProduct: Maybe<RegularProductApi>;
    Variant: Maybe<VariantApi>;
    /** Access personal data using hash received in email from personal data access request */
    accessPersonalData: PersonalDataApi;
    /** Returns list of advert positions. */
    advertPositions: Array<AdvertPositionApi>;
    /** Returns list of adverts, optionally filtered by `positionName` */
    adverts: Array<AdvertApi>;
    /** Returns article filtered using UUID or URL slug */
    article: Maybe<NotBlogArticleInterfaceApi>;
    /**
     * Returns list of articles that can be paginated using `first`, `last`, `before`
     * and `after` keywords and filtered by `placement`
     */
    articles: ArticleConnectionApi;
    /** Returns list of searched articles and blog articles */
    articlesSearch: Array<ArticleInterfaceApi>;
    /** Returns blog article filtered using UUID or URL slug */
    blogArticle: Maybe<BlogArticleApi>;
    /** Returns a list of the blog articles that can be paginated using `first`, `last`, `before` and `after` keywords */
    blogArticles: BlogArticleConnectionApi;
    /** Returns a complete list of the blog categories */
    blogCategories: Array<BlogCategoryApi>;
    /** Returns blog category filtered using UUID or URL slug */
    blogCategory: Maybe<BlogCategoryApi>;
    /** Returns brand filtered using UUID or URL slug */
    brand: Maybe<BrandApi>;
    /** Returns list of searched brands */
    brandSearch: Array<BrandApi>;
    /** Returns complete list of brands */
    brands: Array<BrandApi>;
    /** Return cart of logged customer or cart by UUID for anonymous user */
    cart: Maybe<CartApi>;
    /** Returns complete list of categories */
    categories: Array<CategoryApi>;
    /** Returns list of searched categories that can be paginated using `first`, `last`, `before` and `after` keywords */
    categoriesSearch: CategoryConnectionApi;
    /** Returns category filtered using UUID or URL slug */
    category: Maybe<CategoryApi>;
    /** Get comparison by UUID or comparison of logged customer user. */
    comparison: Maybe<ComparisonApi>;
    /** Returns information about cookies article */
    cookiesArticle: Maybe<ArticleSiteApi>;
    /** Returns available countries */
    countries: Array<CountryApi>;
    /** Returns currently logged in customer user */
    currentCustomerUser: Maybe<CustomerUserApi>;
    /** Returns a flag by uuid or url slug */
    flag: Maybe<FlagApi>;
    /** Returns a complete list of the flags */
    flags: Maybe<Array<FlagApi>>;
    /** Check if email is registered */
    isCustomerUserRegistered: Scalars['Boolean']['output'];
    /** Return user translated language constants for current domain locale */
    languageConstants: Array<LanguageConstantApi>;
    /** Returns last order of the user or null if no order was placed yet */
    lastOrder: Maybe<OrderApi>;
    /** Returns complete navigation menu */
    navigation: Array<NavigationItemApi>;
    /** Returns a list of notifications supposed to be displayed on all pages */
    notificationBars: Maybe<Array<NotificationBarApi>>;
    /** Returns order filtered using UUID, orderNumber, or urlHash */
    order: Maybe<OrderApi>;
    /** Returns HTML content for order sent page. */
    orderSentPageContent: Scalars['String']['output'];
    /** Returns list of orders that can be paginated using `first`, `last`, `before` and `after` keywords */
    orders: Maybe<OrderConnectionApi>;
    /** Returns payment filtered using UUID */
    payment: Maybe<PaymentApi>;
    /** Returns complete list of payment methods */
    payments: Array<PaymentApi>;
    /** Return personal data page content and URL */
    personalDataPage: Maybe<PersonalDataPageApi>;
    /** Returns privacy policy article */
    privacyPolicyArticle: Maybe<ArticleSiteApi>;
    /** Returns product filtered using UUID or URL slug */
    product: Maybe<ProductApi>;
    /** Returns list of ordered products that can be paginated using `first`, `last`, `before` and `after` keywords */
    products: ProductConnectionApi;
    /** Returns list of products by catalog numbers */
    productsByCatnums: Array<ProductApi>;
    /** Returns promoted categories */
    promotedCategories: Array<CategoryApi>;
    /** Returns promoted products */
    promotedProducts: Array<ProductApi>;
    /** Returns SEO settings for a specific page based on the url slug of that page */
    seoPage: Maybe<SeoPageApi>;
    /** Returns current setting */
    settings: Maybe<SettingsApi>;
    /** Returns a complete list of the slider items */
    sliderItems: Array<SliderItemApi>;
    /** Returns entity by slug */
    slug: Maybe<SlugApi>;
    /** Returns store filtered using UUID or URL slug */
    store: Maybe<StoreApi>;
    /** Returns list of stores that can be paginated using `first`, `last`, `before` and `after` keywords */
    stores: StoreConnectionApi;
    /** Returns Terms and Conditions article */
    termsAndConditionsArticle: Maybe<ArticleSiteApi>;
    /** Returns complete list of transport methods */
    transport: Maybe<TransportApi>;
    /** Returns available transport methods based on the current cart state */
    transports: Array<TransportApi>;
    /** Get wishlist by uuid or if customer is logged, try find for logged customer. */
    wishlist: Maybe<WishlistApi>;
};

export type QueryGoPaySwiftsArgsApi = {
    currencyCode: Scalars['String']['input'];
};

export type QueryAccessPersonalDataArgsApi = {
    hash: Scalars['String']['input'];
};

export type QueryAdvertsArgsApi = {
    categoryUuid: InputMaybe<Scalars['Uuid']['input']>;
    positionName: InputMaybe<Scalars['String']['input']>;
};

export type QueryArticleArgsApi = {
    urlSlug: InputMaybe<Scalars['String']['input']>;
    uuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type QueryArticlesArgsApi = {
    after: InputMaybe<Scalars['String']['input']>;
    before: InputMaybe<Scalars['String']['input']>;
    first: InputMaybe<Scalars['Int']['input']>;
    last: InputMaybe<Scalars['Int']['input']>;
    placement?: InputMaybe<Array<ArticlePlacementTypeEnumApi>>;
};

export type QueryArticlesSearchArgsApi = {
    search: Scalars['String']['input'];
};

export type QueryBlogArticleArgsApi = {
    urlSlug: InputMaybe<Scalars['String']['input']>;
    uuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type QueryBlogArticlesArgsApi = {
    after: InputMaybe<Scalars['String']['input']>;
    before: InputMaybe<Scalars['String']['input']>;
    first: InputMaybe<Scalars['Int']['input']>;
    last: InputMaybe<Scalars['Int']['input']>;
    onlyHomepageArticles?: InputMaybe<Scalars['Boolean']['input']>;
};

export type QueryBlogCategoryArgsApi = {
    urlSlug: InputMaybe<Scalars['String']['input']>;
    uuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type QueryBrandArgsApi = {
    urlSlug: InputMaybe<Scalars['String']['input']>;
    uuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type QueryBrandSearchArgsApi = {
    search: Scalars['String']['input'];
};

export type QueryCartArgsApi = {
    cartInput: InputMaybe<CartInputApi>;
};

export type QueryCategoriesSearchArgsApi = {
    after: InputMaybe<Scalars['String']['input']>;
    before: InputMaybe<Scalars['String']['input']>;
    first: InputMaybe<Scalars['Int']['input']>;
    last: InputMaybe<Scalars['Int']['input']>;
    search: Scalars['String']['input'];
};

export type QueryCategoryArgsApi = {
    filter: InputMaybe<ProductFilterApi>;
    orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
    urlSlug: InputMaybe<Scalars['String']['input']>;
    uuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type QueryComparisonArgsApi = {
    uuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type QueryFlagArgsApi = {
    urlSlug: InputMaybe<Scalars['String']['input']>;
    uuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type QueryIsCustomerUserRegisteredArgsApi = {
    email: Scalars['String']['input'];
};

export type QueryOrderArgsApi = {
    orderNumber: InputMaybe<Scalars['String']['input']>;
    urlHash: InputMaybe<Scalars['String']['input']>;
    uuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type QueryOrderSentPageContentArgsApi = {
    orderUuid: Scalars['Uuid']['input'];
};

export type QueryOrdersArgsApi = {
    after: InputMaybe<Scalars['String']['input']>;
    before: InputMaybe<Scalars['String']['input']>;
    first: InputMaybe<Scalars['Int']['input']>;
    last: InputMaybe<Scalars['Int']['input']>;
};

export type QueryPaymentArgsApi = {
    uuid: Scalars['Uuid']['input'];
};

export type QueryProductArgsApi = {
    urlSlug: InputMaybe<Scalars['String']['input']>;
    uuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type QueryProductsArgsApi = {
    after: InputMaybe<Scalars['String']['input']>;
    before: InputMaybe<Scalars['String']['input']>;
    brandSlug: InputMaybe<Scalars['String']['input']>;
    categorySlug: InputMaybe<Scalars['String']['input']>;
    filter: InputMaybe<ProductFilterApi>;
    first: InputMaybe<Scalars['Int']['input']>;
    flagSlug: InputMaybe<Scalars['String']['input']>;
    last: InputMaybe<Scalars['Int']['input']>;
    orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
    search: InputMaybe<Scalars['String']['input']>;
};

export type QueryProductsByCatnumsArgsApi = {
    catnums: Array<Scalars['String']['input']>;
};

export type QuerySeoPageArgsApi = {
    pageSlug: Scalars['String']['input'];
};

export type QuerySlugArgsApi = {
    slug: Scalars['String']['input'];
};

export type QueryStoreArgsApi = {
    urlSlug: InputMaybe<Scalars['String']['input']>;
    uuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type QueryStoresArgsApi = {
    after: InputMaybe<Scalars['String']['input']>;
    before: InputMaybe<Scalars['String']['input']>;
    first: InputMaybe<Scalars['Int']['input']>;
    last: InputMaybe<Scalars['Int']['input']>;
};

export type QueryTransportArgsApi = {
    uuid: Scalars['Uuid']['input'];
};

export type QueryTransportsArgsApi = {
    cartUuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type QueryWishlistArgsApi = {
    wishlistUuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type RecoverPasswordInputApi = {
    /** Customer user email. */
    email: Scalars['String']['input'];
    /** Hash */
    hash: Scalars['String']['input'];
    /** New customer user password. */
    newPassword: Scalars['Password']['input'];
};

export type RefreshTokenInputApi = {
    /** The refresh token. */
    refreshToken: Scalars['String']['input'];
};

/** Represents the main input object to register customer user */
export type RegistrationDataInputApi = {
    /** Uuid of the cart that should be merged to the cart of the newly registered user */
    cartUuid: InputMaybe<Scalars['Uuid']['input']>;
    /** Billing address city name (will be on the tax invoice) */
    city: Scalars['String']['input'];
    /** Determines whether the customer is a company or not. */
    companyCustomer: InputMaybe<Scalars['Boolean']['input']>;
    /** The customer’s company name (required when companyCustomer is true) */
    companyName: InputMaybe<Scalars['String']['input']>;
    /** The customer’s company identification number (required when companyCustomer is true) */
    companyNumber: InputMaybe<Scalars['String']['input']>;
    /** The customer’s company tax number (required when companyCustomer is true) */
    companyTaxNumber: InputMaybe<Scalars['String']['input']>;
    /** Billing address country code in ISO 3166-1 alpha-2 (Country will be on the tax invoice) */
    country: Scalars['String']['input'];
    /** The customer's email address */
    email: Scalars['String']['input'];
    /** Customer user first name */
    firstName: Scalars['String']['input'];
    /** Customer user last name */
    lastName: Scalars['String']['input'];
    /** Uuid of the last order that should be paired with the newly registered user */
    lastOrderUuid: InputMaybe<Scalars['Uuid']['input']>;
    /** Whether customer user should receive newsletters or not */
    newsletterSubscription: Scalars['Boolean']['input'];
    /** Customer user password */
    password: Scalars['Password']['input'];
    /** Billing address zip code (will be on the tax invoice) */
    postcode: Scalars['String']['input'];
    /** Billing address street name (will be on the tax invoice) */
    street: Scalars['String']['input'];
    /** The customer's telephone number */
    telephone: Scalars['String']['input'];
};

/** Represents an currently logged customer user */
export type RegularCustomerUserApi = CustomerUserApi & {
    __typename?: 'RegularCustomerUser';
    /** Billing address city name */
    city: Scalars['String']['output'];
    /** Billing address country */
    country: CountryApi;
    /** Default customer delivery addresses */
    defaultDeliveryAddress: Maybe<DeliveryAddressApi>;
    /** List of delivery addresses */
    deliveryAddresses: Array<DeliveryAddressApi>;
    /** Email address */
    email: Scalars['String']['output'];
    /** First name */
    firstName: Scalars['String']['output'];
    /** Last name */
    lastName: Scalars['String']['output'];
    /** Whether customer user receives newsletters or not */
    newsletterSubscription: Scalars['Boolean']['output'];
    /** Billing address zip code */
    postcode: Scalars['String']['output'];
    /** The name of the customer pricing group */
    pricingGroup: Scalars['String']['output'];
    /** Billing address street name */
    street: Scalars['String']['output'];
    /** Phone number */
    telephone: Maybe<Scalars['String']['output']>;
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

/** Represents a product */
export type RegularProductApi = BreadcrumbApi &
    ProductApi &
    SlugApi & {
        __typename?: 'RegularProduct';
        accessories: Array<ProductApi>;
        availability: AvailabilityApi;
        /** Number of the stores where the product is available */
        availableStoresCount: Scalars['Int']['output'];
        /** Brand of product */
        brand: Maybe<BrandApi>;
        /** Hierarchy of the current element in relation to the structure */
        breadcrumb: Array<LinkApi>;
        /** Product catalog number */
        catalogNumber: Scalars['String']['output'];
        /** List of categories */
        categories: Array<CategoryApi>;
        description: Maybe<Scalars['String']['output']>;
        /** EAN */
        ean: Maybe<Scalars['String']['output']>;
        /** Number of the stores where the product is exposed */
        exposedStoresCount: Scalars['Int']['output'];
        /** List of downloadable files */
        files: Array<FileApi>;
        /** List of flags */
        flags: Array<FlagApi>;
        /** The full name of the product, which consists of a prefix, name, and a suffix */
        fullName: Scalars['String']['output'];
        /** Distinguishes if the product can be pre-ordered */
        hasPreorder: Scalars['Boolean']['output'];
        /** Product id */
        id: Scalars['Int']['output'];
        /** Product images */
        images: Array<ImageApi>;
        isMainVariant: Scalars['Boolean']['output'];
        isSellingDenied: Scalars['Boolean']['output'];
        isUsingStock: Scalars['Boolean']['output'];
        /** Product link */
        link: Scalars['String']['output'];
        /** Product image by params */
        mainImage: Maybe<ImageApi>;
        /** Localized product name (domain dependent) */
        name: Scalars['String']['output'];
        /** Name prefix */
        namePrefix: Maybe<Scalars['String']['output']>;
        /** Name suffix */
        nameSuffix: Maybe<Scalars['String']['output']>;
        orderingPriority: Scalars['Int']['output'];
        parameters: Array<ParameterApi>;
        /** Product part number */
        partNumber: Maybe<Scalars['String']['output']>;
        /** Product price */
        price: ProductPriceApi;
        productVideos: Array<VideoTokenApi>;
        /** List of related products */
        relatedProducts: Array<ProductApi>;
        /** Seo first level heading of product */
        seoH1: Maybe<Scalars['String']['output']>;
        /** Seo meta description of product */
        seoMetaDescription: Maybe<Scalars['String']['output']>;
        /** Seo title of product */
        seoTitle: Maybe<Scalars['String']['output']>;
        /** Localized product short description (domain dependent) */
        shortDescription: Maybe<Scalars['String']['output']>;
        /** Product URL slug */
        slug: Scalars['String']['output'];
        /** Count of quantity on stock */
        stockQuantity: Scalars['Int']['output'];
        /** List of availabilities in individual stores */
        storeAvailabilities: Array<StoreAvailabilityApi>;
        unit: UnitApi;
        /** List of product's unique selling propositions */
        usps: Array<Scalars['String']['output']>;
        /** UUID */
        uuid: Scalars['Uuid']['output'];
    };

/** Represents a product */
export type RegularProductImagesArgsApi = {
    size: InputMaybe<Scalars['String']['input']>;
    sizes?: InputMaybe<Array<Scalars['String']['input']>>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents a product */
export type RegularProductMainImageArgsApi = {
    size?: InputMaybe<Scalars['String']['input']>;
    type?: InputMaybe<Scalars['String']['input']>;
};

export type RemoveFromCartInputApi = {
    /** Cart item UUID */
    cartItemUuid: Scalars['Uuid']['input'];
    /** Cart identifier, new cart will be created if not provided and customer is not logged in */
    cartUuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type RemovePromoCodeFromCartInputApi = {
    /** Cart identifier or null if customer is logged in */
    cartUuid: InputMaybe<Scalars['Uuid']['input']>;
    /** Promo code to be removed */
    promoCode: Scalars['String']['input'];
};

/** Represents SEO settings for specific page */
export type SeoPageApi = {
    __typename?: 'SeoPage';
    /** Page's canonical link */
    canonicalUrl: Maybe<Scalars['String']['output']>;
    /** Description for meta tag */
    metaDescription: Maybe<Scalars['String']['output']>;
    /** Description for og:description meta tag */
    ogDescription: Maybe<Scalars['String']['output']>;
    /** Image for og image meta tag by params */
    ogImage: Maybe<ImageApi>;
    /** Title for og:title meta tag */
    ogTitle: Maybe<Scalars['String']['output']>;
    /** Document's title that is shown in a browser's title */
    title: Maybe<Scalars['String']['output']>;
};

/** Represents SEO settings for specific page */
export type SeoPageOgImageArgsApi = {
    size?: InputMaybe<Scalars['String']['input']>;
};

/** Represents setting of SEO */
export type SeoSettingApi = {
    __typename?: 'SeoSetting';
    /** Description of the content of a web page */
    metaDescription: Scalars['String']['output'];
    /** Robots.txt's file content */
    robotsTxtContent: Maybe<Scalars['String']['output']>;
    /** Document's title that is shown in a browser's title */
    title: Scalars['String']['output'];
    /** Complement to title */
    titleAddOn: Scalars['String']['output'];
};

/** Represents settings of the current domain */
export type SettingsApi = {
    __typename?: 'Settings';
    /** Main text for contact form */
    contactFormMainText: Scalars['String']['output'];
    /** Settings related to pricing */
    pricing: PricingSettingApi;
    /** Settings related to SEO */
    seo: SeoSettingApi;
};

export type SliderItemApi = {
    __typename?: 'SliderItem';
    /** Text below slider */
    extendedText: Maybe<Scalars['String']['output']>;
    /** Target link of text below slider */
    extendedTextLink: Maybe<Scalars['String']['output']>;
    /** GTM creative */
    gtmCreative: Maybe<Scalars['String']['output']>;
    /** GTM ID */
    gtmId: Scalars['String']['output'];
    /** Slider item images */
    images: Array<ImageApi>;
    /** Target link */
    link: Scalars['String']['output'];
    /** Slider item image by params */
    mainImage: Maybe<ImageApi>;
    /** Slider name */
    name: Scalars['String']['output'];
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

export type SliderItemImagesArgsApi = {
    sizes?: InputMaybe<Array<Scalars['String']['input']>>;
    type?: InputMaybe<Scalars['String']['input']>;
};

export type SliderItemMainImageArgsApi = {
    size?: InputMaybe<Scalars['String']['input']>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents entity retrievable by slug */
export type SlugApi = {
    name: Maybe<Scalars['String']['output']>;
    slug: Scalars['String']['output'];
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

export type StoreApi = BreadcrumbApi &
    SlugApi & {
        __typename?: 'Store';
        /** Hierarchy of the current element in relation to the structure */
        breadcrumb: Array<LinkApi>;
        /** Store address city */
        city: Scalars['String']['output'];
        contactInfo: Maybe<Scalars['String']['output']>;
        /** Store address country */
        country: CountryApi;
        /** Store description */
        description: Maybe<Scalars['String']['output']>;
        /** Store images */
        images: Array<ImageApi>;
        /** Is set as default store */
        isDefault: Scalars['Boolean']['output'];
        /** Store location latitude */
        locationLatitude: Maybe<Scalars['String']['output']>;
        /** Store location longitude */
        locationLongitude: Maybe<Scalars['String']['output']>;
        /** Store name */
        name: Scalars['String']['output'];
        /** Store opening hours */
        openingHours: OpeningHoursApi;
        /** Store address postcode */
        postcode: Scalars['String']['output'];
        /** Store URL slug */
        slug: Scalars['String']['output'];
        specialMessage: Maybe<Scalars['String']['output']>;
        /** Store address street */
        street: Scalars['String']['output'];
        /** UUID */
        uuid: Scalars['Uuid']['output'];
    };

export type StoreImagesArgsApi = {
    sizes?: InputMaybe<Array<Scalars['String']['input']>>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents an availability in an individual store */
export type StoreAvailabilityApi = {
    __typename?: 'StoreAvailability';
    /** Detailed information about availability */
    availabilityInformation: Scalars['String']['output'];
    /** Availability status in a format suitable for usage in the code */
    availabilityStatus: AvailabilityStatusEnumApi;
    /** Is product exposed on this store */
    exposed: Scalars['Boolean']['output'];
    /** Store */
    store: Maybe<StoreApi>;
};

/** A connection to a list of items. */
export type StoreConnectionApi = {
    __typename?: 'StoreConnection';
    /** Information to aid in pagination. */
    edges: Maybe<Array<Maybe<StoreEdgeApi>>>;
    /** Information to aid in pagination. */
    pageInfo: PageInfoApi;
    /** Total number of stores */
    totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type StoreEdgeApi = {
    __typename?: 'StoreEdge';
    /** A cursor for use in pagination. */
    cursor: Scalars['String']['output'];
    /** The item at the end of the edge. */
    node: Maybe<StoreApi>;
};

export type TokenApi = {
    __typename?: 'Token';
    accessToken: Scalars['String']['output'];
    refreshToken: Scalars['String']['output'];
};

/** Represents a transport */
export type TransportApi = {
    __typename?: 'Transport';
    /** Number of days until goods are delivered */
    daysUntilDelivery: Scalars['Int']['output'];
    /** Localized transport description (domain dependent) */
    description: Maybe<Scalars['String']['output']>;
    /** Transport images */
    images: Array<ImageApi>;
    /** Localized transport instruction (domain dependent) */
    instruction: Maybe<Scalars['String']['output']>;
    /** Pointer telling if the transport is of type personal pickup */
    isPersonalPickup: Scalars['Boolean']['output'];
    /** Transport image by params */
    mainImage: Maybe<ImageApi>;
    /** Transport name */
    name: Scalars['String']['output'];
    /** List of assigned payments */
    payments: Array<PaymentApi>;
    /** Transport position */
    position: Scalars['Int']['output'];
    /** Transport price */
    price: PriceApi;
    /** Stores available for personal pickup */
    stores: Maybe<StoreConnectionApi>;
    /** Type of transport */
    transportType: TransportTypeApi;
    /** UUID */
    uuid: Scalars['Uuid']['output'];
};

/** Represents a transport */
export type TransportImagesArgsApi = {
    size: InputMaybe<Scalars['String']['input']>;
    sizes?: InputMaybe<Array<Scalars['String']['input']>>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents a transport */
export type TransportMainImageArgsApi = {
    size?: InputMaybe<Scalars['String']['input']>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents a transport */
export type TransportPriceArgsApi = {
    cartUuid?: InputMaybe<Scalars['Uuid']['input']>;
};

/** Represents a transport in order */
export type TransportInputApi = {
    /** Price for transport */
    price: PriceInputApi;
    /** UUID */
    uuid: Scalars['Uuid']['input'];
};

/** Represents a transport type */
export type TransportTypeApi = {
    __typename?: 'TransportType';
    /** Code of transport */
    code: Scalars['String']['output'];
    /** Name of transport type */
    name: Scalars['String']['output'];
};

/** Represents a unit */
export type UnitApi = {
    __typename?: 'Unit';
    /** Localized unit name (domain dependent) */
    name: Scalars['String']['output'];
};

/** Represents a product */
export type VariantApi = BreadcrumbApi &
    ProductApi &
    SlugApi & {
        __typename?: 'Variant';
        accessories: Array<ProductApi>;
        availability: AvailabilityApi;
        /** Number of the stores where the product is available */
        availableStoresCount: Scalars['Int']['output'];
        /** Brand of product */
        brand: Maybe<BrandApi>;
        /** Hierarchy of the current element in relation to the structure */
        breadcrumb: Array<LinkApi>;
        /** Product catalog number */
        catalogNumber: Scalars['String']['output'];
        /** List of categories */
        categories: Array<CategoryApi>;
        description: Maybe<Scalars['String']['output']>;
        /** EAN */
        ean: Maybe<Scalars['String']['output']>;
        /** Number of the stores where the product is exposed */
        exposedStoresCount: Scalars['Int']['output'];
        /** List of downloadable files */
        files: Array<FileApi>;
        /** List of flags */
        flags: Array<FlagApi>;
        /** The full name of the product, which consists of a prefix, name, and a suffix */
        fullName: Scalars['String']['output'];
        /** Distinguishes if the product can be pre-ordered */
        hasPreorder: Scalars['Boolean']['output'];
        /** Product id */
        id: Scalars['Int']['output'];
        /** Product images */
        images: Array<ImageApi>;
        isMainVariant: Scalars['Boolean']['output'];
        isSellingDenied: Scalars['Boolean']['output'];
        isUsingStock: Scalars['Boolean']['output'];
        /** Product link */
        link: Scalars['String']['output'];
        /** Product image by params */
        mainImage: Maybe<ImageApi>;
        mainVariant: Maybe<MainVariantApi>;
        /** Localized product name (domain dependent) */
        name: Scalars['String']['output'];
        /** Name prefix */
        namePrefix: Maybe<Scalars['String']['output']>;
        /** Name suffix */
        nameSuffix: Maybe<Scalars['String']['output']>;
        orderingPriority: Scalars['Int']['output'];
        parameters: Array<ParameterApi>;
        /** Product part number */
        partNumber: Maybe<Scalars['String']['output']>;
        /** Product price */
        price: ProductPriceApi;
        productVideos: Array<VideoTokenApi>;
        /** List of related products */
        relatedProducts: Array<ProductApi>;
        /** Seo first level heading of product */
        seoH1: Maybe<Scalars['String']['output']>;
        /** Seo meta description of product */
        seoMetaDescription: Maybe<Scalars['String']['output']>;
        /** Seo title of product */
        seoTitle: Maybe<Scalars['String']['output']>;
        /** Localized product short description (domain dependent) */
        shortDescription: Maybe<Scalars['String']['output']>;
        /** Product URL slug */
        slug: Scalars['String']['output'];
        /** Count of quantity on stock */
        stockQuantity: Scalars['Int']['output'];
        /** List of availabilities in individual stores */
        storeAvailabilities: Array<StoreAvailabilityApi>;
        unit: UnitApi;
        /** List of product's unique selling propositions */
        usps: Array<Scalars['String']['output']>;
        /** UUID */
        uuid: Scalars['Uuid']['output'];
    };

/** Represents a product */
export type VariantImagesArgsApi = {
    size: InputMaybe<Scalars['String']['input']>;
    sizes?: InputMaybe<Array<Scalars['String']['input']>>;
    type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents a product */
export type VariantMainImageArgsApi = {
    size?: InputMaybe<Scalars['String']['input']>;
    type?: InputMaybe<Scalars['String']['input']>;
};

export type VideoTokenApi = {
    __typename?: 'VideoToken';
    description: Scalars['String']['output'];
    token: Scalars['String']['output'];
};

export type WishlistApi = {
    __typename?: 'Wishlist';
    /** List of wishlist products */
    products: Array<ProductApi>;
    /** Wishlist identifier */
    uuid: Scalars['Uuid']['output'];
};

export interface PossibleTypesResultData {
    possibleTypes: {
        [key: string]: string[];
    };
}
const result: PossibleTypesResultData = {
    possibleTypes: {
        Advert: ['AdvertCode', 'AdvertImage'],
        ArticleInterface: ['ArticleSite', 'BlogArticle'],
        Breadcrumb: [
            'ArticleSite',
            'BlogArticle',
            'BlogCategory',
            'Brand',
            'Category',
            'Flag',
            'MainVariant',
            'RegularProduct',
            'Store',
            'Variant',
        ],
        CartInterface: ['Cart'],
        CustomerUser: ['CompanyCustomerUser', 'RegularCustomerUser'],
        NotBlogArticleInterface: ['ArticleLink', 'ArticleSite'],
        ParameterFilterOptionInterface: [
            'ParameterCheckboxFilterOption',
            'ParameterColorFilterOption',
            'ParameterSliderFilterOption',
        ],
        PriceInterface: ['Price', 'ProductPrice'],
        Product: ['MainVariant', 'RegularProduct', 'Variant'],
        ProductListable: ['Brand', 'Category', 'Flag'],
        Slug: [
            'ArticleSite',
            'BlogArticle',
            'BlogCategory',
            'Brand',
            'Category',
            'Flag',
            'MainVariant',
            'RegularProduct',
            'Store',
            'Variant',
        ],
    },
};
export default result;
