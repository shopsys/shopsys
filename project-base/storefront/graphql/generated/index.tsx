import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Maybe<T> = T | null;
export type InputMaybe<T> = Maybe<T>;
export type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
export type MakeOptional<T, K extends keyof T> = Omit<T, K> & { [SubKey in K]?: Maybe<T[SubKey]> };
export type MakeMaybe<T, K extends keyof T> = Omit<T, K> & { [SubKey in K]: Maybe<T[SubKey]> };
export type MakeEmpty<T extends { [key: string]: unknown }, K extends keyof T> = { [_ in K]?: never };
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
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

export type AddOrderItemsToCartInputApi = {
  /** Cart identifier or null if customer is logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
  /** UUID of the order based on which the cart should be prefilled */
  orderUuid: Scalars['Uuid']['input'];
  /** Information if the prefilled cart should be merged with the current cart */
  shouldMerge: InputMaybe<Scalars['Boolean']['input']>;
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
  /** True if quantity should be set no matter the current state of the cart. False if quantity should be added to the already existing same item in the cart */
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
  type?: InputMaybe<Scalars['String']['input']>;
};


export type AdvertImageMainImageArgsApi = {
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
  NoneApi = 'none'
}

export type ArticleSiteApi = ArticleInterfaceApi & BreadcrumbApi & NotBlogArticleInterfaceApi & SlugApi & {
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
  OutOfStockApi = 'OutOfStock'
}

export type BlogArticleApi = ArticleInterfaceApi & BreadcrumbApi & HreflangApi & SlugApi & {
  __typename?: 'BlogArticle';
  /** The list of the blog article blog categories */
  blogCategories: Array<BlogCategoryApi>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Date and time of the blog article creation */
  createdAt: Scalars['DateTime']['output'];
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLinkApi>;
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
  type?: InputMaybe<Scalars['String']['input']>;
};


export type BlogArticleMainImageArgsApi = {
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

export type BlogCategoryApi = BreadcrumbApi & HreflangApi & SlugApi & {
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
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLinkApi>;
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
export type BrandApi = BreadcrumbApi & HreflangApi & ProductListableApi & SlugApi & {
  __typename?: 'Brand';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Brand description */
  description: Maybe<Scalars['String']['output']>;
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLinkApi>;
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
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a brand */
export type BrandMainImageArgsApi = {
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
};

/** Brand filter option */
export type BrandFilterOptionApi = {
  __typename?: 'BrandFilterOption';
  /** Brand */
  brand: BrandApi;
  /** Count of products that will be filtered if this filter option is applied. */
  count: Scalars['Int']['output'];
  /** If true than count parameter is number of products that will be displayed if this filter option is applied, if false count parameter is number of products that will be added to current products result. */
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
  /** Rounding amount if payment has rounding allowed */
  roundingPrice: Maybe<PriceApi>;
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
  /** Rounding amount if payment has rounding allowed */
  roundingPrice: Maybe<PriceApi>;
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
  multipleAddedProductModifications: CartMultipleAddedProductModificationsResultApi;
  paymentModifications: CartPaymentModificationsResultApi;
  promoCodeModifications: CartPromoCodeModificationsResultApi;
  someProductWasRemovedFromEshop: Scalars['Boolean']['output'];
  transportModifications: CartTransportModificationsResultApi;
};

export type CartMultipleAddedProductModificationsResultApi = {
  __typename?: 'CartMultipleAddedProductModificationsResult';
  notAddedProducts: Array<ProductApi>;
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
export type CategoryApi = BreadcrumbApi & ProductListableApi & SlugApi & {
  __typename?: 'Category';
  /** Best selling products */
  bestsellers: Array<ProductApi>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** All parent category names with their IDs and UUIDs */
  categoryHierarchy: Array<CategoryHierarchyItemApi>;
  /** Descendant categories */
  children: Array<CategoryApi>;
  /** Localized category description (domain dependent) */
  description: Maybe<Scalars['String']['output']>;
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLinkApi>;
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
  /** Original category URL slug (for CategorySeoMixes slug of assigned category is returned, null is returned for regular category) */
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
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a category */
export type CategoryMainImageArgsApi = {
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
  /** ID of the category */
  id: Scalars['Int']['output'];
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

export type ChangePaymentInOrderInputApi = {
  /** Order identifier */
  orderUuid: Scalars['Uuid']['input'];
  /** Selected bank swift code of goPay payment bank transfer */
  paymentGoPayBankSwift: InputMaybe<Scalars['String']['input']>;
  /** UUID of a payment that should be assigned to the order. */
  paymentUuid: Scalars['Uuid']['input'];
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

/** Represents a flag */
export type FlagApi = BreadcrumbApi & HreflangApi & ProductListableApi & SlugApi & {
  __typename?: 'Flag';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Categories containing at least one product with flag */
  categories: Array<CategoryApi>;
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLinkApi>;
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
};

/** Flag filter option */
export type FlagFilterOptionApi = {
  __typename?: 'FlagFilterOption';
  /** Count of products that will be filtered if this filter option is applied. */
  count: Scalars['Int']['output'];
  /** Flag */
  flag: FlagApi;
  /** If true than count parameter is number of products that will be displayed if this filter option is applied, if false count parameter is number of products that will be added to current products result. */
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

/** Represents entity able to return alternate links for hreflang meta tags */
export type HreflangApi = {
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLinkApi>;
};

export type HreflangLinkApi = {
  __typename?: 'HreflangLink';
  /** URL for hreflang meta tag */
  href: Scalars['String']['output'];
  /** Language code for hreflang meta tag */
  hreflang: Scalars['String']['output'];
};

/** Represents an image */
export type ImageApi = {
  __typename?: 'Image';
  /** Name of the image usable as an alternative text */
  name: Maybe<Scalars['String']['output']>;
  /** URL address of the image */
  url: Scalars['String']['output'];
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
  /** Uuids of product lists that should be merged to the product lists of the user */
  productListsUuids: Array<Scalars['Uuid']['input']>;
  /** A boolean pointer to indicate if the current customer user cart should be overwritten by the cart with cartUuid */
  shouldOverwriteCustomerUserCart: Scalars['Boolean']['input'];
};

export type LoginResultApi = {
  __typename?: 'LoginResult';
  showCartMergeInfo: Scalars['Boolean']['output'];
  tokens: TokenApi;
};

/** Represents a product */
export type MainVariantApi = BreadcrumbApi & HreflangApi & ProductApi & SlugApi & {
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
  /** List of flags */
  flags: Array<FlagApi>;
  /** The full name of the product, which consists of a prefix, name, and a suffix */
  fullName: Scalars['String']['output'];
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLinkApi>;
  /** Product id */
  id: Scalars['Int']['output'];
  /** Product images */
  images: Array<ImageApi>;
  isMainVariant: Scalars['Boolean']['output'];
  isSellingDenied: Scalars['Boolean']['output'];
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
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a product */
export type MainVariantMainImageArgsApi = {
  type?: InputMaybe<Scalars['String']['input']>;
};

export type MutationApi = {
  __typename?: 'Mutation';
  /** Fills cart based on a given order, possibly merging it with the current cart */
  AddOrderItemsToCart: CartApi;
  /** Adds a product to a product list */
  AddProductToList: ProductListApi;
  /** Add product to cart for future checkout */
  AddToCart: AddToCartResultApi;
  /** Apply new promo code for the future checkout */
  ApplyPromoCodeToCart: CartApi;
  /** Changes customer user password */
  ChangePassword: CustomerUserApi;
  /** Add a payment to the cart, or remove a payment from the cart */
  ChangePaymentInCart: CartApi;
  /** change payment in an order after the order creation (available for unpaid GoPay orders only) */
  ChangePaymentInOrder: OrderApi;
  /** Changes customer user personal data */
  ChangePersonalData: CustomerUserApi;
  /** Add a transport to the cart, or remove a transport from the cart */
  ChangeTransportInCart: CartApi;
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
  /** Pay order(create payment transaction in payment gateway) and get payment setup data for redirect or creating JS payment gateway layer */
  PayOrder: PaymentSetupCreationDataApi;
  /** Recover password using hash required from RequestPasswordRecovery */
  RecoverPassword: LoginResultApi;
  /** Refreshes access and refresh tokens */
  RefreshTokens: TokenApi;
  /** Register new customer user */
  Register: LoginResultApi;
  /** Remove product from cart */
  RemoveFromCart: CartApi;
  /** Removes a product from a product list */
  RemoveProductFromList: Maybe<ProductListApi>;
  /** Removes the product list */
  RemoveProductList: Maybe<ProductListApi>;
  /** Remove already used promo code from cart */
  RemovePromoCodeFromCart: CartApi;
  /** Request password recovery - email with hash will be sent */
  RequestPasswordRecovery: Scalars['String']['output'];
  /** Request access to personal data */
  RequestPersonalDataAccess: PersonalDataPageApi;
  /** Set default delivery address by Uuid */
  SetDefaultDeliveryAddress: CustomerUserApi;
  /** check payment status of order after callback from payment service */
  UpdatePaymentStatus: OrderApi;
};


export type MutationAddOrderItemsToCartArgsApi = {
  input: AddOrderItemsToCartInputApi;
};


export type MutationAddProductToListArgsApi = {
  input: ProductListUpdateInputApi;
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


export type MutationChangePaymentInOrderArgsApi = {
  input: ChangePaymentInOrderInputApi;
};


export type MutationChangePersonalDataArgsApi = {
  input: ChangePersonalDataInputApi;
};


export type MutationChangeTransportInCartArgsApi = {
  input: ChangeTransportInCartInputApi;
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


export type MutationRemoveProductFromListArgsApi = {
  input: ProductListUpdateInputApi;
};


export type MutationRemoveProductListArgsApi = {
  input: ProductListInputApi;
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


export type MutationUpdatePaymentStatusArgsApi = {
  orderPaymentStatusPageValidityHash: InputMaybe<Scalars['String']['input']>;
  orderUuid: Scalars['Uuid']['input'];
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
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a notification supposed to be displayed on all pages */
export type NotificationBarMainImageArgsApi = {
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

/** Represents store opening hours for a specific day */
export type OpeningHoursOfDayApi = {
  __typename?: 'OpeningHoursOfDay';
  /** Date of day with display timezone for domain */
  date: Scalars['DateTime']['output'];
  /** Day of the week */
  dayOfWeek: Scalars['Int']['output'];
  /** An array of opening hours ranges (each range contains opening and closing time) */
  openingHoursRanges: Array<OpeningHoursRangeApi>;
};

/** Represents a time period when a store is open */
export type OpeningHoursRangeApi = {
  __typename?: 'OpeningHoursRange';
  /** Closing time */
  closingTime: Scalars['String']['output'];
  /** Opening time */
  openingTime: Scalars['String']['output'];
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
  /** Indicates whether the order is paid successfully with GoPay payment type */
  isPaid: Scalars['Boolean']['output'];
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
  /** Count of the payment transactions related to the order */
  paymentTransactionsCount: Scalars['Int']['output'];
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

export type OrderPaymentsConfigApi = {
  __typename?: 'OrderPaymentsConfig';
  /** All available payment methods for the order (excluding the current one) */
  availablePayments: Array<PaymentApi>;
  /** Current payment method used in the order */
  currentPayment: PaymentApi;
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
export type ParameterValueFilterOptionApi = {
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
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a payment */
export type PaymentMainImageArgsApi = {
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
  ExportApi = 'export'
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
  /** List of flags */
  flags: Array<FlagApi>;
  /** The full name of the product, which consists of a prefix, name, and a suffix */
  fullName: Scalars['String']['output'];
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLinkApi>;
  /** Product id */
  id: Scalars['Int']['output'];
  /** Product images */
  images: Array<ImageApi>;
  isMainVariant: Scalars['Boolean']['output'];
  isSellingDenied: Scalars['Boolean']['output'];
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
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a product */
export type ProductMainImageArgsApi = {
  type?: InputMaybe<Scalars['String']['input']>;
};

/** A connection to a list of items. */
export type ProductConnectionApi = {
  __typename?: 'ProductConnection';
  /** The default ordering mode that is set for the given connection (e.g. in a category, search page, or ready category SEO mix) */
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

export type ProductListApi = {
  __typename?: 'ProductList';
  /** An array of the products in the list */
  products: Array<ProductApi>;
  /** Product list type */
  type: ProductListTypeEnumApi;
  /** Product list identifier */
  uuid: Scalars['Uuid']['output'];
};

export type ProductListInputApi = {
  /** Product list type */
  type: ProductListTypeEnumApi;
  /** Product list identifier */
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};

/** One of possible types of the product list */
export enum ProductListTypeEnumApi {
  ComparisonApi = 'COMPARISON',
  WishlistApi = 'WISHLIST'
}

export type ProductListUpdateInputApi = {
  productListInput: ProductListInputApi;
  /** Product identifier */
  productUuid: Scalars['Uuid']['input'];
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
  RelevanceApi = 'RELEVANCE'
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
  /** Returns list of articles that can be paginated using `first`, `last`, `before` and `after` keywords and filtered by `placement` */
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
  /** Returns HTML content for order with failed payment. */
  orderPaymentFailedContent: Scalars['String']['output'];
  /** Returns HTML content for order with successful payment. */
  orderPaymentSuccessfulContent: Scalars['String']['output'];
  /** Returns payments available for the given order */
  orderPayments: OrderPaymentsConfigApi;
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
  /** Find product list by UUID and type or if customer is logged, try find the the oldest list of the given type for the logged customer. The logged customer can also optionally pass the UUID of his product list. */
  productList: Maybe<ProductListApi>;
  productListsByType: Array<ProductListApi>;
  /** Returns list of ordered products that can be paginated using `first`, `last`, `before` and `after` keywords */
  products: ProductConnectionApi;
  /** Returns list of products by catalog numbers */
  productsByCatnums: Array<ProductApi>;
  /** Returns list of searched products that can be paginated using `first`, `last`, `before` and `after` keywords */
  productsSearch: ProductConnectionApi;
  /** Returns promoted categories */
  promotedCategories: Array<CategoryApi>;
  /** Returns promoted products */
  promotedProducts: Array<ProductApi>;
  /** Returns SEO settings for a specific page based on the url slug of that page */
  seoPage: Maybe<SeoPageApi>;
  /** Returns current settings */
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
  searchInput: SearchInputApi;
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
  searchInput: SearchInputApi;
};


export type QueryCartArgsApi = {
  cartInput: InputMaybe<CartInputApi>;
};


export type QueryCategoriesSearchArgsApi = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  first: InputMaybe<Scalars['Int']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  searchInput: SearchInputApi;
};


export type QueryCategoryArgsApi = {
  filter: InputMaybe<ProductFilterApi>;
  orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
  urlSlug: InputMaybe<Scalars['String']['input']>;
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


export type QueryOrderPaymentFailedContentArgsApi = {
  orderUuid: Scalars['Uuid']['input'];
};


export type QueryOrderPaymentSuccessfulContentArgsApi = {
  orderUuid: Scalars['Uuid']['input'];
};


export type QueryOrderPaymentsArgsApi = {
  orderUuid: Scalars['Uuid']['input'];
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


export type QueryProductListArgsApi = {
  input: ProductListInputApi;
};


export type QueryProductListsByTypeArgsApi = {
  productListType: ProductListTypeEnumApi;
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
};


export type QueryProductsByCatnumsArgsApi = {
  catnums: Array<Scalars['String']['input']>;
};


export type QueryProductsSearchArgsApi = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  filter: InputMaybe<ProductFilterApi>;
  first: InputMaybe<Scalars['Int']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
  search: InputMaybe<Scalars['String']['input']>;
  searchInput: SearchInputApi;
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
  /** Uuids of product lists that should be merged to the product lists of the user after registration */
  productListsUuids: Array<Scalars['Uuid']['input']>;
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
export type RegularProductApi = BreadcrumbApi & HreflangApi & ProductApi & SlugApi & {
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
  /** List of flags */
  flags: Array<FlagApi>;
  /** The full name of the product, which consists of a prefix, name, and a suffix */
  fullName: Scalars['String']['output'];
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLinkApi>;
  /** Product id */
  id: Scalars['Int']['output'];
  /** Product images */
  images: Array<ImageApi>;
  isMainVariant: Scalars['Boolean']['output'];
  isSellingDenied: Scalars['Boolean']['output'];
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
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a product */
export type RegularProductMainImageArgsApi = {
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

/** Represents search input object */
export type SearchInputApi = {
  isAutocomplete: Scalars['Boolean']['input'];
  search: Scalars['String']['input'];
  /** Unique identifier of the user who initiated the search in format UUID version 4 (^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[1-8][0-9A-Fa-f]{3}-[ABab89][0-9A-Fa-f]{3}-[0-9A-Fa-f]{12}$/) */
  userIdentifier: Scalars['Uuid']['input'];
};

/** Represents SEO settings for specific page */
export type SeoPageApi = HreflangApi & {
  __typename?: 'SeoPage';
  /** Page's canonical link */
  canonicalUrl: Maybe<Scalars['String']['output']>;
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLinkApi>;
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
  /** Timezone that is used for displaying time */
  displayTimezone: Scalars['String']['output'];
  /** Max allowed payment transactions (how many times is user allowed to try the same payment) */
  maxAllowedPaymentTransactions: Scalars['Int']['output'];
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
  type?: InputMaybe<Scalars['String']['input']>;
};


export type SliderItemMainImageArgsApi = {
  type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents entity retrievable by slug */
export type SlugApi = {
  name: Maybe<Scalars['String']['output']>;
  slug: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};

export type StoreApi = BreadcrumbApi & SlugApi & {
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
  type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents an availability in an individual store */
export type StoreAvailabilityApi = {
  __typename?: 'StoreAvailability';
  /** Detailed information about availability */
  availabilityInformation: Scalars['String']['output'];
  /** Availability status in a format suitable for usage in the code */
  availabilityStatus: AvailabilityStatusEnumApi;
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
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a transport */
export type TransportMainImageArgsApi = {
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
export type VariantApi = BreadcrumbApi & HreflangApi & ProductApi & SlugApi & {
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
  /** List of flags */
  flags: Array<FlagApi>;
  /** The full name of the product, which consists of a prefix, name, and a suffix */
  fullName: Scalars['String']['output'];
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLinkApi>;
  /** Product id */
  id: Scalars['Int']['output'];
  /** Product images */
  images: Array<ImageApi>;
  isMainVariant: Scalars['Boolean']['output'];
  isSellingDenied: Scalars['Boolean']['output'];
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
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a product */
export type VariantMainImageArgsApi = {
  type?: InputMaybe<Scalars['String']['input']>;
};

export type VideoTokenApi = {
  __typename?: 'VideoToken';
  description: Scalars['String']['output'];
  token: Scalars['String']['output'];
};

type AdvertsFragment_AdvertCode_Api = { __typename: 'AdvertCode', code: string, uuid: string, name: string, positionName: string, type: string, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> };

type AdvertsFragment_AdvertImage_Api = { __typename: 'AdvertImage', link: string | null, uuid: string, name: string, positionName: string, type: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, mainImageMobile: { __typename: 'Image', name: string | null, url: string } | null, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> };

export type AdvertsFragmentApi = AdvertsFragment_AdvertCode_Api | AdvertsFragment_AdvertImage_Api;

export type AdvertsQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type AdvertsQueryApi = { __typename?: 'Query', adverts: Array<{ __typename: 'AdvertCode', code: string, uuid: string, name: string, positionName: string, type: string, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> } | { __typename: 'AdvertImage', link: string | null, uuid: string, name: string, positionName: string, type: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, mainImageMobile: { __typename: 'Image', name: string | null, url: string } | null, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> }> };

export type ArticleDetailQueryVariablesApi = Exact<{
  urlSlug: InputMaybe<Scalars['String']['input']>;
}>;


export type ArticleDetailQueryApi = { __typename?: 'Query', article: { __typename?: 'ArticleLink' } | { __typename: 'ArticleSite', uuid: string, slug: string, placement: string, text: string | null, seoTitle: string | null, seoMetaDescription: string | null, createdAt: any, articleName: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }> } | null };

export type CookiesArticleUrlQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type CookiesArticleUrlQueryApi = { __typename?: 'Query', cookiesArticle: { __typename?: 'ArticleSite', slug: string } | null };

export type PrivacyPolicyArticleUrlQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type PrivacyPolicyArticleUrlQueryApi = { __typename?: 'Query', privacyPolicyArticle: { __typename?: 'ArticleSite', slug: string } | null };

export type TermsAndConditionsArticleUrlQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type TermsAndConditionsArticleUrlQueryApi = { __typename?: 'Query', termsAndConditionsArticle: { __typename?: 'ArticleSite', slug: string } | null };

export type ArticleDetailFragmentApi = { __typename: 'ArticleSite', uuid: string, slug: string, placement: string, text: string | null, seoTitle: string | null, seoMetaDescription: string | null, createdAt: any, articleName: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }> };

export type SimpleArticleLinkFragmentApi = { __typename: 'ArticleLink', uuid: string, name: string, url: string, placement: string, external: boolean };

export type SimpleArticleSiteFragmentApi = { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean };

type SimpleNotBlogArticleFragment_ArticleLink_Api = { __typename: 'ArticleLink', uuid: string, name: string, url: string, placement: string, external: boolean };

type SimpleNotBlogArticleFragment_ArticleSite_Api = { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean };

export type SimpleNotBlogArticleFragmentApi = SimpleNotBlogArticleFragment_ArticleLink_Api | SimpleNotBlogArticleFragment_ArticleSite_Api;

export type ArticlesQueryVariablesApi = Exact<{
  placement: InputMaybe<Array<ArticlePlacementTypeEnumApi> | ArticlePlacementTypeEnumApi>;
  first: InputMaybe<Scalars['Int']['input']>;
}>;


export type ArticlesQueryApi = { __typename?: 'Query', articles: { __typename?: 'ArticleConnection', edges: Array<{ __typename: 'ArticleEdge', node: { __typename: 'ArticleLink', uuid: string, name: string, url: string, placement: string, external: boolean } | { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean } | null } | null> | null } };

export type BlogArticleConnectionFragmentApi = { __typename: 'BlogArticleConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ __typename: 'BlogArticleEdge', node: { __typename: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any, perex: string | null, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }> } | null } | null> | null };

export type BlogArticleDetailFragmentApi = { __typename: 'BlogArticle', id: number, uuid: string, name: string, slug: string, link: string, text: string | null, publishDate: any, seoTitle: string | null, seoMetaDescription: string | null, seoH1: string | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }> };

export type ListedBlogArticleFragmentApi = { __typename: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any, perex: string | null, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }> };

export type SimpleBlogArticleFragmentApi = { __typename: 'BlogArticle', name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null };

export type BlogArticleDetailQueryVariablesApi = Exact<{
  urlSlug: InputMaybe<Scalars['String']['input']>;
}>;


export type BlogArticleDetailQueryApi = { __typename?: 'Query', blogArticle: { __typename: 'BlogArticle', id: number, uuid: string, name: string, slug: string, link: string, text: string | null, publishDate: any, seoTitle: string | null, seoMetaDescription: string | null, seoH1: string | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }> } | null };

export type BlogArticlesQueryVariablesApi = Exact<{
  first: InputMaybe<Scalars['Int']['input']>;
  onlyHomepageArticles: InputMaybe<Scalars['Boolean']['input']>;
}>;


export type BlogArticlesQueryApi = { __typename?: 'Query', blogArticles: { __typename: 'BlogArticleConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ __typename: 'BlogArticleEdge', node: { __typename: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any, perex: string | null, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }> } | null } | null> | null } };

type SimpleArticleInterfaceFragment_ArticleSite_Api = { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean };

type SimpleArticleInterfaceFragment_BlogArticle_Api = { __typename: 'BlogArticle', name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null };

export type SimpleArticleInterfaceFragmentApi = SimpleArticleInterfaceFragment_ArticleSite_Api | SimpleArticleInterfaceFragment_BlogArticle_Api;

export type TokenFragmentsApi = { __typename?: 'Token', accessToken: string, refreshToken: string };

export type LoginMutationVariablesApi = Exact<{
  email: Scalars['String']['input'];
  password: Scalars['Password']['input'];
  previousCartUuid: InputMaybe<Scalars['Uuid']['input']>;
  productListsUuids: Array<Scalars['Uuid']['input']> | Scalars['Uuid']['input'];
  shouldOverwriteCustomerUserCart?: InputMaybe<Scalars['Boolean']['input']>;
}>;


export type LoginMutationApi = { __typename?: 'Mutation', Login: { __typename?: 'LoginResult', showCartMergeInfo: boolean, tokens: { __typename?: 'Token', accessToken: string, refreshToken: string } } };

export type LogoutMutationVariablesApi = Exact<{ [key: string]: never; }>;


export type LogoutMutationApi = { __typename?: 'Mutation', Logout: boolean };

export type RefreshTokensVariablesApi = Exact<{
  refreshToken: Scalars['String']['input'];
}>;


export type RefreshTokensApi = { __typename?: 'Mutation', RefreshTokens: { __typename?: 'Token', accessToken: string, refreshToken: string } };

export type AvailabilityFragmentApi = { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi };

export type BlogCategoriesFragmentApi = { __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null };

export type BlogCategoryDetailFragmentApi = { __typename: 'BlogCategory', uuid: string, name: string, seoTitle: string | null, seoMetaDescription: string | null, articlesTotalCount: number, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, blogCategoriesTree: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }> };

export type SimpleBlogCategoryFragmentApi = { __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null };

export type BlogCategoriesVariablesApi = Exact<{ [key: string]: never; }>;


export type BlogCategoriesApi = { __typename?: 'Query', blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }> };

export type BlogCategoryArticlesVariablesApi = Exact<{
  uuid: Scalars['Uuid']['input'];
  endCursor: Scalars['String']['input'];
  pageSize: InputMaybe<Scalars['Int']['input']>;
}>;


export type BlogCategoryArticlesApi = { __typename?: 'Query', blogCategory: { __typename?: 'BlogCategory', blogArticles: { __typename: 'BlogArticleConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ __typename: 'BlogArticleEdge', node: { __typename: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any, perex: string | null, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }> } | null } | null> | null } } | null };

export type BlogCategoryQueryVariablesApi = Exact<{
  urlSlug: InputMaybe<Scalars['String']['input']>;
}>;


export type BlogCategoryQueryApi = { __typename?: 'Query', blogCategory: { __typename: 'BlogCategory', uuid: string, name: string, seoTitle: string | null, seoMetaDescription: string | null, articlesTotalCount: number, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, blogCategoriesTree: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }> } | null };

export type BlogUrlQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type BlogUrlQueryApi = { __typename?: 'Query', blogCategories: Array<{ __typename?: 'BlogCategory', link: string }> };

export type BrandDetailFragmentApi = { __typename: 'Brand', id: number, uuid: string, slug: string, name: string, seoH1: string | null, seoTitle: string | null, seoMetaDescription: string | null, description: string | null, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: ProductOrderingModeEnumApi | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null } } };

export type ListedBrandFragmentApi = { __typename: 'Brand', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null };

export type SimpleBrandFragmentApi = { __typename: 'Brand', name: string, slug: string };

export type BrandDetailQueryVariablesApi = Exact<{
  urlSlug: InputMaybe<Scalars['String']['input']>;
  orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
  filter: InputMaybe<ProductFilterApi>;
}>;


export type BrandDetailQueryApi = { __typename?: 'Query', brand: { __typename: 'Brand', id: number, uuid: string, slug: string, name: string, seoH1: string | null, seoTitle: string | null, seoMetaDescription: string | null, description: string | null, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: ProductOrderingModeEnumApi | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null } } } | null };

export type BrandsQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type BrandsQueryApi = { __typename?: 'Query', brands: Array<{ __typename: 'Brand', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }> };

export type BreadcrumbFragmentApi = { __typename: 'Link', name: string, slug: string };

export type CartFragmentApi = { __typename: 'Cart', uuid: string | null, remainingAmountWithVatForFreeTransport: string | null, promoCode: string | null, selectedPickupPlaceIdentifier: string | null, paymentGoPayBankSwift: string | null, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> }, multipleAddedProductModifications: { __typename?: 'CartMultipleAddedProductModificationsResult', notAddedProducts: Array<{ __typename?: 'MainVariant', fullName: string } | { __typename?: 'RegularProduct', fullName: string } | { __typename?: 'Variant', fullName: string }> } }, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename: 'TransportType', code: string } } | null, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } | null, roundingPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } | null };

export type CartItemFragmentApi = { __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } };

export type CartItemModificationsFragmentApi = { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }> };

export type CartModificationsFragmentApi = { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> }, multipleAddedProductModifications: { __typename?: 'CartMultipleAddedProductModificationsResult', notAddedProducts: Array<{ __typename?: 'MainVariant', fullName: string } | { __typename?: 'RegularProduct', fullName: string } | { __typename?: 'Variant', fullName: string }> } };

export type CartPaymentModificationsFragmentApi = { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean };

export type CartPromoCodeModificationsFragmentApi = { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> };

export type CartTransportModificationsFragmentApi = { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean };

export type AddOrderItemsToCartMutationVariablesApi = Exact<{
  input: AddOrderItemsToCartInputApi;
}>;


export type AddOrderItemsToCartMutationApi = { __typename?: 'Mutation', AddOrderItemsToCart: { __typename: 'Cart', uuid: string | null, remainingAmountWithVatForFreeTransport: string | null, promoCode: string | null, selectedPickupPlaceIdentifier: string | null, paymentGoPayBankSwift: string | null, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> }, multipleAddedProductModifications: { __typename?: 'CartMultipleAddedProductModificationsResult', notAddedProducts: Array<{ __typename?: 'MainVariant', fullName: string } | { __typename?: 'RegularProduct', fullName: string } | { __typename?: 'Variant', fullName: string }> } }, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename: 'TransportType', code: string } } | null, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } | null, roundingPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } | null } };

export type AddToCartMutationVariablesApi = Exact<{
  input: AddToCartInputApi;
}>;


export type AddToCartMutationApi = { __typename?: 'Mutation', AddToCart: { __typename?: 'AddToCartResult', cart: { __typename: 'Cart', uuid: string | null, remainingAmountWithVatForFreeTransport: string | null, promoCode: string | null, selectedPickupPlaceIdentifier: string | null, paymentGoPayBankSwift: string | null, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> }, multipleAddedProductModifications: { __typename?: 'CartMultipleAddedProductModificationsResult', notAddedProducts: Array<{ __typename?: 'MainVariant', fullName: string } | { __typename?: 'RegularProduct', fullName: string } | { __typename?: 'Variant', fullName: string }> } }, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename: 'TransportType', code: string } } | null, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } | null, roundingPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } | null }, addProductResult: { __typename?: 'AddProductResult', addedQuantity: number, isNew: boolean, notOnStockQuantity: number, cartItem: { __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } } } } };

