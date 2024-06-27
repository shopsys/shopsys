export type Maybe<T> = T | null;
export type InputMaybe<T> = Maybe<T>;
export type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
export type MakeOptional<T, K extends keyof T> = Omit<T, K> & { [SubKey in K]?: Maybe<T[SubKey]> };
export type MakeMaybe<T, K extends keyof T> = Omit<T, K> & { [SubKey in K]: Maybe<T[SubKey]> };
export type MakeEmpty<T extends { [key: string]: unknown }, K extends keyof T> = { [_ in K]?: never };
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
/** All built-in and custom scalars, mapped to their actual values */
export type Scalars = {
  ID: { input: string; output: string; }
  String: { input: string; output: string; }
  Boolean: { input: boolean; output: boolean; }
  Int: { input: number; output: number; }
  Float: { input: number; output: number; }
  /** Represents and encapsulates an ISO-8601 encoded UTC date-time value */
  DateTime: { input: any; output: any; }
  /** Represents and encapsulates monetary value */
  Money: { input: string; output: string; }
  /** Represents and encapsulates a string for password */
  Password: { input: any; output: any; }
  /** Represents and encapsulates an ISO-8601 encoded UTC date-time value */
  Uuid: { input: string; output: string; }
};

export type TypeAddOrderItemsToCartInput = {
  /** Cart identifier or null if customer is logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
  /** UUID of the order based on which the cart should be prefilled */
  orderUuid: Scalars['Uuid']['input'];
  /** Information if the prefilled cart should be merged with the current cart */
  shouldMerge: InputMaybe<Scalars['Boolean']['input']>;
};

export type TypeAddProductResult = {
  __typename?: 'AddProductResult';
  addedQuantity: Scalars['Int']['output'];
  cartItem: TypeCartItem;
  isNew: Scalars['Boolean']['output'];
  notOnStockQuantity: Scalars['Int']['output'];
};

export type TypeAddToCartInput = {
  /** Cart identifier, new cart will be created if not provided and customer is not logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
  /** True if quantity should be set no matter the current state of the cart. False if quantity should be added to the already existing same item in the cart */
  isAbsoluteQuantity: InputMaybe<Scalars['Boolean']['input']>;
  /** Product UUID */
  productUuid: Scalars['Uuid']['input'];
  /** Item quantity */
  quantity: Scalars['Int']['input'];
};

export type TypeAddToCartResult = {
  __typename?: 'AddToCartResult';
  addProductResult: TypeAddProductResult;
  cart: TypeCart;
};

export type TypeAdvert = {
  /** Restricted categories of the advert (the advert is shown in these categories only) */
  categories: Array<TypeCategory>;
  /** Name of advert */
  name: Scalars['String']['output'];
  /** Position of advert */
  positionName: Scalars['String']['output'];
  /** Type of advert */
  type: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};

