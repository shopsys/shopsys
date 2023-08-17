export type Maybe<T> = T | null;
export type InputMaybe<T> = Maybe<T>;
export type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
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

export type ApplyPromoCodeToCartInputApi = {
    /** Cart identifier or null if customer is logged in */
    cartUuid: InputMaybe<Scalars['Uuid']['input']>;
    /** Promo code to be used after checkout */
    promoCode: Scalars['String']['input'];
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

export type CategoryHierarchyItemApi = {
    __typename?: 'CategoryHierarchyItem';
    /** Localized category name (domain dependent) */
    name: Scalars['String']['output'];
    /** UUID */
    uuid: Scalars['Uuid']['output'];
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

/** Represents an internal link */
export type LinkApi = {
    __typename?: 'Link';
    /** Clickable text for a hyperlink */
    name: Scalars['String']['output'];
    /** Target URL slug */
    slug: Scalars['String']['output'];
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

/** One of two possible types for personal data access request */
export enum PersonalDataAccessRequestTypeEnumApi {
    /** Display data */
    DisplayApi = 'display',
    /** Export data */
    ExportApi = 'export',
}

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
export type PriceInterfaceApi = {
    /** Price with VAT */
    priceWithVat: Scalars['Money']['output'];
    /** Price without VAT */
    priceWithoutVat: Scalars['Money']['output'];
    /** Total value of VAT */
    vatAmount: Scalars['Money']['output'];
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

/** Represents the main input object to register customer user */

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

export type VideoTokenApi = {
    __typename?: 'VideoToken';
    description: Scalars['String']['output'];
    token: Scalars['String']['output'];
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