export type ApplyPromoCodeToCartMutationVariablesApi = Exact<{
  input: ApplyPromoCodeToCartInputApi;
}>;


export type ApplyPromoCodeToCartMutationApi = { __typename?: 'Mutation', ApplyPromoCodeToCart: { __typename: 'Cart', uuid: string | null, remainingAmountWithVatForFreeTransport: string | null, promoCode: string | null, selectedPickupPlaceIdentifier: string | null, paymentGoPayBankSwift: string | null, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> }, multipleAddedProductModifications: { __typename?: 'CartMultipleAddedProductModificationsResult', notAddedProducts: Array<{ __typename?: 'MainVariant', fullName: string } | { __typename?: 'RegularProduct', fullName: string } | { __typename?: 'Variant', fullName: string }> } }, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename: 'TransportType', code: string } } | null, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } | null, roundingPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } | null } };

export type ChangePaymentInCartMutationVariablesApi = Exact<{
  input: ChangePaymentInCartInputApi;
}>;


export type ChangePaymentInCartMutationApi = { __typename?: 'Mutation', ChangePaymentInCart: { __typename: 'Cart', uuid: string | null, remainingAmountWithVatForFreeTransport: string | null, promoCode: string | null, selectedPickupPlaceIdentifier: string | null, paymentGoPayBankSwift: string | null, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> }, multipleAddedProductModifications: { __typename?: 'CartMultipleAddedProductModificationsResult', notAddedProducts: Array<{ __typename?: 'MainVariant', fullName: string } | { __typename?: 'RegularProduct', fullName: string } | { __typename?: 'Variant', fullName: string }> } }, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename: 'TransportType', code: string } } | null, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } | null, roundingPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } | null } };

export type ChangeTransportInCartMutationVariablesApi = Exact<{
  input: ChangeTransportInCartInputApi;
}>;


export type ChangeTransportInCartMutationApi = { __typename?: 'Mutation', ChangeTransportInCart: { __typename: 'Cart', uuid: string | null, remainingAmountWithVatForFreeTransport: string | null, promoCode: string | null, selectedPickupPlaceIdentifier: string | null, paymentGoPayBankSwift: string | null, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> }, multipleAddedProductModifications: { __typename?: 'CartMultipleAddedProductModificationsResult', notAddedProducts: Array<{ __typename?: 'MainVariant', fullName: string } | { __typename?: 'RegularProduct', fullName: string } | { __typename?: 'Variant', fullName: string }> } }, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename: 'TransportType', code: string } } | null, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } | null, roundingPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } | null } };

export type RemoveFromCartMutationVariablesApi = Exact<{
  input: RemoveFromCartInputApi;
}>;


export type RemoveFromCartMutationApi = { __typename?: 'Mutation', RemoveFromCart: { __typename: 'Cart', uuid: string | null, remainingAmountWithVatForFreeTransport: string | null, promoCode: string | null, selectedPickupPlaceIdentifier: string | null, paymentGoPayBankSwift: string | null, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> }, multipleAddedProductModifications: { __typename?: 'CartMultipleAddedProductModificationsResult', notAddedProducts: Array<{ __typename?: 'MainVariant', fullName: string } | { __typename?: 'RegularProduct', fullName: string } | { __typename?: 'Variant', fullName: string }> } }, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename: 'TransportType', code: string } } | null, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } | null, roundingPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } | null } };

export type RemovePromoCodeFromCartMutationVariablesApi = Exact<{
  input: RemovePromoCodeFromCartInputApi;
}>;


export type RemovePromoCodeFromCartMutationApi = { __typename?: 'Mutation', RemovePromoCodeFromCart: { __typename: 'Cart', uuid: string | null, remainingAmountWithVatForFreeTransport: string | null, promoCode: string | null, selectedPickupPlaceIdentifier: string | null, paymentGoPayBankSwift: string | null, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> }, multipleAddedProductModifications: { __typename?: 'CartMultipleAddedProductModificationsResult', notAddedProducts: Array<{ __typename?: 'MainVariant', fullName: string } | { __typename?: 'RegularProduct', fullName: string } | { __typename?: 'Variant', fullName: string }> } }, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename: 'TransportType', code: string } } | null, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } | null, roundingPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } | null } };

export type CartQueryVariablesApi = Exact<{
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
}>;


export type CartQueryApi = { __typename?: 'Query', cart: { __typename: 'Cart', uuid: string | null, remainingAmountWithVatForFreeTransport: string | null, promoCode: string | null, selectedPickupPlaceIdentifier: string | null, paymentGoPayBankSwift: string | null, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> }, multipleAddedProductModifications: { __typename?: 'CartMultipleAddedProductModificationsResult', notAddedProducts: Array<{ __typename?: 'MainVariant', fullName: string } | { __typename?: 'RegularProduct', fullName: string } | { __typename?: 'Variant', fullName: string }> } }, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename: 'TransportType', code: string } } | null, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } | null, roundingPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } | null } | null };

export type MinimalCartQueryVariablesApi = Exact<{
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
}>;


export type MinimalCartQueryApi = { __typename?: 'Query', cart: { __typename?: 'Cart', items: Array<{ __typename?: 'CartItem', uuid: string }>, transport: { __typename?: 'Transport', uuid: string } | null, payment: { __typename?: 'Payment', uuid: string } | null } | null };

export type CategoryDetailFragmentApi = { __typename: 'Category', id: number, uuid: string, slug: string, originalCategorySlug: string | null, name: string, description: string | null, seoH1: string | null, seoTitle: string | null, seoMetaDescription: string | null, readyCategorySeoMixLinks: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, categoryHierarchy: Array<{ __typename?: 'CategoryHierarchyItem', id: number, name: string }>, children: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', totalCount: number } }>, linkedCategories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', totalCount: number } }>, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: ProductOrderingModeEnumApi | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null } }, bestsellers: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> };

export type CategoryPreviewFragmentApi = { __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', totalCount: number } };

export type ListedCategoryConnectionFragmentApi = { __typename: 'CategoryConnection', totalCount: number, edges: Array<{ __typename: 'CategoryEdge', node: { __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', totalCount: number } } | null } | null> | null };

export type ListedCategoryFragmentApi = { __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', totalCount: number } };

export type NavigationSubCategoriesLinkFragmentApi = { __typename: 'Category', uuid: string, children: Array<{ __typename: 'Category', name: string, slug: string }> };

export type SimpleCategoryConnectionFragmentApi = { __typename: 'CategoryConnection', totalCount: number, edges: Array<{ __typename: 'CategoryEdge', node: { __typename: 'Category', uuid: string, name: string, slug: string } | null } | null> | null };

export type SimpleCategoryFragmentApi = { __typename: 'Category', uuid: string, name: string, slug: string };

export type CategoryDetailQueryVariablesApi = Exact<{
  urlSlug: InputMaybe<Scalars['String']['input']>;
  orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
  filter: InputMaybe<ProductFilterApi>;
}>;


export type CategoryDetailQueryApi = { __typename?: 'Query', category: { __typename: 'Category', id: number, uuid: string, slug: string, originalCategorySlug: string | null, name: string, description: string | null, seoH1: string | null, seoTitle: string | null, seoMetaDescription: string | null, readyCategorySeoMixLinks: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, categoryHierarchy: Array<{ __typename?: 'CategoryHierarchyItem', id: number, name: string }>, children: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', totalCount: number } }>, linkedCategories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', totalCount: number } }>, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: ProductOrderingModeEnumApi | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null } }, bestsellers: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> } | null };

export type PromotedCategoriesQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type PromotedCategoriesQueryApi = { __typename?: 'Query', promotedCategories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', totalCount: number } }> };

export type ContactMutationVariablesApi = Exact<{
  input: ContactInputApi;
}>;


export type ContactMutationApi = { __typename?: 'Mutation', Contact: boolean };

export type CountryFragmentApi = { __typename: 'Country', name: string, code: string };

export type CountriesQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type CountriesQueryApi = { __typename?: 'Query', countries: Array<{ __typename: 'Country', name: string, code: string }> };

type CustomerUserFragment_CompanyCustomerUser_Api = { __typename: 'CompanyCustomerUser', companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, uuid: string, firstName: string, lastName: string, email: string, telephone: string | null, street: string, city: string, postcode: string, newsletterSubscription: boolean, pricingGroup: string, country: { __typename: 'Country', name: string, code: string }, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }> };

type CustomerUserFragment_RegularCustomerUser_Api = { __typename: 'RegularCustomerUser', uuid: string, firstName: string, lastName: string, email: string, telephone: string | null, street: string, city: string, postcode: string, newsletterSubscription: boolean, pricingGroup: string, country: { __typename: 'Country', name: string, code: string }, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }> };