export type TypeAdvertCode = TypeAdvert & {
  __typename?: 'AdvertCode';
  /** Restricted categories of the advert (the advert is shown in these categories only) */
  categories: Array<TypeCategory>;
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

export type TypeAdvertImage = TypeAdvert & {
  __typename?: 'AdvertImage';
  /** Restricted categories of the advert (the advert is shown in these categories only) */
  categories: Array<TypeCategory>;
  /** Advert images */
  images: Array<TypeImage>;
  /** Advert link */
  link: Maybe<Scalars['String']['output']>;
  /** Adverts first image by params */
  mainImage: Maybe<TypeImage>;
  /** Name of advert */
  name: Scalars['String']['output'];
  /** Position of advert */
  positionName: Scalars['String']['output'];
  /** Type of advert */
  type: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};


export type TypeAdvertImageImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


export type TypeAdvertImageMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

export type TypeAdvertPosition = {
  __typename?: 'AdvertPosition';
  /** Description of advert position */
  description: Scalars['String']['output'];
  /** Position of advert */
  positionName: Scalars['String']['output'];
};

export type TypeApplyPromoCodeToCartInput = {
  /** Cart identifier or null if customer is logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
  /** Promo code to be used after checkout */
  promoCode: Scalars['String']['input'];
};

/** A connection to a list of items. */
export type TypeArticleConnection = {
  __typename?: 'ArticleConnection';
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<TypeArticleEdge>>>;
  /** Information to aid in pagination. */
  pageInfo: TypePageInfo;
  /** Total number of articles */
  totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type TypeArticleEdge = {
  __typename?: 'ArticleEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String']['output'];
  /** The item at the end of the edge. */
  node: Maybe<TypeNotBlogArticleInterface>;
};

/** Represents entity that is considered to be an article on the eshop */
export type TypeArticleInterface = {
  breadcrumb: Array<TypeLink>;
  name: Scalars['String']['output'];
  seoH1: Maybe<Scalars['String']['output']>;
  seoMetaDescription: Maybe<Scalars['String']['output']>;
  seoTitle: Maybe<Scalars['String']['output']>;
  slug: Scalars['String']['output'];
  text: Maybe<Scalars['String']['output']>;
  uuid: Scalars['Uuid']['output'];
};

export type TypeArticleLink = TypeNotBlogArticleInterface & {
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
export enum TypeArticlePlacementTypeEnum {
  /** Articles in 1st footer column */
  Footer1 = 'footer1',
  /** Articles in 2nd footer column */
  Footer2 = 'footer2',
  /** Articles in 3rd footer column */
  Footer3 = 'footer3',
  /** Articles in 4th footer column */
  Footer4 = 'footer4',
  /** Articles without specific placement */
  None = 'none'
}

export type TypeArticleSite = TypeArticleInterface & TypeBreadcrumb & TypeNotBlogArticleInterface & TypeSlug & {
  __typename?: 'ArticleSite';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<TypeLink>;
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
export type TypeAvailability = {
  __typename?: 'Availability';
  /** Localized availability name (domain dependent) */
  name: Scalars['String']['output'];
  /** Availability status in a format suitable for usage in the code */
  status: TypeAvailabilityStatusEnum;
};

/** Product Availability statuses */
export enum TypeAvailabilityStatusEnum {
  /** Product availability status in stock */
  InStock = 'InStock',
  /** Product availability status out of stock */
  OutOfStock = 'OutOfStock'
}

export type TypeBlogArticle = TypeArticleInterface & TypeBreadcrumb & TypeHreflang & TypeSlug & {
  __typename?: 'BlogArticle';
  /** The list of the blog article blog categories */
  blogCategories: Array<TypeBlogCategory>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<TypeLink>;
  /** Date and time of the blog article creation */
  createdAt: Scalars['DateTime']['output'];
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<TypeHreflangLink>;
  /** ID of category */
  id: Scalars['Int']['output'];
  /** Blog article images */
  images: Array<TypeImage>;
  /** The blog article absolute URL */
  link: Scalars['String']['output'];
  /** Blog article image by params */
  mainImage: Maybe<TypeImage>;
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


export type TypeBlogArticleImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


export type TypeBlogArticleMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

/** A connection to a list of items. */
export type TypeBlogArticleConnection = {
  __typename?: 'BlogArticleConnection';
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<TypeBlogArticleEdge>>>;
  /** Information to aid in pagination. */
  pageInfo: TypePageInfo;
  /** Total number of the blog articles */
  totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type TypeBlogArticleEdge = {
  __typename?: 'BlogArticleEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String']['output'];
  /** The item at the end of the edge. */
  node: Maybe<TypeBlogArticle>;
};

export type TypeBlogCategory = TypeBreadcrumb & TypeHreflang & TypeSlug & {
  __typename?: 'BlogCategory';
  /** Total count of blog articles in this category */
  articlesTotalCount: Scalars['Int']['output'];
  /** Paginated blog articles of the given blog category */
  blogArticles: TypeBlogArticleConnection;
  /** Tho whole blog categories tree (used for blog navigation rendering) */
  blogCategoriesTree: Array<TypeBlogCategory>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<TypeLink>;
  /** The blog category children */
  children: Array<TypeBlogCategory>;
  /** The blog category description */
  description: Maybe<Scalars['String']['output']>;
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<TypeHreflangLink>;
  /** The blog category absolute URL */
  link: Scalars['String']['output'];
  /** The blog category name */
  name: Scalars['String']['output'];
  /** The blog category parent */
  parent: Maybe<TypeBlogCategory>;
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


export type TypeBlogCategoryBlogArticlesArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  first: InputMaybe<Scalars['Int']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  onlyHomepageArticles?: InputMaybe<Scalars['Boolean']['input']>;
};

/** Represents a brand */
export type TypeBrand = TypeBreadcrumb & TypeHreflang & TypeProductListable & TypeSlug & {
  __typename?: 'Brand';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<TypeLink>;
  /** Brand description */
  description: Maybe<Scalars['String']['output']>;
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<TypeHreflangLink>;
  /** ID of category */
  id: Scalars['Int']['output'];
  /** Brand images */
  images: Array<TypeImage>;
  /** Brand main URL */
  link: Scalars['String']['output'];
  /** Brand image by params */
  mainImage: Maybe<TypeImage>;
  /** Brand name */
  name: Scalars['String']['output'];
  /** Paginated and ordered products of brand */
  products: TypeProductConnection;
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
export type TypeBrandImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a brand */
export type TypeBrandMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a brand */
export type TypeBrandProductsArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  brandSlug: InputMaybe<Scalars['String']['input']>;
  categorySlug: InputMaybe<Scalars['String']['input']>;
  filter: InputMaybe<TypeProductFilter>;
  first: InputMaybe<Scalars['Int']['input']>;
  flagSlug: InputMaybe<Scalars['String']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  orderingMode: InputMaybe<TypeProductOrderingModeEnum>;
};

/** Brand filter option */
export type TypeBrandFilterOption = {
  __typename?: 'BrandFilterOption';
  /** Brand */
  brand: TypeBrand;
  /** Count of products that will be filtered if this filter option is applied. */
  count: Scalars['Int']['output'];
  /** If true than count parameter is number of products that will be displayed if this filter option is applied, if false count parameter is number of products that will be added to current products result. */
  isAbsolute: Scalars['Boolean']['output'];
};

/** Represents entity able to return breadcrumb */
export type TypeBreadcrumb = {
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<TypeLink>;
};

export type TypeCart = {
  __typename?: 'Cart';
  /** All items in the cart */
  items: Array<TypeCartItem>;
  modifications: TypeCartModificationsResult;
  /** Selected payment if payment provided */
  payment: Maybe<TypePayment>;
  /** Selected bank swift code of goPay payment bank transfer */
  paymentGoPayBankSwift: Maybe<Scalars['String']['output']>;
  /** Applied promo code if provided */
  promoCode: Maybe<Scalars['String']['output']>;
  /** Remaining amount for free transport and payment; null = transport cannot be free */
  remainingAmountWithVatForFreeTransport: Maybe<Scalars['Money']['output']>;
  /** Rounding amount if payment has rounding allowed */
  roundingPrice: Maybe<TypePrice>;
  /** Selected pickup place identifier if provided */
  selectedPickupPlaceIdentifier: Maybe<Scalars['String']['output']>;
  totalDiscountPrice: TypePrice;
  /** Total items price (excluding transport and payment) */
  totalItemsPrice: TypePrice;
  /** Total price including transport and payment */
  totalPrice: TypePrice;
  /** Total price (exluding discount, transport and payment) */
  totalPriceWithoutDiscountTransportAndPayment: TypePrice;
  /** Selected transport if transport provided */
  transport: Maybe<TypeTransport>;
  /** UUID of the cart, null for authenticated user */
  uuid: Maybe<Scalars['Uuid']['output']>;
};

export type TypeCartInput = {
  /** Cart identifier, new cart will be created if not provided and customer is not logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
};

/** Represent one item in the cart */
export type TypeCartItem = {
  __typename?: 'CartItem';
  /** Product in the cart */
  product: TypeProduct;
  /** Quantity of items in the cart */
  quantity: Scalars['Int']['output'];
  /** Cart item UUID */
  uuid: Scalars['Uuid']['output'];
};

export type TypeCartItemModificationsResult = {
  __typename?: 'CartItemModificationsResult';
  cartItemsWithChangedQuantity: Array<TypeCartItem>;
  cartItemsWithModifiedPrice: Array<TypeCartItem>;
  noLongerAvailableCartItemsDueToQuantity: Array<TypeCartItem>;
  noLongerListableCartItems: Array<TypeCartItem>;
};

export type TypeCartModificationsResult = {
  __typename?: 'CartModificationsResult';
  itemModifications: TypeCartItemModificationsResult;
  multipleAddedProductModifications: TypeCartMultipleAddedProductModificationsResult;
  paymentModifications: TypeCartPaymentModificationsResult;
  promoCodeModifications: TypeCartPromoCodeModificationsResult;
  someProductWasRemovedFromEshop: Scalars['Boolean']['output'];
  transportModifications: TypeCartTransportModificationsResult;
};

export type TypeCartMultipleAddedProductModificationsResult = {
  __typename?: 'CartMultipleAddedProductModificationsResult';
  notAddedProducts: Array<TypeProduct>;
};

export type TypeCartPaymentModificationsResult = {
  __typename?: 'CartPaymentModificationsResult';
  paymentPriceChanged: Scalars['Boolean']['output'];
  paymentUnavailable: Scalars['Boolean']['output'];
};

export type TypeCartPromoCodeModificationsResult = {
  __typename?: 'CartPromoCodeModificationsResult';
  noLongerApplicablePromoCode: Array<Scalars['String']['output']>;
};

export type TypeCartTransportModificationsResult = {
  __typename?: 'CartTransportModificationsResult';
  personalPickupStoreUnavailable: Scalars['Boolean']['output'];
  transportPriceChanged: Scalars['Boolean']['output'];
  transportUnavailable: Scalars['Boolean']['output'];
  transportWeightLimitExceeded: Scalars['Boolean']['output'];
};

/** Represents a category */
export type TypeCategory = TypeBreadcrumb & TypeProductListable & TypeSlug & {
  __typename?: 'Category';
  /** Best selling products */
  bestsellers: Array<TypeProduct>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<TypeLink>;
  /** All parent category names with their IDs and UUIDs */
  categoryHierarchy: Array<TypeCategoryHierarchyItem>;
  /** Descendant categories */
  children: Array<TypeCategory>;
  /** Localized category description (domain dependent) */
  description: Maybe<Scalars['String']['output']>;
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<TypeHreflangLink>;
  /** ID of category */
  id: Scalars['Int']['output'];
  /** Category images */
  images: Array<TypeImage>;
  /** A list of categories linked to the given category */
  linkedCategories: Array<TypeCategory>;
  /** Category image by params */
  mainImage: Maybe<TypeImage>;
  /** Localized category name (domain dependent) */
  name: Scalars['String']['output'];
  /** Original category URL slug (for CategorySeoMixes slug of assigned category is returned, null is returned for regular category) */
  originalCategorySlug: Maybe<Scalars['String']['output']>;
  /** Ancestor category */
  parent: Maybe<TypeCategory>;
  /** Paginated and ordered products of category */
  products: TypeProductConnection;
  /** An array of links of prepared category SEO mixes of a given category */
  readyCategorySeoMixLinks: Array<TypeLink>;
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
export type TypeCategoryImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a category */
export type TypeCategoryMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a category */
export type TypeCategoryProductsArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  brandSlug: InputMaybe<Scalars['String']['input']>;
  categorySlug: InputMaybe<Scalars['String']['input']>;
  filter: InputMaybe<TypeProductFilter>;
  first: InputMaybe<Scalars['Int']['input']>;
  flagSlug: InputMaybe<Scalars['String']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  orderingMode: InputMaybe<TypeProductOrderingModeEnum>;
};

/** A connection to a list of items. */
export type TypeCategoryConnection = {
  __typename?: 'CategoryConnection';
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<TypeCategoryEdge>>>;
  /** Information to aid in pagination. */
  pageInfo: TypePageInfo;
  /** Total number of categories */
  totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type TypeCategoryEdge = {
  __typename?: 'CategoryEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String']['output'];
  /** The item at the end of the edge. */
  node: Maybe<TypeCategory>;
};

export type TypeCategoryHierarchyItem = {
  __typename?: 'CategoryHierarchyItem';
  /** ID of the category */
  id: Scalars['Int']['output'];
  /** Localized category name (domain dependent) */
  name: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};

export type TypeChangePasswordInput = {
  /** Customer user email. */
  email: Scalars['String']['input'];
  /** New customer user password. */
  newPassword: Scalars['Password']['input'];
  /** Current customer user password. */
  oldPassword: Scalars['Password']['input'];
};

export type TypeChangePaymentInCartInput = {
  /** Cart identifier or null if customer is logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
  /** Selected bank swift code of goPay payment bank transfer */
  paymentGoPayBankSwift: InputMaybe<Scalars['String']['input']>;
  /** UUID of a payment that should be added to the cart. If this is set to null, the payment is removed from the cart */
  paymentUuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type TypeChangePaymentInOrderInput = {
  /** Order identifier */
  orderUuid: Scalars['Uuid']['input'];
  /** Selected bank swift code of goPay payment bank transfer */
  paymentGoPayBankSwift: InputMaybe<Scalars['String']['input']>;
  /** UUID of a payment that should be assigned to the order. */
  paymentUuid: Scalars['Uuid']['input'];
};

export type TypeChangePersonalDataInput = {
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

export type TypeChangeTransportInCartInput = {
  /** Cart identifier or null if customer is logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
  /** The identifier of selected personal pickup place */
  pickupPlaceIdentifier: InputMaybe<Scalars['String']['input']>;
  /** UUID of a transport that should be added to the cart. If this is set to null, the transport is removed from the cart */
  transportUuid: InputMaybe<Scalars['Uuid']['input']>;
};

/** Represents an currently logged customer user */
export type TypeCompanyCustomerUser = TypeCustomerUser & {
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
  country: TypeCountry;
  /** Default customer delivery addresses */
  defaultDeliveryAddress: Maybe<TypeDeliveryAddress>;
  /** List of delivery addresses */
  deliveryAddresses: Array<TypeDeliveryAddress>;
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

export type TypeContactFormInput = {
  /** Email address of the sender */
  email: Scalars['String']['input'];
  /** Message that will be sent to recipient */
  message: Scalars['String']['input'];
  /** Name of the sender */
  name: Scalars['String']['input'];
};

/** Represents country */
export type TypeCountry = {
  __typename?: 'Country';
  /** Country code in ISO 3166-1 alpha-2 */
  code: Scalars['String']['output'];
  /** Localized country name */
  name: Scalars['String']['output'];
};

export type TypeCreateOrderResult = {
  __typename?: 'CreateOrderResult';
  cart: Maybe<TypeCart>;
  order: Maybe<TypeOrder>;
  orderCreated: Scalars['Boolean']['output'];
};

/** Represents an currently logged customer user */
export type TypeCustomerUser = {
  /** Billing address city name */
  city: Scalars['String']['output'];
  /** Billing address country */
  country: TypeCountry;
  /** Default customer delivery addresses */
  defaultDeliveryAddress: Maybe<TypeDeliveryAddress>;
  /** List of delivery addresses */
  deliveryAddresses: Array<TypeDeliveryAddress>;
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

export type TypeDeliveryAddress = {
  __typename?: 'DeliveryAddress';
  /** Delivery address city name */
  city: Maybe<Scalars['String']['output']>;
  /** Delivery address company name */
  companyName: Maybe<Scalars['String']['output']>;
  /** Delivery address country */
  country: Maybe<TypeCountry>;
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

export type TypeDeliveryAddressInput = {
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

/** Represents a flag */
export type TypeFlag = TypeBreadcrumb & TypeHreflang & TypeProductListable & TypeSlug & {
  __typename?: 'Flag';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<TypeLink>;
  /** Categories containing at least one product with flag */
  categories: Array<TypeCategory>;
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<TypeHreflangLink>;
  /** Localized flag name (domain dependent) */
  name: Scalars['String']['output'];
  /** Paginated and ordered products of flag */
  products: TypeProductConnection;
  /** Flag color in rgb format */
  rgbColor: Scalars['String']['output'];
  /** URL slug of flag */
  slug: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};


/** Represents a flag */
export type TypeFlagCategoriesArgs = {
  productFilter: InputMaybe<TypeProductFilter>;
};


/** Represents a flag */
export type TypeFlagProductsArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  brandSlug: InputMaybe<Scalars['String']['input']>;
  categorySlug: InputMaybe<Scalars['String']['input']>;
  filter: InputMaybe<TypeProductFilter>;
  first: InputMaybe<Scalars['Int']['input']>;
  flagSlug: InputMaybe<Scalars['String']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  orderingMode: InputMaybe<TypeProductOrderingModeEnum>;
};

/** Flag filter option */
export type TypeFlagFilterOption = {
  __typename?: 'FlagFilterOption';
  /** Count of products that will be filtered if this filter option is applied. */
  count: Scalars['Int']['output'];
  /** Flag */
  flag: TypeFlag;
  /** If true than count parameter is number of products that will be displayed if this filter option is applied, if false count parameter is number of products that will be added to current products result. */
  isAbsolute: Scalars['Boolean']['output'];
  /** Indicator whether the option is already selected (used for "ready category seo mixes") */
  isSelected: Scalars['Boolean']['output'];
};

export type TypeGoPayBankSwift = {
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

export type TypeGoPayCreatePaymentSetup = {
  __typename?: 'GoPayCreatePaymentSetup';
  /** url of gopay embedJs file */
  embedJs: Scalars['String']['output'];
  /** redirect URL to payment gateway */
  gatewayUrl: Scalars['String']['output'];
  /** payment transaction identifier */
  goPayId: Scalars['String']['output'];
};

export type TypeGoPayPaymentMethod = {
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

/** Represents entity able to return alternate links for hreflang meta tags */
export type TypeHreflang = {
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<TypeHreflangLink>;
};

export type TypeHreflangLink = {
  __typename?: 'HreflangLink';
  /** URL for hreflang meta tag */
  href: Scalars['String']['output'];
  /** Language code for hreflang meta tag */
  hreflang: Scalars['String']['output'];
};

/** Represents an image */
export type TypeImage = {
  __typename?: 'Image';
  /** Name of the image usable as an alternative text */
  name: Maybe<Scalars['String']['output']>;
  /** URL address of the image */
  url: Scalars['String']['output'];
};

/** Represents a single user translation of language constant */
export type TypeLanguageConstant = {
  __typename?: 'LanguageConstant';
  /** Translation key */
  key: Scalars['String']['output'];
  /** User translation */
  translation: Scalars['String']['output'];
};

/** Represents an internal link */
export type TypeLink = {
  __typename?: 'Link';
  /** Clickable text for a hyperlink */
  name: Scalars['String']['output'];
  /** Target URL slug */
  slug: Scalars['String']['output'];
};

export type TypeLoginInput = {
  /** Uuid of the cart that should be merged to the cart of the user */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
  /** The user email. */
  email: Scalars['String']['input'];
  /** The user password. */
  password: Scalars['Password']['input'];
  /** Uuids of product lists that should be merged to the product lists of the user */
  productListsUuids: Array<Scalars['Uuid']['input']>;
  /** A boolean pointer to indicate if the current customer user cart should be overwritten by the cart with cartUuid */
  shouldOverwriteCustomerUserCart: Scalars['Boolean']['input'];
};

export type TypeLoginResult = {
  __typename?: 'LoginResult';
  showCartMergeInfo: Scalars['Boolean']['output'];
  tokens: TypeToken;
};

/** Represents a product */
export type TypeMainVariant = TypeBreadcrumb & TypeHreflang & TypeProduct & TypeSlug & {
  __typename?: 'MainVariant';
  accessories: Array<TypeProduct>;
  availability: TypeAvailability;
  /** Number of the stores where the product is available */
  availableStoresCount: Scalars['Int']['output'];
  /** Brand of product */
  brand: Maybe<TypeBrand>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<TypeLink>;
  /** Product catalog number */
  catalogNumber: Scalars['String']['output'];
  /** List of categories */
  categories: Array<TypeCategory>;
  description: Maybe<Scalars['String']['output']>;
  /** EAN */
  ean: Maybe<Scalars['String']['output']>;
  /** List of flags */
  flags: Array<TypeFlag>;
  /** The full name of the product, which consists of a prefix, name, and a suffix */
  fullName: Scalars['String']['output'];
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<TypeHreflangLink>;
  /** Product id */
  id: Scalars['Int']['output'];
  /** Product images */
  images: Array<TypeImage>;
  isMainVariant: Scalars['Boolean']['output'];
  isSellingDenied: Scalars['Boolean']['output'];
  /** Product link */
  link: Scalars['String']['output'];
  /** Product image by params */
  mainImage: Maybe<TypeImage>;
  /** Localized product name (domain dependent) */
  name: Scalars['String']['output'];
  /** Name prefix */
  namePrefix: Maybe<Scalars['String']['output']>;
  /** Name suffix */
  nameSuffix: Maybe<Scalars['String']['output']>;
  orderingPriority: Scalars['Int']['output'];
  parameters: Array<TypeParameter>;
  /** Product part number */
  partNumber: Maybe<Scalars['String']['output']>;
  /** Product price */
  price: TypeProductPrice;
  productVideos: Array<TypeVideoToken>;
  /** List of related products */
  relatedProducts: Array<TypeProduct>;
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
  storeAvailabilities: Array<TypeStoreAvailability>;
  unit: TypeUnit;
  /** List of product's unique selling propositions */
  usps: Array<Scalars['String']['output']>;
  /** UUID */
  uuid: Scalars['Uuid']['output'];
  variants: Array<TypeVariant>;
};


/** Represents a product */
export type TypeMainVariantImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a product */
export type TypeMainVariantMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

export type TypeMutation = {
  __typename?: 'Mutation';
  /** Fills cart based on a given order, possibly merging it with the current cart */
  AddOrderItemsToCart: TypeCart;
  /** Adds a product to a product list */
  AddProductToList: TypeProductList;
  /** Add product to cart for future checkout */
  AddToCart: TypeAddToCartResult;
  /** Apply new promo code for the future checkout */
  ApplyPromoCodeToCart: TypeCart;
  /** Changes customer user password */
  ChangePassword: TypeCustomerUser;
  /** Add a payment to the cart, or remove a payment from the cart */
  ChangePaymentInCart: TypeCart;
  /** change payment in an order after the order creation (available for unpaid GoPay orders only) */
  ChangePaymentInOrder: TypeOrder;
  /** Changes customer user personal data */
  ChangePersonalData: TypeCustomerUser;
  /** Add a transport to the cart, or remove a transport from the cart */
  ChangeTransportInCart: TypeCart;
  /** Send message to the site owner */
  ContactForm: Scalars['Boolean']['output'];
  /** Creates complete order with products and addresses */
  CreateOrder: TypeCreateOrderResult;
  /** Delete delivery address by Uuid */
  DeleteDeliveryAddress: Array<TypeDeliveryAddress>;
  /** Edit delivery address by Uuid */
  EditDeliveryAddress: Array<TypeDeliveryAddress>;
  /** Login customer user */
  Login: TypeLoginResult;
  /** Logout user */
  Logout: Scalars['Boolean']['output'];
  /** Subscribe for e-mail newsletter */
  NewsletterSubscribe: Scalars['Boolean']['output'];
  /** Pay order(create payment transaction in payment gateway) and get payment setup data for redirect or creating JS payment gateway layer */
  PayOrder: TypePaymentSetupCreationData;
  /** Recover password using hash required from RequestPasswordRecovery */
  RecoverPassword: TypeLoginResult;
  /** Refreshes access and refresh tokens */
  RefreshTokens: TypeToken;
  /** Register new customer user */
  Register: TypeLoginResult;
  /** Remove product from cart */
  RemoveFromCart: TypeCart;
  /** Removes a product from a product list */
  RemoveProductFromList: Maybe<TypeProductList>;
  /** Removes the product list */
  RemoveProductList: Maybe<TypeProductList>;
  /** Remove already used promo code from cart */
  RemovePromoCodeFromCart: TypeCart;
  /** Request password recovery - email with hash will be sent */
  RequestPasswordRecovery: Scalars['String']['output'];
  /** Request access to personal data */
  RequestPersonalDataAccess: TypePersonalDataPage;
  /** Set default delivery address by Uuid */
  SetDefaultDeliveryAddress: TypeCustomerUser;
  /** check payment status of order after callback from payment service */
  UpdatePaymentStatus: TypeOrder;
};


export type TypeMutationAddOrderItemsToCartArgs = {
  input: TypeAddOrderItemsToCartInput;
};


export type TypeMutationAddProductToListArgs = {
  input: TypeProductListUpdateInput;
};


export type TypeMutationAddToCartArgs = {
  input: TypeAddToCartInput;
};


export type TypeMutationApplyPromoCodeToCartArgs = {
  input: TypeApplyPromoCodeToCartInput;
};


export type TypeMutationChangePasswordArgs = {
  input: TypeChangePasswordInput;
};


export type TypeMutationChangePaymentInCartArgs = {
  input: TypeChangePaymentInCartInput;
};


export type TypeMutationChangePaymentInOrderArgs = {
  input: TypeChangePaymentInOrderInput;
};


export type TypeMutationChangePersonalDataArgs = {
  input: TypeChangePersonalDataInput;
};


export type TypeMutationChangeTransportInCartArgs = {
  input: TypeChangeTransportInCartInput;
};


export type TypeMutationContactFormArgs = {
  input: TypeContactFormInput;
};


export type TypeMutationCreateOrderArgs = {
  input: TypeOrderInput;
};


export type TypeMutationDeleteDeliveryAddressArgs = {
  deliveryAddressUuid: Scalars['Uuid']['input'];
};


export type TypeMutationEditDeliveryAddressArgs = {
  input: TypeDeliveryAddressInput;
};


export type TypeMutationLoginArgs = {
  input: TypeLoginInput;
};


export type TypeMutationNewsletterSubscribeArgs = {
  input: TypeNewsletterSubscriptionDataInput;
};


export type TypeMutationPayOrderArgs = {
  orderUuid: Scalars['Uuid']['input'];
};


export type TypeMutationRecoverPasswordArgs = {
  input: TypeRecoverPasswordInput;
};


export type TypeMutationRefreshTokensArgs = {
  input: TypeRefreshTokenInput;
};


export type TypeMutationRegisterArgs = {
  input: TypeRegistrationDataInput;
};


export type TypeMutationRemoveFromCartArgs = {
  input: TypeRemoveFromCartInput;
};


export type TypeMutationRemoveProductFromListArgs = {
  input: TypeProductListUpdateInput;
};


export type TypeMutationRemoveProductListArgs = {
  input: TypeProductListInput;
};


export type TypeMutationRemovePromoCodeFromCartArgs = {
  input: TypeRemovePromoCodeFromCartInput;
};


export type TypeMutationRequestPasswordRecoveryArgs = {
  email: Scalars['String']['input'];
};


export type TypeMutationRequestPersonalDataAccessArgs = {
  input: TypePersonalDataAccessRequestInput;
};


export type TypeMutationSetDefaultDeliveryAddressArgs = {
  deliveryAddressUuid: Scalars['Uuid']['input'];
};


export type TypeMutationUpdatePaymentStatusArgs = {
  orderPaymentStatusPageValidityHash: InputMaybe<Scalars['String']['input']>;
  orderUuid: Scalars['Uuid']['input'];
};

/** Represents a navigation structure item */
export type TypeNavigationItem = {
  __typename?: 'NavigationItem';
  /** Categories separated into columns */
  categoriesByColumns: Array<TypeNavigationItemCategoriesByColumns>;
  /** Target URL */
  link: Scalars['String']['output'];
  /** Navigation item name */
  name: Scalars['String']['output'];
};

/** Represents a single column inside the navigation item */
export type TypeNavigationItemCategoriesByColumns = {
  __typename?: 'NavigationItemCategoriesByColumns';
  /** Categories */
  categories: Array<TypeCategory>;
  /** Column number */
  columnNumber: Scalars['Int']['output'];
};

export type TypeNewsletterSubscriber = {
  __typename?: 'NewsletterSubscriber';
  /** Date and time of subscription */
  createdAt: Scalars['DateTime']['output'];
  /** Subscribed email address */
  email: Scalars['String']['output'];
};

/** Represents the main input object to subscribe for e-mail newsletter */
export type TypeNewsletterSubscriptionDataInput = {
  email: Scalars['String']['input'];
};

/** Represents an article that is not a blog article */
export type TypeNotBlogArticleInterface = {
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
export type TypeNotificationBar = {
  __typename?: 'NotificationBar';
  /** Notification bar images */
  images: Array<TypeImage>;
  /** Notification bar image by params */
  mainImage: Maybe<TypeImage>;
  /** Color of the notification */
  rgbColor: Scalars['String']['output'];
  /** Message of the notification */
  text: Scalars['String']['output'];
};


/** Represents a notification supposed to be displayed on all pages */
export type TypeNotificationBarImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a notification supposed to be displayed on all pages */
export type TypeNotificationBarMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents store opening hours */
export type TypeOpeningHours = {
  __typename?: 'OpeningHours';
  /** Current day of the week */
  dayOfWeek: Scalars['Int']['output'];
  /** Is store currently open? */
  isOpen: Scalars['Boolean']['output'];
  /** Opening hours for every day of the week (1 for Monday 7 for Sunday) */
  openingHoursOfDays: Array<TypeOpeningHoursOfDay>;
};

/** Represents store opening hours for a specific day */
export type TypeOpeningHoursOfDay = {
  __typename?: 'OpeningHoursOfDay';
  /** Date of day with display timezone for domain */
  date: Scalars['DateTime']['output'];
  /** Day of the week */
  dayOfWeek: Scalars['Int']['output'];
  /** An array of opening hours ranges (each range contains opening and closing time) */
  openingHoursRanges: Array<TypeOpeningHoursRange>;
};

/** Represents a time period when a store is open */
export type TypeOpeningHoursRange = {
  __typename?: 'OpeningHoursRange';
  /** Closing time */
  closingTime: Scalars['String']['output'];
  /** Opening time */
  openingTime: Scalars['String']['output'];
};

export type TypeOrder = {
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
  country: TypeCountry;
  /** Date and time when the order was created */
  creationDate: Scalars['DateTime']['output'];
  /** City name for delivery */
  deliveryCity: Maybe<Scalars['String']['output']>;
  /** Company name for delivery */
  deliveryCompanyName: Maybe<Scalars['String']['output']>;
  /** Country for delivery */
  deliveryCountry: Maybe<TypeCountry>;
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
  /** The customer's email address */
  email: Scalars['String']['output'];
  /** The customer's first name */
  firstName: Maybe<Scalars['String']['output']>;
  /** Determines whether the customer agrees with sending satisfaction questionnaires within the Verified by Customers Heureka program */
  heurekaAgreement: Scalars['Boolean']['output'];
  /** Indicates whether the billing address is other than a delivery address */
  isDeliveryAddressDifferentFromBilling: Scalars['Boolean']['output'];
  /** Indicates whether the order is paid successfully with GoPay payment type */
  isPaid: Scalars['Boolean']['output'];
  /** All items in the order including payment and transport */
  items: Array<TypeOrderItem>;
  /** The customer's last name */
  lastName: Maybe<Scalars['String']['output']>;
  /** Other information related to the order */
  note: Maybe<Scalars['String']['output']>;
  /** Unique order number */
  number: Scalars['String']['output'];
  /** Payment method applied to the order */
  payment: TypePayment;
  /** Count of the payment transactions related to the order */
  paymentTransactionsCount: Scalars['Int']['output'];
  /** Selected pickup place identifier */
  pickupPlaceIdentifier: Maybe<Scalars['String']['output']>;
  /** Billing address zip code */
  postcode: Scalars['String']['output'];
  /** All product items in the order */
  productItems: Array<TypeOrderItem>;
  /** Promo code (coupon) used in the order */
  promoCode: Maybe<Scalars['String']['output']>;
  /** Current status of the order */
  status: Scalars['String']['output'];
  /** Billing address street name  */
  street: Scalars['String']['output'];
  /** The customer's telephone number */
  telephone: Scalars['String']['output'];
  /** Total price of the order including transport and payment prices */
  totalPrice: TypePrice;
  /** The order tracking number */
  trackingNumber: Maybe<Scalars['String']['output']>;
  /** The order tracking link */
  trackingUrl: Maybe<Scalars['String']['output']>;
  /** Transport method applied to the order */
  transport: TypeTransport;
  /** Unique url hash that can be used to  */
  urlHash: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};

/** A connection to a list of items. */
export type TypeOrderConnection = {
  __typename?: 'OrderConnection';
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<TypeOrderEdge>>>;
  /** Information to aid in pagination. */
  pageInfo: TypePageInfo;
  /** Total number of orders */
  totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type TypeOrderEdge = {
  __typename?: 'OrderEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String']['output'];
  /** The item at the end of the edge. */
  node: Maybe<TypeOrder>;
};

/** Represents the main input object to create orders */
export type TypeOrderInput = {
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
  /** Delivery address identifier. Can be used by logged users only. If set, it takes precedence over the individual delivery address fields (deliveryFirstName, deliveryLastName, etc.) */
  deliveryAddressUuid: InputMaybe<Scalars['Uuid']['input']>;
  /** City name for delivery (required when isDeliveryAddressDifferentFromBilling is true and deliveryAddressUuid is null) */
  deliveryCity: InputMaybe<Scalars['String']['input']>;
  /** Company name for delivery */
  deliveryCompanyName: InputMaybe<Scalars['String']['input']>;
  /** Country code in ISO 3166-1 alpha-2 for delivery (required when isDeliveryAddressDifferentFromBilling is true and deliveryAddressUuid is null) */
  deliveryCountry: InputMaybe<Scalars['String']['input']>;
  /** First name of the contact person for delivery (required when isDeliveryAddressDifferentFromBilling is true and deliveryAddressUuid is null) */
  deliveryFirstName: InputMaybe<Scalars['String']['input']>;
  /** Last name of the contact person for delivery (required when isDeliveryAddressDifferentFromBilling is true and deliveryAddressUuid is null) */
  deliveryLastName: InputMaybe<Scalars['String']['input']>;
  /** Zip code for delivery (required when isDeliveryAddressDifferentFromBilling is true and deliveryAddressUuid is null) */
  deliveryPostcode: InputMaybe<Scalars['String']['input']>;
  /** Street name for delivery (required when isDeliveryAddressDifferentFromBilling is true and deliveryAddressUuid is null) */
  deliveryStreet: InputMaybe<Scalars['String']['input']>;
  /** Contact telephone number for delivery */
  deliveryTelephone: InputMaybe<Scalars['String']['input']>;
  /** The customer's email address */
  email: Scalars['String']['input'];
  /** The customer's first name */
  firstName: Scalars['String']['input'];
  /** Determines whether the customer agrees with sending satisfaction questionnaires within the Verified by Customers Heureka program */
  heurekaAgreement: Scalars['Boolean']['input'];
  /** Determines whether to deliver products to a different address than the billing one */
  isDeliveryAddressDifferentFromBilling: Scalars['Boolean']['input'];
  /** The customer's last name */
  lastName: Scalars['String']['input'];
  /** Allows user to subscribe/unsubscribe newsletter. */
  newsletterSubscription: InputMaybe<Scalars['Boolean']['input']>;
  /** Other information related to the order */
  note: InputMaybe<Scalars['String']['input']>;
  /** Determines whether the order is made on the company behalf. */
  onCompanyBehalf: Scalars['Boolean']['input'];
  /** Billing address zip code (will be on the tax invoice) */
  postcode: Scalars['String']['input'];
  /** Billing address street name (will be on the tax invoice) */
  street: Scalars['String']['input'];
  /** The customer's phone number */
  telephone: Scalars['String']['input'];
};

/** Represent one item in the order */
export type TypeOrderItem = {
  __typename?: 'OrderItem';
  /** Name of the order item */
  name: Scalars['String']['output'];
  /** Quantity of order items in the order */
  quantity: Scalars['Int']['output'];
  /** Total price for the quantity of order item */
  totalPrice: TypePrice;
  /** Unit of measurement used for the order item */
  unit: Maybe<Scalars['String']['output']>;
  /** Order item price per unit */
  unitPrice: TypePrice;
  /** Applied VAT rate percentage applied to the order item */
  vatRate: Scalars['String']['output'];
};

export type TypeOrderPaymentsConfig = {
  __typename?: 'OrderPaymentsConfig';
  /** All available payment methods for the order (excluding the current one) */
  availablePayments: Array<TypePayment>;
  /** Current payment method used in the order */
  currentPayment: TypePayment;
};

/** Information about pagination in a connection. */
export type TypePageInfo = {
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
export type TypeParameter = {
  __typename?: 'Parameter';
  /** Parameter group to which the parameter is assigned */
  group: Maybe<Scalars['String']['output']>;
  /** Parameter name */
  name: Scalars['String']['output'];
  /** Unit of the parameter */
  unit: Maybe<TypeUnit>;
  /** UUID */
  uuid: Scalars['Uuid']['output'];
  values: Array<TypeParameterValue>;
  visible: Scalars['Boolean']['output'];
};

/** Parameter filter option */
export type TypeParameterCheckboxFilterOption = TypeParameterFilterOptionInterface & {
  __typename?: 'ParameterCheckboxFilterOption';
  /** Indicator whether the parameter should be collapsed based on the current category setting */
  isCollapsed: Scalars['Boolean']['output'];
  /** The parameter name */
  name: Scalars['String']['output'];
  /** The parameter unit */
  unit: Maybe<TypeUnit>;
  /** The parameter UUID */
  uuid: Scalars['Uuid']['output'];
  /** Filter options of parameter values */
  values: Array<TypeParameterValueFilterOption>;
};

/** Parameter filter option */
export type TypeParameterColorFilterOption = TypeParameterFilterOptionInterface & {
  __typename?: 'ParameterColorFilterOption';
  /** Indicator whether the parameter should be collapsed based on the current category setting */
  isCollapsed: Scalars['Boolean']['output'];
  /** The parameter name */
  name: Scalars['String']['output'];
  /** The parameter unit */
  unit: Maybe<TypeUnit>;
  /** The parameter UUID */
  uuid: Scalars['Uuid']['output'];
  /** Filter options of parameter values */
  values: Array<TypeParameterValueColorFilterOption>;
};

/** Represents a parameter filter */
export type TypeParameterFilter = {
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
export type TypeParameterFilterOptionInterface = {
  /** Indicator whether the parameter should be collapsed based on the current category setting */
  isCollapsed: Scalars['Boolean']['output'];
  /** The parameter name */
  name: Scalars['String']['output'];
  /** The parameter unit */
  unit: Maybe<TypeUnit>;
  /** The parameter UUID */
  uuid: Scalars['Uuid']['output'];
};

/** Parameter filter option */
export type TypeParameterSliderFilterOption = TypeParameterFilterOptionInterface & {
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
  unit: Maybe<TypeUnit>;
  /** The parameter UUID */
  uuid: Scalars['Uuid']['output'];
};

/** Represents a parameter value */
export type TypeParameterValue = {
  __typename?: 'ParameterValue';
  /** Parameter value */
  text: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};

/** Parameter value filter option */
export type TypeParameterValueColorFilterOption = {
  __typename?: 'ParameterValueColorFilterOption';
  /** Count of products that will be filtered if this filter option is applied. */
  count: Scalars['Int']['output'];
  /** If true than count parameter is number of products that will be displayed if this filter option is applied, if false count parameter is number of products that will be added to current products result. */
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
export type TypeParameterValueFilterOption = {
  __typename?: 'ParameterValueFilterOption';
  /** Count of products that will be filtered if this filter option is applied. */
  count: Scalars['Int']['output'];
  /** If true than count parameter is number of products that will be displayed if this filter option is applied, if false count parameter is number of products that will be added to current products result. */
  isAbsolute: Scalars['Boolean']['output'];
  /** Indicator whether the option is already selected (used for "ready category seo mixes") */
  isSelected: Scalars['Boolean']['output'];
  /** Parameter value */
  text: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};

/** Represents a payment */
export type TypePayment = {
  __typename?: 'Payment';
  /** Localized payment description (domain dependent) */
  description: Maybe<Scalars['String']['output']>;
  /** Additional data for GoPay payment */
  goPayPaymentMethod: Maybe<TypeGoPayPaymentMethod>;
  /** Payment images */
  images: Array<TypeImage>;
  /** Localized payment instruction (domain dependent) */
  instruction: Maybe<Scalars['String']['output']>;
  /** Payment image by params */
  mainImage: Maybe<TypeImage>;
  /** Payment name */
  name: Scalars['String']['output'];
  /** Payment position */
  position: Scalars['Int']['output'];
  /** Payment price */
  price: TypePrice;
  /** List of assigned transports */
  transports: Array<TypeTransport>;
  /** Type of payment */
  type: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};


/** Represents a payment */
export type TypePaymentImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a payment */
export type TypePaymentMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a payment */
export type TypePaymentPriceArgs = {
  cartUuid?: InputMaybe<Scalars['Uuid']['input']>;
};

export type TypePaymentSetupCreationData = {
  __typename?: 'PaymentSetupCreationData';
  /** Identifiers of GoPay payment method */
  goPayCreatePaymentSetup: Maybe<TypeGoPayCreatePaymentSetup>;
};

export type TypePersonalData = {
  __typename?: 'PersonalData';
  /** Customer user data */
  customerUser: Maybe<TypeCustomerUser>;
  /** A link for downloading the personal data in an XML file */
  exportLink: Scalars['String']['output'];
  /** Newsletter subscription */
  newsletterSubscriber: Maybe<TypeNewsletterSubscriber>;
  /** Customer orders */
  orders: Array<TypeOrder>;
};

export type TypePersonalDataAccessRequestInput = {
  /** The customer's email address */
  email: Scalars['String']['input'];
  /** One of two possible types for personal data access request - display or export */
  type: InputMaybe<TypePersonalDataAccessRequestTypeEnum>;
};

/** One of two possible types for personal data access request */
export enum TypePersonalDataAccessRequestTypeEnum {
  /** Display data */
  Display = 'display',
  /** Export data */
  Export = 'export'
}

export type TypePersonalDataPage = {
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
export type TypePrice = {
  __typename?: 'Price';
  /** Price with VAT */
  priceWithVat: Scalars['Money']['output'];
  /** Price without VAT */
  priceWithoutVat: Scalars['Money']['output'];
  /** Total value of VAT */
  vatAmount: Scalars['Money']['output'];
};

/** Represents setting of pricing */
export type TypePricingSetting = {
  __typename?: 'PricingSetting';
  /** Code of the default currency used on the current domain */
  defaultCurrencyCode: Scalars['String']['output'];
  /** Minimum number of decimal places for the price on the current domain */
  minimumFractionDigits: Scalars['Int']['output'];
};

/** Represents a product */
export type TypeProduct = {
  accessories: Array<TypeProduct>;
  availability: TypeAvailability;
  /** Number of the stores where the product is available */
  availableStoresCount: Scalars['Int']['output'];
  /** Brand of product */
  brand: Maybe<TypeBrand>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<TypeLink>;
  /** Product catalog number */
  catalogNumber: Scalars['String']['output'];
  /** List of categories */
  categories: Array<TypeCategory>;
  description: Maybe<Scalars['String']['output']>;
  /** EAN */
  ean: Maybe<Scalars['String']['output']>;
  /** List of flags */
  flags: Array<TypeFlag>;
  /** The full name of the product, which consists of a prefix, name, and a suffix */
  fullName: Scalars['String']['output'];
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<TypeHreflangLink>;
  /** Product id */
  id: Scalars['Int']['output'];
  /** Product images */
  images: Array<TypeImage>;
  isMainVariant: Scalars['Boolean']['output'];
  isSellingDenied: Scalars['Boolean']['output'];
  /** Product link */
  link: Scalars['String']['output'];
  /** Product image by params */
  mainImage: Maybe<TypeImage>;
  /** Localized product name (domain dependent) */
  name: Scalars['String']['output'];
  /** Name prefix */
  namePrefix: Maybe<Scalars['String']['output']>;
  /** Name suffix */
  nameSuffix: Maybe<Scalars['String']['output']>;
  orderingPriority: Scalars['Int']['output'];
  parameters: Array<TypeParameter>;
  /** Product part number */
  partNumber: Maybe<Scalars['String']['output']>;
  /** Product price */
  price: TypeProductPrice;
  productVideos: Array<TypeVideoToken>;
  /** List of related products */
  relatedProducts: Array<TypeProduct>;
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
  storeAvailabilities: Array<TypeStoreAvailability>;
  unit: TypeUnit;
  /** List of product's unique selling propositions */
  usps: Array<Scalars['String']['output']>;
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};


/** Represents a product */
export type TypeProductImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a product */
export type TypeProductMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

/** A connection to a list of items. */
export type TypeProductConnection = {
  __typename?: 'ProductConnection';
  /** The default ordering mode that is set for the given connection (e.g. in a category, search page, or ready category SEO mix) */
  defaultOrderingMode: Maybe<TypeProductOrderingModeEnum>;
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<TypeProductEdge>>>;
  /** The current ordering mode */
  orderingMode: TypeProductOrderingModeEnum;
  /** Information to aid in pagination. */
  pageInfo: TypePageInfo;
  productFilterOptions: TypeProductFilterOptions;
  /** Total number of products (-1 means that the total count is not available) */
  totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type TypeProductEdge = {
  __typename?: 'ProductEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String']['output'];
  /** The item at the end of the edge. */
  node: Maybe<TypeProduct>;
};

/** Represents a product filter */
export type TypeProductFilter = {
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
  parameters: InputMaybe<Array<TypeParameterFilter>>;
};

/** Represents a product filter options */
export type TypeProductFilterOptions = {
  __typename?: 'ProductFilterOptions';
  /** Brands filter options */
  brands: Maybe<Array<TypeBrandFilterOption>>;
  /** Flags filter options */
  flags: Maybe<Array<TypeFlagFilterOption>>;
  /** Number of products in stock that will be filtered */
  inStock: Scalars['Int']['output'];
  /** Maximal price of products for filtering */
  maximalPrice: Scalars['Money']['output'];
  /** Minimal price of products for filtering */
  minimalPrice: Scalars['Money']['output'];
  /** Parameter filter options */
  parameters: Maybe<Array<TypeParameterFilterOptionInterface>>;
};

export type TypeProductList = {
  __typename?: 'ProductList';
  /** An array of the products in the list */
  products: Array<TypeProduct>;
  /** Product list type */
  type: TypeProductListTypeEnum;
  /** Product list identifier */
  uuid: Scalars['Uuid']['output'];
};

export type TypeProductListInput = {
  /** Product list type */
  type: TypeProductListTypeEnum;
  /** Product list identifier */
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};

/** One of possible types of the product list */
export enum TypeProductListTypeEnum {
  Comparison = 'COMPARISON',
  Wishlist = 'WISHLIST'
}

export type TypeProductListUpdateInput = {
  productListInput: TypeProductListInput;
  /** Product identifier */
  productUuid: Scalars['Uuid']['input'];
};

/** Paginated and ordered products */
export type TypeProductListable = {
  /** Paginated and ordered products */
  products: TypeProductConnection;
};


/** Paginated and ordered products */
export type TypeProductListableProductsArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  brandSlug: InputMaybe<Scalars['String']['input']>;
  categorySlug: InputMaybe<Scalars['String']['input']>;
  filter: InputMaybe<TypeProductFilter>;
  first: InputMaybe<Scalars['Int']['input']>;
  flagSlug: InputMaybe<Scalars['String']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  orderingMode: InputMaybe<TypeProductOrderingModeEnum>;
};

/** One of possible ordering modes for product */
export enum TypeProductOrderingModeEnum {
  /** Order by name ascending */
  NameAsc = 'NAME_ASC',
  /** Order by name descending */
  NameDesc = 'NAME_DESC',
  /** Order by price ascending */
  PriceAsc = 'PRICE_ASC',
  /** Order by price descending */
  PriceDesc = 'PRICE_DESC',
  /** Order by priority */
  Priority = 'PRIORITY',
  /** Order by relevance */
  Relevance = 'RELEVANCE'
}

/** Represents the price of the product */
export type TypeProductPrice = {
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

export type TypeQuery = {
  __typename?: 'Query';
  AdvertCode: Maybe<TypeAdvertCode>;
  AdvertImage: Maybe<TypeAdvertImage>;
  ArticleLink: Maybe<TypeArticleLink>;
  ArticleSite: Maybe<TypeArticleSite>;
  CompanyCustomerUser: Maybe<TypeCompanyCustomerUser>;
  /** List of available banks for GoPay bank transfer payment */
  GoPaySwifts: Array<TypeGoPayBankSwift>;
  MainVariant: Maybe<TypeMainVariant>;
  ParameterCheckboxFilterOption: Maybe<TypeParameterCheckboxFilterOption>;
  ParameterColorFilterOption: Maybe<TypeParameterColorFilterOption>;
  ParameterSliderFilterOption: Maybe<TypeParameterSliderFilterOption>;
  RegularCustomerUser: Maybe<TypeRegularCustomerUser>;
  RegularProduct: Maybe<TypeRegularProduct>;
  Variant: Maybe<TypeVariant>;
  /** Access personal data using hash received in email from personal data access request */
  accessPersonalData: TypePersonalData;
  /** Returns list of advert positions. */
  advertPositions: Array<TypeAdvertPosition>;
  /** Returns list of adverts, optionally filtered by `positionName` */
  adverts: Array<TypeAdvert>;
  /** Returns article filtered using UUID or URL slug */
  article: Maybe<TypeNotBlogArticleInterface>;
  /** Returns list of articles that can be paginated using `first`, `last`, `before` and `after` keywords and filtered by `placement` */
  articles: TypeArticleConnection;
  /** Returns list of searched articles and blog articles */
  articlesSearch: Array<TypeArticleInterface>;
  /** Returns blog article filtered using UUID or URL slug */
  blogArticle: Maybe<TypeBlogArticle>;
  /** Returns a list of the blog articles that can be paginated using `first`, `last`, `before` and `after` keywords */
  blogArticles: TypeBlogArticleConnection;
  /** Returns a complete list of the blog categories */
  blogCategories: Array<TypeBlogCategory>;
  /** Returns blog category filtered using UUID or URL slug */
  blogCategory: Maybe<TypeBlogCategory>;
  /** Returns brand filtered using UUID or URL slug */
  brand: Maybe<TypeBrand>;
  /** Returns list of searched brands */
  brandSearch: Array<TypeBrand>;
  /** Returns complete list of brands */
  brands: Array<TypeBrand>;
  /** Return cart of logged customer or cart by UUID for anonymous user */
  cart: Maybe<TypeCart>;
  /** Returns complete list of categories */
  categories: Array<TypeCategory>;
  /** Returns list of searched categories that can be paginated using `first`, `last`, `before` and `after` keywords */
  categoriesSearch: TypeCategoryConnection;
  /** Returns category filtered using UUID or URL slug */
  category: Maybe<TypeCategory>;
  /** Returns available countries */
  countries: Array<TypeCountry>;
  /** Returns currently logged in customer user */
  currentCustomerUser: Maybe<TypeCustomerUser>;
  /** Returns a flag by uuid or url slug */
  flag: Maybe<TypeFlag>;
  /** Returns a complete list of the flags */
  flags: Maybe<Array<TypeFlag>>;
  /** Check if email is registered */
  isCustomerUserRegistered: Scalars['Boolean']['output'];
  /** Return user translated language constants for current domain locale */
  languageConstants: Array<TypeLanguageConstant>;
  /** Returns last order of the user or null if no order was placed yet */
  lastOrder: Maybe<TypeOrder>;
  /** Returns complete navigation menu */
  navigation: Array<TypeNavigationItem>;
  /** Returns a list of notifications supposed to be displayed on all pages */
  notificationBars: Maybe<Array<TypeNotificationBar>>;
  /** Returns order filtered using UUID, orderNumber, or urlHash */
  order: Maybe<TypeOrder>;
  /** Returns HTML content for order with failed payment. */
  orderPaymentFailedContent: Scalars['String']['output'];
  /** Returns HTML content for order with successful payment. */
  orderPaymentSuccessfulContent: Scalars['String']['output'];
  /** Returns payments available for the given order */
  orderPayments: TypeOrderPaymentsConfig;
  /** Returns HTML content for order sent page. */
  orderSentPageContent: Scalars['String']['output'];
  /** Returns list of orders that can be paginated using `first`, `last`, `before` and `after` keywords */
  orders: Maybe<TypeOrderConnection>;
  /** Returns payment filtered using UUID */
  payment: Maybe<TypePayment>;
  /** Returns complete list of payment methods */
  payments: Array<TypePayment>;
  /** Return personal data page content and URL */
  personalDataPage: Maybe<TypePersonalDataPage>;
  /** Returns product filtered using UUID or URL slug */
  product: Maybe<TypeProduct>;
  /** Find product list by UUID and type or if customer is logged, try find the the oldest list of the given type for the logged customer. The logged customer can also optionally pass the UUID of his product list. */
  productList: Maybe<TypeProductList>;
  productListsByType: Array<TypeProductList>;
  /** Returns list of ordered products that can be paginated using `first`, `last`, `before` and `after` keywords */
  products: TypeProductConnection;
  /** Returns list of products by catalog numbers */
  productsByCatnums: Array<TypeProduct>;
  /** Returns list of searched products that can be paginated using `first`, `last`, `before` and `after` keywords */
  productsSearch: TypeProductConnection;
  /** Returns promoted categories */
  promotedCategories: Array<TypeCategory>;
  /** Returns promoted products */
  promotedProducts: Array<TypeProduct>;
  /** Return recommended products from Luigi's Box by provided arguments */
  recommendedProducts: Array<TypeProduct>;
  /** Returns SEO settings for a specific page based on the url slug of that page */
  seoPage: Maybe<TypeSeoPage>;
  /** Returns current settings */
  settings: Maybe<TypeSettings>;
  /** Returns a complete list of the slider items */
  sliderItems: Array<TypeSliderItem>;
  /** Returns entity by slug */
  slug: Maybe<TypeSlug>;
  /** Returns store filtered using UUID or URL slug */
  store: Maybe<TypeStore>;
  /** Returns list of stores that can be paginated using `first`, `last`, `before` and `after` keywords */
  stores: TypeStoreConnection;
  /** Returns complete list of transport methods */
  transport: Maybe<TypeTransport>;
  /** Returns available transport methods based on the current cart state */
  transports: Array<TypeTransport>;
};


export type TypeQueryGoPaySwiftsArgs = {
  currencyCode: Scalars['String']['input'];
};


export type TypeQueryAccessPersonalDataArgs = {
  hash: Scalars['String']['input'];
};


export type TypeQueryAdvertsArgs = {
  categoryUuid: InputMaybe<Scalars['Uuid']['input']>;
  positionName: InputMaybe<Scalars['String']['input']>;
};


export type TypeQueryArticleArgs = {
  urlSlug: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type TypeQueryArticlesArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  first: InputMaybe<Scalars['Int']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  placement?: InputMaybe<Array<TypeArticlePlacementTypeEnum>>;
};


export type TypeQueryArticlesSearchArgs = {
  searchInput: TypeSearchInput;
};


export type TypeQueryBlogArticleArgs = {
  urlSlug: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type TypeQueryBlogArticlesArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  first: InputMaybe<Scalars['Int']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  onlyHomepageArticles?: InputMaybe<Scalars['Boolean']['input']>;
};


export type TypeQueryBlogCategoryArgs = {
  urlSlug: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type TypeQueryBrandArgs = {
  urlSlug: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type TypeQueryBrandSearchArgs = {
  searchInput: TypeSearchInput;
};


export type TypeQueryCartArgs = {
  cartInput: InputMaybe<TypeCartInput>;
};


export type TypeQueryCategoriesSearchArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  first: InputMaybe<Scalars['Int']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  searchInput: TypeSearchInput;
};


export type TypeQueryCategoryArgs = {
  filter: InputMaybe<TypeProductFilter>;
  orderingMode: InputMaybe<TypeProductOrderingModeEnum>;
  urlSlug: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type TypeQueryFlagArgs = {
  urlSlug: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type TypeQueryIsCustomerUserRegisteredArgs = {
  email: Scalars['String']['input'];
};


export type TypeQueryOrderArgs = {
  orderNumber: InputMaybe<Scalars['String']['input']>;
  urlHash: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type TypeQueryOrderPaymentFailedContentArgs = {
  orderUuid: Scalars['Uuid']['input'];
};


export type TypeQueryOrderPaymentSuccessfulContentArgs = {
  orderUuid: Scalars['Uuid']['input'];
};


export type TypeQueryOrderPaymentsArgs = {
  orderUuid: Scalars['Uuid']['input'];
};


export type TypeQueryOrderSentPageContentArgs = {
  orderUuid: Scalars['Uuid']['input'];
};


export type TypeQueryOrdersArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  first: InputMaybe<Scalars['Int']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
};


export type TypeQueryPaymentArgs = {
  uuid: Scalars['Uuid']['input'];
};


export type TypeQueryProductArgs = {
  urlSlug: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type TypeQueryProductListArgs = {
  input: TypeProductListInput;
};


export type TypeQueryProductListsByTypeArgs = {
  productListType: TypeProductListTypeEnum;
};


export type TypeQueryProductsArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  brandSlug: InputMaybe<Scalars['String']['input']>;
  categorySlug: InputMaybe<Scalars['String']['input']>;
  filter: InputMaybe<TypeProductFilter>;
  first: InputMaybe<Scalars['Int']['input']>;
  flagSlug: InputMaybe<Scalars['String']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  orderingMode: InputMaybe<TypeProductOrderingModeEnum>;
};


export type TypeQueryProductsByCatnumsArgs = {
  catnums: Array<Scalars['String']['input']>;
};


export type TypeQueryProductsSearchArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  filter: InputMaybe<TypeProductFilter>;
  first: InputMaybe<Scalars['Int']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  orderingMode: InputMaybe<TypeProductOrderingModeEnum>;
  search: InputMaybe<Scalars['String']['input']>;
  searchInput: TypeSearchInput;
};


export type TypeQueryRecommendedProductsArgs = {
  itemUuids: InputMaybe<Array<Scalars['Uuid']['input']>>;
  limit?: InputMaybe<Scalars['Int']['input']>;
  recommendationType: TypeRecommendationType;
  userIdentifier: Scalars['Uuid']['input'];
};


export type TypeQuerySeoPageArgs = {
  pageSlug: Scalars['String']['input'];
};


export type TypeQuerySlugArgs = {
  slug: Scalars['String']['input'];
};


export type TypeQueryStoreArgs = {
  urlSlug: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type TypeQueryStoresArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  first: InputMaybe<Scalars['Int']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
};


export type TypeQueryTransportArgs = {
  uuid: Scalars['Uuid']['input'];
};


export type TypeQueryTransportsArgs = {
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
};

export enum TypeRecommendationType {
  Basket = 'basket',
  BasketPopup = 'basket_popup',
  Category = 'category',
  ItemDetail = 'item_detail',
  Personalized = 'personalized'
}

export type TypeRecoverPasswordInput = {
  /** Customer user email. */
  email: Scalars['String']['input'];
  /** Hash */
  hash: Scalars['String']['input'];
  /** New customer user password. */
  newPassword: Scalars['Password']['input'];
};

export type TypeRefreshTokenInput = {
  /** The refresh token. */
  refreshToken: Scalars['String']['input'];
};

/** Represents the main input object to register customer user */
export type TypeRegistrationDataInput = {
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
  /** Uuids of product lists that should be merged to the product lists of the user after registration */
  productListsUuids: Array<Scalars['Uuid']['input']>;
  /** Billing address street name (will be on the tax invoice) */
  street: Scalars['String']['input'];
  /** The customer's telephone number */
  telephone: Scalars['String']['input'];
};

/** Represents an currently logged customer user */
export type TypeRegularCustomerUser = TypeCustomerUser & {
  __typename?: 'RegularCustomerUser';
  /** Billing address city name */
  city: Scalars['String']['output'];
  /** Billing address country */
  country: TypeCountry;
  /** Default customer delivery addresses */
  defaultDeliveryAddress: Maybe<TypeDeliveryAddress>;
  /** List of delivery addresses */
  deliveryAddresses: Array<TypeDeliveryAddress>;
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
export type TypeRegularProduct = TypeBreadcrumb & TypeHreflang & TypeProduct & TypeSlug & {
  __typename?: 'RegularProduct';
  accessories: Array<TypeProduct>;
  availability: TypeAvailability;
  /** Number of the stores where the product is available */
  availableStoresCount: Scalars['Int']['output'];
  /** Brand of product */
  brand: Maybe<TypeBrand>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<TypeLink>;
  /** Product catalog number */
  catalogNumber: Scalars['String']['output'];
  /** List of categories */
  categories: Array<TypeCategory>;
  description: Maybe<Scalars['String']['output']>;
  /** EAN */
  ean: Maybe<Scalars['String']['output']>;
  /** List of flags */
  flags: Array<TypeFlag>;
  /** The full name of the product, which consists of a prefix, name, and a suffix */
  fullName: Scalars['String']['output'];
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<TypeHreflangLink>;
  /** Product id */
  id: Scalars['Int']['output'];
  /** Product images */
  images: Array<TypeImage>;
  isMainVariant: Scalars['Boolean']['output'];
  isSellingDenied: Scalars['Boolean']['output'];
  /** Product link */
  link: Scalars['String']['output'];
  /** Product image by params */
  mainImage: Maybe<TypeImage>;
  /** Localized product name (domain dependent) */
  name: Scalars['String']['output'];
  /** Name prefix */
  namePrefix: Maybe<Scalars['String']['output']>;
  /** Name suffix */
  nameSuffix: Maybe<Scalars['String']['output']>;
  orderingPriority: Scalars['Int']['output'];
  parameters: Array<TypeParameter>;
  /** Product part number */
  partNumber: Maybe<Scalars['String']['output']>;
  /** Product price */
  price: TypeProductPrice;
  productVideos: Array<TypeVideoToken>;
  /** List of related products */
  relatedProducts: Array<TypeProduct>;
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
  storeAvailabilities: Array<TypeStoreAvailability>;
  unit: TypeUnit;
  /** List of product's unique selling propositions */
  usps: Array<Scalars['String']['output']>;
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};


/** Represents a product */
export type TypeRegularProductImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a product */
export type TypeRegularProductMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

export type TypeRemoveFromCartInput = {
  /** Cart item UUID */
  cartItemUuid: Scalars['Uuid']['input'];
  /** Cart identifier, new cart will be created if not provided and customer is not logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type TypeRemovePromoCodeFromCartInput = {
  /** Cart identifier or null if customer is logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
  /** Promo code to be removed */
  promoCode: Scalars['String']['input'];
};

/** Represents search input object */
export type TypeSearchInput = {
  isAutocomplete: Scalars['Boolean']['input'];
  search: Scalars['String']['input'];
  /** Unique identifier of the user who initiated the search in format UUID version 4 (^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[1-8][0-9A-Fa-f]{3}-[ABab89][0-9A-Fa-f]{3}-[0-9A-Fa-f]{12}$/) */
  userIdentifier: Scalars['Uuid']['input'];
};

/** Represents SEO settings for specific page */
export type TypeSeoPage = TypeHreflang & {
  __typename?: 'SeoPage';
  /** Page's canonical link */
  canonicalUrl: Maybe<Scalars['String']['output']>;
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<TypeHreflangLink>;
  /** Description for meta tag */
  metaDescription: Maybe<Scalars['String']['output']>;
  /** Description for og:description meta tag */
  ogDescription: Maybe<Scalars['String']['output']>;
  /** Image for og image meta tag by params */
  ogImage: Maybe<TypeImage>;
  /** Title for og:title meta tag */
  ogTitle: Maybe<Scalars['String']['output']>;
  /** Document's title that is shown in a browser's title */
  title: Maybe<Scalars['String']['output']>;
};

/** Represents settings of SEO */
export type TypeSeoSetting = {
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
export type TypeSettings = {
  __typename?: 'Settings';
  /** Main text for contact form */
  contactFormMainText: Scalars['String']['output'];
  /** Timezone that is used for displaying time */
  displayTimezone: Scalars['String']['output'];
  /** Returns true if Heureka is available for the current domain */
  heurekaEnabled: Scalars['Boolean']['output'];
  /** Absolute URL of the blog main category */
  mainBlogCategoryUrl: Maybe<Scalars['String']['output']>;
  /** Max allowed payment transactions (how many times is user allowed to try the same payment) */
  maxAllowedPaymentTransactions: Scalars['Int']['output'];
  /** Settings related to pricing */
  pricing: TypePricingSetting;
  /** Returns privacy policy article's url */
  privacyPolicyArticleUrl: Maybe<Scalars['String']['output']>;
  /** Settings related to SEO */
  seo: TypeSeoSetting;
  /** Returns Terms and Conditions article's url */
  termsAndConditionsArticleUrl: Maybe<Scalars['String']['output']>;
  /** Returns User consent policy article's url */
  userConsentPolicyArticleUrl: Maybe<Scalars['String']['output']>;
};

export type TypeSliderItem = {
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
  images: Array<TypeImage>;
  /** Target link */
  link: Scalars['String']['output'];
  /** Slider item image by params */
  mainImage: Maybe<TypeImage>;
  /** Slider name */
  name: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};


export type TypeSliderItemImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


export type TypeSliderItemMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents entity retrievable by slug */
export type TypeSlug = {
  name: Maybe<Scalars['String']['output']>;
  slug: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};

export type TypeStore = TypeBreadcrumb & TypeSlug & {
  __typename?: 'Store';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<TypeLink>;
  /** Store address city */
  city: Scalars['String']['output'];
  contactInfo: Maybe<Scalars['String']['output']>;
  /** Store address country */
  country: TypeCountry;
  /** Store description */
  description: Maybe<Scalars['String']['output']>;
  /** Store images */
  images: Array<TypeImage>;
  /** Is set as default store */
  isDefault: Scalars['Boolean']['output'];
  /** Store location latitude */
  locationLatitude: Maybe<Scalars['String']['output']>;
  /** Store location longitude */
  locationLongitude: Maybe<Scalars['String']['output']>;
  /** Store name */
  name: Scalars['String']['output'];
  /** Store opening hours */
  openingHours: TypeOpeningHours;
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


export type TypeStoreImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents an availability in an individual store */
export type TypeStoreAvailability = {
  __typename?: 'StoreAvailability';
  /** Detailed information about availability */
  availabilityInformation: Scalars['String']['output'];
  /** Availability status in a format suitable for usage in the code */
  availabilityStatus: TypeAvailabilityStatusEnum;
  /** Store */
  store: Maybe<TypeStore>;
};

/** A connection to a list of items. */
export type TypeStoreConnection = {
  __typename?: 'StoreConnection';
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<TypeStoreEdge>>>;
  /** Information to aid in pagination. */
  pageInfo: TypePageInfo;
  /** Total number of stores */
  totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type TypeStoreEdge = {
  __typename?: 'StoreEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String']['output'];
  /** The item at the end of the edge. */
  node: Maybe<TypeStore>;
};

export type TypeToken = {
  __typename?: 'Token';
  accessToken: Scalars['String']['output'];
  refreshToken: Scalars['String']['output'];
};

/** Represents a transport */
export type TypeTransport = {
  __typename?: 'Transport';
  /** Number of days until goods are delivered */
  daysUntilDelivery: Scalars['Int']['output'];
  /** Localized transport description (domain dependent) */
  description: Maybe<Scalars['String']['output']>;
  /** Transport images */
  images: Array<TypeImage>;
  /** Localized transport instruction (domain dependent) */
  instruction: Maybe<Scalars['String']['output']>;
  /** Pointer telling if the transport is of type personal pickup */
  isPersonalPickup: Scalars['Boolean']['output'];
  /** Transport image by params */
  mainImage: Maybe<TypeImage>;
  /** Transport name */
  name: Scalars['String']['output'];
  /** List of assigned payments */
  payments: Array<TypePayment>;
  /** Transport position */
  position: Scalars['Int']['output'];
  /** Transport price */
  price: TypePrice;
  /** Stores available for personal pickup */
  stores: Maybe<TypeStoreConnection>;
  /** Type of transport */
  transportType: TypeTransportType;
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};


/** Represents a transport */
export type TypeTransportImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a transport */
export type TypeTransportMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a transport */
export type TypeTransportPriceArgs = {
  cartUuid?: InputMaybe<Scalars['Uuid']['input']>;
};

/** Represents a transport type */
export type TypeTransportType = {
  __typename?: 'TransportType';
  /** Code of transport */
  code: Scalars['String']['output'];
  /** Name of transport type */
  name: Scalars['String']['output'];
};

/** Represents a unit */
export type TypeUnit = {
  __typename?: 'Unit';
  /** Localized unit name (domain dependent) */
  name: Scalars['String']['output'];
};

/** Represents a product */
export type TypeVariant = TypeBreadcrumb & TypeHreflang & TypeProduct & TypeSlug & {
  __typename?: 'Variant';
  accessories: Array<TypeProduct>;
  availability: TypeAvailability;
  /** Number of the stores where the product is available */
  availableStoresCount: Scalars['Int']['output'];
  /** Brand of product */
  brand: Maybe<TypeBrand>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<TypeLink>;
  /** Product catalog number */
  catalogNumber: Scalars['String']['output'];
  /** List of categories */
  categories: Array<TypeCategory>;
  description: Maybe<Scalars['String']['output']>;
  /** EAN */
  ean: Maybe<Scalars['String']['output']>;
  /** List of flags */
  flags: Array<TypeFlag>;
  /** The full name of the product, which consists of a prefix, name, and a suffix */
  fullName: Scalars['String']['output'];
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<TypeHreflangLink>;
  /** Product id */
  id: Scalars['Int']['output'];
  /** Product images */
  images: Array<TypeImage>;
  isMainVariant: Scalars['Boolean']['output'];
  isSellingDenied: Scalars['Boolean']['output'];
  /** Product link */
  link: Scalars['String']['output'];
  /** Product image by params */
  mainImage: Maybe<TypeImage>;
  mainVariant: Maybe<TypeMainVariant>;
  /** Localized product name (domain dependent) */
  name: Scalars['String']['output'];
  /** Name prefix */
  namePrefix: Maybe<Scalars['String']['output']>;
  /** Name suffix */
  nameSuffix: Maybe<Scalars['String']['output']>;
  orderingPriority: Scalars['Int']['output'];
  parameters: Array<TypeParameter>;
  /** Product part number */
  partNumber: Maybe<Scalars['String']['output']>;
  /** Product price */
  price: TypeProductPrice;
  productVideos: Array<TypeVideoToken>;
  /** List of related products */
  relatedProducts: Array<TypeProduct>;
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
  storeAvailabilities: Array<TypeStoreAvailability>;
  unit: TypeUnit;
  /** List of product's unique selling propositions */
  usps: Array<Scalars['String']['output']>;
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};


/** Represents a product */
export type TypeVariantImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a product */
export type TypeVariantMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

export type TypeVideoToken = {
  __typename?: 'VideoToken';
  description: Scalars['String']['output'];
  token: Scalars['String']['output'];
};