export type CustomerUserFragmentApi = CustomerUserFragment_CompanyCustomerUser_Api | CustomerUserFragment_RegularCustomerUser_Api;

export type DeliveryAddressFragmentApi = { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null };

export type ChangePasswordMutationVariablesApi = Exact<{
  email: Scalars['String']['input'];
  oldPassword: Scalars['Password']['input'];
  newPassword: Scalars['Password']['input'];
}>;


export type ChangePasswordMutationApi = { __typename?: 'Mutation', ChangePassword: { __typename?: 'CompanyCustomerUser', email: string } | { __typename?: 'RegularCustomerUser', email: string } };

export type ChangePersonalDataMutationVariablesApi = Exact<{
  input: ChangePersonalDataInputApi;
}>;


export type ChangePersonalDataMutationApi = { __typename?: 'Mutation', ChangePersonalData: { __typename: 'CompanyCustomerUser', companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, uuid: string, firstName: string, lastName: string, email: string, telephone: string | null, street: string, city: string, postcode: string, newsletterSubscription: boolean, pricingGroup: string, country: { __typename: 'Country', name: string, code: string }, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }> } | { __typename: 'RegularCustomerUser', uuid: string, firstName: string, lastName: string, email: string, telephone: string | null, street: string, city: string, postcode: string, newsletterSubscription: boolean, pricingGroup: string, country: { __typename: 'Country', name: string, code: string }, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }> } };

export type DeleteDeliveryAddressMutationVariablesApi = Exact<{
  deliveryAddressUuid: Scalars['Uuid']['input'];
}>;


export type DeleteDeliveryAddressMutationApi = { __typename?: 'Mutation', DeleteDeliveryAddress: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }> };

export type SetDefaultDeliveryAddressMutationVariablesApi = Exact<{
  deliveryAddressUuid: Scalars['Uuid']['input'];
}>;


export type SetDefaultDeliveryAddressMutationApi = { __typename?: 'Mutation', SetDefaultDeliveryAddress: { __typename?: 'CompanyCustomerUser', uuid: string, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null } | { __typename?: 'RegularCustomerUser', uuid: string, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null } };

export type CurrentCustomerUserQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type CurrentCustomerUserQueryApi = { __typename?: 'Query', currentCustomerUser: { __typename: 'CompanyCustomerUser', companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, uuid: string, firstName: string, lastName: string, email: string, telephone: string | null, street: string, city: string, postcode: string, newsletterSubscription: boolean, pricingGroup: string, country: { __typename: 'Country', name: string, code: string }, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }> } | { __typename: 'RegularCustomerUser', uuid: string, firstName: string, lastName: string, email: string, telephone: string | null, street: string, city: string, postcode: string, newsletterSubscription: boolean, pricingGroup: string, country: { __typename: 'Country', name: string, code: string }, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }> } | null };

export type IsCustomerUserRegisteredQueryVariablesApi = Exact<{
  email: Scalars['String']['input'];
}>;


export type IsCustomerUserRegisteredQueryApi = { __typename?: 'Query', isCustomerUserRegistered: boolean };

export type FlagDetailFragmentApi = { __typename: 'Flag', uuid: string, slug: string, name: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: ProductOrderingModeEnumApi | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null } }, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }> };

export type SimpleFlagFragmentApi = { __typename: 'Flag', uuid: string, name: string, rgbColor: string };

export type FlagDetailQueryVariablesApi = Exact<{
  urlSlug: InputMaybe<Scalars['String']['input']>;
  orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
  filter: InputMaybe<ProductFilterApi>;
}>;


export type FlagDetailQueryApi = { __typename?: 'Query', flag: { __typename: 'Flag', uuid: string, slug: string, name: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: ProductOrderingModeEnumApi | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null } }, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }> } | null };

export type HreflangLinksFragmentApi = { __typename?: 'HreflangLink', hreflang: string, href: string };

export type ImageFragmentApi = { __typename: 'Image', name: string | null, url: string };

export type CategoriesByColumnFragmentApi = { __typename: 'NavigationItem', name: string, link: string, categoriesByColumns: Array<{ __typename: 'NavigationItemCategoriesByColumns', columnNumber: number, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, children: Array<{ __typename: 'Category', name: string, slug: string }> }> }> };

export type ColumnCategoriesFragmentApi = { __typename: 'NavigationItemCategoriesByColumns', columnNumber: number, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, children: Array<{ __typename: 'Category', name: string, slug: string }> }> };

export type ColumnCategoryFragmentApi = { __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, children: Array<{ __typename: 'Category', name: string, slug: string }> };

export type NavigationQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type NavigationQueryApi = { __typename?: 'Query', navigation: Array<{ __typename: 'NavigationItem', name: string, link: string, categoriesByColumns: Array<{ __typename: 'NavigationItemCategoriesByColumns', columnNumber: number, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, children: Array<{ __typename: 'Category', name: string, slug: string }> }> }> }> };

export type NewsletterSubscribeMutationVariablesApi = Exact<{
  email: Scalars['String']['input'];
}>;


export type NewsletterSubscribeMutationApi = { __typename?: 'Mutation', NewsletterSubscribe: boolean };

export type NotificationBarsFragmentApi = { __typename: 'NotificationBar', text: string, rgbColor: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null };

export type NotificationBarsVariablesApi = Exact<{ [key: string]: never; }>;


export type NotificationBarsApi = { __typename?: 'Query', notificationBars: Array<{ __typename: 'NotificationBar', text: string, rgbColor: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }> | null };

export type LastOrderFragmentApi = { __typename: 'Order', pickupPlaceIdentifier: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, transportType: { __typename?: 'TransportType', code: string } }, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }, deliveryCountry: { __typename: 'Country', name: string, code: string } | null };

export type ListedOrderFragmentApi = { __typename: 'Order', uuid: string, number: string, creationDate: any, isPaid: boolean, status: string, productItems: Array<{ __typename: 'OrderItem', quantity: number }>, transport: { __typename: 'Transport', name: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }, payment: { __typename: 'Payment', name: string, type: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } };

export type OrderDetailFragmentApi = { __typename: 'Order', uuid: string, number: string, creationDate: any, status: string, firstName: string | null, lastName: string | null, email: string, telephone: string, companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, street: string, city: string, postcode: string, differentDeliveryAddress: boolean, deliveryFirstName: string | null, deliveryLastName: string | null, deliveryCompanyName: string | null, deliveryTelephone: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, note: string | null, urlHash: string, promoCode: string | null, trackingNumber: string | null, trackingUrl: string | null, paymentTransactionsCount: number, isPaid: boolean, items: Array<{ __typename: 'OrderItem', name: string, vatRate: string, quantity: number, unit: string | null, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }>, transport: { __typename: 'Transport', name: string }, payment: { __typename: 'Payment', name: string, type: string }, country: { __typename: 'Country', name: string }, deliveryCountry: { __typename: 'Country', name: string } | null, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } };

export type OrderDetailItemFragmentApi = { __typename: 'OrderItem', name: string, vatRate: string, quantity: number, unit: string | null, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } };

export type OrderListFragmentApi = { __typename: 'OrderConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ __typename: 'OrderEdge', cursor: string, node: { __typename: 'Order', uuid: string, number: string, creationDate: any, isPaid: boolean, status: string, productItems: Array<{ __typename: 'OrderItem', quantity: number }>, transport: { __typename: 'Transport', name: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }, payment: { __typename: 'Payment', name: string, type: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } } | null } | null> | null };

export type ChangePaymentInOrderMutationVariablesApi = Exact<{
  input: ChangePaymentInOrderInputApi;
}>;


export type ChangePaymentInOrderMutationApi = { __typename?: 'Mutation', ChangePaymentInOrder: { __typename?: 'Order', urlHash: string, number: string, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } } };

export type CreateOrderMutationVariablesApi = Exact<{
  firstName: Scalars['String']['input'];
  lastName: Scalars['String']['input'];
  email: Scalars['String']['input'];
  telephone: Scalars['String']['input'];
  onCompanyBehalf: Scalars['Boolean']['input'];
  companyName: InputMaybe<Scalars['String']['input']>;
  companyNumber: InputMaybe<Scalars['String']['input']>;
  companyTaxNumber: InputMaybe<Scalars['String']['input']>;
  street: Scalars['String']['input'];
  city: Scalars['String']['input'];
  postcode: Scalars['String']['input'];
  country: Scalars['String']['input'];
  differentDeliveryAddress: Scalars['Boolean']['input'];
  deliveryFirstName: InputMaybe<Scalars['String']['input']>;
  deliveryLastName: InputMaybe<Scalars['String']['input']>;
  deliveryCompanyName: InputMaybe<Scalars['String']['input']>;
  deliveryTelephone: InputMaybe<Scalars['String']['input']>;
  deliveryStreet: InputMaybe<Scalars['String']['input']>;
  deliveryCity: InputMaybe<Scalars['String']['input']>;
  deliveryPostcode: InputMaybe<Scalars['String']['input']>;
  deliveryCountry: InputMaybe<Scalars['String']['input']>;
  deliveryAddressUuid: InputMaybe<Scalars['Uuid']['input']>;
  note: InputMaybe<Scalars['String']['input']>;
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
  newsletterSubscription: InputMaybe<Scalars['Boolean']['input']>;
}>;


export type CreateOrderMutationApi = { __typename?: 'Mutation', CreateOrder: { __typename?: 'CreateOrderResult', orderCreated: boolean, order: { __typename?: 'Order', number: string, uuid: string, urlHash: string, payment: { __typename?: 'Payment', type: string } } | null, cart: { __typename: 'Cart', uuid: string | null, remainingAmountWithVatForFreeTransport: string | null, promoCode: string | null, selectedPickupPlaceIdentifier: string | null, paymentGoPayBankSwift: string | null, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> }, multipleAddedProductModifications: { __typename?: 'CartMultipleAddedProductModificationsResult', notAddedProducts: Array<{ __typename?: 'MainVariant', fullName: string } | { __typename?: 'RegularProduct', fullName: string } | { __typename?: 'Variant', fullName: string }> } }, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename: 'TransportType', code: string } } | null, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } | null, roundingPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } | null } | null } };

export type PayOrderMutationVariablesApi = Exact<{
  orderUuid: Scalars['Uuid']['input'];
}>;


export type PayOrderMutationApi = { __typename?: 'Mutation', PayOrder: { __typename?: 'PaymentSetupCreationData', goPayCreatePaymentSetup: { __typename?: 'GoPayCreatePaymentSetup', gatewayUrl: string, goPayId: string, embedJs: string } | null } };

export type UpdatePaymentStatusMutationVariablesApi = Exact<{
  orderUuid: Scalars['Uuid']['input'];
  orderPaymentStatusPageValidityHash?: InputMaybe<Scalars['String']['input']>;
}>;


export type UpdatePaymentStatusMutationApi = { __typename?: 'Mutation', UpdatePaymentStatus: { __typename?: 'Order', isPaid: boolean, paymentTransactionsCount: number, payment: { __typename?: 'Payment', type: string } } };

export type LastOrderQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type LastOrderQueryApi = { __typename?: 'Query', lastOrder: { __typename: 'Order', pickupPlaceIdentifier: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, transportType: { __typename?: 'TransportType', code: string } }, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }, deliveryCountry: { __typename: 'Country', name: string, code: string } | null } | null };

export type OrderAvailablePaymentsQueryVariablesApi = Exact<{
  orderUuid: Scalars['Uuid']['input'];
}>;


export type OrderAvailablePaymentsQueryApi = { __typename?: 'Query', orderPayments: { __typename?: 'OrderPaymentsConfig', availablePayments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, currentPayment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } } };

export type OrderDetailByHashQueryVariablesApi = Exact<{
  urlHash: InputMaybe<Scalars['String']['input']>;
}>;


export type OrderDetailByHashQueryApi = { __typename?: 'Query', order: { __typename: 'Order', uuid: string, number: string, creationDate: any, status: string, firstName: string | null, lastName: string | null, email: string, telephone: string, companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, street: string, city: string, postcode: string, differentDeliveryAddress: boolean, deliveryFirstName: string | null, deliveryLastName: string | null, deliveryCompanyName: string | null, deliveryTelephone: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, note: string | null, urlHash: string, promoCode: string | null, trackingNumber: string | null, trackingUrl: string | null, paymentTransactionsCount: number, isPaid: boolean, items: Array<{ __typename: 'OrderItem', name: string, vatRate: string, quantity: number, unit: string | null, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }>, transport: { __typename: 'Transport', name: string }, payment: { __typename: 'Payment', name: string, type: string }, country: { __typename: 'Country', name: string }, deliveryCountry: { __typename: 'Country', name: string } | null, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } } | null };

export type OrderDetailQueryVariablesApi = Exact<{
  orderNumber: InputMaybe<Scalars['String']['input']>;
}>;


export type OrderDetailQueryApi = { __typename?: 'Query', order: { __typename: 'Order', uuid: string, number: string, creationDate: any, status: string, firstName: string | null, lastName: string | null, email: string, telephone: string, companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, street: string, city: string, postcode: string, differentDeliveryAddress: boolean, deliveryFirstName: string | null, deliveryLastName: string | null, deliveryCompanyName: string | null, deliveryTelephone: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, note: string | null, urlHash: string, promoCode: string | null, trackingNumber: string | null, trackingUrl: string | null, paymentTransactionsCount: number, isPaid: boolean, items: Array<{ __typename: 'OrderItem', name: string, vatRate: string, quantity: number, unit: string | null, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }>, transport: { __typename: 'Transport', name: string }, payment: { __typename: 'Payment', name: string, type: string }, country: { __typename: 'Country', name: string }, deliveryCountry: { __typename: 'Country', name: string } | null, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } } | null };

export type OrderPaymentFailedContentQueryVariablesApi = Exact<{
  orderUuid: Scalars['Uuid']['input'];
}>;


export type OrderPaymentFailedContentQueryApi = { __typename?: 'Query', orderPaymentFailedContent: string };

export type OrderPaymentSuccessfulContentQueryVariablesApi = Exact<{
  orderUuid: Scalars['Uuid']['input'];
}>;


export type OrderPaymentSuccessfulContentQueryApi = { __typename?: 'Query', orderPaymentSuccessfulContent: string };

export type OrderSentPageContentQueryVariablesApi = Exact<{
  orderUuid: Scalars['Uuid']['input'];
}>;


export type OrderSentPageContentQueryApi = { __typename?: 'Query', orderSentPageContent: string };

export type OrdersQueryVariablesApi = Exact<{
  after: InputMaybe<Scalars['String']['input']>;
  first: InputMaybe<Scalars['Int']['input']>;
}>;


export type OrdersQueryApi = { __typename?: 'Query', orders: { __typename: 'OrderConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ __typename: 'OrderEdge', cursor: string, node: { __typename: 'Order', uuid: string, number: string, creationDate: any, isPaid: boolean, status: string, productItems: Array<{ __typename: 'OrderItem', quantity: number }>, transport: { __typename: 'Transport', name: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }, payment: { __typename: 'Payment', name: string, type: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } } | null } | null> | null } | null };

export type PageInfoFragmentApi = { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null };

export type ParameterFragmentApi = { __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> };

export type PasswordRecoveryMutationVariablesApi = Exact<{
  email: Scalars['String']['input'];
}>;


export type PasswordRecoveryMutationApi = { __typename?: 'Mutation', RequestPasswordRecovery: string };

export type RecoverPasswordMutationVariablesApi = Exact<{
  email: Scalars['String']['input'];
  hash: Scalars['String']['input'];
  newPassword: Scalars['Password']['input'];
}>;


export type RecoverPasswordMutationApi = { __typename?: 'Mutation', RecoverPassword: { __typename?: 'LoginResult', tokens: { __typename?: 'Token', accessToken: string, refreshToken: string } } };

export type SimplePaymentFragmentApi = { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null };

export type GoPaySwiftsQueryVariablesApi = Exact<{
  currencyCode: Scalars['String']['input'];
}>;


export type GoPaySwiftsQueryApi = { __typename?: 'Query', GoPaySwifts: Array<{ __typename?: 'GoPayBankSwift', name: string, imageNormalUrl: string, swift: string }> };

export type PersonalDataRequestMutationVariablesApi = Exact<{
  email: Scalars['String']['input'];
  type: InputMaybe<PersonalDataAccessRequestTypeEnumApi>;
}>;


export type PersonalDataRequestMutationApi = { __typename?: 'Mutation', RequestPersonalDataAccess: { __typename?: 'PersonalDataPage', displaySiteSlug: string, exportSiteSlug: string } };

export type PersonalDataDetailQueryVariablesApi = Exact<{
  hash: Scalars['String']['input'];
}>;


export type PersonalDataDetailQueryApi = { __typename?: 'Query', accessPersonalData: { __typename: 'PersonalData', exportLink: string, orders: Array<{ __typename: 'Order', uuid: string, city: string, companyName: string | null, number: string, creationDate: any, firstName: string | null, lastName: string | null, telephone: string, companyNumber: string | null, companyTaxNumber: string | null, street: string, postcode: string, deliveryFirstName: string | null, deliveryLastName: string | null, deliveryCompanyName: string | null, deliveryTelephone: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, country: { __typename: 'Country', name: string, code: string }, deliveryCountry: { __typename: 'Country', name: string, code: string } | null, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, transportType: { __typename?: 'TransportType', code: string } }, productItems: Array<{ __typename: 'OrderItem', name: string, vatRate: string, quantity: number, unit: string | null, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }>, totalPrice: { __typename?: 'Price', priceWithVat: string } }>, customerUser: { __typename: 'CompanyCustomerUser', companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, uuid: string, firstName: string, lastName: string, email: string, telephone: string | null, street: string, city: string, postcode: string, newsletterSubscription: boolean, pricingGroup: string, country: { __typename: 'Country', name: string, code: string }, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }> } | { __typename: 'RegularCustomerUser', uuid: string, firstName: string, lastName: string, email: string, telephone: string | null, street: string, city: string, postcode: string, newsletterSubscription: boolean, pricingGroup: string, country: { __typename: 'Country', name: string, code: string }, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }> } | null, newsletterSubscriber: { __typename: 'NewsletterSubscriber', email: string, createdAt: any } | null } };

export type PersonalDataPageTextQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type PersonalDataPageTextQueryApi = { __typename?: 'Query', personalDataPage: { __typename?: 'PersonalDataPage', displaySiteContent: string, exportSiteContent: string } | null };

export type PriceFragmentApi = { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string };

export type ProductFilterOptionsBrandsFragmentApi = { __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } };

export type ProductFilterOptionsFlagsFragmentApi = { __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } };

export type ProductFilterOptionsFragmentApi = { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null };

export type ProductFilterOptionsParametersCheckboxFragmentApi = { __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> };

export type ProductFilterOptionsParametersColorFragmentApi = { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> };

export type ProductFilterOptionsParametersSliderFragmentApi = { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null };

type ProductInProductListFragment_MainVariant_Api = { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> };

type ProductInProductListFragment_RegularProduct_Api = { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> };

type ProductInProductListFragment_Variant_Api = { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> };

export type ProductInProductListFragmentApi = ProductInProductListFragment_MainVariant_Api | ProductInProductListFragment_RegularProduct_Api | ProductInProductListFragment_Variant_Api;

export type ProductListFragmentApi = { __typename: 'ProductList', uuid: string, products: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> };

export type AddProductToListMutationVariablesApi = Exact<{
  input: ProductListUpdateInputApi;
}>;


export type AddProductToListMutationApi = { __typename?: 'Mutation', AddProductToList: { __typename: 'ProductList', uuid: string, products: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> } };

export type RemoveProductFromListMutationVariablesApi = Exact<{
  input: ProductListUpdateInputApi;
}>;


export type RemoveProductFromListMutationApi = { __typename?: 'Mutation', RemoveProductFromList: { __typename: 'ProductList', uuid: string, products: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> } | null };

export type RemoveProductListMutationVariablesApi = Exact<{
  input: ProductListInputApi;
}>;


export type RemoveProductListMutationApi = { __typename?: 'Mutation', RemoveProductList: { __typename?: 'ProductList', uuid: string } | null };

export type ProductListQueryVariablesApi = Exact<{
  input: ProductListInputApi;
}>;


export type ProductListQueryApi = { __typename?: 'Query', productList: { __typename: 'ProductList', uuid: string, products: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> } | null };

export type ListedProductConnectionFragmentApi = { __typename: 'ProductConnection', pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean }, edges: Array<{ __typename: 'ProductEdge', node: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | null } | null> | null };

export type ListedProductConnectionPreviewFragmentApi = { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: ProductOrderingModeEnumApi | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null } };

type ListedProductFragment_MainVariant_Api = { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> };

type ListedProductFragment_RegularProduct_Api = { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> };

type ListedProductFragment_Variant_Api = { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> };

export type ListedProductFragmentApi = ListedProductFragment_MainVariant_Api | ListedProductFragment_RegularProduct_Api | ListedProductFragment_Variant_Api;

export type MainVariantDetailFragmentApi = { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, ean: string | null, description: string | null, stockQuantity: number, isSellingDenied: boolean, seoTitle: string | null, seoMetaDescription: string | null, isMainVariant: boolean, variants: Array<{ __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, storeAvailabilities: Array<{ __typename: 'StoreAvailability', availabilityInformation: string, availabilityStatus: AvailabilityStatusEnumApi, store: { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename: 'Country', name: string, code: string }, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: string | null, url: string }> } | null }>, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: string | null, url: string }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }>, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }>, relatedProducts: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> };

export type ProductDetailFragmentApi = { __typename: 'RegularProduct', shortDescription: string | null, usps: Array<string>, availableStoresCount: number, id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, ean: string | null, description: string | null, stockQuantity: number, isSellingDenied: boolean, seoTitle: string | null, seoMetaDescription: string | null, isMainVariant: boolean, storeAvailabilities: Array<{ __typename: 'StoreAvailability', availabilityInformation: string, availabilityStatus: AvailabilityStatusEnumApi, store: { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename: 'Country', name: string, code: string }, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: string | null, url: string }> } | null }>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: string | null, url: string }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }>, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }>, relatedProducts: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> };

type ProductDetailInterfaceFragment_MainVariant_Api = { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, ean: string | null, description: string | null, stockQuantity: number, isSellingDenied: boolean, seoTitle: string | null, seoMetaDescription: string | null, isMainVariant: boolean, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: string | null, url: string }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }>, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }>, relatedProducts: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> };

type ProductDetailInterfaceFragment_RegularProduct_Api = { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, ean: string | null, description: string | null, stockQuantity: number, isSellingDenied: boolean, seoTitle: string | null, seoMetaDescription: string | null, isMainVariant: boolean, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: string | null, url: string }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }>, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }>, relatedProducts: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> };

type ProductDetailInterfaceFragment_Variant_Api = { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, ean: string | null, description: string | null, stockQuantity: number, isSellingDenied: boolean, seoTitle: string | null, seoMetaDescription: string | null, isMainVariant: boolean, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: string | null, url: string }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }>, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }>, relatedProducts: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> };

export type ProductDetailInterfaceFragmentApi = ProductDetailInterfaceFragment_MainVariant_Api | ProductDetailInterfaceFragment_RegularProduct_Api | ProductDetailInterfaceFragment_Variant_Api;

export type ProductPriceFragmentApi = { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean };

type SimpleProductFragment_MainVariant_Api = { __typename: 'MainVariant', id: number, uuid: string, catalogNumber: string, fullName: string, slug: string, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi } };

type SimpleProductFragment_RegularProduct_Api = { __typename: 'RegularProduct', id: number, uuid: string, catalogNumber: string, fullName: string, slug: string, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi } };

type SimpleProductFragment_Variant_Api = { __typename: 'Variant', id: number, uuid: string, catalogNumber: string, fullName: string, slug: string, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi } };

export type SimpleProductFragmentApi = SimpleProductFragment_MainVariant_Api | SimpleProductFragment_RegularProduct_Api | SimpleProductFragment_Variant_Api;

export type VideoTokenFragmentApi = { __typename: 'VideoToken', description: string, token: string };

export type BrandProductsQueryVariablesApi = Exact<{
  endCursor: Scalars['String']['input'];
  orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
  filter: InputMaybe<ProductFilterApi>;
  urlSlug: InputMaybe<Scalars['String']['input']>;
  pageSize: InputMaybe<Scalars['Int']['input']>;
}>;


export type BrandProductsQueryApi = { __typename?: 'Query', products: { __typename: 'ProductConnection', pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean }, edges: Array<{ __typename: 'ProductEdge', node: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | null } | null> | null } };

export type CategoryProductsQueryVariablesApi = Exact<{
  endCursor: Scalars['String']['input'];
  orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
  filter: InputMaybe<ProductFilterApi>;
  urlSlug: InputMaybe<Scalars['String']['input']>;
  pageSize: InputMaybe<Scalars['Int']['input']>;
}>;


export type CategoryProductsQueryApi = { __typename?: 'Query', products: { __typename: 'ProductConnection', pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean }, edges: Array<{ __typename: 'ProductEdge', node: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | null } | null> | null } };

export type FlagProductsQueryVariablesApi = Exact<{
  endCursor: Scalars['String']['input'];
  orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
  filter: InputMaybe<ProductFilterApi>;
  urlSlug: InputMaybe<Scalars['String']['input']>;
  pageSize: InputMaybe<Scalars['Int']['input']>;
}>;


export type FlagProductsQueryApi = { __typename?: 'Query', products: { __typename: 'ProductConnection', pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean }, edges: Array<{ __typename: 'ProductEdge', node: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | null } | null> | null } };

export type ProductDetailQueryVariablesApi = Exact<{
  urlSlug: InputMaybe<Scalars['String']['input']>;
}>;


export type ProductDetailQueryApi = { __typename?: 'Query', product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, ean: string | null, description: string | null, stockQuantity: number, isSellingDenied: boolean, seoTitle: string | null, seoMetaDescription: string | null, isMainVariant: boolean, variants: Array<{ __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, storeAvailabilities: Array<{ __typename: 'StoreAvailability', availabilityInformation: string, availabilityStatus: AvailabilityStatusEnumApi, store: { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename: 'Country', name: string, code: string }, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: string | null, url: string }> } | null }>, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: string | null, url: string }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }>, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }>, relatedProducts: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> } | { __typename: 'RegularProduct', shortDescription: string | null, usps: Array<string>, availableStoresCount: number, id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, ean: string | null, description: string | null, stockQuantity: number, isSellingDenied: boolean, seoTitle: string | null, seoMetaDescription: string | null, isMainVariant: boolean, storeAvailabilities: Array<{ __typename: 'StoreAvailability', availabilityInformation: string, availabilityStatus: AvailabilityStatusEnumApi, store: { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename: 'Country', name: string, code: string }, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: string | null, url: string }> } | null }>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: string | null, url: string }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }>, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }>, relatedProducts: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> } | { __typename: 'Variant', catalogNumber: string, mainVariant: { __typename?: 'MainVariant', slug: string } | null } | null };

export type ProductsByCatnumsVariablesApi = Exact<{
  catnums: Array<Scalars['String']['input']> | Scalars['String']['input'];
}>;


export type ProductsByCatnumsApi = { __typename?: 'Query', productsByCatnums: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> };

export type PromotedProductsQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type PromotedProductsQueryApi = { __typename?: 'Query', promotedProducts: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> };

export type SearchProductsQueryVariablesApi = Exact<{
  endCursor: Scalars['String']['input'];
  orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
  filter: InputMaybe<ProductFilterApi>;
  search: Scalars['String']['input'];
  pageSize: InputMaybe<Scalars['Int']['input']>;
  isAutocomplete: Scalars['Boolean']['input'];
  userIdentifier: Scalars['Uuid']['input'];
}>;


export type SearchProductsQueryApi = { __typename?: 'Query', productsSearch: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: ProductOrderingModeEnumApi | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null }, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean }, edges: Array<{ __typename: 'ProductEdge', node: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | null } | null> | null } };

export type RegistrationMutationVariablesApi = Exact<{
  input: RegistrationDataInputApi;
}>;


export type RegistrationMutationApi = { __typename?: 'Mutation', Register: { __typename?: 'LoginResult', showCartMergeInfo: boolean, tokens: { __typename?: 'Token', accessToken: string, refreshToken: string } } };

export type RobotsTxtQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type RobotsTxtQueryApi = { __typename?: 'Query', settings: { __typename?: 'Settings', seo: { __typename?: 'SeoSetting', robotsTxtContent: string | null } } | null };

export type AutocompleteSearchQueryVariablesApi = Exact<{
  search: Scalars['String']['input'];
  maxProductCount: InputMaybe<Scalars['Int']['input']>;
  maxCategoryCount: InputMaybe<Scalars['Int']['input']>;
  isAutocomplete: Scalars['Boolean']['input'];
  userIdentifier: Scalars['Uuid']['input'];
}>;


export type AutocompleteSearchQueryApi = { __typename?: 'Query', articlesSearch: Array<{ __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean } | { __typename: 'BlogArticle', name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }>, brandSearch: Array<{ __typename: 'Brand', name: string, slug: string }>, categoriesSearch: { __typename: 'CategoryConnection', totalCount: number, edges: Array<{ __typename: 'CategoryEdge', node: { __typename: 'Category', uuid: string, name: string, slug: string } | null } | null> | null }, productsSearch: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: ProductOrderingModeEnumApi | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null }, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean }, edges: Array<{ __typename: 'ProductEdge', node: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | null } | null> | null } };

export type SearchQueryVariablesApi = Exact<{
  search: Scalars['String']['input'];
  orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
  filter: InputMaybe<ProductFilterApi>;
  pageSize: InputMaybe<Scalars['Int']['input']>;
  isAutocomplete: Scalars['Boolean']['input'];
  userIdentifier: Scalars['Uuid']['input'];
}>;


export type SearchQueryApi = { __typename?: 'Query', articlesSearch: Array<{ __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean } | { __typename: 'BlogArticle', name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }>, brandSearch: Array<{ __typename: 'Brand', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }>, categoriesSearch: { __typename: 'CategoryConnection', totalCount: number, edges: Array<{ __typename: 'CategoryEdge', node: { __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', totalCount: number } } | null } | null> | null }, productsSearch: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: ProductOrderingModeEnumApi | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null } } };

export type SeoPageFragmentApi = { __typename: 'SeoPage', title: string | null, metaDescription: string | null, canonicalUrl: string | null, ogTitle: string | null, ogDescription: string | null, ogImage: { __typename: 'Image', name: string | null, url: string } | null, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }> };

export type SeoPageQueryVariablesApi = Exact<{
  pageSlug: Scalars['String']['input'];
}>;


export type SeoPageQueryApi = { __typename?: 'Query', seoPage: { __typename: 'SeoPage', title: string | null, metaDescription: string | null, canonicalUrl: string | null, ogTitle: string | null, ogDescription: string | null, ogImage: { __typename: 'Image', name: string | null, url: string } | null, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }> } | null };

export type PricingSettingFragmentApi = { __typename: 'PricingSetting', defaultCurrencyCode: string, minimumFractionDigits: number };

export type SeoSettingFragmentApi = { __typename: 'SeoSetting', title: string, titleAddOn: string, metaDescription: string };

export type SettingsQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type SettingsQueryApi = { __typename?: 'Query', settings: { __typename?: 'Settings', contactFormMainText: string, maxAllowedPaymentTransactions: number, displayTimezone: string, pricing: { __typename: 'PricingSetting', defaultCurrencyCode: string, minimumFractionDigits: number }, seo: { __typename: 'SeoSetting', title: string, titleAddOn: string, metaDescription: string } } | null };

export type SliderItemFragmentApi = { __typename: 'SliderItem', uuid: string, name: string, link: string, extendedText: string | null, extendedTextLink: string | null, webMainImage: { __typename: 'Image', name: string | null, url: string } | null, mobileMainImage: { __typename: 'Image', name: string | null, url: string } | null };

export type SliderItemsQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type SliderItemsQueryApi = { __typename?: 'Query', sliderItems: Array<{ __typename: 'SliderItem', uuid: string, name: string, link: string, extendedText: string | null, extendedTextLink: string | null, webMainImage: { __typename: 'Image', name: string | null, url: string } | null, mobileMainImage: { __typename: 'Image', name: string | null, url: string } | null }> };

export type SlugTypeQueryVariablesApi = Exact<{
  slug: Scalars['String']['input'];
}>;


export type SlugTypeQueryApi = { __typename?: 'Query', slug: { __typename: 'ArticleSite' } | { __typename: 'BlogArticle' } | { __typename: 'BlogCategory' } | { __typename: 'Brand' } | { __typename: 'Category' } | { __typename: 'Flag' } | { __typename: 'MainVariant' } | { __typename: 'RegularProduct' } | { __typename: 'Store' } | { __typename: 'Variant' } | null };

export type SlugQueryVariablesApi = Exact<{
  slug: Scalars['String']['input'];
  orderingMode: InputMaybe<ProductOrderingModeEnumApi>;
  filter: InputMaybe<ProductFilterApi>;
}>;


export type SlugQueryApi = { __typename?: 'Query', slug: { __typename: 'ArticleSite', uuid: string, slug: string, placement: string, text: string | null, seoTitle: string | null, seoMetaDescription: string | null, createdAt: any, articleName: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }> } | { __typename: 'BlogArticle', id: number, uuid: string, name: string, slug: string, link: string, text: string | null, publishDate: any, seoTitle: string | null, seoMetaDescription: string | null, seoH1: string | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }> } | { __typename: 'BlogCategory', uuid: string, name: string, seoTitle: string | null, seoMetaDescription: string | null, articlesTotalCount: number, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, blogCategoriesTree: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }> } | { __typename: 'Brand', id: number, uuid: string, slug: string, name: string, seoH1: string | null, seoTitle: string | null, seoMetaDescription: string | null, description: string | null, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: ProductOrderingModeEnumApi | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null } } } | { __typename: 'Category', id: number, uuid: string, slug: string, originalCategorySlug: string | null, name: string, description: string | null, seoH1: string | null, seoTitle: string | null, seoMetaDescription: string | null, readyCategorySeoMixLinks: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, categoryHierarchy: Array<{ __typename?: 'CategoryHierarchyItem', id: number, name: string }>, children: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', totalCount: number } }>, linkedCategories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', totalCount: number } }>, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: ProductOrderingModeEnumApi | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null } }, bestsellers: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> } | { __typename: 'Flag', uuid: string, slug: string, name: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: ProductOrderingModeEnumApi | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null } }, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }> } | { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, ean: string | null, description: string | null, stockQuantity: number, isSellingDenied: boolean, seoTitle: string | null, seoMetaDescription: string | null, isMainVariant: boolean, variants: Array<{ __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, storeAvailabilities: Array<{ __typename: 'StoreAvailability', availabilityInformation: string, availabilityStatus: AvailabilityStatusEnumApi, store: { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename: 'Country', name: string, code: string }, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: string | null, url: string }> } | null }>, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: string | null, url: string }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }>, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }>, relatedProducts: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> } | { __typename: 'RegularProduct', shortDescription: string | null, usps: Array<string>, availableStoresCount: number, id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, ean: string | null, description: string | null, stockQuantity: number, isSellingDenied: boolean, seoTitle: string | null, seoMetaDescription: string | null, isMainVariant: boolean, storeAvailabilities: Array<{ __typename: 'StoreAvailability', availabilityInformation: string, availabilityStatus: AvailabilityStatusEnumApi, store: { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename: 'Country', name: string, code: string }, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: string | null, url: string }> } | null }>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: string | null, url: string }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }>, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }>, relatedProducts: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> }> } | { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename: 'Country', name: string, code: string }, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: string | null, url: string }> } | { __typename: 'Variant', mainVariant: { __typename?: 'MainVariant', slug: string } | null } | null };

export type StoreAvailabilityFragmentApi = { __typename: 'StoreAvailability', availabilityInformation: string, availabilityStatus: AvailabilityStatusEnumApi, store: { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename: 'Country', name: string, code: string }, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: string | null, url: string }> } | null };

export type ListedStoreConnectionFragmentApi = { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } } | null } | null> | null };

export type ListedStoreFragmentApi = { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } };

export type OpeningHoursFragmentApi = { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> };

export type StoreDetailFragmentApi = { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename: 'Country', name: string, code: string }, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: string | null, url: string }> };

export type StoreDetailQueryVariablesApi = Exact<{
  urlSlug: InputMaybe<Scalars['String']['input']>;
}>;


export type StoreDetailQueryApi = { __typename?: 'Query', store: { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename: 'Country', name: string, code: string }, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: string | null, url: string }> } | null };

export type StoreQueryVariablesApi = Exact<{
  uuid: InputMaybe<Scalars['Uuid']['input']>;
}>;


export type StoreQueryApi = { __typename?: 'Query', store: { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } } | null };

export type StoresQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type StoresQueryApi = { __typename?: 'Query', stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } } | null } | null> | null } };

export type SimpleTransportFragmentApi = { __typename: 'Transport', uuid: string, name: string, description: string | null, transportType: { __typename?: 'TransportType', code: string } };

export type TransportWithAvailablePaymentsAndStoresFragmentApi = { __typename: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename: 'TransportType', code: string } };

export type TransportsQueryVariablesApi = Exact<{
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
}>;


export type TransportsQueryApi = { __typename?: 'Query', transports: Array<{ __typename: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename: 'TransportType', code: string } }> };


      export interface PossibleTypesResultData {
        possibleTypes: {
          [key: string]: string[]
        }
      }
      const result: PossibleTypesResultData = {
  "possibleTypes": {
    "Advert": [
      "AdvertCode",
      "AdvertImage"
    ],
    "ArticleInterface": [
      "ArticleSite",
      "BlogArticle"
    ],
    "Breadcrumb": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ],
    "CartInterface": [
      "Cart"
    ],
    "CustomerUser": [
      "CompanyCustomerUser",
      "RegularCustomerUser"
    ],
    "Hreflang": [
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "SeoPage",
      "Variant"
    ],
    "NotBlogArticleInterface": [
      "ArticleLink",
      "ArticleSite"
    ],
    "ParameterFilterOptionInterface": [
      "ParameterCheckboxFilterOption",
      "ParameterColorFilterOption",
      "ParameterSliderFilterOption"
    ],
    "PriceInterface": [
      "Price",
      "ProductPrice"
    ],
    "Product": [
      "MainVariant",
      "RegularProduct",
      "Variant"
    ],
    "ProductListable": [
      "Brand",
      "Category",
      "Flag"
    ],
    "Slug": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ]
  }
};
      export default result;
    
export const SimpleCategoryFragmentApi = gql`
    fragment SimpleCategoryFragment on Category {
  __typename
  uuid
  name
  slug
}
    `;
export const ImageFragmentApi = gql`
    fragment ImageFragment on Image {
  __typename
  name
  url
}
    `;
export const AdvertsFragmentApi = gql`
    fragment AdvertsFragment on Advert {
  __typename
  uuid
  name
  positionName
  type
  categories {
    ...SimpleCategoryFragment
  }
  ... on AdvertCode {
    code
  }
  ... on AdvertImage {
    link
    mainImage(type: "web") {
      ...ImageFragment
    }
    mainImageMobile: mainImage(type: "mobile") {
      ...ImageFragment
    }
  }
}
    ${SimpleCategoryFragmentApi}
${ImageFragmentApi}`;
export const BreadcrumbFragmentApi = gql`
    fragment BreadcrumbFragment on Link {
  __typename
  name
  slug
}
    `;
export const ArticleDetailFragmentApi = gql`
    fragment ArticleDetailFragment on ArticleSite {
  __typename
  uuid
  slug
  placement
  articleName: name
  text
  breadcrumb {
    ...BreadcrumbFragment
  }
  seoTitle
  seoMetaDescription
  createdAt
}
    ${BreadcrumbFragmentApi}`;
export const SimpleArticleSiteFragmentApi = gql`
    fragment SimpleArticleSiteFragment on ArticleSite {
  __typename
  uuid
  name
  slug
  placement
  external
}
    `;
export const SimpleArticleLinkFragmentApi = gql`
    fragment SimpleArticleLinkFragment on ArticleLink {
  __typename
  uuid
  name
  url
  placement
  external
}
    `;
export const SimpleNotBlogArticleFragmentApi = gql`
    fragment SimpleNotBlogArticleFragment on NotBlogArticleInterface {
  __typename
  ...SimpleArticleSiteFragment
  ...SimpleArticleLinkFragment
}
    ${SimpleArticleSiteFragmentApi}
${SimpleArticleLinkFragmentApi}`;
export const PageInfoFragmentApi = gql`
    fragment PageInfoFragment on PageInfo {
  __typename
  hasNextPage
  hasPreviousPage
  endCursor
}
    `;
export const SimpleBlogCategoryFragmentApi = gql`
    fragment SimpleBlogCategoryFragment on BlogCategory {
  __typename
  uuid
  name
  link
  parent {
    name
  }
}
    `;
export const ListedBlogArticleFragmentApi = gql`
    fragment ListedBlogArticleFragment on BlogArticle {
  __typename
  uuid
  name
  link
  mainImage {
    ...ImageFragment
  }
  publishDate
  perex
  slug
  blogCategories {
    ...SimpleBlogCategoryFragment
  }
}
    ${ImageFragmentApi}
${SimpleBlogCategoryFragmentApi}`;
export const BlogArticleConnectionFragmentApi = gql`
    fragment BlogArticleConnectionFragment on BlogArticleConnection {
  __typename
  totalCount
  pageInfo {
    ...PageInfoFragment
  }
  edges {
    __typename
    node {
      ...ListedBlogArticleFragment
    }
  }
}
    ${PageInfoFragmentApi}
${ListedBlogArticleFragmentApi}`;
export const HreflangLinksFragmentApi = gql`
    fragment HreflangLinksFragment on HreflangLink {
  hreflang
  href
}
    `;
export const BlogArticleDetailFragmentApi = gql`
    fragment BlogArticleDetailFragment on BlogArticle {
  __typename
  id
  uuid
  name
  slug
  link
  mainImage {
    ...ImageFragment
  }
  breadcrumb {
    ...BreadcrumbFragment
  }
  text
  publishDate
  seoTitle
  seoMetaDescription
  seoH1
  hreflangLinks {
    ...HreflangLinksFragment
  }
}
    ${ImageFragmentApi}
${BreadcrumbFragmentApi}
${HreflangLinksFragmentApi}`;
export const SimpleBlogArticleFragmentApi = gql`
    fragment SimpleBlogArticleFragment on BlogArticle {
  __typename
  name
  slug
  mainImage {
    ...ImageFragment
  }
}
    ${ImageFragmentApi}`;
export const SimpleArticleInterfaceFragmentApi = gql`
    fragment SimpleArticleInterfaceFragment on ArticleInterface {
  __typename
  ...SimpleArticleSiteFragment
  ...SimpleBlogArticleFragment
}
    ${SimpleArticleSiteFragmentApi}
${SimpleBlogArticleFragmentApi}`;
export const TokenFragmentsApi = gql`
    fragment TokenFragments on Token {
  accessToken
  refreshToken
}
    `;
export const BlogCategoriesFragmentApi = gql`
    fragment BlogCategoriesFragment on BlogCategory {
  ...SimpleBlogCategoryFragment
  children {
    ...SimpleBlogCategoryFragment
    children {
      ...SimpleBlogCategoryFragment
      children {
        ...SimpleBlogCategoryFragment
        children {
          ...SimpleBlogCategoryFragment
        }
      }
    }
  }
}
    ${SimpleBlogCategoryFragmentApi}`;
export const BlogCategoryDetailFragmentApi = gql`
    fragment BlogCategoryDetailFragment on BlogCategory {
  __typename
  uuid
  name
  breadcrumb {
    ...BreadcrumbFragment
  }
  seoTitle
  seoMetaDescription
  hreflangLinks {
    ...HreflangLinksFragment
  }
  blogCategoriesTree {
    ...BlogCategoriesFragment
  }
  articlesTotalCount
}
    ${BreadcrumbFragmentApi}
${HreflangLinksFragmentApi}
${BlogCategoriesFragmentApi}`;
export const ProductFilterOptionsBrandsFragmentApi = gql`
    fragment ProductFilterOptionsBrandsFragment on BrandFilterOption {
  __typename
  count
  brand {
    __typename
    uuid
    name
  }
}
    `;
export const SimpleFlagFragmentApi = gql`
    fragment SimpleFlagFragment on Flag {
  __typename
  uuid
  name
  rgbColor
}
    `;
export const ProductFilterOptionsFlagsFragmentApi = gql`
    fragment ProductFilterOptionsFlagsFragment on FlagFilterOption {
  __typename
  count
  flag {
    ...SimpleFlagFragment
  }
  isSelected
}
    ${SimpleFlagFragmentApi}`;
export const ProductFilterOptionsParametersCheckboxFragmentApi = gql`
    fragment ProductFilterOptionsParametersCheckboxFragment on ParameterCheckboxFilterOption {
  name
  uuid
  __typename
  values {
    __typename
    uuid
    text
    count
    isSelected
  }
  isCollapsed
}
    `;
export const ProductFilterOptionsParametersColorFragmentApi = gql`
    fragment ProductFilterOptionsParametersColorFragment on ParameterColorFilterOption {
  name
  uuid
  __typename
  values {
    __typename
    uuid
    text
    count
    rgbHex
    isSelected
  }
  isCollapsed
}
    `;
export const ProductFilterOptionsParametersSliderFragmentApi = gql`
    fragment ProductFilterOptionsParametersSliderFragment on ParameterSliderFilterOption {
  name
  uuid
  __typename
  minimalValue
  maximalValue
  unit {
    __typename
    name
  }
  isCollapsed
  selectedValue
  isSelectable
}
    `;
export const ProductFilterOptionsFragmentApi = gql`
    fragment ProductFilterOptionsFragment on ProductFilterOptions {
  __typename
  minimalPrice
  maximalPrice
  brands {
    ...ProductFilterOptionsBrandsFragment
  }
  inStock
  flags {
    ...ProductFilterOptionsFlagsFragment
  }
  parameters {
    ...ProductFilterOptionsParametersCheckboxFragment
    ...ProductFilterOptionsParametersColorFragment
    ...ProductFilterOptionsParametersSliderFragment
  }
}
    ${ProductFilterOptionsBrandsFragmentApi}
${ProductFilterOptionsFlagsFragmentApi}
${ProductFilterOptionsParametersCheckboxFragmentApi}
${ProductFilterOptionsParametersColorFragmentApi}
${ProductFilterOptionsParametersSliderFragmentApi}`;
export const ListedProductConnectionPreviewFragmentApi = gql`
    fragment ListedProductConnectionPreviewFragment on ProductConnection {
  __typename
  orderingMode
  defaultOrderingMode
  productFilterOptions {
    ...ProductFilterOptionsFragment
  }
  totalCount
}
    ${ProductFilterOptionsFragmentApi}`;
export const BrandDetailFragmentApi = gql`
    fragment BrandDetailFragment on Brand {
  __typename
  id
  uuid
  slug
  breadcrumb {
    ...BreadcrumbFragment
  }
  name
  seoH1
  seoTitle
  seoMetaDescription
  hreflangLinks {
    ...HreflangLinksFragment
  }
  description
  mainImage {
    ...ImageFragment
  }
  products(orderingMode: $orderingMode, filter: $filter) {
    ...ListedProductConnectionPreviewFragment
  }
}
    ${BreadcrumbFragmentApi}
${HreflangLinksFragmentApi}
${ImageFragmentApi}
${ListedProductConnectionPreviewFragmentApi}`;
export const ListedBrandFragmentApi = gql`
    fragment ListedBrandFragment on Brand {
  __typename
  uuid
  name
  slug
  mainImage {
    ...ImageFragment
  }
}
    ${ImageFragmentApi}`;
export const AvailabilityFragmentApi = gql`
    fragment AvailabilityFragment on Availability {
  __typename
  name
  status
}
    `;
export const ProductPriceFragmentApi = gql`
    fragment ProductPriceFragment on ProductPrice {
  __typename
  priceWithVat
  priceWithoutVat
  vatAmount
  isPriceFrom
}
    `;
export const SimpleBrandFragmentApi = gql`
    fragment SimpleBrandFragment on Brand {
  __typename
  name
  slug
}
    `;
export const CartItemFragmentApi = gql`
    fragment CartItemFragment on CartItem {
  __typename
  uuid
  quantity
  product {
    __typename
    id
    uuid
    slug
    ... on Variant {
      mainVariant {
        slug
      }
    }
    fullName
    catalogNumber
    stockQuantity
    flags {
      ...SimpleFlagFragment
    }
    mainImage {
      ...ImageFragment
    }
    stockQuantity
    availability {
      ...AvailabilityFragment
    }
    price {
      ...ProductPriceFragment
    }
    availableStoresCount
    unit {
      name
    }
    brand {
      ...SimpleBrandFragment
    }
    categories {
      name
    }
  }
}
    ${SimpleFlagFragmentApi}
${ImageFragmentApi}
${AvailabilityFragmentApi}
${ProductPriceFragmentApi}
${SimpleBrandFragmentApi}`;
export const PriceFragmentApi = gql`
    fragment PriceFragment on Price {
  __typename
  priceWithVat
  priceWithoutVat
  vatAmount
}
    `;
export const CartItemModificationsFragmentApi = gql`
    fragment CartItemModificationsFragment on CartItemModificationsResult {
  __typename
  noLongerListableCartItems {
    ...CartItemFragment
  }
  cartItemsWithModifiedPrice {
    ...CartItemFragment
  }
  cartItemsWithChangedQuantity {
    ...CartItemFragment
  }
  noLongerAvailableCartItemsDueToQuantity {
    ...CartItemFragment
  }
}
    ${CartItemFragmentApi}`;
export const CartTransportModificationsFragmentApi = gql`
    fragment CartTransportModificationsFragment on CartTransportModificationsResult {
  __typename
  transportPriceChanged
  transportUnavailable
  transportWeightLimitExceeded
  personalPickupStoreUnavailable
}
    `;
export const CartPaymentModificationsFragmentApi = gql`
    fragment CartPaymentModificationsFragment on CartPaymentModificationsResult {
  __typename
  paymentPriceChanged
  paymentUnavailable
}
    `;
export const CartPromoCodeModificationsFragmentApi = gql`
    fragment CartPromoCodeModificationsFragment on CartPromoCodeModificationsResult {
  __typename
  noLongerApplicablePromoCode
}
    `;
export const CartModificationsFragmentApi = gql`
    fragment CartModificationsFragment on CartModificationsResult {
  __typename
  itemModifications {
    ...CartItemModificationsFragment
  }
  transportModifications {
    ...CartTransportModificationsFragment
  }
  paymentModifications {
    ...CartPaymentModificationsFragment
  }
  promoCodeModifications {
    ...CartPromoCodeModificationsFragment
  }
  someProductWasRemovedFromEshop
  multipleAddedProductModifications {
    notAddedProducts {
      fullName
    }
  }
}
    ${CartItemModificationsFragmentApi}
${CartTransportModificationsFragmentApi}
${CartPaymentModificationsFragmentApi}
${CartPromoCodeModificationsFragmentApi}`;
export const SimplePaymentFragmentApi = gql`
    fragment SimplePaymentFragment on Payment {
  __typename
  uuid
  name
  description
  instruction
  price {
    ...PriceFragment
  }
  mainImage {
    ...ImageFragment
  }
  type
  goPayPaymentMethod {
    __typename
    identifier
    name
    paymentGroup
  }
}
    ${PriceFragmentApi}
${ImageFragmentApi}`;
export const OpeningHoursFragmentApi = gql`
    fragment OpeningHoursFragment on OpeningHours {
  isOpen
  dayOfWeek
  openingHoursOfDays {
    date
    dayOfWeek
    openingHoursRanges {
      openingTime
      closingTime
    }
  }
}
    `;
export const CountryFragmentApi = gql`
    fragment CountryFragment on Country {
  __typename
  name
  code
}
    `;
export const ListedStoreFragmentApi = gql`
    fragment ListedStoreFragment on Store {
  __typename
  slug
  identifier: uuid
  name
  description
  openingHours {
    ...OpeningHoursFragment
  }
  locationLatitude
  locationLongitude
  street
  postcode
  city
  country {
    ...CountryFragment
  }
}
    ${OpeningHoursFragmentApi}
${CountryFragmentApi}`;
export const ListedStoreConnectionFragmentApi = gql`
    fragment ListedStoreConnectionFragment on StoreConnection {
  __typename
  edges {
    __typename
    node {
      ...ListedStoreFragment
    }
  }
}
    ${ListedStoreFragmentApi}`;
export const TransportWithAvailablePaymentsAndStoresFragmentApi = gql`
    fragment TransportWithAvailablePaymentsAndStoresFragment on Transport {
  __typename
  uuid
  name
  description
  instruction
  price {
    ...PriceFragment
  }
  mainImage {
    ...ImageFragment
  }
  payments {
    ...SimplePaymentFragment
  }
  daysUntilDelivery
  stores {
    ...ListedStoreConnectionFragment
  }
  transportType {
    __typename
    code
  }
  isPersonalPickup
}
    ${PriceFragmentApi}
${ImageFragmentApi}
${SimplePaymentFragmentApi}
${ListedStoreConnectionFragmentApi}`;
export const CartFragmentApi = gql`
    fragment CartFragment on CartInterface {
  __typename
  uuid
  items {
    ...CartItemFragment
  }
  totalPrice {
    ...PriceFragment
  }
  totalItemsPrice {
    ...PriceFragment
  }
  totalDiscountPrice {
    ...PriceFragment
  }
  modifications {
    ...CartModificationsFragment
  }
  remainingAmountWithVatForFreeTransport
  transport {
    ...TransportWithAvailablePaymentsAndStoresFragment
  }
  payment {
    ...SimplePaymentFragment
  }
  promoCode
  selectedPickupPlaceIdentifier
  paymentGoPayBankSwift
  roundingPrice {
    ...PriceFragment
  }
}
    ${CartItemFragmentApi}
${PriceFragmentApi}
${CartModificationsFragmentApi}
${TransportWithAvailablePaymentsAndStoresFragmentApi}
${SimplePaymentFragmentApi}`;
export const CategoryPreviewFragmentApi = gql`
    fragment CategoryPreviewFragment on Category {
  __typename
  uuid
  name
  slug
  mainImage {
    ...ImageFragment
  }
  products {
    __typename
    totalCount
  }
}
    ${ImageFragmentApi}`;
export const ListedProductFragmentApi = gql`
    fragment ListedProductFragment on Product {
  __typename
  id
  uuid
  slug
  fullName
  name
  stockQuantity
  isSellingDenied
  flags {
    ...SimpleFlagFragment
  }
  mainImage {
    ...ImageFragment
  }
  price {
    ...ProductPriceFragment
  }
  availability {
    ...AvailabilityFragment
  }
  availableStoresCount
  catalogNumber
  brand {
    ...SimpleBrandFragment
  }
  categories {
    __typename
    name
  }
  isMainVariant
  ... on Variant {
    mainVariant {
      slug
    }
  }
}
    ${SimpleFlagFragmentApi}
${ImageFragmentApi}
${ProductPriceFragmentApi}
${AvailabilityFragmentApi}
${SimpleBrandFragmentApi}`;
export const CategoryDetailFragmentApi = gql`
    fragment CategoryDetailFragment on Category {
  __typename
  id
  uuid
  slug
  originalCategorySlug
  name
  description
  seoH1
  seoTitle
  seoMetaDescription
  readyCategorySeoMixLinks {
    __typename
    name
    slug
  }
  hreflangLinks {
    ...HreflangLinksFragment
  }
  breadcrumb {
    ...BreadcrumbFragment
  }
  categoryHierarchy {
    id
    name
  }
  children {
    ...CategoryPreviewFragment
  }
  linkedCategories {
    ...CategoryPreviewFragment
  }
  products(orderingMode: $orderingMode, filter: $filter) {
    ...ListedProductConnectionPreviewFragment
  }
  bestsellers {
    ...ListedProductFragment
  }
}
    ${HreflangLinksFragmentApi}
${BreadcrumbFragmentApi}
${CategoryPreviewFragmentApi}
${ListedProductConnectionPreviewFragmentApi}
${ListedProductFragmentApi}`;
export const ListedCategoryFragmentApi = gql`
    fragment ListedCategoryFragment on Category {
  __typename
  uuid
  name
  slug
  mainImage {
    ...ImageFragment
  }
  products {
    __typename
    totalCount
  }
}
    ${ImageFragmentApi}`;
export const ListedCategoryConnectionFragmentApi = gql`
    fragment ListedCategoryConnectionFragment on CategoryConnection {
  __typename
  totalCount
  edges {
    __typename
    node {
      ...ListedCategoryFragment
    }
  }
}
    ${ListedCategoryFragmentApi}`;
export const SimpleCategoryConnectionFragmentApi = gql`
    fragment SimpleCategoryConnectionFragment on CategoryConnection {
  __typename
  totalCount
  edges {
    __typename
    node {
      ...SimpleCategoryFragment
    }
  }
}
    ${SimpleCategoryFragmentApi}`;
export const DeliveryAddressFragmentApi = gql`
    fragment DeliveryAddressFragment on DeliveryAddress {
  __typename
  uuid
  companyName
  street
  city
  postcode
  telephone
  country {
    ...CountryFragment
  }
  firstName
  lastName
}
    ${CountryFragmentApi}`;
export const CustomerUserFragmentApi = gql`
    fragment CustomerUserFragment on CustomerUser {
  __typename
  uuid
  firstName
  lastName
  email
  telephone
  street
  city
  postcode
  country {
    ...CountryFragment
  }
  newsletterSubscription
  defaultDeliveryAddress {
    ...DeliveryAddressFragment
  }
  deliveryAddresses {
    ...DeliveryAddressFragment
  }
  ... on CompanyCustomerUser {
    companyName
    companyNumber
    companyTaxNumber
  }
  pricingGroup
}
    ${CountryFragmentApi}
${DeliveryAddressFragmentApi}`;
export const FlagDetailFragmentApi = gql`
    fragment FlagDetailFragment on Flag {
  __typename
  uuid
  slug
  breadcrumb {
    ...BreadcrumbFragment
  }
  name
  products(orderingMode: $orderingMode, filter: $filter) {
    ...ListedProductConnectionPreviewFragment
  }
  hreflangLinks {
    ...HreflangLinksFragment
  }
}
    ${BreadcrumbFragmentApi}
${ListedProductConnectionPreviewFragmentApi}
${HreflangLinksFragmentApi}`;
export const NavigationSubCategoriesLinkFragmentApi = gql`
    fragment NavigationSubCategoriesLinkFragment on Category {
  __typename
  uuid
  children {
    __typename
    name
    slug
  }
}
    `;
export const ColumnCategoryFragmentApi = gql`
    fragment ColumnCategoryFragment on Category {
  __typename
  uuid
  name
  slug
  mainImage {
    ...ImageFragment
  }
  ...NavigationSubCategoriesLinkFragment
}
    ${ImageFragmentApi}
${NavigationSubCategoriesLinkFragmentApi}`;
export const ColumnCategoriesFragmentApi = gql`
    fragment ColumnCategoriesFragment on NavigationItemCategoriesByColumns {
  __typename
  columnNumber
  categories {
    ...ColumnCategoryFragment
  }
}
    ${ColumnCategoryFragmentApi}`;
export const CategoriesByColumnFragmentApi = gql`
    fragment CategoriesByColumnFragment on NavigationItem {
  __typename
  name
  link
  categoriesByColumns {
    ...ColumnCategoriesFragment
  }
}
    ${ColumnCategoriesFragmentApi}`;
export const NotificationBarsFragmentApi = gql`
    fragment NotificationBarsFragment on NotificationBar {
  __typename
  text
  rgbColor
  mainImage {
    ...ImageFragment
  }
}
    ${ImageFragmentApi}`;
export const SimpleTransportFragmentApi = gql`
    fragment SimpleTransportFragment on Transport {
  __typename
  uuid
  name
  description
  transportType {
    code
  }
}
    `;
export const LastOrderFragmentApi = gql`
    fragment LastOrderFragment on Order {
  __typename
  transport {
    ...SimpleTransportFragment
  }
  payment {
    ...SimplePaymentFragment
  }
  pickupPlaceIdentifier
  deliveryStreet
  deliveryCity
  deliveryPostcode
  deliveryCountry {
    ...CountryFragment
  }
}
    ${SimpleTransportFragmentApi}
${SimplePaymentFragmentApi}
${CountryFragmentApi}`;
export const OrderDetailItemFragmentApi = gql`
    fragment OrderDetailItemFragment on OrderItem {
  __typename
  name
  unitPrice {
    ...PriceFragment
  }
  totalPrice {
    ...PriceFragment
  }
  vatRate
  quantity
  unit
}
    ${PriceFragmentApi}`;
export const OrderDetailFragmentApi = gql`
    fragment OrderDetailFragment on Order {
  __typename
  uuid
  number
  creationDate
  items {
    ...OrderDetailItemFragment
  }
  transport {
    __typename
    name
  }
  payment {
    __typename
    name
    type
  }
  status
  firstName
  lastName
  email
  telephone
  companyName
  companyNumber
  companyTaxNumber
  street
  city
  postcode
  country {
    __typename
    name
  }
  differentDeliveryAddress
  deliveryFirstName
  deliveryLastName
  deliveryCompanyName
  deliveryTelephone
  deliveryStreet
  deliveryCity
  deliveryPostcode
  deliveryCountry {
    __typename
    name
  }
  note
  urlHash
  promoCode
  trackingNumber
  trackingUrl
  totalPrice {
    ...PriceFragment
  }
  paymentTransactionsCount
  isPaid
}
    ${OrderDetailItemFragmentApi}
${PriceFragmentApi}`;
export const ListedOrderFragmentApi = gql`
    fragment ListedOrderFragment on Order {
  __typename
  uuid
  number
  creationDate
  productItems {
    __typename
    quantity
  }
  transport {
    __typename
    name
    mainImage {
      ...ImageFragment
    }
  }
  payment {
    __typename
    name
    type
  }
  totalPrice {
    ...PriceFragment
  }
  isPaid
  status
}
    ${ImageFragmentApi}
${PriceFragmentApi}`;
export const OrderListFragmentApi = gql`
    fragment OrderListFragment on OrderConnection {
  __typename
  totalCount
  pageInfo {
    ...PageInfoFragment
  }
  edges {
    __typename
    node {
      ...ListedOrderFragment
    }
    cursor
  }
}
    ${PageInfoFragmentApi}
${ListedOrderFragmentApi}`;
export const ParameterFragmentApi = gql`
    fragment ParameterFragment on Parameter {
  __typename
  uuid
  name
  visible
  values {
    __typename
    uuid
    text
  }
}
    `;
export const ProductInProductListFragmentApi = gql`
    fragment ProductInProductListFragment on Product {
  ...ListedProductFragment
  parameters {
    ...ParameterFragment
  }
}
    ${ListedProductFragmentApi}
${ParameterFragmentApi}`;
export const ProductListFragmentApi = gql`
    fragment ProductListFragment on ProductList {
  __typename
  uuid
  products {
    ...ProductInProductListFragment
  }
}
    ${ProductInProductListFragmentApi}`;
export const ListedProductConnectionFragmentApi = gql`
    fragment ListedProductConnectionFragment on ProductConnection {
  __typename
  pageInfo {
    hasNextPage
  }
  edges {
    __typename
    node {
      ...ListedProductFragment
    }
  }
}
    ${ListedProductFragmentApi}`;
export const VideoTokenFragmentApi = gql`
    fragment VideoTokenFragment on VideoToken {
  __typename
  description
  token
}
    `;
export const ProductDetailInterfaceFragmentApi = gql`
    fragment ProductDetailInterfaceFragment on Product {
  __typename
  id
  uuid
  slug
  fullName
  name
  namePrefix
  nameSuffix
  breadcrumb {
    ...BreadcrumbFragment
  }
  catalogNumber
  ean
  description
  images {
    ...ImageFragment
  }
  price {
    ...ProductPriceFragment
  }
  parameters {
    ...ParameterFragment
  }
  stockQuantity
  accessories {
    ...ListedProductFragment
  }
  brand {
    ...SimpleBrandFragment
  }
  categories {
    name
  }
  flags {
    ...SimpleFlagFragment
  }
  isSellingDenied
  availability {
    ...AvailabilityFragment
  }
  seoTitle
  seoMetaDescription
  hreflangLinks {
    ...HreflangLinksFragment
  }
  isMainVariant
  productVideos {
    ...VideoTokenFragment
  }
  relatedProducts {
    ...ListedProductFragment
  }
}
    ${BreadcrumbFragmentApi}
${ImageFragmentApi}
${ProductPriceFragmentApi}
${ParameterFragmentApi}
${ListedProductFragmentApi}
${SimpleBrandFragmentApi}
${SimpleFlagFragmentApi}
${AvailabilityFragmentApi}
${HreflangLinksFragmentApi}
${VideoTokenFragmentApi}`;
export const StoreDetailFragmentApi = gql`
    fragment StoreDetailFragment on Store {
  __typename
  uuid
  slug
  storeName: name
  description
  street
  city
  postcode
  country {
    ...CountryFragment
  }
  openingHours {
    ...OpeningHoursFragment
  }
  contactInfo
  specialMessage
  locationLatitude
  locationLongitude
  breadcrumb {
    ...BreadcrumbFragment
  }
  storeImages: images {
    ...ImageFragment
  }
}
    ${CountryFragmentApi}
${OpeningHoursFragmentApi}
${BreadcrumbFragmentApi}
${ImageFragmentApi}`;
export const StoreAvailabilityFragmentApi = gql`
    fragment StoreAvailabilityFragment on StoreAvailability {
  __typename
  availabilityInformation
  availabilityStatus
  store {
    ...StoreDetailFragment
  }
}
    ${StoreDetailFragmentApi}`;
export const MainVariantDetailFragmentApi = gql`
    fragment MainVariantDetailFragment on MainVariant {
  ...ProductDetailInterfaceFragment
  variants {
    ...ListedProductFragment
    storeAvailabilities {
      ...StoreAvailabilityFragment
    }
  }
}
    ${ProductDetailInterfaceFragmentApi}
${ListedProductFragmentApi}
${StoreAvailabilityFragmentApi}`;
export const ProductDetailFragmentApi = gql`
    fragment ProductDetailFragment on RegularProduct {
  ...ProductDetailInterfaceFragment
  shortDescription
  usps
  storeAvailabilities {
    ...StoreAvailabilityFragment
  }
  availableStoresCount
}
    ${ProductDetailInterfaceFragmentApi}
${StoreAvailabilityFragmentApi}`;
export const SimpleProductFragmentApi = gql`
    fragment SimpleProductFragment on Product {
  __typename
  id
  uuid
  catalogNumber
  fullName
  slug
  price {
    ...ProductPriceFragment
  }
  mainImage {
    ...ImageFragment
  }
  unit {
    name
  }
  brand {
    ...SimpleBrandFragment
  }
  categories {
    name
  }
  flags {
    ...SimpleFlagFragment
  }
  availability {
    ...AvailabilityFragment
  }
}
    ${ProductPriceFragmentApi}
${ImageFragmentApi}
${SimpleBrandFragmentApi}
${SimpleFlagFragmentApi}
${AvailabilityFragmentApi}`;
export const SeoPageFragmentApi = gql`
    fragment SeoPageFragment on SeoPage {
  __typename
  title
  metaDescription
  canonicalUrl
  ogTitle
  ogDescription
  ogImage {
    ...ImageFragment
  }
  hreflangLinks {
    ...HreflangLinksFragment
  }
}
    ${ImageFragmentApi}
${HreflangLinksFragmentApi}`;
export const PricingSettingFragmentApi = gql`
    fragment PricingSettingFragment on PricingSetting {
  __typename
  defaultCurrencyCode
  minimumFractionDigits
}
    `;
export const SeoSettingFragmentApi = gql`
    fragment SeoSettingFragment on SeoSetting {
  __typename
  title
  titleAddOn
  metaDescription
}
    `;
export const SliderItemFragmentApi = gql`
    fragment SliderItemFragment on SliderItem {
  __typename
  uuid
  name
  link
  extendedText
  extendedTextLink
  webMainImage: mainImage(type: "web") {
    ...ImageFragment
  }
  mobileMainImage: mainImage(type: "mobile") {
    ...ImageFragment
  }
}
    ${ImageFragmentApi}`;
export const AdvertsQueryDocumentApi = gql`
    query AdvertsQuery {
  adverts {
    ...AdvertsFragment
  }
}
    ${AdvertsFragmentApi}`;

export function useAdvertsQueryApi(options?: Omit<Urql.UseQueryArgs<AdvertsQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<AdvertsQueryApi, AdvertsQueryVariablesApi>({ query: AdvertsQueryDocumentApi, ...options });
};
export const ArticleDetailQueryDocumentApi = gql`
    query ArticleDetailQuery($urlSlug: String) @friendlyUrl {
  article(urlSlug: $urlSlug) {
    ...ArticleDetailFragment
  }
}
    ${ArticleDetailFragmentApi}`;

export function useArticleDetailQueryApi(options?: Omit<Urql.UseQueryArgs<ArticleDetailQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<ArticleDetailQueryApi, ArticleDetailQueryVariablesApi>({ query: ArticleDetailQueryDocumentApi, ...options });
};
export const CookiesArticleUrlQueryDocumentApi = gql`
    query CookiesArticleUrlQuery {
  cookiesArticle {
    slug
  }
}
    `;

export function useCookiesArticleUrlQueryApi(options?: Omit<Urql.UseQueryArgs<CookiesArticleUrlQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<CookiesArticleUrlQueryApi, CookiesArticleUrlQueryVariablesApi>({ query: CookiesArticleUrlQueryDocumentApi, ...options });
};
export const PrivacyPolicyArticleUrlQueryDocumentApi = gql`
    query PrivacyPolicyArticleUrlQuery {
  privacyPolicyArticle {
    slug
  }
}
    `;

export function usePrivacyPolicyArticleUrlQueryApi(options?: Omit<Urql.UseQueryArgs<PrivacyPolicyArticleUrlQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<PrivacyPolicyArticleUrlQueryApi, PrivacyPolicyArticleUrlQueryVariablesApi>({ query: PrivacyPolicyArticleUrlQueryDocumentApi, ...options });
};
export const TermsAndConditionsArticleUrlQueryDocumentApi = gql`
    query TermsAndConditionsArticleUrlQuery {
  termsAndConditionsArticle {
    slug
  }
}
    `;

export function useTermsAndConditionsArticleUrlQueryApi(options?: Omit<Urql.UseQueryArgs<TermsAndConditionsArticleUrlQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<TermsAndConditionsArticleUrlQueryApi, TermsAndConditionsArticleUrlQueryVariablesApi>({ query: TermsAndConditionsArticleUrlQueryDocumentApi, ...options });
};
export const ArticlesQueryDocumentApi = gql`
    query ArticlesQuery($placement: [ArticlePlacementTypeEnum!], $first: Int) @redisCache(ttl: 3600) {
  articles(placement: $placement, first: $first) {
    edges {
      __typename
      node {
        ...SimpleNotBlogArticleFragment
      }
    }
  }
}
    ${SimpleNotBlogArticleFragmentApi}`;

export function useArticlesQueryApi(options?: Omit<Urql.UseQueryArgs<ArticlesQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<ArticlesQueryApi, ArticlesQueryVariablesApi>({ query: ArticlesQueryDocumentApi, ...options });
};
export const BlogArticleDetailQueryDocumentApi = gql`
    query BlogArticleDetailQuery($urlSlug: String) @friendlyUrl {
  blogArticle(urlSlug: $urlSlug) {
    ...BlogArticleDetailFragment
  }
}
    ${BlogArticleDetailFragmentApi}`;

export function useBlogArticleDetailQueryApi(options?: Omit<Urql.UseQueryArgs<BlogArticleDetailQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<BlogArticleDetailQueryApi, BlogArticleDetailQueryVariablesApi>({ query: BlogArticleDetailQueryDocumentApi, ...options });
};
export const BlogArticlesQueryDocumentApi = gql`
    query BlogArticlesQuery($first: Int, $onlyHomepageArticles: Boolean) @redisCache(ttl: 3600) {
  blogArticles(first: $first, onlyHomepageArticles: $onlyHomepageArticles) {
    ...BlogArticleConnectionFragment
  }
}
    ${BlogArticleConnectionFragmentApi}`;

export function useBlogArticlesQueryApi(options?: Omit<Urql.UseQueryArgs<BlogArticlesQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<BlogArticlesQueryApi, BlogArticlesQueryVariablesApi>({ query: BlogArticlesQueryDocumentApi, ...options });
};
export const LoginMutationDocumentApi = gql`
    mutation LoginMutation($email: String!, $password: Password!, $previousCartUuid: Uuid, $productListsUuids: [Uuid!]!, $shouldOverwriteCustomerUserCart: Boolean = false) {
  Login(
    input: {email: $email, password: $password, cartUuid: $previousCartUuid, productListsUuids: $productListsUuids, shouldOverwriteCustomerUserCart: $shouldOverwriteCustomerUserCart}
  ) {
    tokens {
      ...TokenFragments
    }
    showCartMergeInfo
  }
}
    ${TokenFragmentsApi}`;

export function useLoginMutationApi() {
  return Urql.useMutation<LoginMutationApi, LoginMutationVariablesApi>(LoginMutationDocumentApi);
};
export const LogoutMutationDocumentApi = gql`
    mutation LogoutMutation {
  Logout
}
    `;

export function useLogoutMutationApi() {
  return Urql.useMutation<LogoutMutationApi, LogoutMutationVariablesApi>(LogoutMutationDocumentApi);
};
export const RefreshTokensDocumentApi = gql`
    mutation RefreshTokens($refreshToken: String!) {
  RefreshTokens(input: {refreshToken: $refreshToken}) {
    ...TokenFragments
  }
}
    ${TokenFragmentsApi}`;

export function useRefreshTokensApi() {
  return Urql.useMutation<RefreshTokensApi, RefreshTokensVariablesApi>(RefreshTokensDocumentApi);
};
export const BlogCategoriesDocumentApi = gql`
    query BlogCategories {
  blogCategories {
    ...BlogCategoriesFragment
  }
}
    ${BlogCategoriesFragmentApi}`;

export function useBlogCategoriesApi(options?: Omit<Urql.UseQueryArgs<BlogCategoriesVariablesApi>, 'query'>) {
  return Urql.useQuery<BlogCategoriesApi, BlogCategoriesVariablesApi>({ query: BlogCategoriesDocumentApi, ...options });
};
export const BlogCategoryArticlesDocumentApi = gql`
    query BlogCategoryArticles($uuid: Uuid!, $endCursor: String!, $pageSize: Int) {
  blogCategory(uuid: $uuid) {
    blogArticles(after: $endCursor, first: $pageSize) {
      ...BlogArticleConnectionFragment
    }
  }
}
    ${BlogArticleConnectionFragmentApi}`;

export function useBlogCategoryArticlesApi(options: Omit<Urql.UseQueryArgs<BlogCategoryArticlesVariablesApi>, 'query'>) {
  return Urql.useQuery<BlogCategoryArticlesApi, BlogCategoryArticlesVariablesApi>({ query: BlogCategoryArticlesDocumentApi, ...options });
};
export const BlogCategoryQueryDocumentApi = gql`
    query BlogCategoryQuery($urlSlug: String) @friendlyUrl {
  blogCategory(urlSlug: $urlSlug) {
    ...BlogCategoryDetailFragment
  }
}
    ${BlogCategoryDetailFragmentApi}`;

export function useBlogCategoryQueryApi(options?: Omit<Urql.UseQueryArgs<BlogCategoryQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<BlogCategoryQueryApi, BlogCategoryQueryVariablesApi>({ query: BlogCategoryQueryDocumentApi, ...options });
};
export const BlogUrlQueryDocumentApi = gql`
    query BlogUrlQuery {
  blogCategories {
    link
  }
}
    `;

export function useBlogUrlQueryApi(options?: Omit<Urql.UseQueryArgs<BlogUrlQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<BlogUrlQueryApi, BlogUrlQueryVariablesApi>({ query: BlogUrlQueryDocumentApi, ...options });
};
export const BrandDetailQueryDocumentApi = gql`
    query BrandDetailQuery($urlSlug: String, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter) @friendlyUrl {
  brand(urlSlug: $urlSlug) {
    ...BrandDetailFragment
  }
}
    ${BrandDetailFragmentApi}`;

export function useBrandDetailQueryApi(options?: Omit<Urql.UseQueryArgs<BrandDetailQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<BrandDetailQueryApi, BrandDetailQueryVariablesApi>({ query: BrandDetailQueryDocumentApi, ...options });
};
export const BrandsQueryDocumentApi = gql`
    query BrandsQuery {
  brands {
    ...ListedBrandFragment
  }
}
    ${ListedBrandFragmentApi}`;

export function useBrandsQueryApi(options?: Omit<Urql.UseQueryArgs<BrandsQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<BrandsQueryApi, BrandsQueryVariablesApi>({ query: BrandsQueryDocumentApi, ...options });
};
export const AddOrderItemsToCartMutationDocumentApi = gql`
    mutation AddOrderItemsToCartMutation($input: AddOrderItemsToCartInput!) {
  AddOrderItemsToCart(input: $input) {
    ...CartFragment
  }
}
    ${CartFragmentApi}`;

export function useAddOrderItemsToCartMutationApi() {
  return Urql.useMutation<AddOrderItemsToCartMutationApi, AddOrderItemsToCartMutationVariablesApi>(AddOrderItemsToCartMutationDocumentApi);
};
export const AddToCartMutationDocumentApi = gql`
    mutation AddToCartMutation($input: AddToCartInput!) {
  AddToCart(input: $input) {
    cart {
      ...CartFragment
    }
    addProductResult {
      cartItem {
        ...CartItemFragment
      }
      addedQuantity
      isNew
      notOnStockQuantity
    }
  }
}
    ${CartFragmentApi}
${CartItemFragmentApi}`;

export function useAddToCartMutationApi() {
  return Urql.useMutation<AddToCartMutationApi, AddToCartMutationVariablesApi>(AddToCartMutationDocumentApi);
};
export const ApplyPromoCodeToCartMutationDocumentApi = gql`
    mutation ApplyPromoCodeToCartMutation($input: ApplyPromoCodeToCartInput!) {
  ApplyPromoCodeToCart(input: $input) {
    ...CartFragment
  }
}
    ${CartFragmentApi}`;

export function useApplyPromoCodeToCartMutationApi() {
  return Urql.useMutation<ApplyPromoCodeToCartMutationApi, ApplyPromoCodeToCartMutationVariablesApi>(ApplyPromoCodeToCartMutationDocumentApi);
};
export const ChangePaymentInCartMutationDocumentApi = gql`
    mutation ChangePaymentInCartMutation($input: ChangePaymentInCartInput!) {
  ChangePaymentInCart(input: $input) {
    ...CartFragment
  }
}
    ${CartFragmentApi}`;

export function useChangePaymentInCartMutationApi() {
  return Urql.useMutation<ChangePaymentInCartMutationApi, ChangePaymentInCartMutationVariablesApi>(ChangePaymentInCartMutationDocumentApi);
};
export const ChangeTransportInCartMutationDocumentApi = gql`
    mutation ChangeTransportInCartMutation($input: ChangeTransportInCartInput!) {
  ChangeTransportInCart(input: $input) {
    ...CartFragment
  }
}
    ${CartFragmentApi}`;

export function useChangeTransportInCartMutationApi() {
  return Urql.useMutation<ChangeTransportInCartMutationApi, ChangeTransportInCartMutationVariablesApi>(ChangeTransportInCartMutationDocumentApi);
};
export const RemoveFromCartMutationDocumentApi = gql`
    mutation RemoveFromCartMutation($input: RemoveFromCartInput!) {
  RemoveFromCart(input: $input) {
    ...CartFragment
  }
}
    ${CartFragmentApi}`;

export function useRemoveFromCartMutationApi() {
  return Urql.useMutation<RemoveFromCartMutationApi, RemoveFromCartMutationVariablesApi>(RemoveFromCartMutationDocumentApi);
};
export const RemovePromoCodeFromCartMutationDocumentApi = gql`
    mutation RemovePromoCodeFromCartMutation($input: RemovePromoCodeFromCartInput!) {
  RemovePromoCodeFromCart(input: $input) {
    ...CartFragment
  }
}
    ${CartFragmentApi}`;

export function useRemovePromoCodeFromCartMutationApi() {
  return Urql.useMutation<RemovePromoCodeFromCartMutationApi, RemovePromoCodeFromCartMutationVariablesApi>(RemovePromoCodeFromCartMutationDocumentApi);
};
export const CartQueryDocumentApi = gql`
    query CartQuery($cartUuid: Uuid) {
  cart(cartInput: {cartUuid: $cartUuid}) {
    ...CartFragment
  }
}
    ${CartFragmentApi}`;

export function useCartQueryApi(options?: Omit<Urql.UseQueryArgs<CartQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<CartQueryApi, CartQueryVariablesApi>({ query: CartQueryDocumentApi, ...options });
};
export const MinimalCartQueryDocumentApi = gql`
    query MinimalCartQuery($cartUuid: Uuid) {
  cart(cartInput: {cartUuid: $cartUuid}) {
    items {
      uuid
    }
    transport {
      uuid
    }
    payment {
      uuid
    }
  }
}
    `;

export function useMinimalCartQueryApi(options?: Omit<Urql.UseQueryArgs<MinimalCartQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<MinimalCartQueryApi, MinimalCartQueryVariablesApi>({ query: MinimalCartQueryDocumentApi, ...options });
};
export const CategoryDetailQueryDocumentApi = gql`
    query CategoryDetailQuery($urlSlug: String, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter) @friendlyUrl {
  category(urlSlug: $urlSlug, orderingMode: $orderingMode, filter: $filter) {
    ...CategoryDetailFragment
  }
}
    ${CategoryDetailFragmentApi}`;

export function useCategoryDetailQueryApi(options?: Omit<Urql.UseQueryArgs<CategoryDetailQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<CategoryDetailQueryApi, CategoryDetailQueryVariablesApi>({ query: CategoryDetailQueryDocumentApi, ...options });
};
export const PromotedCategoriesQueryDocumentApi = gql`
    query PromotedCategoriesQuery {
  promotedCategories {
    ...ListedCategoryFragment
  }
}
    ${ListedCategoryFragmentApi}`;

export function usePromotedCategoriesQueryApi(options?: Omit<Urql.UseQueryArgs<PromotedCategoriesQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<PromotedCategoriesQueryApi, PromotedCategoriesQueryVariablesApi>({ query: PromotedCategoriesQueryDocumentApi, ...options });
};
export const ContactMutationDocumentApi = gql`
    mutation ContactMutation($input: ContactInput!) {
  Contact(input: $input)
}
    `;

export function useContactMutationApi() {
  return Urql.useMutation<ContactMutationApi, ContactMutationVariablesApi>(ContactMutationDocumentApi);
};
export const CountriesQueryDocumentApi = gql`
    query CountriesQuery {
  countries {
    ...CountryFragment
  }
}
    ${CountryFragmentApi}`;

export function useCountriesQueryApi(options?: Omit<Urql.UseQueryArgs<CountriesQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<CountriesQueryApi, CountriesQueryVariablesApi>({ query: CountriesQueryDocumentApi, ...options });
};
export const ChangePasswordMutationDocumentApi = gql`
    mutation ChangePasswordMutation($email: String!, $oldPassword: Password!, $newPassword: Password!) {
  ChangePassword(
    input: {email: $email, oldPassword: $oldPassword, newPassword: $newPassword}
  ) {
    email
  }
}
    `;

export function useChangePasswordMutationApi() {
  return Urql.useMutation<ChangePasswordMutationApi, ChangePasswordMutationVariablesApi>(ChangePasswordMutationDocumentApi);
};
export const ChangePersonalDataMutationDocumentApi = gql`
    mutation ChangePersonalDataMutation($input: ChangePersonalDataInput!) {
  ChangePersonalData(input: $input) {
    ...CustomerUserFragment
  }
}
    ${CustomerUserFragmentApi}`;

export function useChangePersonalDataMutationApi() {
  return Urql.useMutation<ChangePersonalDataMutationApi, ChangePersonalDataMutationVariablesApi>(ChangePersonalDataMutationDocumentApi);
};
export const DeleteDeliveryAddressMutationDocumentApi = gql`
    mutation DeleteDeliveryAddressMutation($deliveryAddressUuid: Uuid!) {
  DeleteDeliveryAddress(deliveryAddressUuid: $deliveryAddressUuid) {
    ...DeliveryAddressFragment
  }
}
    ${DeliveryAddressFragmentApi}`;

export function useDeleteDeliveryAddressMutationApi() {
  return Urql.useMutation<DeleteDeliveryAddressMutationApi, DeleteDeliveryAddressMutationVariablesApi>(DeleteDeliveryAddressMutationDocumentApi);
};
export const SetDefaultDeliveryAddressMutationDocumentApi = gql`
    mutation SetDefaultDeliveryAddressMutation($deliveryAddressUuid: Uuid!) {
  SetDefaultDeliveryAddress(deliveryAddressUuid: $deliveryAddressUuid) {
    uuid
    defaultDeliveryAddress {
      ...DeliveryAddressFragment
    }
  }
}
    ${DeliveryAddressFragmentApi}`;

export function useSetDefaultDeliveryAddressMutationApi() {
  return Urql.useMutation<SetDefaultDeliveryAddressMutationApi, SetDefaultDeliveryAddressMutationVariablesApi>(SetDefaultDeliveryAddressMutationDocumentApi);
};
export const CurrentCustomerUserQueryDocumentApi = gql`
    query CurrentCustomerUserQuery {
  currentCustomerUser {
    ...CustomerUserFragment
  }
}
    ${CustomerUserFragmentApi}`;

export function useCurrentCustomerUserQueryApi(options?: Omit<Urql.UseQueryArgs<CurrentCustomerUserQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<CurrentCustomerUserQueryApi, CurrentCustomerUserQueryVariablesApi>({ query: CurrentCustomerUserQueryDocumentApi, ...options });
};
export const IsCustomerUserRegisteredQueryDocumentApi = gql`
    query IsCustomerUserRegisteredQuery($email: String!) {
  isCustomerUserRegistered(email: $email)
}
    `;

export function useIsCustomerUserRegisteredQueryApi(options: Omit<Urql.UseQueryArgs<IsCustomerUserRegisteredQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<IsCustomerUserRegisteredQueryApi, IsCustomerUserRegisteredQueryVariablesApi>({ query: IsCustomerUserRegisteredQueryDocumentApi, ...options });
};
export const FlagDetailQueryDocumentApi = gql`
    query FlagDetailQuery($urlSlug: String, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter) @friendlyUrl {
  flag(urlSlug: $urlSlug) {
    ...FlagDetailFragment
  }
}
    ${FlagDetailFragmentApi}`;

export function useFlagDetailQueryApi(options?: Omit<Urql.UseQueryArgs<FlagDetailQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<FlagDetailQueryApi, FlagDetailQueryVariablesApi>({ query: FlagDetailQueryDocumentApi, ...options });
};
export const NavigationQueryDocumentApi = gql`
    query NavigationQuery @redisCache(ttl: 3600) {
  navigation {
    ...CategoriesByColumnFragment
  }
}
    ${CategoriesByColumnFragmentApi}`;

export function useNavigationQueryApi(options?: Omit<Urql.UseQueryArgs<NavigationQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<NavigationQueryApi, NavigationQueryVariablesApi>({ query: NavigationQueryDocumentApi, ...options });
};
export const NewsletterSubscribeMutationDocumentApi = gql`
    mutation NewsletterSubscribeMutation($email: String!) {
  NewsletterSubscribe(input: {email: $email})
}
    `;

export function useNewsletterSubscribeMutationApi() {
  return Urql.useMutation<NewsletterSubscribeMutationApi, NewsletterSubscribeMutationVariablesApi>(NewsletterSubscribeMutationDocumentApi);
};
export const NotificationBarsDocumentApi = gql`
    query NotificationBars @redisCache(ttl: 3600) {
  notificationBars {
    ...NotificationBarsFragment
  }
}
    ${NotificationBarsFragmentApi}`;

export function useNotificationBarsApi(options?: Omit<Urql.UseQueryArgs<NotificationBarsVariablesApi>, 'query'>) {
  return Urql.useQuery<NotificationBarsApi, NotificationBarsVariablesApi>({ query: NotificationBarsDocumentApi, ...options });
};
export const ChangePaymentInOrderMutationDocumentApi = gql`
    mutation ChangePaymentInOrderMutation($input: ChangePaymentInOrderInput!) {
  ChangePaymentInOrder(input: $input) {
    urlHash
    number
    payment {
      ...SimplePaymentFragment
    }
  }
}
    ${SimplePaymentFragmentApi}`;

export function useChangePaymentInOrderMutationApi() {
  return Urql.useMutation<ChangePaymentInOrderMutationApi, ChangePaymentInOrderMutationVariablesApi>(ChangePaymentInOrderMutationDocumentApi);
};
export const CreateOrderMutationDocumentApi = gql`
    mutation CreateOrderMutation($firstName: String!, $lastName: String!, $email: String!, $telephone: String!, $onCompanyBehalf: Boolean!, $companyName: String, $companyNumber: String, $companyTaxNumber: String, $street: String!, $city: String!, $postcode: String!, $country: String!, $differentDeliveryAddress: Boolean!, $deliveryFirstName: String, $deliveryLastName: String, $deliveryCompanyName: String, $deliveryTelephone: String, $deliveryStreet: String, $deliveryCity: String, $deliveryPostcode: String, $deliveryCountry: String, $deliveryAddressUuid: Uuid, $note: String, $cartUuid: Uuid, $newsletterSubscription: Boolean) {
  CreateOrder(
    input: {firstName: $firstName, lastName: $lastName, email: $email, telephone: $telephone, onCompanyBehalf: $onCompanyBehalf, companyName: $companyName, companyNumber: $companyNumber, companyTaxNumber: $companyTaxNumber, street: $street, city: $city, postcode: $postcode, country: $country, differentDeliveryAddress: $differentDeliveryAddress, deliveryFirstName: $deliveryFirstName, deliveryLastName: $deliveryLastName, deliveryCompanyName: $deliveryCompanyName, deliveryTelephone: $deliveryTelephone, deliveryStreet: $deliveryStreet, deliveryCity: $deliveryCity, deliveryPostcode: $deliveryPostcode, deliveryCountry: $deliveryCountry, deliveryAddressUuid: $deliveryAddressUuid, note: $note, cartUuid: $cartUuid, newsletterSubscription: $newsletterSubscription}
  ) {
    orderCreated
    order {
      number
      uuid
      urlHash
      payment {
        type
      }
    }
    cart {
      ...CartFragment
    }
  }
}
    ${CartFragmentApi}`;

export function useCreateOrderMutationApi() {
  return Urql.useMutation<CreateOrderMutationApi, CreateOrderMutationVariablesApi>(CreateOrderMutationDocumentApi);
};
export const PayOrderMutationDocumentApi = gql`
    mutation PayOrderMutation($orderUuid: Uuid!) {
  PayOrder(orderUuid: $orderUuid) {
    goPayCreatePaymentSetup {
      gatewayUrl
      goPayId
      embedJs
    }
  }
}
    `;

export function usePayOrderMutationApi() {
  return Urql.useMutation<PayOrderMutationApi, PayOrderMutationVariablesApi>(PayOrderMutationDocumentApi);
};
export const UpdatePaymentStatusMutationDocumentApi = gql`
    mutation UpdatePaymentStatusMutation($orderUuid: Uuid!, $orderPaymentStatusPageValidityHash: String = null) {
  UpdatePaymentStatus(
    orderUuid: $orderUuid
    orderPaymentStatusPageValidityHash: $orderPaymentStatusPageValidityHash
  ) {
    isPaid
    paymentTransactionsCount
    payment {
      type
    }
  }
}
    `;

export function useUpdatePaymentStatusMutationApi() {
  return Urql.useMutation<UpdatePaymentStatusMutationApi, UpdatePaymentStatusMutationVariablesApi>(UpdatePaymentStatusMutationDocumentApi);
};
export const LastOrderQueryDocumentApi = gql`
    query LastOrderQuery {
  lastOrder {
    ...LastOrderFragment
  }
}
    ${LastOrderFragmentApi}`;

export function useLastOrderQueryApi(options?: Omit<Urql.UseQueryArgs<LastOrderQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<LastOrderQueryApi, LastOrderQueryVariablesApi>({ query: LastOrderQueryDocumentApi, ...options });
};
export const OrderAvailablePaymentsQueryDocumentApi = gql`
    query OrderAvailablePaymentsQuery($orderUuid: Uuid!) {
  orderPayments(orderUuid: $orderUuid) {
    availablePayments {
      ...SimplePaymentFragment
    }
    currentPayment {
      ...SimplePaymentFragment
    }
  }
}
    ${SimplePaymentFragmentApi}`;

export function useOrderAvailablePaymentsQueryApi(options: Omit<Urql.UseQueryArgs<OrderAvailablePaymentsQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<OrderAvailablePaymentsQueryApi, OrderAvailablePaymentsQueryVariablesApi>({ query: OrderAvailablePaymentsQueryDocumentApi, ...options });
};
export const OrderDetailByHashQueryDocumentApi = gql`
    query OrderDetailByHashQuery($urlHash: String) {
  order(urlHash: $urlHash) {
    ...OrderDetailFragment
  }
}
    ${OrderDetailFragmentApi}`;

export function useOrderDetailByHashQueryApi(options?: Omit<Urql.UseQueryArgs<OrderDetailByHashQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<OrderDetailByHashQueryApi, OrderDetailByHashQueryVariablesApi>({ query: OrderDetailByHashQueryDocumentApi, ...options });
};
export const OrderDetailQueryDocumentApi = gql`
    query OrderDetailQuery($orderNumber: String) {
  order(orderNumber: $orderNumber) {
    ...OrderDetailFragment
  }
}
    ${OrderDetailFragmentApi}`;

export function useOrderDetailQueryApi(options?: Omit<Urql.UseQueryArgs<OrderDetailQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<OrderDetailQueryApi, OrderDetailQueryVariablesApi>({ query: OrderDetailQueryDocumentApi, ...options });
};
export const OrderPaymentFailedContentQueryDocumentApi = gql`
    query OrderPaymentFailedContentQuery($orderUuid: Uuid!) {
  orderPaymentFailedContent(orderUuid: $orderUuid)
}
    `;

export function useOrderPaymentFailedContentQueryApi(options: Omit<Urql.UseQueryArgs<OrderPaymentFailedContentQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<OrderPaymentFailedContentQueryApi, OrderPaymentFailedContentQueryVariablesApi>({ query: OrderPaymentFailedContentQueryDocumentApi, ...options });
};
export const OrderPaymentSuccessfulContentQueryDocumentApi = gql`
    query OrderPaymentSuccessfulContentQuery($orderUuid: Uuid!) {
  orderPaymentSuccessfulContent(orderUuid: $orderUuid)
}
    `;

export function useOrderPaymentSuccessfulContentQueryApi(options: Omit<Urql.UseQueryArgs<OrderPaymentSuccessfulContentQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<OrderPaymentSuccessfulContentQueryApi, OrderPaymentSuccessfulContentQueryVariablesApi>({ query: OrderPaymentSuccessfulContentQueryDocumentApi, ...options });
};
export const OrderSentPageContentQueryDocumentApi = gql`
    query OrderSentPageContentQuery($orderUuid: Uuid!) {
  orderSentPageContent(orderUuid: $orderUuid)
}
    `;

export function useOrderSentPageContentQueryApi(options: Omit<Urql.UseQueryArgs<OrderSentPageContentQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<OrderSentPageContentQueryApi, OrderSentPageContentQueryVariablesApi>({ query: OrderSentPageContentQueryDocumentApi, ...options });
};
export const OrdersQueryDocumentApi = gql`
    query OrdersQuery($after: String, $first: Int) {
  orders(after: $after, first: $first) {
    ...OrderListFragment
  }
}
    ${OrderListFragmentApi}`;

export function useOrdersQueryApi(options?: Omit<Urql.UseQueryArgs<OrdersQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<OrdersQueryApi, OrdersQueryVariablesApi>({ query: OrdersQueryDocumentApi, ...options });
};
export const PasswordRecoveryMutationDocumentApi = gql`
    mutation PasswordRecoveryMutation($email: String!) {
  RequestPasswordRecovery(email: $email)
}
    `;

export function usePasswordRecoveryMutationApi() {
  return Urql.useMutation<PasswordRecoveryMutationApi, PasswordRecoveryMutationVariablesApi>(PasswordRecoveryMutationDocumentApi);
};
export const RecoverPasswordMutationDocumentApi = gql`
    mutation RecoverPasswordMutation($email: String!, $hash: String!, $newPassword: Password!) {
  RecoverPassword(input: {email: $email, hash: $hash, newPassword: $newPassword}) {
    tokens {
      ...TokenFragments
    }
  }
}
    ${TokenFragmentsApi}`;

export function useRecoverPasswordMutationApi() {
  return Urql.useMutation<RecoverPasswordMutationApi, RecoverPasswordMutationVariablesApi>(RecoverPasswordMutationDocumentApi);
};
export const GoPaySwiftsQueryDocumentApi = gql`
    query GoPaySwiftsQuery($currencyCode: String!) {
  GoPaySwifts(currencyCode: $currencyCode) {
    name
    imageNormalUrl
    swift
  }
}
    `;

export function useGoPaySwiftsQueryApi(options: Omit<Urql.UseQueryArgs<GoPaySwiftsQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<GoPaySwiftsQueryApi, GoPaySwiftsQueryVariablesApi>({ query: GoPaySwiftsQueryDocumentApi, ...options });
};
export const PersonalDataRequestMutationDocumentApi = gql`
    mutation PersonalDataRequestMutation($email: String!, $type: PersonalDataAccessRequestTypeEnum) {
  RequestPersonalDataAccess(input: {email: $email, type: $type}) {
    displaySiteSlug
    exportSiteSlug
  }
}
    `;

export function usePersonalDataRequestMutationApi() {
  return Urql.useMutation<PersonalDataRequestMutationApi, PersonalDataRequestMutationVariablesApi>(PersonalDataRequestMutationDocumentApi);
};
export const PersonalDataDetailQueryDocumentApi = gql`
    query PersonalDataDetailQuery($hash: String!) {
  accessPersonalData(hash: $hash) {
    __typename
    orders {
      __typename
      uuid
      city
      companyName
      number
      creationDate
      firstName
      lastName
      telephone
      companyNumber
      companyTaxNumber
      street
      city
      postcode
      country {
        ...CountryFragment
      }
      deliveryFirstName
      deliveryLastName
      deliveryCompanyName
      deliveryTelephone
      deliveryStreet
      deliveryCity
      deliveryPostcode
      deliveryCountry {
        ...CountryFragment
      }
      payment {
        ...SimplePaymentFragment
      }
      transport {
        ...SimpleTransportFragment
      }
      productItems {
        ...OrderDetailItemFragment
      }
      totalPrice {
        priceWithVat
      }
    }
    customerUser {
      ...CustomerUserFragment
    }
    newsletterSubscriber {
      __typename
      email
      createdAt
    }
    exportLink
  }
}
    ${CountryFragmentApi}
${SimplePaymentFragmentApi}
${SimpleTransportFragmentApi}
${OrderDetailItemFragmentApi}
${CustomerUserFragmentApi}`;

export function usePersonalDataDetailQueryApi(options: Omit<Urql.UseQueryArgs<PersonalDataDetailQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<PersonalDataDetailQueryApi, PersonalDataDetailQueryVariablesApi>({ query: PersonalDataDetailQueryDocumentApi, ...options });
};
export const PersonalDataPageTextQueryDocumentApi = gql`
    query PersonalDataPageTextQuery {
  personalDataPage {
    displaySiteContent
    exportSiteContent
  }
}
    `;

export function usePersonalDataPageTextQueryApi(options?: Omit<Urql.UseQueryArgs<PersonalDataPageTextQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<PersonalDataPageTextQueryApi, PersonalDataPageTextQueryVariablesApi>({ query: PersonalDataPageTextQueryDocumentApi, ...options });
};
export const AddProductToListMutationDocumentApi = gql`
    mutation AddProductToListMutation($input: ProductListUpdateInput!) {
  AddProductToList(input: $input) {
    ...ProductListFragment
  }
}
    ${ProductListFragmentApi}`;

export function useAddProductToListMutationApi() {
  return Urql.useMutation<AddProductToListMutationApi, AddProductToListMutationVariablesApi>(AddProductToListMutationDocumentApi);
};
export const RemoveProductFromListMutationDocumentApi = gql`
    mutation RemoveProductFromListMutation($input: ProductListUpdateInput!) {
  RemoveProductFromList(input: $input) {
    ...ProductListFragment
  }
}
    ${ProductListFragmentApi}`;

export function useRemoveProductFromListMutationApi() {
  return Urql.useMutation<RemoveProductFromListMutationApi, RemoveProductFromListMutationVariablesApi>(RemoveProductFromListMutationDocumentApi);
};
export const RemoveProductListMutationDocumentApi = gql`
    mutation RemoveProductListMutation($input: ProductListInput!) {
  RemoveProductList(input: $input) {
    uuid
  }
}
    `;

export function useRemoveProductListMutationApi() {
  return Urql.useMutation<RemoveProductListMutationApi, RemoveProductListMutationVariablesApi>(RemoveProductListMutationDocumentApi);
};
export const ProductListQueryDocumentApi = gql`
    query ProductListQuery($input: ProductListInput!) {
  productList(input: $input) {
    ...ProductListFragment
  }
}
    ${ProductListFragmentApi}`;

export function useProductListQueryApi(options: Omit<Urql.UseQueryArgs<ProductListQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<ProductListQueryApi, ProductListQueryVariablesApi>({ query: ProductListQueryDocumentApi, ...options });
};
export const BrandProductsQueryDocumentApi = gql`
    query BrandProductsQuery($endCursor: String!, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter, $urlSlug: String, $pageSize: Int) {
  products(
    brandSlug: $urlSlug
    after: $endCursor
    orderingMode: $orderingMode
    filter: $filter
    first: $pageSize
  ) {
    ...ListedProductConnectionFragment
  }
}
    ${ListedProductConnectionFragmentApi}`;

export function useBrandProductsQueryApi(options: Omit<Urql.UseQueryArgs<BrandProductsQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<BrandProductsQueryApi, BrandProductsQueryVariablesApi>({ query: BrandProductsQueryDocumentApi, ...options });
};
export const CategoryProductsQueryDocumentApi = gql`
    query CategoryProductsQuery($endCursor: String!, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter, $urlSlug: String, $pageSize: Int) {
  products(
    categorySlug: $urlSlug
    after: $endCursor
    orderingMode: $orderingMode
    filter: $filter
    first: $pageSize
  ) {
    ...ListedProductConnectionFragment
  }
}
    ${ListedProductConnectionFragmentApi}`;

export function useCategoryProductsQueryApi(options: Omit<Urql.UseQueryArgs<CategoryProductsQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<CategoryProductsQueryApi, CategoryProductsQueryVariablesApi>({ query: CategoryProductsQueryDocumentApi, ...options });
};
export const FlagProductsQueryDocumentApi = gql`
    query FlagProductsQuery($endCursor: String!, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter, $urlSlug: String, $pageSize: Int) {
  products(
    flagSlug: $urlSlug
    after: $endCursor
    orderingMode: $orderingMode
    filter: $filter
    first: $pageSize
  ) {
    ...ListedProductConnectionFragment
  }
}
    ${ListedProductConnectionFragmentApi}`;

export function useFlagProductsQueryApi(options: Omit<Urql.UseQueryArgs<FlagProductsQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<FlagProductsQueryApi, FlagProductsQueryVariablesApi>({ query: FlagProductsQueryDocumentApi, ...options });
};
export const ProductDetailQueryDocumentApi = gql`
    query ProductDetailQuery($urlSlug: String) @friendlyUrl {
  product(urlSlug: $urlSlug) {
    ... on Product {
      ...ProductDetailFragment
    }
    ... on MainVariant {
      ...MainVariantDetailFragment
    }
    ... on Variant {
      __typename
      catalogNumber
      mainVariant {
        slug
      }
    }
  }
}
    ${ProductDetailFragmentApi}
${MainVariantDetailFragmentApi}`;

export function useProductDetailQueryApi(options?: Omit<Urql.UseQueryArgs<ProductDetailQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<ProductDetailQueryApi, ProductDetailQueryVariablesApi>({ query: ProductDetailQueryDocumentApi, ...options });
};
export const ProductsByCatnumsDocumentApi = gql`
    query ProductsByCatnums($catnums: [String!]!) {
  productsByCatnums(catnums: $catnums) {
    ...ListedProductFragment
  }
}
    ${ListedProductFragmentApi}`;

export function useProductsByCatnumsApi(options: Omit<Urql.UseQueryArgs<ProductsByCatnumsVariablesApi>, 'query'>) {
  return Urql.useQuery<ProductsByCatnumsApi, ProductsByCatnumsVariablesApi>({ query: ProductsByCatnumsDocumentApi, ...options });
};
export const PromotedProductsQueryDocumentApi = gql`
    query PromotedProductsQuery {
  promotedProducts {
    ...ListedProductFragment
  }
}
    ${ListedProductFragmentApi}`;

export function usePromotedProductsQueryApi(options?: Omit<Urql.UseQueryArgs<PromotedProductsQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<PromotedProductsQueryApi, PromotedProductsQueryVariablesApi>({ query: PromotedProductsQueryDocumentApi, ...options });
};
export const SearchProductsQueryDocumentApi = gql`
    query SearchProductsQuery($endCursor: String!, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter, $search: String!, $pageSize: Int, $isAutocomplete: Boolean!, $userIdentifier: Uuid!) {
  productsSearch(
    after: $endCursor
    orderingMode: $orderingMode
    filter: $filter
    first: $pageSize
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier}
  ) {
    orderingMode
    defaultOrderingMode
    totalCount
    productFilterOptions {
      ...ProductFilterOptionsFragment
    }
    ...ListedProductConnectionFragment
  }
}
    ${ProductFilterOptionsFragmentApi}
${ListedProductConnectionFragmentApi}`;

export function useSearchProductsQueryApi(options: Omit<Urql.UseQueryArgs<SearchProductsQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<SearchProductsQueryApi, SearchProductsQueryVariablesApi>({ query: SearchProductsQueryDocumentApi, ...options });
};
export const RegistrationMutationDocumentApi = gql`
    mutation RegistrationMutation($input: RegistrationDataInput!) {
  Register(input: $input) {
    tokens {
      ...TokenFragments
    }
    showCartMergeInfo
  }
}
    ${TokenFragmentsApi}`;

export function useRegistrationMutationApi() {
  return Urql.useMutation<RegistrationMutationApi, RegistrationMutationVariablesApi>(RegistrationMutationDocumentApi);
};
export const RobotsTxtQueryDocumentApi = gql`
    query RobotsTxtQuery {
  settings {
    seo {
      robotsTxtContent
    }
  }
}
    `;

export function useRobotsTxtQueryApi(options?: Omit<Urql.UseQueryArgs<RobotsTxtQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<RobotsTxtQueryApi, RobotsTxtQueryVariablesApi>({ query: RobotsTxtQueryDocumentApi, ...options });
};
export const AutocompleteSearchQueryDocumentApi = gql`
    query AutocompleteSearchQuery($search: String!, $maxProductCount: Int, $maxCategoryCount: Int, $isAutocomplete: Boolean!, $userIdentifier: Uuid!) {
  articlesSearch(
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier}
  ) {
    ...SimpleArticleInterfaceFragment
  }
  brandSearch(
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier}
  ) {
    ...SimpleBrandFragment
  }
  categoriesSearch(
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier}
    first: $maxCategoryCount
  ) {
    ...SimpleCategoryConnectionFragment
  }
  productsSearch: productsSearch(
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier}
    first: $maxProductCount
  ) {
    orderingMode
    defaultOrderingMode
    totalCount
    productFilterOptions {
      ...ProductFilterOptionsFragment
    }
    ...ListedProductConnectionFragment
  }
}
    ${SimpleArticleInterfaceFragmentApi}
${SimpleBrandFragmentApi}
${SimpleCategoryConnectionFragmentApi}
${ProductFilterOptionsFragmentApi}
${ListedProductConnectionFragmentApi}`;

export function useAutocompleteSearchQueryApi(options: Omit<Urql.UseQueryArgs<AutocompleteSearchQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<AutocompleteSearchQueryApi, AutocompleteSearchQueryVariablesApi>({ query: AutocompleteSearchQueryDocumentApi, ...options });
};
export const SearchQueryDocumentApi = gql`
    query SearchQuery($search: String!, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter, $pageSize: Int, $isAutocomplete: Boolean!, $userIdentifier: Uuid!) {
  articlesSearch(
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier}
  ) {
    ...SimpleArticleInterfaceFragment
  }
  brandSearch(
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier}
  ) {
    ...ListedBrandFragment
  }
  categoriesSearch(
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier}
  ) {
    ...ListedCategoryConnectionFragment
  }
  productsSearch: productsSearch(
    searchInput: {search: $search, isAutocomplete: $isAutocomplete, userIdentifier: $userIdentifier}
    orderingMode: $orderingMode
    filter: $filter
    first: $pageSize
  ) {
    ...ListedProductConnectionPreviewFragment
  }
}
    ${SimpleArticleInterfaceFragmentApi}
${ListedBrandFragmentApi}
${ListedCategoryConnectionFragmentApi}
${ListedProductConnectionPreviewFragmentApi}`;

export function useSearchQueryApi(options: Omit<Urql.UseQueryArgs<SearchQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<SearchQueryApi, SearchQueryVariablesApi>({ query: SearchQueryDocumentApi, ...options });
};
export const SeoPageQueryDocumentApi = gql`
    query SeoPageQuery($pageSlug: String!) {
  seoPage(pageSlug: $pageSlug) {
    ...SeoPageFragment
  }
}
    ${SeoPageFragmentApi}`;

export function useSeoPageQueryApi(options: Omit<Urql.UseQueryArgs<SeoPageQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<SeoPageQueryApi, SeoPageQueryVariablesApi>({ query: SeoPageQueryDocumentApi, ...options });
};
export const SettingsQueryDocumentApi = gql`
    query SettingsQuery @redisCache(ttl: 3600) {
  settings {
    pricing {
      ...PricingSettingFragment
    }
    seo {
      ...SeoSettingFragment
    }
    contactFormMainText
    maxAllowedPaymentTransactions
    displayTimezone
  }
}
    ${PricingSettingFragmentApi}
${SeoSettingFragmentApi}`;

export function useSettingsQueryApi(options?: Omit<Urql.UseQueryArgs<SettingsQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<SettingsQueryApi, SettingsQueryVariablesApi>({ query: SettingsQueryDocumentApi, ...options });
};
export const SliderItemsQueryDocumentApi = gql`
    query SliderItemsQuery {
  sliderItems {
    ...SliderItemFragment
  }
}
    ${SliderItemFragmentApi}`;

export function useSliderItemsQueryApi(options?: Omit<Urql.UseQueryArgs<SliderItemsQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<SliderItemsQueryApi, SliderItemsQueryVariablesApi>({ query: SliderItemsQueryDocumentApi, ...options });
};
export const SlugTypeQueryDocumentApi = gql`
    query SlugTypeQuery($slug: String!) {
  slug(slug: $slug) {
    __typename
  }
}
    `;

export function useSlugTypeQueryApi(options: Omit<Urql.UseQueryArgs<SlugTypeQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<SlugTypeQueryApi, SlugTypeQueryVariablesApi>({ query: SlugTypeQueryDocumentApi, ...options });
};
export const SlugQueryDocumentApi = gql`
    query SlugQuery($slug: String!, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter) {
  slug(slug: $slug) {
    __typename
    ... on RegularProduct {
      ...ProductDetailFragment
    }
    ... on Variant {
      mainVariant {
        slug
      }
    }
    ... on MainVariant {
      ...MainVariantDetailFragment
    }
    ... on Category {
      ...CategoryDetailFragment
    }
    ... on Store {
      ...StoreDetailFragment
    }
    ... on ArticleSite {
      ...ArticleDetailFragment
    }
    ... on BlogArticle {
      ...BlogArticleDetailFragment
    }
    ... on Brand {
      ...BrandDetailFragment
    }
    ... on Flag {
      ...FlagDetailFragment
    }
    ... on BlogCategory {
      ...BlogCategoryDetailFragment
    }
  }
}
    ${ProductDetailFragmentApi}
${MainVariantDetailFragmentApi}
${CategoryDetailFragmentApi}
${StoreDetailFragmentApi}
${ArticleDetailFragmentApi}
${BlogArticleDetailFragmentApi}
${BrandDetailFragmentApi}
${FlagDetailFragmentApi}
${BlogCategoryDetailFragmentApi}`;

export function useSlugQueryApi(options: Omit<Urql.UseQueryArgs<SlugQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<SlugQueryApi, SlugQueryVariablesApi>({ query: SlugQueryDocumentApi, ...options });
};
export const StoreDetailQueryDocumentApi = gql`
    query StoreDetailQuery($urlSlug: String) @friendlyUrl {
  store(urlSlug: $urlSlug) {
    ...StoreDetailFragment
  }
}
    ${StoreDetailFragmentApi}`;

export function useStoreDetailQueryApi(options?: Omit<Urql.UseQueryArgs<StoreDetailQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<StoreDetailQueryApi, StoreDetailQueryVariablesApi>({ query: StoreDetailQueryDocumentApi, ...options });
};
export const StoreQueryDocumentApi = gql`
    query StoreQuery($uuid: Uuid) {
  store(uuid: $uuid) {
    ...ListedStoreFragment
  }
}
    ${ListedStoreFragmentApi}`;

export function useStoreQueryApi(options?: Omit<Urql.UseQueryArgs<StoreQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<StoreQueryApi, StoreQueryVariablesApi>({ query: StoreQueryDocumentApi, ...options });
};
export const StoresQueryDocumentApi = gql`
    query StoresQuery {
  stores {
    ...ListedStoreConnectionFragment
  }
}
    ${ListedStoreConnectionFragmentApi}`;

export function useStoresQueryApi(options?: Omit<Urql.UseQueryArgs<StoresQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<StoresQueryApi, StoresQueryVariablesApi>({ query: StoresQueryDocumentApi, ...options });
};
export const TransportsQueryDocumentApi = gql`
    query TransportsQuery($cartUuid: Uuid) {
  transports(cartUuid: $cartUuid) {
    ...TransportWithAvailablePaymentsAndStoresFragment
  }
}
    ${TransportWithAvailablePaymentsAndStoresFragmentApi}`;

export function useTransportsQueryApi(options?: Omit<Urql.UseQueryArgs<TransportsQueryVariablesApi>, 'query'>) {
  return Urql.useQuery<TransportsQueryApi, TransportsQueryVariablesApi>({ query: TransportsQueryDocumentApi, ...options });
};