import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Maybe<T> = T | null;
export type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
export type MakeOptional<T, K extends keyof T> = Omit<T, K> & { [SubKey in K]?: Maybe<T[SubKey]> };
export type MakeMaybe<T, K extends keyof T> = Omit<T, K> & { [SubKey in K]: Maybe<T[SubKey]> };
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** All built-in and custom scalars, mapped to their actual values */
export type Scalars = {
  ID: string;
  String: string;
  Boolean: boolean;
  Int: number;
  Float: number;
  /** Represents and encapsulates an ISO-8601 encoded UTC date-time value */
  DateTime: any;
  /** Represents and encapsulates monetary value */
  Money: string;
  /** Represents and encapsulates a string for password */
  Password: any;
  /** Represents and encapsulates an ISO-8601 encoded UTC date-time value */
  Uuid: string;
};

export type AddProductResultApi = {
  __typename?: 'AddProductResult';
  addedQuantity: Scalars['Int'];
  cartItem: CartItemApi;
  isNew: Scalars['Boolean'];
  notOnStockQuantity: Scalars['Int'];
};

export type AddToCartInputApi = {
  /** Cart identifier, new cart will be created if not provided and customer is not logged in */
  cartUuid: Maybe<Scalars['Uuid']>;
  /**
   * True if quantity should be set no matter the current state of the cart. False
   * if quantity should be added to the already existing same item in the cart
   */
  isAbsoluteQuantity: Maybe<Scalars['Boolean']>;
  /** Product UUID */
  productUuid: Scalars['Uuid'];
  /** Item quantity */
  quantity: Scalars['Int'];
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
  height: Maybe<Scalars['Int']>;
  /** Recommended media query defined in images.yaml */
  media: Scalars['String'];
  /** URL address of image */
  url: Scalars['String'];
  /** Width in pixels defined in images.yaml */
  width: Maybe<Scalars['Int']>;
};

export type AdvertApi = {
  /** Restricted categories of the advert (the advert is shown in these categories only) */
  categories: Array<CategoryApi>;
  /** Name of advert */
  name: Scalars['String'];
  /** Position of advert */
  positionName: Scalars['String'];
  /** Type of advert */
  type: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};

export type AdvertCodeApi = AdvertApi & {
  __typename?: 'AdvertCode';
  /** Restricted categories of the advert (the advert is shown in these categories only) */
  categories: Array<CategoryApi>;
  /** Advert code */
  code: Scalars['String'];
  /** Name of advert */
  name: Scalars['String'];
  /** Position of advert */
  positionName: Scalars['String'];
  /** Type of advert */
  type: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};

export type AdvertImageApi = AdvertApi & {
  __typename?: 'AdvertImage';
  /** Restricted categories of the advert (the advert is shown in these categories only) */
  categories: Array<CategoryApi>;
  /** Advert images */
  images: Array<ImageApi>;
  /** Advert link */
  link: Maybe<Scalars['String']>;
  /** Adverts first image by params */
  mainImage: Maybe<ImageApi>;
  /** Name of advert */
  name: Scalars['String'];
  /** Position of advert */
  positionName: Scalars['String'];
  /** Type of advert */
  type: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};


export type AdvertImageImagesArgsApi = {
  size?: Maybe<Scalars['String']>;
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};


export type AdvertImageMainImageArgsApi = {
  size?: Maybe<Scalars['String']>;
  type?: Maybe<Scalars['String']>;
};

export type AdvertPositionApi = {
  __typename?: 'AdvertPosition';
  /** Desription of advert position */
  description: Scalars['String'];
  /** Position of advert */
  positionName: Scalars['String'];
};

export type ApplyPromoCodeToCartInputApi = {
  /** Cart identifier or null if customer is logged in */
  cartUuid: Maybe<Scalars['Uuid']>;
  /** Promo code to be used after checkout */
  promoCode: Scalars['String'];
};

/** A connection to a list of items. */
export type ArticleConnectionApi = {
  __typename?: 'ArticleConnection';
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<ArticleEdgeApi>>>;
  /** Information to aid in pagination. */
  pageInfo: PageInfoApi;
  /** Total number of articles */
  totalCount: Scalars['Int'];
};

/** An edge in a connection. */
export type ArticleEdgeApi = {
  __typename?: 'ArticleEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String'];
  /** The item at the end of the edge. */
  node: Maybe<NotBlogArticleInterfaceApi>;
};

/** Represents entity that is considered to be an article on the eshop */
export type ArticleInterfaceApi = {
  breadcrumb: Array<LinkApi>;
  name: Scalars['String'];
  seoH1: Maybe<Scalars['String']>;
  seoMetaDescription: Maybe<Scalars['String']>;
  seoTitle: Maybe<Scalars['String']>;
  slug: Scalars['String'];
  text: Maybe<Scalars['String']>;
  uuid: Scalars['Uuid'];
};

export type ArticleLinkApi = NotBlogArticleInterfaceApi & {
  __typename?: 'ArticleLink';
  /** Creation date time of the article link */
  createdAt: Scalars['DateTime'];
  /** If the the article should be open in a new tab */
  external: Scalars['Boolean'];
  /** Name of article link, used as anchor text */
  name: Scalars['String'];
  /** Placement of the article link */
  placement: Scalars['String'];
  /** Destination url of article link */
  url: Scalars['String'];
  /** UUID of the article link */
  uuid: Scalars['Uuid'];
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
  TopMenuApi = 'topMenu'
}

export type ArticleSiteApi = ArticleInterfaceApi & BreadcrumbApi & NotBlogArticleInterfaceApi & SlugApi & {
  __typename?: 'ArticleSite';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Date and time of the article creation */
  createdAt: Scalars['DateTime'];
  /** If the the article should be open in a new tab */
  external: Scalars['Boolean'];
  /** Name of article */
  name: Scalars['String'];
  /** Placement of article */
  placement: Scalars['String'];
  /** Seo first level heading of article */
  seoH1: Maybe<Scalars['String']>;
  /** Seo meta description of article */
  seoMetaDescription: Maybe<Scalars['String']>;
  /** Seo title of article */
  seoTitle: Maybe<Scalars['String']>;
  /** Article URL slug */
  slug: Scalars['String'];
  /** Text of article */
  text: Maybe<Scalars['String']>;
  /** UUID */
  uuid: Scalars['Uuid'];
};

/** Represents an availability */
export type AvailabilityApi = {
  __typename?: 'Availability';
  /** Localized availability name (domain dependent) */
  name: Scalars['String'];
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

export type BlogArticleApi = ArticleInterfaceApi & BreadcrumbApi & SlugApi & {
  __typename?: 'BlogArticle';
  /** The list of the blog article blog categories */
  blogCategories: Array<BlogCategoryApi>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Date and time of the blog article creation */
  createdAt: Scalars['DateTime'];
  /** ID of category */
  id: Scalars['Int'];
  /** Blog article images */
  images: Array<ImageApi>;
  /** The blog article absolute URL */
  link: Scalars['String'];
  /** Blog article image by params */
  mainImage: Maybe<ImageApi>;
  /** The blog article title */
  name: Scalars['String'];
  /** The blog article perex */
  perex: Maybe<Scalars['String']>;
  /** Date and time of the blog article publishing */
  publishDate: Scalars['DateTime'];
  /** The blog article SEO H1 heading */
  seoH1: Maybe<Scalars['String']>;
  /** The blog article SEO meta description */
  seoMetaDescription: Maybe<Scalars['String']>;
  /** The blog article SEO title */
  seoTitle: Maybe<Scalars['String']>;
  /** The blog article URL slug */
  slug: Scalars['String'];
  /** The blog article text */
  text: Maybe<Scalars['String']>;
  /** The blog article UUID */
  uuid: Scalars['Uuid'];
  /** Indicates whether the blog article is displayed on homepage */
  visibleOnHomepage: Scalars['Boolean'];
};


export type BlogArticleImagesArgsApi = {
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};


export type BlogArticleMainImageArgsApi = {
  size?: Maybe<Scalars['String']>;
  type?: Maybe<Scalars['String']>;
};

/** A connection to a list of items. */
export type BlogArticleConnectionApi = {
  __typename?: 'BlogArticleConnection';
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<BlogArticleEdgeApi>>>;
  /** Information to aid in pagination. */
  pageInfo: PageInfoApi;
  /** Total number of the blog articles */
  totalCount: Scalars['Int'];
};

/** An edge in a connection. */
export type BlogArticleEdgeApi = {
  __typename?: 'BlogArticleEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String'];
  /** The item at the end of the edge. */
  node: Maybe<BlogArticleApi>;
};

export type BlogCategoryApi = BreadcrumbApi & SlugApi & {
  __typename?: 'BlogCategory';
  /** Total count of blog articles in this category */
  articlesTotalCount: Scalars['Int'];
  /** Paginated blog articles of the given blog category */
  blogArticles: BlogArticleConnectionApi;
  /** Tho whole blog categories tree (used for blog navigation rendering) */
  blogCategoriesTree: Array<BlogCategoryApi>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** The blog category children */
  children: Array<BlogCategoryApi>;
  /** The blog category description */
  description: Maybe<Scalars['String']>;
  /** The blog category absolute URL */
  link: Scalars['String'];
  /** The blog category name */
  name: Scalars['String'];
  /** The blog category parent */
  parent: Maybe<BlogCategoryApi>;
  /** The blog category SEO H1 heading */
  seoH1: Maybe<Scalars['String']>;
  /** The blog category SEO meta description */
  seoMetaDescription: Maybe<Scalars['String']>;
  /** The blog category SEO title */
  seoTitle: Maybe<Scalars['String']>;
  /** The blog category URL slug */
  slug: Scalars['String'];
  /** The blog category UUID */
  uuid: Scalars['Uuid'];
};


export type BlogCategoryBlogArticlesArgsApi = {
  after: Maybe<Scalars['String']>;
  before: Maybe<Scalars['String']>;
  first: Maybe<Scalars['Int']>;
  last: Maybe<Scalars['Int']>;
  onlyHomepageArticles?: Maybe<Scalars['Boolean']>;
};

/** Represents a brand */
export type BrandApi = BreadcrumbApi & ProductListableApi & SlugApi & {
  __typename?: 'Brand';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Brand description */
  description: Maybe<Scalars['String']>;
  /** ID of category */
  id: Scalars['Int'];
  /** Brand images */
  images: Array<ImageApi>;
  /** Brand main URL */
  link: Scalars['String'];
  /** Brand image by params */
  mainImage: Maybe<ImageApi>;
  /** Brand name */
  name: Scalars['String'];
  /** Paginated and ordered products of brand */
  products: ProductConnectionApi;
  /** Brand SEO H1 */
  seoH1: Maybe<Scalars['String']>;
  /** Brand SEO meta description */
  seoMetaDescription: Maybe<Scalars['String']>;
  /** Brand SEO title */
  seoTitle: Maybe<Scalars['String']>;
  /** Brand URL slug */
  slug: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};


/** Represents a brand */
export type BrandImagesArgsApi = {
  size: Maybe<Scalars['String']>;
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};


/** Represents a brand */
export type BrandMainImageArgsApi = {
  size?: Maybe<Scalars['String']>;
  type?: Maybe<Scalars['String']>;
};


/** Represents a brand */
export type BrandProductsArgsApi = {
  after: Maybe<Scalars['String']>;
  before: Maybe<Scalars['String']>;
  brandSlug: Maybe<Scalars['String']>;
  categorySlug: Maybe<Scalars['String']>;
  filter: Maybe<ProductFilterApi>;
  first: Maybe<Scalars['Int']>;
  flagSlug: Maybe<Scalars['String']>;
  last: Maybe<Scalars['Int']>;
  orderingMode: Maybe<ProductOrderingModeEnumApi>;
  search: Maybe<Scalars['String']>;
};

/** Brand filter option */
export type BrandFilterOptionApi = {
  __typename?: 'BrandFilterOption';
  /** Brand */
  brand: BrandApi;
  /** Count of products that will be filtered if this filter option is applied. */
  count: Scalars['Int'];
  /**
   * If true than count parameter is number of products that will be displayed if
   * this filter option is applied, if false count parameter is number of products
   * that will be added to current products result.
   */
  isAbsolute: Scalars['Boolean'];
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
  paymentGoPayBankSwift: Maybe<Scalars['String']>;
  /** Applied promo code if provided */
  promoCode: Maybe<Scalars['String']>;
  /** Remaining amount for free transport and payment; null = transport cannot be free */
  remainingAmountWithVatForFreeTransport: Maybe<Scalars['Money']>;
  /** Selected pickup place identifier if provided */
  selectedPickupPlaceIdentifier: Maybe<Scalars['String']>;
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
  uuid: Maybe<Scalars['Uuid']>;
};

export type CartInputApi = {
  /** Cart identifier, new cart will be created if not provided and customer is not logged in */
  cartUuid: Maybe<Scalars['Uuid']>;
};

export type CartInterfaceApi = {
  items: Array<CartItemApi>;
  modifications: CartModificationsResultApi;
  payment: Maybe<PaymentApi>;
  paymentGoPayBankSwift: Maybe<Scalars['String']>;
  promoCode: Maybe<Scalars['String']>;
  remainingAmountWithVatForFreeTransport: Maybe<Scalars['Money']>;
  selectedPickupPlaceIdentifier: Maybe<Scalars['String']>;
  totalDiscountPrice: PriceApi;
  /** Total items price (excluding transport and payment) */
  totalItemsPrice: PriceApi;
  /** Total price including transport and payment */
  totalPrice: PriceApi;
  transport: Maybe<TransportApi>;
  uuid: Maybe<Scalars['Uuid']>;
};

/** Represent one item in the cart */
export type CartItemApi = {
  __typename?: 'CartItem';
  /** Product in the cart */
  product: ProductApi;
  /** Quantity of items in the cart */
  quantity: Scalars['Int'];
  /** Cart item UUID */
  uuid: Scalars['Uuid'];
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
  someProductWasRemovedFromEshop: Scalars['Boolean'];
  transportModifications: CartTransportModificationsResultApi;
};

export type CartPaymentModificationsResultApi = {
  __typename?: 'CartPaymentModificationsResult';
  paymentPriceChanged: Scalars['Boolean'];
  paymentUnavailable: Scalars['Boolean'];
};

export type CartPromoCodeModificationsResultApi = {
  __typename?: 'CartPromoCodeModificationsResult';
  noLongerApplicablePromoCode: Array<Scalars['String']>;
};

export type CartTransportModificationsResultApi = {
  __typename?: 'CartTransportModificationsResult';
  personalPickupStoreUnavailable: Scalars['Boolean'];
  transportPriceChanged: Scalars['Boolean'];
  transportUnavailable: Scalars['Boolean'];
  transportWeightLimitExceeded: Scalars['Boolean'];
};

/** Represents a category */
export type CategoryApi = BreadcrumbApi & ProductListableApi & SlugApi & {
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
  description: Maybe<Scalars['String']>;
  /** ID of category */
  id: Scalars['Int'];
  /** Category images */
  images: Array<ImageApi>;
  /** A list of categories linked to the given category */
  linkedCategories: Array<CategoryApi>;
  /** Category image by params */
  mainImage: Maybe<ImageApi>;
  /** Localized category name (domain dependent) */
  name: Scalars['String'];
  /**
   * Original category URL slug (for CategorySeoMixes slug of assigned category is
   * returned, null is returned for regular category)
   */
  originalCategorySlug: Maybe<Scalars['String']>;
  /** Ancestor category */
  parent: Maybe<CategoryApi>;
  /** Paginated and ordered products of category */
  products: ProductConnectionApi;
  /** An array of links of prepared category SEO mixes of a given category */
  readyCategorySeoMixLinks: Array<LinkApi>;
  /** Seo first level heading of category */
  seoH1: Maybe<Scalars['String']>;
  /** Seo meta description of category */
  seoMetaDescription: Maybe<Scalars['String']>;
  /** Seo title of category */
  seoTitle: Maybe<Scalars['String']>;
  /** Category URL slug */
  slug: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};


/** Represents a category */
export type CategoryImagesArgsApi = {
  size: Maybe<Scalars['String']>;
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};


/** Represents a category */
export type CategoryMainImageArgsApi = {
  size?: Maybe<Scalars['String']>;
  type?: Maybe<Scalars['String']>;
};


/** Represents a category */
export type CategoryProductsArgsApi = {
  after: Maybe<Scalars['String']>;
  before: Maybe<Scalars['String']>;
  brandSlug: Maybe<Scalars['String']>;
  categorySlug: Maybe<Scalars['String']>;
  filter: Maybe<ProductFilterApi>;
  first: Maybe<Scalars['Int']>;
  flagSlug: Maybe<Scalars['String']>;
  last: Maybe<Scalars['Int']>;
  orderingMode: Maybe<ProductOrderingModeEnumApi>;
  search: Maybe<Scalars['String']>;
};

/** A connection to a list of items. */
export type CategoryConnectionApi = {
  __typename?: 'CategoryConnection';
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<CategoryEdgeApi>>>;
  /** Information to aid in pagination. */
  pageInfo: PageInfoApi;
  /** Total number of categories */
  totalCount: Scalars['Int'];
};

/** An edge in a connection. */
export type CategoryEdgeApi = {
  __typename?: 'CategoryEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String'];
  /** The item at the end of the edge. */
  node: Maybe<CategoryApi>;
};

export type CategoryHierarchyItemApi = {
  __typename?: 'CategoryHierarchyItem';
  /** Localized category name (domain dependent) */
  name: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};

export type ChangePasswordInputApi = {
  /** Customer user email. */
  email: Scalars['String'];
  /** New customer user password. */
  newPassword: Scalars['Password'];
  /** Current customer user password. */
  oldPassword: Scalars['Password'];
};

export type ChangePaymentInCartInputApi = {
  /** Cart identifier or null if customer is logged in */
  cartUuid: Maybe<Scalars['Uuid']>;
  /** Selected bank swift code of goPay payment bank transfer */
  paymentGoPayBankSwift: Maybe<Scalars['String']>;
  /** UUID of a payment that should be added to the cart. If this is set to null, the payment is removed from the cart */
  paymentUuid: Maybe<Scalars['Uuid']>;
};

export type ChangePersonalDataInputApi = {
  /** Billing address city name (will be on the tax invoice) */
  city: Scalars['String'];
  /** Determines whether the customer is a company or not. */
  companyCustomer: Maybe<Scalars['Boolean']>;
  /** The customer’s company name (required when companyCustomer is true) */
  companyName: Maybe<Scalars['String']>;
  /** The customer’s company identification number (required when companyCustomer is true) */
  companyNumber: Maybe<Scalars['String']>;
  /** The customer’s company tax number (required when companyCustomer is true) */
  companyTaxNumber: Maybe<Scalars['String']>;
  /** Billing address country code in ISO 3166-1 alpha-2 (Country will be on the tax invoice) */
  country: Scalars['String'];
  /** Customer user first name */
  firstName: Scalars['String'];
  /** Customer user last name */
  lastName: Scalars['String'];
  /** Whether customer user should receive newsletters or not */
  newsletterSubscription: Scalars['Boolean'];
  /** Billing address zip code (will be on the tax invoice) */
  postcode: Scalars['String'];
  /** Billing address street name (will be on the tax invoice) */
  street: Scalars['String'];
  /** The customer's telephone number */
  telephone: Scalars['String'];
};

export type ChangeTransportInCartInputApi = {
  /** Cart identifier or null if customer is logged in */
  cartUuid: Maybe<Scalars['Uuid']>;
  /** The identifier of selected personal pickup place */
  pickupPlaceIdentifier: Maybe<Scalars['String']>;
  /** UUID of a transport that should be added to the cart. If this is set to null, the transport is removed from the cart */
  transportUuid: Maybe<Scalars['Uuid']>;
};

/** Represents an currently logged customer user */
export type CompanyCustomerUserApi = CustomerUserApi & {
  __typename?: 'CompanyCustomerUser';
  /** Billing address city name */
  city: Scalars['String'];
  /** The customer’s company name (only when customer is a company) */
  companyName: Maybe<Scalars['String']>;
  /** The customer’s company identification number (only when customer is a company) */
  companyNumber: Maybe<Scalars['String']>;
  /** The customer’s company tax number (only when customer is a company) */
  companyTaxNumber: Maybe<Scalars['String']>;
  /** Billing address country */
  country: CountryApi;
  /** Default customer delivery addresses */
  defaultDeliveryAddress: Maybe<DeliveryAddressApi>;
  /** List of delivery addresses */
  deliveryAddresses: Array<DeliveryAddressApi>;
  /** Email address */
  email: Scalars['String'];
  /** First name */
  firstName: Scalars['String'];
  /** Last name */
  lastName: Scalars['String'];
  /** Whether customer user receives newsletters or not */
  newsletterSubscription: Scalars['Boolean'];
  /** Billing address zip code */
  postcode: Scalars['String'];
  /** The name of the customer pricing group */
  pricingGroup: Scalars['String'];
  /** Billing address street name */
  street: Scalars['String'];
  /** Phone number */
  telephone: Maybe<Scalars['String']>;
  /** UUID */
  uuid: Scalars['Uuid'];
};

export type ComparisonApi = {
  __typename?: 'Comparison';
  /** List of compared products */
  products: Array<ProductApi>;
  /** Comparison identifier */
  uuid: Scalars['Uuid'];
};

export type ContactInputApi = {
  /** Email address of the sender */
  email: Scalars['String'];
  /** Message sent to recipient */
  message: Scalars['String'];
  /** Name of the sender */
  name: Scalars['String'];
};

/** Represents country */
export type CountryApi = {
  __typename?: 'Country';
  /** Country code in ISO 3166-1 alpha-2 */
  code: Scalars['String'];
  /** Localized country name */
  name: Scalars['String'];
};

export type CreateOrderResultApi = {
  __typename?: 'CreateOrderResult';
  cart: Maybe<CartApi>;
  order: Maybe<OrderApi>;
  orderCreated: Scalars['Boolean'];
};

/** Represents an currently logged customer user */
export type CustomerUserApi = {
  /** Billing address city name */
  city: Scalars['String'];
  /** Billing address country */
  country: CountryApi;
  /** Default customer delivery addresses */
  defaultDeliveryAddress: Maybe<DeliveryAddressApi>;
  /** List of delivery addresses */
  deliveryAddresses: Array<DeliveryAddressApi>;
  /** Email address */
  email: Scalars['String'];
  /** First name */
  firstName: Scalars['String'];
  /** Last name */
  lastName: Scalars['String'];
  /** Whether customer user receives newsletters or not */
  newsletterSubscription: Scalars['Boolean'];
  /** Billing address zip code */
  postcode: Scalars['String'];
  /** The name of the customer pricing group */
  pricingGroup: Scalars['String'];
  /** Billing address street name */
  street: Scalars['String'];
  /** Phone number */
  telephone: Maybe<Scalars['String']>;
  /** UUID */
  uuid: Scalars['Uuid'];
};

export type DeliveryAddressApi = {
  __typename?: 'DeliveryAddress';
  /** Delivery address city name */
  city: Maybe<Scalars['String']>;
  /** Delivery address company name */
  companyName: Maybe<Scalars['String']>;
  /** Delivery address country */
  country: Maybe<CountryApi>;
  /** Delivery address firstname */
  firstName: Maybe<Scalars['String']>;
  /** Delivery address lastname */
  lastName: Maybe<Scalars['String']>;
  /** Delivery address zip code */
  postcode: Maybe<Scalars['String']>;
  /** Delivery address street name */
  street: Maybe<Scalars['String']>;
  /** Delivery address telephone */
  telephone: Maybe<Scalars['String']>;
  /** UUID */
  uuid: Scalars['Uuid'];
};

export type DeliveryAddressInputApi = {
  /** Delivery address city name */
  city: Scalars['String'];
  /** Delivery address company name */
  companyName: Maybe<Scalars['String']>;
  /** Delivery address country */
  country: Scalars['String'];
  /** Delivery address first name */
  firstName: Scalars['String'];
  /** Delivery address last name */
  lastName: Scalars['String'];
  /** Delivery address zip code */
  postcode: Scalars['String'];
  /** Delivery address street name */
  street: Scalars['String'];
  /** Delivery address telephone */
  telephone: Maybe<Scalars['String']>;
  /** UUID */
  uuid: Maybe<Scalars['Uuid']>;
};

/** Represents a downloadable file */
export type FileApi = {
  __typename?: 'File';
  /** Clickable text for a hyperlink */
  anchorText: Scalars['String'];
  /** Url to download the file */
  url: Scalars['String'];
};

/** Represents a flag */
export type FlagApi = BreadcrumbApi & ProductListableApi & SlugApi & {
  __typename?: 'Flag';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Categories containing at least one product with flag */
  categories: Array<CategoryApi>;
  /** Localized flag name (domain dependent) */
  name: Scalars['String'];
  /** Paginated and ordered products of flag */
  products: ProductConnectionApi;
  /** Flag color in rgb format */
  rgbColor: Scalars['String'];
  /** URL slug of flag */
  slug: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};


/** Represents a flag */
export type FlagCategoriesArgsApi = {
  productFilter: Maybe<ProductFilterApi>;
};


/** Represents a flag */
export type FlagProductsArgsApi = {
  after: Maybe<Scalars['String']>;
  before: Maybe<Scalars['String']>;
  brandSlug: Maybe<Scalars['String']>;
  categorySlug: Maybe<Scalars['String']>;
  filter: Maybe<ProductFilterApi>;
  first: Maybe<Scalars['Int']>;
  flagSlug: Maybe<Scalars['String']>;
  last: Maybe<Scalars['Int']>;
  orderingMode: Maybe<ProductOrderingModeEnumApi>;
  search: Maybe<Scalars['String']>;
};

/** Flag filter option */
export type FlagFilterOptionApi = {
  __typename?: 'FlagFilterOption';
  /** Count of products that will be filtered if this filter option is applied. */
  count: Scalars['Int'];
  /** Flag */
  flag: FlagApi;
  /**
   * If true than count parameter is number of products that will be displayed if
   * this filter option is applied, if false count parameter is number of products
   * that will be added to current products result.
   */
  isAbsolute: Scalars['Boolean'];
  /** Indicator whether the option is already selected (used for "ready category seo mixes") */
  isSelected: Scalars['Boolean'];
};

export type GoPayBankSwiftApi = {
  __typename?: 'GoPayBankSwift';
  /** large image url */
  imageLargeUrl: Scalars['String'];
  /** normal image url */
  imageNormalUrl: Scalars['String'];
  isOnline: Scalars['Boolean'];
  /** Bank name */
  name: Scalars['String'];
  /** Swift code */
  swift: Scalars['String'];
};

export type GoPayCreatePaymentSetupApi = {
  __typename?: 'GoPayCreatePaymentSetup';
  /** url of gopay embedJs file */
  embedJs: Scalars['String'];
  /** redirect URL to payment gateway */
  gatewayUrl: Scalars['String'];
  /** payment transaction identifier */
  goPayId: Scalars['String'];
};

export type GoPayPaymentMethodApi = {
  __typename?: 'GoPayPaymentMethod';
  /** Identifier of payment method */
  identifier: Scalars['String'];
  /** URL to large size image of payment method */
  imageLargeUrl: Scalars['String'];
  /** URL to normal size image of payment method */
  imageNormalUrl: Scalars['String'];
  /** Name of payment method */
  name: Scalars['String'];
  /** Group of payment methods */
  paymentGroup: Scalars['String'];
};

/** Represents an image */
export type ImageApi = {
  __typename?: 'Image';
  /** Image name for ALT attribute */
  name: Maybe<Scalars['String']>;
  /** Position of image in list */
  position: Maybe<Scalars['Int']>;
  sizes: Array<ImageSizeApi>;
  /** Image type */
  type: Maybe<Scalars['String']>;
};

/** Represents a single image size */
export type ImageSizeApi = {
  __typename?: 'ImageSize';
  /** Additional sizes for different screen types */
  additionalSizes: Array<AdditionalSizeApi>;
  /** Height in pixels defined in images.yaml */
  height: Maybe<Scalars['Int']>;
  /** Image size defined in images.yaml */
  size: Scalars['String'];
  /** URL address of image */
  url: Scalars['String'];
  /** Width in pixels defined in images.yaml */
  width: Maybe<Scalars['Int']>;
};

/** Represents a single user translation of language constant */
export type LanguageConstantApi = {
  __typename?: 'LanguageConstant';
  /** Translation key */
  key: Scalars['String'];
  /** User translation */
  translation: Scalars['String'];
};

/** Represents an internal link */
export type LinkApi = {
  __typename?: 'Link';
  /** Clickable text for a hyperlink */
  name: Scalars['String'];
  /** Target URL slug */
  slug: Scalars['String'];
};

export type LoginInputApi = {
  /** Uuid of the cart that should be merged to the cart of the user */
  cartUuid: Maybe<Scalars['Uuid']>;
  /** The user email. */
  email: Scalars['String'];
  /** The user password. */
  password: Scalars['Password'];
};

export type LoginResultApi = {
  __typename?: 'LoginResult';
  showCartMergeInfo: Scalars['Boolean'];
  tokens: TokenApi;
};

/** Represents a product */
export type MainVariantApi = BreadcrumbApi & ProductApi & SlugApi & {
  __typename?: 'MainVariant';
  accessories: Array<ProductApi>;
  availability: AvailabilityApi;
  /** Number of the stores where the product is available */
  availableStoresCount: Scalars['Int'];
  /** Brand of product */
  brand: Maybe<BrandApi>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Product catalog number */
  catalogNumber: Scalars['String'];
  /** List of categories */
  categories: Array<CategoryApi>;
  description: Maybe<Scalars['String']>;
  /** EAN */
  ean: Maybe<Scalars['String']>;
  /** Number of the stores where the product is exposed */
  exposedStoresCount: Scalars['Int'];
  /** List of downloadable files */
  files: Array<FileApi>;
  /** List of flags */
  flags: Array<FlagApi>;
  /** The full name of the product, which consists of a prefix, name, and a suffix */
  fullName: Scalars['String'];
  /** Distinguishes if the product can be pre-ordered */
  hasPreorder: Scalars['Boolean'];
  /** Product id */
  id: Scalars['Int'];
  /** Product images */
  images: Array<ImageApi>;
  isMainVariant: Scalars['Boolean'];
  isSellingDenied: Scalars['Boolean'];
  isUsingStock: Scalars['Boolean'];
  /** Product link */
  link: Scalars['String'];
  /** Product image by params */
  mainImage: Maybe<ImageApi>;
  /** Localized product name (domain dependent) */
  name: Scalars['String'];
  /** Name prefix */
  namePrefix: Maybe<Scalars['String']>;
  /** Name suffix */
  nameSuffix: Maybe<Scalars['String']>;
  orderingPriority: Scalars['Int'];
  parameters: Array<ParameterApi>;
  /** Product part number */
  partNumber: Maybe<Scalars['String']>;
  /** Product price */
  price: ProductPriceApi;
  productVideos: Array<VideoTokenApi>;
  /** List of related products */
  relatedProducts: Array<ProductApi>;
  /** Seo first level heading of product */
  seoH1: Maybe<Scalars['String']>;
  /** Seo meta description of product */
  seoMetaDescription: Maybe<Scalars['String']>;
  /** Seo title of product */
  seoTitle: Maybe<Scalars['String']>;
  /** Localized product short description (domain dependent) */
  shortDescription: Maybe<Scalars['String']>;
  /** Product URL slug */
  slug: Scalars['String'];
  /** Count of quantity on stock */
  stockQuantity: Scalars['Int'];
  /** List of availabilities in individual stores */
  storeAvailabilities: Array<StoreAvailabilityApi>;
  unit: UnitApi;
  /** List of product's unique selling propositions */
  usps: Array<Scalars['String']>;
  /** UUID */
  uuid: Scalars['Uuid'];
  variants: Array<VariantApi>;
};


/** Represents a product */
export type MainVariantImagesArgsApi = {
  size: Maybe<Scalars['String']>;
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};


/** Represents a product */
export type MainVariantMainImageArgsApi = {
  size?: Maybe<Scalars['String']>;
  type?: Maybe<Scalars['String']>;
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
  CheckPaymentStatus: Scalars['Boolean'];
  /** Send message to the site owner */
  Contact: Scalars['Boolean'];
  /** Creates complete order with products and addresses */
  CreateOrder: CreateOrderResultApi;
  /** Delete delivery address by Uuid */
  DeleteDeliveryAddress: Array<DeliveryAddressApi>;
  /** Edit delivery address by Uuid */
  EditDeliveryAddress: Array<DeliveryAddressApi>;
  /** Login customer user */
  Login: LoginResultApi;
  /** Logout user */
  Logout: Scalars['Boolean'];
  /** Subscribe for e-mail newsletter */
  NewsletterSubscribe: Scalars['Boolean'];
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
  RequestPasswordRecovery: Scalars['String'];
  /** Request access to personal data */
  RequestPersonalDataAccess: PersonalDataPageApi;
  /** Set default delivery address by Uuid */
  SetDefaultDeliveryAddress: CustomerUserApi;
  /** Add product to Comparison and create if not exists. */
  addProductToComparison: ComparisonApi;
  /** Remove all products from Comparison and remove it. */
  cleanComparison: Scalars['String'];
  /** Remove product from Comparison and if is Comparison empty remove it. */
  removeProductFromComparison: Maybe<ComparisonApi>;
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
  orderUuid: Scalars['Uuid'];
};


export type MutationContactArgsApi = {
  input: ContactInputApi;
};


export type MutationCreateOrderArgsApi = {
  input: OrderInputApi;
};


export type MutationDeleteDeliveryAddressArgsApi = {
  deliveryAddressUuid: Scalars['Uuid'];
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
  orderUuid: Scalars['Uuid'];
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
  email: Scalars['String'];
};


export type MutationRequestPersonalDataAccessArgsApi = {
  input: PersonalDataAccessRequestInputApi;
};


export type MutationSetDefaultDeliveryAddressArgsApi = {
  deliveryAddressUuid: Scalars['Uuid'];
};


export type MutationAddProductToComparisonArgsApi = {
  comparisonUuid: Maybe<Scalars['Uuid']>;
  productUuid: Scalars['Uuid'];
};


export type MutationCleanComparisonArgsApi = {
  comparisonUuid: Maybe<Scalars['Uuid']>;
};


export type MutationRemoveProductFromComparisonArgsApi = {
  comparisonUuid: Maybe<Scalars['Uuid']>;
  productUuid: Scalars['Uuid'];
};

/** Represents a navigation structure item */
export type NavigationItemApi = {
  __typename?: 'NavigationItem';
  /** Categories separated into columns */
  categoriesByColumns: Array<NavigationItemCategoriesByColumnsApi>;
  /** Target URL */
  link: Scalars['String'];
  /** Navigation item name */
  name: Scalars['String'];
};

/** Represents a single column inside the navigation item */
export type NavigationItemCategoriesByColumnsApi = {
  __typename?: 'NavigationItemCategoriesByColumns';
  /** Categories */
  categories: Array<CategoryApi>;
  /** Column number */
  columnNumber: Scalars['Int'];
};

export type NewsletterSubscriberApi = {
  __typename?: 'NewsletterSubscriber';
  /** Date and time of subscription */
  createdAt: Scalars['DateTime'];
  /** Subscribed email address */
  email: Scalars['String'];
};

/** Represents the main input object to subscribe for e-mail newsletter */
export type NewsletterSubscriptionDataInputApi = {
  email: Scalars['String'];
};

/** Represents an article that is not a blog article */
export type NotBlogArticleInterfaceApi = {
  /** creation date time of the article */
  createdAt: Scalars['DateTime'];
  /** If the the article should be open in a new tab */
  external: Scalars['Boolean'];
  /** name of article link */
  name: Scalars['String'];
  /** placement of the article */
  placement: Scalars['String'];
  /** UUID of the article link */
  uuid: Scalars['Uuid'];
};

/** Represents a notification supposed to be displayed on all pages */
export type NotificationBarApi = {
  __typename?: 'NotificationBar';
  /** Notification bar images */
  images: Array<ImageApi>;
  /** Notification bar image by params */
  mainImage: Maybe<ImageApi>;
  /** Color of the notification */
  rgbColor: Scalars['String'];
  /** Message of the notification */
  text: Scalars['String'];
};


/** Represents a notification supposed to be displayed on all pages */
export type NotificationBarImagesArgsApi = {
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};


/** Represents a notification supposed to be displayed on all pages */
export type NotificationBarMainImageArgsApi = {
  size?: Maybe<Scalars['String']>;
  type?: Maybe<Scalars['String']>;
};

export type OrderApi = {
  __typename?: 'Order';
  /** Billing address city name */
  city: Scalars['String'];
  /** The customer’s company name (only when ordered on the company behalf) */
  companyName: Maybe<Scalars['String']>;
  /** The customer’s company identification number (only when ordered on the company behalf) */
  companyNumber: Maybe<Scalars['String']>;
  /** The customer’s company tax number (only when ordered on the company behalf) */
  companyTaxNumber: Maybe<Scalars['String']>;
  /** Billing address country */
  country: CountryApi;
  /** Date and time when the order was created */
  creationDate: Scalars['DateTime'];
  /** City name for delivery */
  deliveryCity: Maybe<Scalars['String']>;
  /** Company name for delivery */
  deliveryCompanyName: Maybe<Scalars['String']>;
  /** Country for delivery */
  deliveryCountry: Maybe<CountryApi>;
  /** First name of the contact person for delivery */
  deliveryFirstName: Maybe<Scalars['String']>;
  /** Last name of the contact person for delivery */
  deliveryLastName: Maybe<Scalars['String']>;
  /** Zip code for delivery */
  deliveryPostcode: Maybe<Scalars['String']>;
  /** Street name for delivery */
  deliveryStreet: Maybe<Scalars['String']>;
  /** Contact telephone number for delivery */
  deliveryTelephone: Maybe<Scalars['String']>;
  /** Indicates whether the billing address is other than a delivery address */
  differentDeliveryAddress: Scalars['Boolean'];
  /** The customer's email address */
  email: Scalars['String'];
  /** The customer's first name */
  firstName: Maybe<Scalars['String']>;
  /** All items in the order including payment and transport */
  items: Array<OrderItemApi>;
  /** The customer's last name */
  lastName: Maybe<Scalars['String']>;
  /** Other information related to the order */
  note: Maybe<Scalars['String']>;
  /** Unique order number */
  number: Scalars['String'];
  /** Payment method applied to the order */
  payment: PaymentApi;
  /** Selected pickup place identifier */
  pickupPlaceIdentifier: Maybe<Scalars['String']>;
  /** Billing address zip code */
  postcode: Scalars['String'];
  /** All product items in the order */
  productItems: Array<OrderItemApi>;
  /** Promo code (coupon) used in the order */
  promoCode: Maybe<Scalars['String']>;
  /** Current status of the order */
  status: Scalars['String'];
  /** Billing address street name  */
  street: Scalars['String'];
  /** The customer's telephone number */
  telephone: Scalars['String'];
  /** Total price of the order including transport and payment prices */
  totalPrice: PriceApi;
  /** The order tracking number */
  trackingNumber: Maybe<Scalars['String']>;
  /** The order tracking link */
  trackingUrl: Maybe<Scalars['String']>;
  /** Transport method applied to the order */
  transport: TransportApi;
  /** Unique url hash that can be used to  */
  urlHash: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};

/** A connection to a list of items. */
export type OrderConnectionApi = {
  __typename?: 'OrderConnection';
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<OrderEdgeApi>>>;
  /** Information to aid in pagination. */
  pageInfo: PageInfoApi;
  /** Total number of orders */
  totalCount: Scalars['Int'];
};

/** An edge in a connection. */
export type OrderEdgeApi = {
  __typename?: 'OrderEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String'];
  /** The item at the end of the edge. */
  node: Maybe<OrderApi>;
};

/** Represents the main input object to create orders */
export type OrderInputApi = {
  /** Cart identifier used for getting carts of not logged customers */
  cartUuid: Maybe<Scalars['Uuid']>;
  /** Billing address city name (will be on the tax invoice) */
  city: Scalars['String'];
  /** The customer’s company name (required when onCompanyBehalf is true) */
  companyName: Maybe<Scalars['String']>;
  /** The customer’s company identification number (required when onCompanyBehalf is true) */
  companyNumber: Maybe<Scalars['String']>;
  /** The customer’s company tax number (required when onCompanyBehalf is true) */
  companyTaxNumber: Maybe<Scalars['String']>;
  /** Billing address country code in ISO 3166-1 alpha-2 (Country will be on the tax invoice) */
  country: Scalars['String'];
  /** Delivery address identifier */
  deliveryAddressUuid: Maybe<Scalars['Uuid']>;
  /** City name for delivery (required when differentDeliveryAddress is true) */
  deliveryCity: Maybe<Scalars['String']>;
  /** Company name for delivery */
  deliveryCompanyName: Maybe<Scalars['String']>;
  /** Country code in ISO 3166-1 alpha-2 for delivery (required when differentDeliveryAddress is true) */
  deliveryCountry: Maybe<Scalars['String']>;
  /** First name of the contact person for delivery (required when differentDeliveryAddress is true) */
  deliveryFirstName: Maybe<Scalars['String']>;
  /** Last name of the contact person for delivery (required when differentDeliveryAddress is true) */
  deliveryLastName: Maybe<Scalars['String']>;
  /** Zip code for delivery (required when differentDeliveryAddress is true) */
  deliveryPostcode: Maybe<Scalars['String']>;
  /** Street name for delivery (required when differentDeliveryAddress is true) */
  deliveryStreet: Maybe<Scalars['String']>;
  /** Contact telephone number for delivery */
  deliveryTelephone: Maybe<Scalars['String']>;
  /** Determines whether to deliver products to a different address than the billing one */
  differentDeliveryAddress: Scalars['Boolean'];
  /** The customer's email address */
  email: Scalars['String'];
  /** The customer's first name */
  firstName: Scalars['String'];
  /** The customer's last name */
  lastName: Scalars['String'];
  /** Allows user to subscribe/unsubscribe newsletter. */
  newsletterSubscription: Maybe<Scalars['Boolean']>;
  /** Other information related to the order */
  note: Maybe<Scalars['String']>;
  /** Determines whether the order is made on the company behalf. */
  onCompanyBehalf: Scalars['Boolean'];
  /** Deprecated, this field is not used, the payment is taken from the server cart instead. */
  payment: Maybe<PaymentInputApi>;
  /** Billing address zip code (will be on the tax invoice) */
  postcode: Scalars['String'];
  /** Deprecated, this field is not used, the products are taken from the server cart instead. */
  products: Maybe<Array<OrderProductInputApi>>;
  /** Billing address street name (will be on the tax invoice) */
  street: Scalars['String'];
  /** The customer's phone number */
  telephone: Scalars['String'];
  /** Deprecated, this field is not used, the transport is taken from the server cart instead. */
  transport: Maybe<TransportInputApi>;
};

/** Represent one item in the order */
export type OrderItemApi = {
  __typename?: 'OrderItem';
  /** Name of the order item */
  name: Scalars['String'];
  /** Quantity of order items in the order */
  quantity: Scalars['Int'];
  /** Total price for the quantity of order item */
  totalPrice: PriceApi;
  /** Unit of measurement used for the order item */
  unit: Maybe<Scalars['String']>;
  /** Order item price per unit */
  unitPrice: PriceApi;
  /** Applied VAT rate percentage applied to the order item */
  vatRate: Scalars['String'];
};

/** Represents a product in order */
export type OrderProductInputApi = {
  /** Quantity of products */
  quantity: Scalars['Int'];
  /** Product price per unit */
  unitPrice: PriceInputApi;
  /** UUID */
  uuid: Scalars['Uuid'];
};

/** Information about pagination in a connection. */
export type PageInfoApi = {
  __typename?: 'PageInfo';
  /** When paginating forwards, the cursor to continue. */
  endCursor: Maybe<Scalars['String']>;
  /** When paginating forwards, are there more items? */
  hasNextPage: Scalars['Boolean'];
  /** When paginating backwards, are there more items? */
  hasPreviousPage: Scalars['Boolean'];
  /** When paginating backwards, the cursor to continue. */
  startCursor: Maybe<Scalars['String']>;
};

/** Represents a parameter */
export type ParameterApi = {
  __typename?: 'Parameter';
  /** Parameter group to which the parameter is assigned */
  group: Maybe<Scalars['String']>;
  /** Parameter name */
  name: Scalars['String'];
  /** Unit of the parameter */
  unit: Maybe<UnitApi>;
  /** UUID */
  uuid: Scalars['Uuid'];
  values: Array<ParameterValueApi>;
  visible: Scalars['Boolean'];
};

/** Parameter filter option */
export type ParameterCheckboxFilterOptionApi = ParameterFilterOptionInterfaceApi & {
  __typename?: 'ParameterCheckboxFilterOption';
  /** Indicator whether the parameter should be collapsed based on the current category setting */
  isCollapsed: Scalars['Boolean'];
  /** The parameter name */
  name: Scalars['String'];
  /** The parameter unit */
  unit: Maybe<UnitApi>;
  /** The parameter UUID */
  uuid: Scalars['Uuid'];
  /** Filter options of parameter values */
  values: Array<ParameterValueFilterOptionApi>;
};

/** Parameter filter option */
export type ParameterColorFilterOptionApi = ParameterFilterOptionInterfaceApi & {
  __typename?: 'ParameterColorFilterOption';
  /** Indicator whether the parameter should be collapsed based on the current category setting */
  isCollapsed: Scalars['Boolean'];
  /** The parameter name */
  name: Scalars['String'];
  /** The parameter unit */
  unit: Maybe<UnitApi>;
  /** The parameter UUID */
  uuid: Scalars['Uuid'];
  /** Filter options of parameter values */
  values: Array<ParameterValueColorFilterOptionApi>;
};

/** Represents a parameter filter */
export type ParameterFilterApi = {
  /** The parameter maximal value (for parameters with "slider" type) */
  maximalValue: Maybe<Scalars['Float']>;
  /** The parameter minimal value (for parameters with "slider" type) */
  minimalValue: Maybe<Scalars['Float']>;
  /** Uuid of filtered parameter */
  parameter: Scalars['Uuid'];
  /** Array of uuids representing parameter values to be filtered by */
  values: Array<Scalars['Uuid']>;
};

/** Represents parameter filter option */
export type ParameterFilterOptionInterfaceApi = {
  /** Indicator whether the parameter should be collapsed based on the current category setting */
  isCollapsed: Scalars['Boolean'];
  /** The parameter name */
  name: Scalars['String'];
  /** The parameter unit */
  unit: Maybe<UnitApi>;
  /** The parameter UUID */
  uuid: Scalars['Uuid'];
};

/** Parameter filter option */
export type ParameterSliderFilterOptionApi = ParameterFilterOptionInterfaceApi & {
  __typename?: 'ParameterSliderFilterOption';
  /** Indicator whether the parameter should be collapsed based on the current category setting */
  isCollapsed: Scalars['Boolean'];
  /** The parameter maximal value */
  maximalValue: Scalars['Float'];
  /** The parameter minimal value */
  minimalValue: Scalars['Float'];
  /** The parameter name */
  name: Scalars['String'];
  /** The pre-selected value (used for "ready category seo mixes") */
  selectedValue: Maybe<Scalars['Float']>;
  /** The parameter unit */
  unit: Maybe<UnitApi>;
  /** The parameter UUID */
  uuid: Scalars['Uuid'];
};

/** Represents a parameter value */
export type ParameterValueApi = {
  __typename?: 'ParameterValue';
  /** Parameter value */
  text: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};

/** Parameter value filter option */
export type ParameterValueColorFilterOptionApi = {
  __typename?: 'ParameterValueColorFilterOption';
  /** Count of products that will be filtered if this filter option is applied. */
  count: Scalars['Int'];
  /**
   * If true than count parameter is number of products that will be displayed if
   * this filter option is applied, if false count parameter is number of products
   * that will be added to current products result.
   */
  isAbsolute: Scalars['Boolean'];
  /** Indicator whether the option is already selected (used for "ready category seo mixes") */
  isSelected: Scalars['Boolean'];
  /** RGB hex of color parameter */
  rgbHex: Maybe<Scalars['String']>;
  /** Parameter value */
  text: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};

/** Parameter value filter option */
export type ParameterValueFilterOptionApi = {
  __typename?: 'ParameterValueFilterOption';
  /** Count of products that will be filtered if this filter option is applied. */
  count: Scalars['Int'];
  /**
   * If true than count parameter is number of products that will be displayed if
   * this filter option is applied, if false count parameter is number of products
   * that will be added to current products result.
   */
  isAbsolute: Scalars['Boolean'];
  /** Indicator whether the option is already selected (used for "ready category seo mixes") */
  isSelected: Scalars['Boolean'];
  /** Parameter value */
  text: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};

/** Represents a payment */
export type PaymentApi = {
  __typename?: 'Payment';
  /** Localized payment description (domain dependent) */
  description: Maybe<Scalars['String']>;
  /** Additional data for GoPay payment */
  goPayPaymentMethod: Maybe<GoPayPaymentMethodApi>;
  /** Payment images */
  images: Array<ImageApi>;
  /** Localized payment instruction (domain dependent) */
  instruction: Maybe<Scalars['String']>;
  /** Payment image by params */
  mainImage: Maybe<ImageApi>;
  /** Payment name */
  name: Scalars['String'];
  /** Payment position */
  position: Scalars['Int'];
  /** Payment price */
  price: PriceApi;
  /** List of assigned transports */
  transports: Array<TransportApi>;
  /** Type of payment */
  type: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};


/** Represents a payment */
export type PaymentImagesArgsApi = {
  size: Maybe<Scalars['String']>;
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};


/** Represents a payment */
export type PaymentMainImageArgsApi = {
  size?: Maybe<Scalars['String']>;
  type?: Maybe<Scalars['String']>;
};


/** Represents a payment */
export type PaymentPriceArgsApi = {
  cartUuid?: Maybe<Scalars['Uuid']>;
};

/** Represents a payment in order */
export type PaymentInputApi = {
  /** Price for payment */
  price: PriceInputApi;
  /** UUID */
  uuid: Scalars['Uuid'];
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
  exportLink: Scalars['String'];
  /** Newsletter subscription */
  newsletterSubscriber: Maybe<NewsletterSubscriberApi>;
  /** Customer orders */
  orders: Array<OrderApi>;
};

export type PersonalDataAccessRequestInputApi = {
  /** The customer's email address */
  email: Scalars['String'];
  /** One of two possible types for personal data access request - display or export */
  type: Maybe<PersonalDataAccessRequestTypeEnumApi>;
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
  displaySiteContent: Scalars['String'];
  /** URL slug of display site */
  displaySiteSlug: Scalars['String'];
  /** The HTML content of the site where a customer can request exporting his personal data */
  exportSiteContent: Scalars['String'];
  /** URL slug of export site */
  exportSiteSlug: Scalars['String'];
};

/** Represents the price */
export type PriceApi = PriceInterfaceApi & {
  __typename?: 'Price';
  /** Price with VAT */
  priceWithVat: Scalars['Money'];
  /** Price without VAT */
  priceWithoutVat: Scalars['Money'];
  /** Total value of VAT */
  vatAmount: Scalars['Money'];
};

/** Represents the price */
export type PriceInputApi = {
  /** Price with VAT */
  priceWithVat: Scalars['Money'];
  /** Price without VAT */
  priceWithoutVat: Scalars['Money'];
  /** Total value of VAT */
  vatAmount: Scalars['Money'];
};

/** Represents the price */
export type PriceInterfaceApi = {
  /** Price with VAT */
  priceWithVat: Scalars['Money'];
  /** Price without VAT */
  priceWithoutVat: Scalars['Money'];
  /** Total value of VAT */
  vatAmount: Scalars['Money'];
};

/** Represents setting of pricing */
export type PricingSettingApi = {
  __typename?: 'PricingSetting';
  /** Code of the default currency used on the current domain */
  defaultCurrencyCode: Scalars['String'];
  /** Minimum number of decimal places for the price on the current domain */
  minimumFractionDigits: Scalars['Int'];
};

/** Represents a product */
export type ProductApi = {
  accessories: Array<ProductApi>;
  availability: AvailabilityApi;
  /** Number of the stores where the product is available */
  availableStoresCount: Scalars['Int'];
  /** Brand of product */
  brand: Maybe<BrandApi>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Product catalog number */
  catalogNumber: Scalars['String'];
  /** List of categories */
  categories: Array<CategoryApi>;
  description: Maybe<Scalars['String']>;
  /** EAN */
  ean: Maybe<Scalars['String']>;
  /** Number of the stores where the product is exposed */
  exposedStoresCount: Scalars['Int'];
  /** List of downloadable files */
  files: Array<FileApi>;
  /** List of flags */
  flags: Array<FlagApi>;
  /** The full name of the product, which consists of a prefix, name, and a suffix */
  fullName: Scalars['String'];
  /** Distinguishes if the product can be pre-ordered */
  hasPreorder: Scalars['Boolean'];
  /** Product id */
  id: Scalars['Int'];
  /** Product images */
  images: Array<ImageApi>;
  isMainVariant: Scalars['Boolean'];
  isSellingDenied: Scalars['Boolean'];
  isUsingStock: Scalars['Boolean'];
  /** Product link */
  link: Scalars['String'];
  /** Product image by params */
  mainImage: Maybe<ImageApi>;
  /** Localized product name (domain dependent) */
  name: Scalars['String'];
  /** Name prefix */
  namePrefix: Maybe<Scalars['String']>;
  /** Name suffix */
  nameSuffix: Maybe<Scalars['String']>;
  orderingPriority: Scalars['Int'];
  parameters: Array<ParameterApi>;
  /** Product part number */
  partNumber: Maybe<Scalars['String']>;
  /** Product price */
  price: ProductPriceApi;
  productVideos: Array<VideoTokenApi>;
  /** List of related products */
  relatedProducts: Array<ProductApi>;
  /** Seo first level heading of product */
  seoH1: Maybe<Scalars['String']>;
  /** Seo meta description of product */
  seoMetaDescription: Maybe<Scalars['String']>;
  /** Seo title of product */
  seoTitle: Maybe<Scalars['String']>;
  /** Localized product short description (domain dependent) */
  shortDescription: Maybe<Scalars['String']>;
  /** Product URL slug */
  slug: Scalars['String'];
  /** Count of quantity on stock */
  stockQuantity: Scalars['Int'];
  /** List of availabilities in individual stores */
  storeAvailabilities: Array<StoreAvailabilityApi>;
  unit: UnitApi;
  /** List of product's unique selling propositions */
  usps: Array<Scalars['String']>;
  /** UUID */
  uuid: Scalars['Uuid'];
};


/** Represents a product */
export type ProductImagesArgsApi = {
  size: Maybe<Scalars['String']>;
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};


/** Represents a product */
export type ProductMainImageArgsApi = {
  size?: Maybe<Scalars['String']>;
  type?: Maybe<Scalars['String']>;
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
  totalCount: Scalars['Int'];
};

/** An edge in a connection. */
export type ProductEdgeApi = {
  __typename?: 'ProductEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String'];
  /** The item at the end of the edge. */
  node: Maybe<ProductApi>;
};

/** Represents a product filter */
export type ProductFilterApi = {
  /** Array of uuids of brands filter */
  brands: Maybe<Array<Scalars['Uuid']>>;
  /** Array of uuids of flags filter */
  flags: Maybe<Array<Scalars['Uuid']>>;
  /** Maximal price filter */
  maximalPrice: Maybe<Scalars['Money']>;
  /** Minimal price filter */
  minimalPrice: Maybe<Scalars['Money']>;
  /** Only in stock filter */
  onlyInStock: Maybe<Scalars['Boolean']>;
  /** Parameter filter */
  parameters: Maybe<Array<ParameterFilterApi>>;
};

/** Represents a product filter options */
export type ProductFilterOptionsApi = {
  __typename?: 'ProductFilterOptions';
  /** Brands filter options */
  brands: Maybe<Array<BrandFilterOptionApi>>;
  /** Flags filter options */
  flags: Maybe<Array<FlagFilterOptionApi>>;
  /** Number of products in stock that will be filtered */
  inStock: Scalars['Int'];
  /** Maximal price of products for filtering */
  maximalPrice: Scalars['Money'];
  /** Minimal price of products for filtering */
  minimalPrice: Scalars['Money'];
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
  after: Maybe<Scalars['String']>;
  before: Maybe<Scalars['String']>;
  brandSlug: Maybe<Scalars['String']>;
  categorySlug: Maybe<Scalars['String']>;
  filter: Maybe<ProductFilterApi>;
  first: Maybe<Scalars['Int']>;
  flagSlug: Maybe<Scalars['String']>;
  last: Maybe<Scalars['Int']>;
  orderingMode: Maybe<ProductOrderingModeEnumApi>;
  search: Maybe<Scalars['String']>;
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
  isPriceFrom: Scalars['Boolean'];
  /** Price with VAT */
  priceWithVat: Scalars['Money'];
  /** Price without VAT */
  priceWithoutVat: Scalars['Money'];
  /** Total value of VAT */
  vatAmount: Scalars['Money'];
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
  isCustomerUserRegistered: Scalars['Boolean'];
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
  orderSentPageContent: Scalars['String'];
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
};


export type QueryGoPaySwiftsArgsApi = {
  currencyCode: Scalars['String'];
};


export type QueryAccessPersonalDataArgsApi = {
  hash: Scalars['String'];
};


export type QueryAdvertsArgsApi = {
  categoryUuid: Maybe<Scalars['Uuid']>;
  positionName: Maybe<Scalars['String']>;
};


export type QueryArticleArgsApi = {
  urlSlug: Maybe<Scalars['String']>;
  uuid: Maybe<Scalars['Uuid']>;
};


export type QueryArticlesArgsApi = {
  after: Maybe<Scalars['String']>;
  before: Maybe<Scalars['String']>;
  first: Maybe<Scalars['Int']>;
  last: Maybe<Scalars['Int']>;
  placement?: Maybe<Array<ArticlePlacementTypeEnumApi>>;
};


export type QueryArticlesSearchArgsApi = {
  search: Scalars['String'];
};


export type QueryBlogArticleArgsApi = {
  urlSlug: Maybe<Scalars['String']>;
  uuid: Maybe<Scalars['Uuid']>;
};


export type QueryBlogArticlesArgsApi = {
  after: Maybe<Scalars['String']>;
  before: Maybe<Scalars['String']>;
  first: Maybe<Scalars['Int']>;
  last: Maybe<Scalars['Int']>;
  onlyHomepageArticles?: Maybe<Scalars['Boolean']>;
};


export type QueryBlogCategoryArgsApi = {
  urlSlug: Maybe<Scalars['String']>;
  uuid: Maybe<Scalars['Uuid']>;
};


export type QueryBrandArgsApi = {
  urlSlug: Maybe<Scalars['String']>;
  uuid: Maybe<Scalars['Uuid']>;
};


export type QueryBrandSearchArgsApi = {
  search: Scalars['String'];
};


export type QueryCartArgsApi = {
  cartInput: Maybe<CartInputApi>;
};


export type QueryCategoriesSearchArgsApi = {
  after: Maybe<Scalars['String']>;
  before: Maybe<Scalars['String']>;
  first: Maybe<Scalars['Int']>;
  last: Maybe<Scalars['Int']>;
  search: Scalars['String'];
};


export type QueryCategoryArgsApi = {
  urlSlug: Maybe<Scalars['String']>;
  uuid: Maybe<Scalars['Uuid']>;
};


export type QueryComparisonArgsApi = {
  uuid: Maybe<Scalars['Uuid']>;
};


export type QueryFlagArgsApi = {
  urlSlug: Maybe<Scalars['String']>;
  uuid: Maybe<Scalars['Uuid']>;
};


export type QueryIsCustomerUserRegisteredArgsApi = {
  email: Scalars['String'];
};


export type QueryOrderArgsApi = {
  orderNumber: Maybe<Scalars['String']>;
  urlHash: Maybe<Scalars['String']>;
  uuid: Maybe<Scalars['Uuid']>;
};


export type QueryOrderSentPageContentArgsApi = {
  orderUuid: Scalars['Uuid'];
};


export type QueryOrdersArgsApi = {
  after: Maybe<Scalars['String']>;
  before: Maybe<Scalars['String']>;
  first: Maybe<Scalars['Int']>;
  last: Maybe<Scalars['Int']>;
};


export type QueryPaymentArgsApi = {
  uuid: Scalars['Uuid'];
};


export type QueryProductArgsApi = {
  urlSlug: Maybe<Scalars['String']>;
  uuid: Maybe<Scalars['Uuid']>;
};


export type QueryProductsArgsApi = {
  after: Maybe<Scalars['String']>;
  before: Maybe<Scalars['String']>;
  brandSlug: Maybe<Scalars['String']>;
  categorySlug: Maybe<Scalars['String']>;
  filter: Maybe<ProductFilterApi>;
  first: Maybe<Scalars['Int']>;
  flagSlug: Maybe<Scalars['String']>;
  last: Maybe<Scalars['Int']>;
  orderingMode: Maybe<ProductOrderingModeEnumApi>;
  search: Maybe<Scalars['String']>;
};


export type QueryProductsByCatnumsArgsApi = {
  catnums: Array<Scalars['String']>;
};


export type QuerySeoPageArgsApi = {
  pageSlug: Scalars['String'];
};


export type QuerySlugArgsApi = {
  slug: Scalars['String'];
};


export type QueryStoreArgsApi = {
  urlSlug: Maybe<Scalars['String']>;
  uuid: Maybe<Scalars['Uuid']>;
};


export type QueryStoresArgsApi = {
  after: Maybe<Scalars['String']>;
  before: Maybe<Scalars['String']>;
  first: Maybe<Scalars['Int']>;
  last: Maybe<Scalars['Int']>;
};


export type QueryTransportArgsApi = {
  uuid: Scalars['Uuid'];
};


export type QueryTransportsArgsApi = {
  cartUuid: Maybe<Scalars['Uuid']>;
};

export type RecoverPasswordInputApi = {
  /** Customer user email. */
  email: Scalars['String'];
  /** Hash */
  hash: Scalars['String'];
  /** New customer user password. */
  newPassword: Scalars['Password'];
};

export type RefreshTokenInputApi = {
  /** The refresh token. */
  refreshToken: Scalars['String'];
};

/** Represents the main input object to register customer user */
export type RegistrationDataInputApi = {
  /** Uuid of the cart that should be merged to the cart of the newly registered user */
  cartUuid: Maybe<Scalars['Uuid']>;
  /** Billing address city name (will be on the tax invoice) */
  city: Scalars['String'];
  /** Determines whether the customer is a company or not. */
  companyCustomer: Maybe<Scalars['Boolean']>;
  /** The customer’s company name (required when companyCustomer is true) */
  companyName: Maybe<Scalars['String']>;
  /** The customer’s company identification number (required when companyCustomer is true) */
  companyNumber: Maybe<Scalars['String']>;
  /** The customer’s company tax number (required when companyCustomer is true) */
  companyTaxNumber: Maybe<Scalars['String']>;
  /** Billing address country code in ISO 3166-1 alpha-2 (Country will be on the tax invoice) */
  country: Scalars['String'];
  /** The customer's email address */
  email: Scalars['String'];
  /** Customer user first name */
  firstName: Scalars['String'];
  /** Customer user last name */
  lastName: Scalars['String'];
  /** Uuid of the last order that should be paired with the newly registered user */
  lastOrderUuid: Maybe<Scalars['Uuid']>;
  /** Whether customer user should receive newsletters or not */
  newsletterSubscription: Scalars['Boolean'];
  /** Customer user password */
  password: Scalars['Password'];
  /** Billing address zip code (will be on the tax invoice) */
  postcode: Scalars['String'];
  /** Billing address street name (will be on the tax invoice) */
  street: Scalars['String'];
  /** The customer's telephone number */
  telephone: Scalars['String'];
};

/** Represents an currently logged customer user */
export type RegularCustomerUserApi = CustomerUserApi & {
  __typename?: 'RegularCustomerUser';
  /** Billing address city name */
  city: Scalars['String'];
  /** Billing address country */
  country: CountryApi;
  /** Default customer delivery addresses */
  defaultDeliveryAddress: Maybe<DeliveryAddressApi>;
  /** List of delivery addresses */
  deliveryAddresses: Array<DeliveryAddressApi>;
  /** Email address */
  email: Scalars['String'];
  /** First name */
  firstName: Scalars['String'];
  /** Last name */
  lastName: Scalars['String'];
  /** Whether customer user receives newsletters or not */
  newsletterSubscription: Scalars['Boolean'];
  /** Billing address zip code */
  postcode: Scalars['String'];
  /** The name of the customer pricing group */
  pricingGroup: Scalars['String'];
  /** Billing address street name */
  street: Scalars['String'];
  /** Phone number */
  telephone: Maybe<Scalars['String']>;
  /** UUID */
  uuid: Scalars['Uuid'];
};

/** Represents a product */
export type RegularProductApi = BreadcrumbApi & ProductApi & SlugApi & {
  __typename?: 'RegularProduct';
  accessories: Array<ProductApi>;
  availability: AvailabilityApi;
  /** Number of the stores where the product is available */
  availableStoresCount: Scalars['Int'];
  /** Brand of product */
  brand: Maybe<BrandApi>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Product catalog number */
  catalogNumber: Scalars['String'];
  /** List of categories */
  categories: Array<CategoryApi>;
  description: Maybe<Scalars['String']>;
  /** EAN */
  ean: Maybe<Scalars['String']>;
  /** Number of the stores where the product is exposed */
  exposedStoresCount: Scalars['Int'];
  /** List of downloadable files */
  files: Array<FileApi>;
  /** List of flags */
  flags: Array<FlagApi>;
  /** The full name of the product, which consists of a prefix, name, and a suffix */
  fullName: Scalars['String'];
  /** Distinguishes if the product can be pre-ordered */
  hasPreorder: Scalars['Boolean'];
  /** Product id */
  id: Scalars['Int'];
  /** Product images */
  images: Array<ImageApi>;
  isMainVariant: Scalars['Boolean'];
  isSellingDenied: Scalars['Boolean'];
  isUsingStock: Scalars['Boolean'];
  /** Product link */
  link: Scalars['String'];
  /** Product image by params */
  mainImage: Maybe<ImageApi>;
  /** Localized product name (domain dependent) */
  name: Scalars['String'];
  /** Name prefix */
  namePrefix: Maybe<Scalars['String']>;
  /** Name suffix */
  nameSuffix: Maybe<Scalars['String']>;
  orderingPriority: Scalars['Int'];
  parameters: Array<ParameterApi>;
  /** Product part number */
  partNumber: Maybe<Scalars['String']>;
  /** Product price */
  price: ProductPriceApi;
  productVideos: Array<VideoTokenApi>;
  /** List of related products */
  relatedProducts: Array<ProductApi>;
  /** Seo first level heading of product */
  seoH1: Maybe<Scalars['String']>;
  /** Seo meta description of product */
  seoMetaDescription: Maybe<Scalars['String']>;
  /** Seo title of product */
  seoTitle: Maybe<Scalars['String']>;
  /** Localized product short description (domain dependent) */
  shortDescription: Maybe<Scalars['String']>;
  /** Product URL slug */
  slug: Scalars['String'];
  /** Count of quantity on stock */
  stockQuantity: Scalars['Int'];
  /** List of availabilities in individual stores */
  storeAvailabilities: Array<StoreAvailabilityApi>;
  unit: UnitApi;
  /** List of product's unique selling propositions */
  usps: Array<Scalars['String']>;
  /** UUID */
  uuid: Scalars['Uuid'];
};


/** Represents a product */
export type RegularProductImagesArgsApi = {
  size: Maybe<Scalars['String']>;
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};


/** Represents a product */
export type RegularProductMainImageArgsApi = {
  size?: Maybe<Scalars['String']>;
  type?: Maybe<Scalars['String']>;
};

export type RemoveFromCartInputApi = {
  /** Cart item UUID */
  cartItemUuid: Scalars['Uuid'];
  /** Cart identifier, new cart will be created if not provided and customer is not logged in */
  cartUuid: Maybe<Scalars['Uuid']>;
};

export type RemovePromoCodeFromCartInputApi = {
  /** Cart identifier or null if customer is logged in */
  cartUuid: Maybe<Scalars['Uuid']>;
  /** Promo code to be removed */
  promoCode: Scalars['String'];
};

/** Represents SEO settings for specific page */
export type SeoPageApi = {
  __typename?: 'SeoPage';
  /** Page's canonical link */
  canonicalUrl: Maybe<Scalars['String']>;
  /** Description for meta tag */
  metaDescription: Maybe<Scalars['String']>;
  /** Description for og:description meta tag */
  ogDescription: Maybe<Scalars['String']>;
  /** Image for og image meta tag by params */
  ogImage: Maybe<ImageApi>;
  /** Title for og:title meta tag */
  ogTitle: Maybe<Scalars['String']>;
  /** Document's title that is shown in a browser's title */
  title: Maybe<Scalars['String']>;
};


/** Represents SEO settings for specific page */
export type SeoPageOgImageArgsApi = {
  size?: Maybe<Scalars['String']>;
};

/** Represents setting of SEO */
export type SeoSettingApi = {
  __typename?: 'SeoSetting';
  /** Description of the content of a web page */
  metaDescription: Scalars['String'];
  /** Robots.txt's file content */
  robotsTxtContent: Maybe<Scalars['String']>;
  /** Document's title that is shown in a browser's title */
  title: Scalars['String'];
  /** Complement to title */
  titleAddOn: Scalars['String'];
};

/** Represents settings of the current domain */
export type SettingsApi = {
  __typename?: 'Settings';
  /** Main text for contact form */
  contactFormMainText: Scalars['String'];
  /** Settings related to pricing */
  pricing: PricingSettingApi;
  /** Settings related to SEO */
  seo: SeoSettingApi;
};

export type SliderItemApi = {
  __typename?: 'SliderItem';
  /** Text below slider */
  extendedText: Maybe<Scalars['String']>;
  /** Target link of text below slider */
  extendedTextLink: Maybe<Scalars['String']>;
  /** GTM creative */
  gtmCreative: Maybe<Scalars['String']>;
  /** GTM ID */
  gtmId: Scalars['String'];
  /** Slider item images */
  images: Array<ImageApi>;
  /** Target link */
  link: Scalars['String'];
  /** Slider item image by params */
  mainImage: Maybe<ImageApi>;
  /** Slider name */
  name: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};


export type SliderItemImagesArgsApi = {
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};


export type SliderItemMainImageArgsApi = {
  size?: Maybe<Scalars['String']>;
  type?: Maybe<Scalars['String']>;
};

/** Represents entity retrievable by slug */
export type SlugApi = {
  name: Maybe<Scalars['String']>;
  slug: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};

export type StoreApi = BreadcrumbApi & SlugApi & {
  __typename?: 'Store';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Store address city */
  city: Scalars['String'];
  contactInfo: Maybe<Scalars['String']>;
  /** Store address country */
  country: CountryApi;
  /** Store description */
  description: Maybe<Scalars['String']>;
  /** Store images */
  images: Array<ImageApi>;
  /** Is set as default store */
  isDefault: Scalars['Boolean'];
  /** Store location latitude */
  locationLatitude: Maybe<Scalars['String']>;
  /** Store location longitude */
  locationLongitude: Maybe<Scalars['String']>;
  /** Store name */
  name: Scalars['String'];
  /** Store opening hours */
  openingHours: Maybe<Scalars['String']>;
  /** Store opening hours, newlines are rendered as HTML breakline */
  openingHoursHtml: Maybe<Scalars['String']>;
  /** Store address postcode */
  postcode: Scalars['String'];
  /** Store URL slug */
  slug: Scalars['String'];
  specialMessage: Maybe<Scalars['String']>;
  /** Store address street */
  street: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};


export type StoreImagesArgsApi = {
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};

/** Represents an availability in an individual store */
export type StoreAvailabilityApi = {
  __typename?: 'StoreAvailability';
  /** Detailed information about availability */
  availabilityInformation: Scalars['String'];
  /** Availability status in a format suitable for usage in the code */
  availabilityStatus: AvailabilityStatusEnumApi;
  /** Is product exposed on this store */
  exposed: Scalars['Boolean'];
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
  totalCount: Scalars['Int'];
};

/** An edge in a connection. */
export type StoreEdgeApi = {
  __typename?: 'StoreEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String'];
  /** The item at the end of the edge. */
  node: Maybe<StoreApi>;
};

export type TokenApi = {
  __typename?: 'Token';
  accessToken: Scalars['String'];
  refreshToken: Scalars['String'];
};

/** Represents a transport */
export type TransportApi = {
  __typename?: 'Transport';
  /** Number of days until goods are delivered */
  daysUntilDelivery: Scalars['Int'];
  /** Localized transport description (domain dependent) */
  description: Maybe<Scalars['String']>;
  /** Transport images */
  images: Array<ImageApi>;
  /** Localized transport instruction (domain dependent) */
  instruction: Maybe<Scalars['String']>;
  /** Pointer telling if the transport is of type personal pickup */
  isPersonalPickup: Scalars['Boolean'];
  /** Transport image by params */
  mainImage: Maybe<ImageApi>;
  /** Transport name */
  name: Scalars['String'];
  /** List of assigned payments */
  payments: Array<PaymentApi>;
  /** Transport position */
  position: Scalars['Int'];
  /** Transport price */
  price: PriceApi;
  /** Stores available for personal pickup */
  stores: Maybe<StoreConnectionApi>;
  /** Type of transport */
  transportType: TransportTypeApi;
  /** UUID */
  uuid: Scalars['Uuid'];
};


/** Represents a transport */
export type TransportImagesArgsApi = {
  size: Maybe<Scalars['String']>;
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};


/** Represents a transport */
export type TransportMainImageArgsApi = {
  size?: Maybe<Scalars['String']>;
  type?: Maybe<Scalars['String']>;
};


/** Represents a transport */
export type TransportPriceArgsApi = {
  cartUuid?: Maybe<Scalars['Uuid']>;
};

/** Represents a transport in order */
export type TransportInputApi = {
  /** Price for transport */
  price: PriceInputApi;
  /** UUID */
  uuid: Scalars['Uuid'];
};

/** Represents a transport type */
export type TransportTypeApi = {
  __typename?: 'TransportType';
  /** Code of transport */
  code: Scalars['String'];
  /** Name of transport type */
  name: Scalars['String'];
};

/** Represents a unit */
export type UnitApi = {
  __typename?: 'Unit';
  /** Localized unit name (domain dependent) */
  name: Scalars['String'];
};

/** Represents a product */
export type VariantApi = BreadcrumbApi & ProductApi & SlugApi & {
  __typename?: 'Variant';
  accessories: Array<ProductApi>;
  availability: AvailabilityApi;
  /** Number of the stores where the product is available */
  availableStoresCount: Scalars['Int'];
  /** Brand of product */
  brand: Maybe<BrandApi>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Product catalog number */
  catalogNumber: Scalars['String'];
  /** List of categories */
  categories: Array<CategoryApi>;
  description: Maybe<Scalars['String']>;
  /** EAN */
  ean: Maybe<Scalars['String']>;
  /** Number of the stores where the product is exposed */
  exposedStoresCount: Scalars['Int'];
  /** List of downloadable files */
  files: Array<FileApi>;
  /** List of flags */
  flags: Array<FlagApi>;
  /** The full name of the product, which consists of a prefix, name, and a suffix */
  fullName: Scalars['String'];
  /** Distinguishes if the product can be pre-ordered */
  hasPreorder: Scalars['Boolean'];
  /** Product id */
  id: Scalars['Int'];
  /** Product images */
  images: Array<ImageApi>;
  isMainVariant: Scalars['Boolean'];
  isSellingDenied: Scalars['Boolean'];
  isUsingStock: Scalars['Boolean'];
  /** Product link */
  link: Scalars['String'];
  /** Product image by params */
  mainImage: Maybe<ImageApi>;
  mainVariant: Maybe<MainVariantApi>;
  /** Localized product name (domain dependent) */
  name: Scalars['String'];
  /** Name prefix */
  namePrefix: Maybe<Scalars['String']>;
  /** Name suffix */
  nameSuffix: Maybe<Scalars['String']>;
  orderingPriority: Scalars['Int'];
  parameters: Array<ParameterApi>;
  /** Product part number */
  partNumber: Maybe<Scalars['String']>;
  /** Product price */
  price: ProductPriceApi;
  productVideos: Array<VideoTokenApi>;
  /** List of related products */
  relatedProducts: Array<ProductApi>;
  /** Seo first level heading of product */
  seoH1: Maybe<Scalars['String']>;
  /** Seo meta description of product */
  seoMetaDescription: Maybe<Scalars['String']>;
  /** Seo title of product */
  seoTitle: Maybe<Scalars['String']>;
  /** Localized product short description (domain dependent) */
  shortDescription: Maybe<Scalars['String']>;
  /** Product URL slug */
  slug: Scalars['String'];
  /** Count of quantity on stock */
  stockQuantity: Scalars['Int'];
  /** List of availabilities in individual stores */
  storeAvailabilities: Array<StoreAvailabilityApi>;
  unit: UnitApi;
  /** List of product's unique selling propositions */
  usps: Array<Scalars['String']>;
  /** UUID */
  uuid: Scalars['Uuid'];
};


/** Represents a product */
export type VariantImagesArgsApi = {
  size: Maybe<Scalars['String']>;
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};


/** Represents a product */
export type VariantMainImageArgsApi = {
  size?: Maybe<Scalars['String']>;
  type?: Maybe<Scalars['String']>;
};

export type VideoTokenApi = {
  __typename?: 'VideoToken';
  description: Scalars['String'];
  token: Scalars['String'];
};

type AdvertsFragment_AdvertCode_Api = { __typename: 'AdvertCode', code: string, uuid: string, name: string, positionName: string, type: string, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> };

type AdvertsFragment_AdvertImage_Api = { __typename: 'AdvertImage', link: Maybe<string>, uuid: string, name: string, positionName: string, type: string, mainImage: Maybe<{ __typename: 'Image', position: Maybe<number>, name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, mainImageMobile: Maybe<{ __typename: 'Image', position: Maybe<number>, name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> };

export type AdvertsFragmentApi = AdvertsFragment_AdvertCode_Api | AdvertsFragment_AdvertImage_Api;

export type AdvertsQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type AdvertsQueryApi = { __typename?: 'Query', adverts: Array<{ __typename: 'AdvertCode', code: string, uuid: string, name: string, positionName: string, type: string, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> } | { __typename: 'AdvertImage', link: Maybe<string>, uuid: string, name: string, positionName: string, type: string, mainImage: Maybe<{ __typename: 'Image', position: Maybe<number>, name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, mainImageMobile: Maybe<{ __typename: 'Image', position: Maybe<number>, name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> }> };

export type ArticleDetailQueryVariablesApi = Exact<{
  urlSlug: Maybe<Scalars['String']>;
}>;


export type ArticleDetailQueryApi = { __typename?: 'Query', article: Maybe<{ __typename?: 'ArticleLink' } | { __typename: 'ArticleSite', uuid: string, slug: string, placement: string, text: Maybe<string>, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, createdAt: any, articleName: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }> }> };

export type CookiesArticleUrlQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type CookiesArticleUrlQueryApi = { __typename?: 'Query', cookiesArticle: Maybe<{ __typename?: 'ArticleSite', slug: string }> };

export type PrivacyPolicyArticleUrlQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type PrivacyPolicyArticleUrlQueryApi = { __typename?: 'Query', privacyPolicyArticle: Maybe<{ __typename?: 'ArticleSite', slug: string }> };

export type TermsAndConditionsArticleUrlQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type TermsAndConditionsArticleUrlQueryApi = { __typename?: 'Query', termsAndConditionsArticle: Maybe<{ __typename?: 'ArticleSite', slug: string }> };

export type ArticleDetailFragmentApi = { __typename: 'ArticleSite', uuid: string, slug: string, placement: string, text: Maybe<string>, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, createdAt: any, articleName: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }> };

export type SimpleArticleLinkFragmentApi = { __typename: 'ArticleLink', uuid: string, name: string, url: string, placement: string, external: boolean };

export type SimpleArticleSiteFragmentApi = { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean };

type SimpleNotBlogArticleFragment_ArticleLink_Api = { __typename: 'ArticleLink', uuid: string, name: string, url: string, placement: string, external: boolean };

type SimpleNotBlogArticleFragment_ArticleSite_Api = { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean };

export type SimpleNotBlogArticleFragmentApi = SimpleNotBlogArticleFragment_ArticleLink_Api | SimpleNotBlogArticleFragment_ArticleSite_Api;

export type ArticlesQueryVariablesApi = Exact<{
  placement: Maybe<Array<ArticlePlacementTypeEnumApi> | ArticlePlacementTypeEnumApi>;
  first: Maybe<Scalars['Int']>;
}>;


export type ArticlesQueryApi = { __typename?: 'Query', articles: { __typename?: 'ArticleConnection', edges: Maybe<Array<Maybe<{ __typename: 'ArticleEdge', node: Maybe<{ __typename: 'ArticleLink', uuid: string, name: string, url: string, placement: string, external: boolean } | { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean }> }>>> } };

export type BlogArticleConnectionFragmentApi = { __typename: 'BlogArticleConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: Maybe<string>, endCursor: Maybe<string> }, edges: Maybe<Array<Maybe<{ __typename: 'BlogArticleEdge', node: Maybe<{ __typename: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any, perex: Maybe<string>, slug: string, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> }>>> };

export type BlogArticleDetailFragmentApi = { __typename: 'BlogArticle', id: number, uuid: string, name: string, slug: string, link: string, text: Maybe<string>, publishDate: any, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type ListedBlogArticleFragmentApi = { __typename: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any, perex: Maybe<string>, slug: string, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type SimpleBlogArticleFragmentApi = { __typename: 'BlogArticle', name: string, slug: string, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type BlogArticleImageListFragmentApi = { __typename?: 'BlogArticle', mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type BlogArticleImageListGridFragmentApi = { __typename?: 'BlogArticle', mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type BlogArticleDetailQueryVariablesApi = Exact<{
  urlSlug: Maybe<Scalars['String']>;
}>;


export type BlogArticleDetailQueryApi = { __typename?: 'Query', blogArticle: Maybe<{ __typename: 'BlogArticle', id: number, uuid: string, name: string, slug: string, link: string, text: Maybe<string>, publishDate: any, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> };

export type BlogArticlesQueryVariablesApi = Exact<{
  first: Maybe<Scalars['Int']>;
  onlyHomepageArticles: Maybe<Scalars['Boolean']>;
}>;


export type BlogArticlesQueryApi = { __typename?: 'Query', blogArticles: { __typename: 'BlogArticleConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: Maybe<string>, endCursor: Maybe<string> }, edges: Maybe<Array<Maybe<{ __typename: 'BlogArticleEdge', node: Maybe<{ __typename: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any, perex: Maybe<string>, slug: string, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> }>>> } };

type SimpleArticleInterfaceFragment_ArticleSite_Api = { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean };

type SimpleArticleInterfaceFragment_BlogArticle_Api = { __typename: 'BlogArticle', name: string, slug: string, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type SimpleArticleInterfaceFragmentApi = SimpleArticleInterfaceFragment_ArticleSite_Api | SimpleArticleInterfaceFragment_BlogArticle_Api;

export type TokenFragmentsApi = { __typename?: 'Token', accessToken: string, refreshToken: string };

export type LoginVariablesApi = Exact<{
  email: Scalars['String'];
  password: Scalars['Password'];
  previousCartUuid: Maybe<Scalars['Uuid']>;
}>;


export type LoginApi = { __typename?: 'Mutation', Login: { __typename?: 'LoginResult', showCartMergeInfo: boolean, tokens: { __typename?: 'Token', accessToken: string, refreshToken: string } } };

export type LogoutVariablesApi = Exact<{ [key: string]: never; }>;


export type LogoutApi = { __typename?: 'Mutation', Logout: boolean };

export type RefreshTokensVariablesApi = Exact<{
  refreshToken: Scalars['String'];
}>;


export type RefreshTokensApi = { __typename?: 'Mutation', RefreshTokens: { __typename?: 'Token', accessToken: string, refreshToken: string } };

export type AvailabilityFragmentApi = { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi };

export type BlogCategoriesFragmentApi = { __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> };

export type BlogCategoryDetailFragmentApi = { __typename: 'BlogCategory', uuid: string, name: string, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, articlesTotalCount: number, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, blogCategoriesTree: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }> };

export type SimpleBlogCategoryFragmentApi = { __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> };

export type BlogCategoriesVariablesApi = Exact<{ [key: string]: never; }>;


export type BlogCategoriesApi = { __typename?: 'Query', blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }> };

export type BlogCategoryArticlesVariablesApi = Exact<{
  uuid: Scalars['Uuid'];
  endCursor: Scalars['String'];
  pageSize: Maybe<Scalars['Int']>;
}>;


export type BlogCategoryArticlesApi = { __typename?: 'Query', blogCategory: Maybe<{ __typename?: 'BlogCategory', blogArticles: { __typename: 'BlogArticleConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: Maybe<string>, endCursor: Maybe<string> }, edges: Maybe<Array<Maybe<{ __typename: 'BlogArticleEdge', node: Maybe<{ __typename: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any, perex: Maybe<string>, slug: string, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> }>>> } }> };

export type BlogCategoryQueryVariablesApi = Exact<{
  urlSlug: Maybe<Scalars['String']>;
}>;


export type BlogCategoryQueryApi = { __typename?: 'Query', blogCategory: Maybe<{ __typename: 'BlogCategory', uuid: string, name: string, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, articlesTotalCount: number, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, blogCategoriesTree: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }> }> };

export type BlogUrlQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type BlogUrlQueryApi = { __typename?: 'Query', blogCategories: Array<{ __typename?: 'BlogCategory', link: string }> };

export type BrandDetailFragmentApi = { __typename: 'Brand', id: number, uuid: string, slug: string, name: string, seoH1: Maybe<string>, description: Maybe<string>, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> } } };

export type ListedBrandFragmentApi = { __typename: 'Brand', uuid: string, name: string, slug: string, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type SimpleBrandFragmentApi = { __typename: 'Brand', name: string, slug: string };

export type BrandImageDefaultFragmentApi = { __typename?: 'Brand', mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type BrandDetailQueryVariablesApi = Exact<{
  urlSlug: Maybe<Scalars['String']>;
  orderingMode: Maybe<ProductOrderingModeEnumApi>;
  filter: Maybe<ProductFilterApi>;
}>;


export type BrandDetailQueryApi = { __typename?: 'Query', brand: Maybe<{ __typename: 'Brand', id: number, uuid: string, slug: string, name: string, seoH1: Maybe<string>, description: Maybe<string>, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> } } }> };

export type BrandsQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type BrandsQueryApi = { __typename?: 'Query', brands: Array<{ __typename: 'Brand', uuid: string, name: string, slug: string, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> };

export type BreadcrumbFragmentApi = { __typename: 'Link', name: string, slug: string };

export type CartFragmentApi = { __typename: 'Cart', uuid: Maybe<string>, remainingAmountWithVatForFreeTransport: Maybe<string>, promoCode: Maybe<string>, selectedPickupPlaceIdentifier: Maybe<string>, paymentGoPayBankSwift: Maybe<string>, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> } }, transport: Maybe<{ __typename: 'Transport', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }>, stores: Maybe<{ __typename: 'StoreConnection', edges: Maybe<Array<Maybe<{ __typename: 'StoreEdge', node: Maybe<{ __typename: 'Store', slug: string, name: string, description: Maybe<string>, openingHoursHtml: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, street: string, postcode: string, city: string, identifier: string, country: { __typename: 'Country', name: string, code: string } }> }>>> }>, transportType: { __typename: 'TransportType', code: string } }>, payment: Maybe<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }> };

export type CartItemFragmentApi = { __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } };

export type CartItemModificationsFragmentApi = { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }> };

export type CartModificationsFragmentApi = { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> } };

export type CartPaymentModificationsFragmentApi = { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean };

export type CartPromoCodeModificationsFragmentApi = { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> };

export type CartTransportModificationsFragmentApi = { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean };

export type AddToCartMutationVariablesApi = Exact<{
  input: AddToCartInputApi;
}>;


export type AddToCartMutationApi = { __typename?: 'Mutation', AddToCart: { __typename?: 'AddToCartResult', cart: { __typename: 'Cart', uuid: Maybe<string>, remainingAmountWithVatForFreeTransport: Maybe<string>, promoCode: Maybe<string>, selectedPickupPlaceIdentifier: Maybe<string>, paymentGoPayBankSwift: Maybe<string>, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> } }, transport: Maybe<{ __typename: 'Transport', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }>, stores: Maybe<{ __typename: 'StoreConnection', edges: Maybe<Array<Maybe<{ __typename: 'StoreEdge', node: Maybe<{ __typename: 'Store', slug: string, name: string, description: Maybe<string>, openingHoursHtml: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, street: string, postcode: string, city: string, identifier: string, country: { __typename: 'Country', name: string, code: string } }> }>>> }>, transportType: { __typename: 'TransportType', code: string } }>, payment: Maybe<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }> }, addProductResult: { __typename?: 'AddProductResult', addedQuantity: number, isNew: boolean, notOnStockQuantity: number, cartItem: { __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } } } } };

export type ApplyPromoCodeToCartMutationVariablesApi = Exact<{
  input: ApplyPromoCodeToCartInputApi;
}>;


export type ApplyPromoCodeToCartMutationApi = { __typename?: 'Mutation', ApplyPromoCodeToCart: { __typename: 'Cart', uuid: Maybe<string>, remainingAmountWithVatForFreeTransport: Maybe<string>, promoCode: Maybe<string>, selectedPickupPlaceIdentifier: Maybe<string>, paymentGoPayBankSwift: Maybe<string>, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> } }, transport: Maybe<{ __typename: 'Transport', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }>, stores: Maybe<{ __typename: 'StoreConnection', edges: Maybe<Array<Maybe<{ __typename: 'StoreEdge', node: Maybe<{ __typename: 'Store', slug: string, name: string, description: Maybe<string>, openingHoursHtml: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, street: string, postcode: string, city: string, identifier: string, country: { __typename: 'Country', name: string, code: string } }> }>>> }>, transportType: { __typename: 'TransportType', code: string } }>, payment: Maybe<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }> } };

export type ChangePaymentInCartMutationVariablesApi = Exact<{
  input: ChangePaymentInCartInputApi;
}>;


export type ChangePaymentInCartMutationApi = { __typename?: 'Mutation', ChangePaymentInCart: { __typename: 'Cart', uuid: Maybe<string>, remainingAmountWithVatForFreeTransport: Maybe<string>, promoCode: Maybe<string>, selectedPickupPlaceIdentifier: Maybe<string>, paymentGoPayBankSwift: Maybe<string>, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> } }, transport: Maybe<{ __typename: 'Transport', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }>, stores: Maybe<{ __typename: 'StoreConnection', edges: Maybe<Array<Maybe<{ __typename: 'StoreEdge', node: Maybe<{ __typename: 'Store', slug: string, name: string, description: Maybe<string>, openingHoursHtml: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, street: string, postcode: string, city: string, identifier: string, country: { __typename: 'Country', name: string, code: string } }> }>>> }>, transportType: { __typename: 'TransportType', code: string } }>, payment: Maybe<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }> } };

export type ChangeTransportInCartMutationVariablesApi = Exact<{
  input: ChangeTransportInCartInputApi;
}>;


export type ChangeTransportInCartMutationApi = { __typename?: 'Mutation', ChangeTransportInCart: { __typename: 'Cart', uuid: Maybe<string>, remainingAmountWithVatForFreeTransport: Maybe<string>, promoCode: Maybe<string>, selectedPickupPlaceIdentifier: Maybe<string>, paymentGoPayBankSwift: Maybe<string>, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> } }, transport: Maybe<{ __typename: 'Transport', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }>, stores: Maybe<{ __typename: 'StoreConnection', edges: Maybe<Array<Maybe<{ __typename: 'StoreEdge', node: Maybe<{ __typename: 'Store', slug: string, name: string, description: Maybe<string>, openingHoursHtml: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, street: string, postcode: string, city: string, identifier: string, country: { __typename: 'Country', name: string, code: string } }> }>>> }>, transportType: { __typename: 'TransportType', code: string } }>, payment: Maybe<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }> } };

export type RemoveFromCartMutationVariablesApi = Exact<{
  input: RemoveFromCartInputApi;
}>;


export type RemoveFromCartMutationApi = { __typename?: 'Mutation', RemoveFromCart: { __typename: 'Cart', uuid: Maybe<string>, remainingAmountWithVatForFreeTransport: Maybe<string>, promoCode: Maybe<string>, selectedPickupPlaceIdentifier: Maybe<string>, paymentGoPayBankSwift: Maybe<string>, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> } }, transport: Maybe<{ __typename: 'Transport', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }>, stores: Maybe<{ __typename: 'StoreConnection', edges: Maybe<Array<Maybe<{ __typename: 'StoreEdge', node: Maybe<{ __typename: 'Store', slug: string, name: string, description: Maybe<string>, openingHoursHtml: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, street: string, postcode: string, city: string, identifier: string, country: { __typename: 'Country', name: string, code: string } }> }>>> }>, transportType: { __typename: 'TransportType', code: string } }>, payment: Maybe<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }> } };

export type RemovePromoCodeFromCartMutationVariablesApi = Exact<{
  input: RemovePromoCodeFromCartInputApi;
}>;


export type RemovePromoCodeFromCartMutationApi = { __typename?: 'Mutation', RemovePromoCodeFromCart: { __typename: 'Cart', uuid: Maybe<string>, remainingAmountWithVatForFreeTransport: Maybe<string>, promoCode: Maybe<string>, selectedPickupPlaceIdentifier: Maybe<string>, paymentGoPayBankSwift: Maybe<string>, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> } }, transport: Maybe<{ __typename: 'Transport', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }>, stores: Maybe<{ __typename: 'StoreConnection', edges: Maybe<Array<Maybe<{ __typename: 'StoreEdge', node: Maybe<{ __typename: 'Store', slug: string, name: string, description: Maybe<string>, openingHoursHtml: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, street: string, postcode: string, city: string, identifier: string, country: { __typename: 'Country', name: string, code: string } }> }>>> }>, transportType: { __typename: 'TransportType', code: string } }>, payment: Maybe<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }> } };

export type CartQueryVariablesApi = Exact<{
  cartUuid: Maybe<Scalars['Uuid']>;
}>;


export type CartQueryApi = { __typename?: 'Query', cart: Maybe<{ __typename: 'Cart', uuid: Maybe<string>, remainingAmountWithVatForFreeTransport: Maybe<string>, promoCode: Maybe<string>, selectedPickupPlaceIdentifier: Maybe<string>, paymentGoPayBankSwift: Maybe<string>, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> } }, transport: Maybe<{ __typename: 'Transport', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }>, stores: Maybe<{ __typename: 'StoreConnection', edges: Maybe<Array<Maybe<{ __typename: 'StoreEdge', node: Maybe<{ __typename: 'Store', slug: string, name: string, description: Maybe<string>, openingHoursHtml: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, street: string, postcode: string, city: string, identifier: string, country: { __typename: 'Country', name: string, code: string } }> }>>> }>, transportType: { __typename: 'TransportType', code: string } }>, payment: Maybe<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }> }> };

export type MinimalCartQueryVariablesApi = Exact<{
  cartUuid: Maybe<Scalars['Uuid']>;
}>;


export type MinimalCartQueryApi = { __typename?: 'Query', cart: Maybe<{ __typename?: 'Cart', items: Array<{ __typename?: 'CartItem', uuid: string }>, transport: Maybe<{ __typename?: 'Transport', uuid: string }>, payment: Maybe<{ __typename?: 'Payment', uuid: string }> }> };

export type CategoryDetailFragmentApi = { __typename: 'Category', id: number, uuid: string, slug: string, originalCategorySlug: Maybe<string>, name: string, description: Maybe<string>, seoH1: Maybe<string>, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, children: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, products: { __typename: 'ProductConnection', totalCount: number }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }>, linkedCategories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, products: { __typename: 'ProductConnection', totalCount: number }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }>, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> } }, readyCategorySeoMixLinks: Array<{ __typename: 'Link', name: string, slug: string }> };

export type CategoryImagesDefaultFragmentApi = { __typename?: 'Category', mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type CategoryPreviewFragmentApi = { __typename: 'Category', uuid: string, name: string, slug: string, products: { __typename: 'ProductConnection', totalCount: number }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type ListedCategoryConnectionFragmentApi = { __typename: 'CategoryConnection', totalCount: number, edges: Maybe<Array<Maybe<{ __typename: 'CategoryEdge', node: Maybe<{ __typename: 'Category', uuid: string, name: string, slug: string, products: { __typename: 'ProductConnection', totalCount: number }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> }>>> };

export type ListedCategoryFragmentApi = { __typename: 'Category', uuid: string, name: string, slug: string, products: { __typename: 'ProductConnection', totalCount: number }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type NavigationSubCategoriesLinkFragmentApi = { __typename: 'Category', uuid: string, children: Array<{ __typename: 'Category', name: string, slug: string }> };

export type SimpleCategoryConnectionFragmentApi = { __typename: 'CategoryConnection', totalCount: number, edges: Maybe<Array<Maybe<{ __typename: 'CategoryEdge', node: Maybe<{ __typename: 'Category', uuid: string, name: string, slug: string }> }>>> };

export type SimpleCategoryFragmentApi = { __typename: 'Category', uuid: string, name: string, slug: string };

export type CategoryDetailQueryVariablesApi = Exact<{
  urlSlug: Maybe<Scalars['String']>;
  orderingMode: Maybe<ProductOrderingModeEnumApi>;
  filter: Maybe<ProductFilterApi>;
}>;


export type CategoryDetailQueryApi = { __typename?: 'Query', category: Maybe<{ __typename: 'Category', id: number, uuid: string, slug: string, originalCategorySlug: Maybe<string>, name: string, description: Maybe<string>, seoH1: Maybe<string>, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, children: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, products: { __typename: 'ProductConnection', totalCount: number }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }>, linkedCategories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, products: { __typename: 'ProductConnection', totalCount: number }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }>, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> } }, readyCategorySeoMixLinks: Array<{ __typename: 'Link', name: string, slug: string }> }> };

export type PromotedCategoriesQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type PromotedCategoriesQueryApi = { __typename?: 'Query', promotedCategories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, products: { __typename: 'ProductConnection', totalCount: number }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> };

export type ContactMutationVariablesApi = Exact<{
  input: ContactInputApi;
}>;


export type ContactMutationApi = { __typename?: 'Mutation', Contact: boolean };

export type CountryFragmentApi = { __typename: 'Country', name: string, code: string };

export type CountriesQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type CountriesQueryApi = { __typename?: 'Query', countries: Array<{ __typename: 'Country', name: string, code: string }> };

type CustomerUserFragment_CompanyCustomerUser_Api = { __typename: 'CompanyCustomerUser', companyName: Maybe<string>, companyNumber: Maybe<string>, companyTaxNumber: Maybe<string>, uuid: string, firstName: string, lastName: string, email: string, telephone: Maybe<string>, street: string, city: string, postcode: string, newsletterSubscription: boolean, pricingGroup: string, country: { __typename: 'Country', name: string, code: string }, defaultDeliveryAddress: Maybe<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }>, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }> };

type CustomerUserFragment_RegularCustomerUser_Api = { __typename: 'RegularCustomerUser', uuid: string, firstName: string, lastName: string, email: string, telephone: Maybe<string>, street: string, city: string, postcode: string, newsletterSubscription: boolean, pricingGroup: string, country: { __typename: 'Country', name: string, code: string }, defaultDeliveryAddress: Maybe<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }>, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }> };

export type CustomerUserFragmentApi = CustomerUserFragment_CompanyCustomerUser_Api | CustomerUserFragment_RegularCustomerUser_Api;

export type DeliveryAddressFragmentApi = { __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> };

export type ChangePasswordMutationVariablesApi = Exact<{
  email: Scalars['String'];
  oldPassword: Scalars['Password'];
  newPassword: Scalars['Password'];
}>;


export type ChangePasswordMutationApi = { __typename?: 'Mutation', ChangePassword: { __typename?: 'CompanyCustomerUser', email: string } | { __typename?: 'RegularCustomerUser', email: string } };

export type ChangePersonalDataMutationVariablesApi = Exact<{
  input: ChangePersonalDataInputApi;
}>;


export type ChangePersonalDataMutationApi = { __typename?: 'Mutation', ChangePersonalData: { __typename: 'CompanyCustomerUser', companyName: Maybe<string>, companyNumber: Maybe<string>, companyTaxNumber: Maybe<string>, uuid: string, firstName: string, lastName: string, email: string, telephone: Maybe<string>, street: string, city: string, postcode: string, newsletterSubscription: boolean, pricingGroup: string, country: { __typename: 'Country', name: string, code: string }, defaultDeliveryAddress: Maybe<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }>, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }> } | { __typename: 'RegularCustomerUser', uuid: string, firstName: string, lastName: string, email: string, telephone: Maybe<string>, street: string, city: string, postcode: string, newsletterSubscription: boolean, pricingGroup: string, country: { __typename: 'Country', name: string, code: string }, defaultDeliveryAddress: Maybe<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }>, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }> } };

export type DeleteDeliveryAddressMutationVariablesApi = Exact<{
  deliveryAddressUuid: Scalars['Uuid'];
}>;


export type DeleteDeliveryAddressMutationApi = { __typename?: 'Mutation', DeleteDeliveryAddress: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }> };

export type SetDefaultDeliveryAddressMutationVariablesApi = Exact<{
  deliveryAddressUuid: Scalars['Uuid'];
}>;


export type SetDefaultDeliveryAddressMutationApi = { __typename?: 'Mutation', SetDefaultDeliveryAddress: { __typename?: 'CompanyCustomerUser', uuid: string, defaultDeliveryAddress: Maybe<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }> } | { __typename?: 'RegularCustomerUser', uuid: string, defaultDeliveryAddress: Maybe<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }> } };

export type CurrentCustomerUserQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type CurrentCustomerUserQueryApi = { __typename?: 'Query', currentCustomerUser: Maybe<{ __typename: 'CompanyCustomerUser', companyName: Maybe<string>, companyNumber: Maybe<string>, companyTaxNumber: Maybe<string>, uuid: string, firstName: string, lastName: string, email: string, telephone: Maybe<string>, street: string, city: string, postcode: string, newsletterSubscription: boolean, pricingGroup: string, country: { __typename: 'Country', name: string, code: string }, defaultDeliveryAddress: Maybe<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }>, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }> } | { __typename: 'RegularCustomerUser', uuid: string, firstName: string, lastName: string, email: string, telephone: Maybe<string>, street: string, city: string, postcode: string, newsletterSubscription: boolean, pricingGroup: string, country: { __typename: 'Country', name: string, code: string }, defaultDeliveryAddress: Maybe<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }>, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }> }> };

export type IsCustomerUserRegisteredQueryVariablesApi = Exact<{
  email: Scalars['String'];
}>;


export type IsCustomerUserRegisteredQueryApi = { __typename?: 'Query', isCustomerUserRegistered: boolean };

export type FlagDetailFragmentApi = { __typename: 'Flag', uuid: string, slug: string, name: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> } } };

export type SimpleFlagFragmentApi = { __typename: 'Flag', uuid: string, name: string, rgbColor: string };

export type FlagDetailQueryVariablesApi = Exact<{
  urlSlug: Maybe<Scalars['String']>;
  orderingMode: Maybe<ProductOrderingModeEnumApi>;
  filter: Maybe<ProductFilterApi>;
}>;


export type FlagDetailQueryApi = { __typename?: 'Query', flag: Maybe<{ __typename: 'Flag', uuid: string, slug: string, name: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> } } }> };

export type AdditionalSizeFragmentApi = { __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> };

export type ImageSizeFragmentApi = { __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> };

export type ImageSizesFragmentApi = { __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> };

export type CategoriesByColumnFragmentApi = { __typename: 'NavigationItem', name: string, link: string, categoriesByColumns: Array<{ __typename: 'NavigationItemCategoriesByColumns', columnNumber: number, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, children: Array<{ __typename: 'Category', name: string, slug: string }> }> }> };

export type ColumnCategoriesFragmentApi = { __typename: 'NavigationItemCategoriesByColumns', columnNumber: number, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, children: Array<{ __typename: 'Category', name: string, slug: string }> }> };

export type ColumnCategoryFragmentApi = { __typename: 'Category', uuid: string, name: string, slug: string, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, children: Array<{ __typename: 'Category', name: string, slug: string }> };

export type NavigationQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type NavigationQueryApi = { __typename?: 'Query', navigation: Array<{ __typename: 'NavigationItem', name: string, link: string, categoriesByColumns: Array<{ __typename: 'NavigationItemCategoriesByColumns', columnNumber: number, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, children: Array<{ __typename: 'Category', name: string, slug: string }> }> }> }> };

export type NewsletterSubscribeMutationVariablesApi = Exact<{
  email: Scalars['String'];
}>;


export type NewsletterSubscribeMutationApi = { __typename?: 'Mutation', NewsletterSubscribe: boolean };

export type NotificationBarsFragmentApi = { __typename: 'NotificationBar', text: string, rgbColor: string, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type NotificationBarsVariablesApi = Exact<{ [key: string]: never; }>;


export type NotificationBarsApi = { __typename?: 'Query', notificationBars: Maybe<Array<{ __typename: 'NotificationBar', text: string, rgbColor: string, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }>> };

export type LastOrderFragmentApi = { __typename: 'Order', pickupPlaceIdentifier: Maybe<string>, deliveryStreet: Maybe<string>, deliveryCity: Maybe<string>, deliveryPostcode: Maybe<string>, transport: { __typename: 'Transport', uuid: string, name: string, description: Maybe<string> }, payment: { __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }, deliveryCountry: Maybe<{ __typename: 'Country', name: string, code: string }> };

export type ListedOrderFragmentApi = { __typename: 'Order', uuid: string, number: string, creationDate: any, productItems: Array<{ __typename: 'OrderItem', quantity: number }>, transport: { __typename: 'Transport', name: string, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }, payment: { __typename: 'Payment', name: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } };

export type OrderDetailFragmentApi = { __typename: 'Order', uuid: string, number: string, creationDate: any, status: string, firstName: Maybe<string>, lastName: Maybe<string>, email: string, telephone: string, companyName: Maybe<string>, companyNumber: Maybe<string>, companyTaxNumber: Maybe<string>, street: string, city: string, postcode: string, differentDeliveryAddress: boolean, deliveryFirstName: Maybe<string>, deliveryLastName: Maybe<string>, deliveryCompanyName: Maybe<string>, deliveryTelephone: Maybe<string>, deliveryStreet: Maybe<string>, deliveryCity: Maybe<string>, deliveryPostcode: Maybe<string>, note: Maybe<string>, urlHash: string, promoCode: Maybe<string>, trackingNumber: Maybe<string>, trackingUrl: Maybe<string>, items: Array<{ __typename: 'OrderItem', name: string, vatRate: string, quantity: number, unit: Maybe<string>, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }>, transport: { __typename: 'Transport', name: string }, payment: { __typename: 'Payment', name: string }, country: { __typename: 'Country', name: string }, deliveryCountry: Maybe<{ __typename: 'Country', name: string }> };

export type OrderDetailItemFragmentApi = { __typename: 'OrderItem', name: string, vatRate: string, quantity: number, unit: Maybe<string>, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } };

export type OrderListFragmentApi = { __typename: 'OrderConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: Maybe<string>, endCursor: Maybe<string> }, edges: Maybe<Array<Maybe<{ __typename: 'OrderEdge', cursor: string, node: Maybe<{ __typename: 'Order', uuid: string, number: string, creationDate: any, productItems: Array<{ __typename: 'OrderItem', quantity: number }>, transport: { __typename: 'Transport', name: string, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }, payment: { __typename: 'Payment', name: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }> }>>> };

export type CheckPaymentStatusMutationVariablesApi = Exact<{
  orderUuid: Scalars['Uuid'];
}>;


export type CheckPaymentStatusMutationApi = { __typename?: 'Mutation', CheckPaymentStatus: boolean };

export type CreateOrderMutationVariablesApi = Exact<{
  firstName: Scalars['String'];
  lastName: Scalars['String'];
  email: Scalars['String'];
  telephone: Scalars['String'];
  onCompanyBehalf: Scalars['Boolean'];
  companyName: Maybe<Scalars['String']>;
  companyNumber: Maybe<Scalars['String']>;
  companyTaxNumber: Maybe<Scalars['String']>;
  street: Scalars['String'];
  city: Scalars['String'];
  postcode: Scalars['String'];
  country: Scalars['String'];
  differentDeliveryAddress: Scalars['Boolean'];
  deliveryFirstName: Maybe<Scalars['String']>;
  deliveryLastName: Maybe<Scalars['String']>;
  deliveryCompanyName: Maybe<Scalars['String']>;
  deliveryTelephone: Maybe<Scalars['String']>;
  deliveryStreet: Maybe<Scalars['String']>;
  deliveryCity: Maybe<Scalars['String']>;
  deliveryPostcode: Maybe<Scalars['String']>;
  deliveryCountry: Maybe<Scalars['String']>;
  deliveryAddressUuid: Maybe<Scalars['Uuid']>;
  note: Maybe<Scalars['String']>;
  cartUuid: Maybe<Scalars['Uuid']>;
  newsletterSubscription: Maybe<Scalars['Boolean']>;
}>;


export type CreateOrderMutationApi = { __typename?: 'Mutation', CreateOrder: { __typename?: 'CreateOrderResult', orderCreated: boolean, order: Maybe<{ __typename?: 'Order', number: string, uuid: string, urlHash: string, payment: { __typename?: 'Payment', type: string } }>, cart: Maybe<{ __typename: 'Cart', uuid: Maybe<string>, remainingAmountWithVatForFreeTransport: Maybe<string>, promoCode: Maybe<string>, selectedPickupPlaceIdentifier: Maybe<string>, paymentGoPayBankSwift: Maybe<string>, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }> } }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> } }, transport: Maybe<{ __typename: 'Transport', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }>, stores: Maybe<{ __typename: 'StoreConnection', edges: Maybe<Array<Maybe<{ __typename: 'StoreEdge', node: Maybe<{ __typename: 'Store', slug: string, name: string, description: Maybe<string>, openingHoursHtml: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, street: string, postcode: string, city: string, identifier: string, country: { __typename: 'Country', name: string, code: string } }> }>>> }>, transportType: { __typename: 'TransportType', code: string } }>, payment: Maybe<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }> }> } };

export type PayOrderMutationVariablesApi = Exact<{
  orderUuid: Scalars['Uuid'];
}>;


export type PayOrderMutationApi = { __typename?: 'Mutation', PayOrder: { __typename?: 'PaymentSetupCreationData', goPayCreatePaymentSetup: Maybe<{ __typename?: 'GoPayCreatePaymentSetup', gatewayUrl: string, goPayId: string, embedJs: string }> } };

export type LastOrderQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type LastOrderQueryApi = { __typename?: 'Query', lastOrder: Maybe<{ __typename: 'Order', pickupPlaceIdentifier: Maybe<string>, deliveryStreet: Maybe<string>, deliveryCity: Maybe<string>, deliveryPostcode: Maybe<string>, transport: { __typename: 'Transport', uuid: string, name: string, description: Maybe<string> }, payment: { __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }, deliveryCountry: Maybe<{ __typename: 'Country', name: string, code: string }> }> };

export type OrderDetailByHashQueryVariablesApi = Exact<{
  urlHash: Maybe<Scalars['String']>;
}>;


export type OrderDetailByHashQueryApi = { __typename?: 'Query', order: Maybe<{ __typename: 'Order', uuid: string, number: string, creationDate: any, status: string, firstName: Maybe<string>, lastName: Maybe<string>, email: string, telephone: string, companyName: Maybe<string>, companyNumber: Maybe<string>, companyTaxNumber: Maybe<string>, street: string, city: string, postcode: string, differentDeliveryAddress: boolean, deliveryFirstName: Maybe<string>, deliveryLastName: Maybe<string>, deliveryCompanyName: Maybe<string>, deliveryTelephone: Maybe<string>, deliveryStreet: Maybe<string>, deliveryCity: Maybe<string>, deliveryPostcode: Maybe<string>, note: Maybe<string>, urlHash: string, promoCode: Maybe<string>, trackingNumber: Maybe<string>, trackingUrl: Maybe<string>, items: Array<{ __typename: 'OrderItem', name: string, vatRate: string, quantity: number, unit: Maybe<string>, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }>, transport: { __typename: 'Transport', name: string }, payment: { __typename: 'Payment', name: string }, country: { __typename: 'Country', name: string }, deliveryCountry: Maybe<{ __typename: 'Country', name: string }> }> };

export type OrderDetailQueryVariablesApi = Exact<{
  orderNumber: Maybe<Scalars['String']>;
}>;


export type OrderDetailQueryApi = { __typename?: 'Query', order: Maybe<{ __typename: 'Order', uuid: string, number: string, creationDate: any, status: string, firstName: Maybe<string>, lastName: Maybe<string>, email: string, telephone: string, companyName: Maybe<string>, companyNumber: Maybe<string>, companyTaxNumber: Maybe<string>, street: string, city: string, postcode: string, differentDeliveryAddress: boolean, deliveryFirstName: Maybe<string>, deliveryLastName: Maybe<string>, deliveryCompanyName: Maybe<string>, deliveryTelephone: Maybe<string>, deliveryStreet: Maybe<string>, deliveryCity: Maybe<string>, deliveryPostcode: Maybe<string>, note: Maybe<string>, urlHash: string, promoCode: Maybe<string>, trackingNumber: Maybe<string>, trackingUrl: Maybe<string>, items: Array<{ __typename: 'OrderItem', name: string, vatRate: string, quantity: number, unit: Maybe<string>, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }>, transport: { __typename: 'Transport', name: string }, payment: { __typename: 'Payment', name: string }, country: { __typename: 'Country', name: string }, deliveryCountry: Maybe<{ __typename: 'Country', name: string }> }> };

export type OrderSentPageContentVariablesApi = Exact<{
  orderUuid: Scalars['Uuid'];
}>;


export type OrderSentPageContentApi = { __typename?: 'Query', orderSentPageContent: string };

export type OrdersQueryVariablesApi = Exact<{
  after: Maybe<Scalars['String']>;
  first: Maybe<Scalars['Int']>;
}>;


export type OrdersQueryApi = { __typename?: 'Query', orders: Maybe<{ __typename: 'OrderConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: Maybe<string>, endCursor: Maybe<string> }, edges: Maybe<Array<Maybe<{ __typename: 'OrderEdge', cursor: string, node: Maybe<{ __typename: 'Order', uuid: string, number: string, creationDate: any, productItems: Array<{ __typename: 'OrderItem', quantity: number }>, transport: { __typename: 'Transport', name: string, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }, payment: { __typename: 'Payment', name: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }> }>>> }> };

export type PageInfoFragmentApi = { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: Maybe<string>, endCursor: Maybe<string> };

export type ParameterFragmentApi = { __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> };

export type PasswordRecoveryMutationVariablesApi = Exact<{
  email: Scalars['String'];
}>;


export type PasswordRecoveryMutationApi = { __typename?: 'Mutation', RequestPasswordRecovery: string };

export type RecoverPasswordMutationVariablesApi = Exact<{
  email: Scalars['String'];
  hash: Scalars['String'];
  newPassword: Scalars['Password'];
}>;


export type RecoverPasswordMutationApi = { __typename?: 'Mutation', RecoverPassword: { __typename?: 'LoginResult', tokens: { __typename?: 'Token', accessToken: string, refreshToken: string } } };

export type SimplePaymentFragmentApi = { __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> };

export type GoPaySwiftsQueryVariablesApi = Exact<{
  currencyCode: Scalars['String'];
}>;


export type GoPaySwiftsQueryApi = { __typename?: 'Query', GoPaySwifts: Array<{ __typename?: 'GoPayBankSwift', name: string, imageNormalUrl: string, swift: string }> };

export type PersonalDataRequestMutationVariablesApi = Exact<{
  email: Scalars['String'];
  type: Maybe<PersonalDataAccessRequestTypeEnumApi>;
}>;


export type PersonalDataRequestMutationApi = { __typename?: 'Mutation', RequestPersonalDataAccess: { __typename?: 'PersonalDataPage', displaySiteSlug: string, exportSiteSlug: string } };

export type PersonalDataDetailQueryVariablesApi = Exact<{
  hash: Scalars['String'];
}>;


export type PersonalDataDetailQueryApi = { __typename?: 'Query', accessPersonalData: { __typename: 'PersonalData', exportLink: string, orders: Array<{ __typename: 'Order', uuid: string, city: string, companyName: Maybe<string>, number: string, creationDate: any, firstName: Maybe<string>, lastName: Maybe<string>, telephone: string, companyNumber: Maybe<string>, companyTaxNumber: Maybe<string>, street: string, postcode: string, deliveryFirstName: Maybe<string>, deliveryLastName: Maybe<string>, deliveryCompanyName: Maybe<string>, deliveryTelephone: Maybe<string>, deliveryStreet: Maybe<string>, deliveryCity: Maybe<string>, deliveryPostcode: Maybe<string>, country: { __typename: 'Country', name: string, code: string }, deliveryCountry: Maybe<{ __typename: 'Country', name: string, code: string }>, payment: { __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }, transport: { __typename: 'Transport', uuid: string, name: string, description: Maybe<string> }, productItems: Array<{ __typename: 'OrderItem', name: string, vatRate: string, quantity: number, unit: Maybe<string>, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }>, totalPrice: { __typename?: 'Price', priceWithVat: string } }>, customerUser: Maybe<{ __typename: 'CompanyCustomerUser', companyName: Maybe<string>, companyNumber: Maybe<string>, companyTaxNumber: Maybe<string>, uuid: string, firstName: string, lastName: string, email: string, telephone: Maybe<string>, street: string, city: string, postcode: string, newsletterSubscription: boolean, pricingGroup: string, country: { __typename: 'Country', name: string, code: string }, defaultDeliveryAddress: Maybe<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }>, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }> } | { __typename: 'RegularCustomerUser', uuid: string, firstName: string, lastName: string, email: string, telephone: Maybe<string>, street: string, city: string, postcode: string, newsletterSubscription: boolean, pricingGroup: string, country: { __typename: 'Country', name: string, code: string }, defaultDeliveryAddress: Maybe<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }>, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: Maybe<string>, street: Maybe<string>, city: Maybe<string>, postcode: Maybe<string>, telephone: Maybe<string>, firstName: Maybe<string>, lastName: Maybe<string>, country: Maybe<{ __typename: 'Country', name: string, code: string }> }> }>, newsletterSubscriber: Maybe<{ __typename: 'NewsletterSubscriber', email: string, createdAt: any }> } };

export type PersonalDataPageTextQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type PersonalDataPageTextQueryApi = { __typename?: 'Query', personalDataPage: Maybe<{ __typename?: 'PersonalDataPage', displaySiteContent: string, exportSiteContent: string }> };

export type PriceFragmentApi = { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string };

export type ProductFilterOptionsBrandsFragmentApi = { __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } };

export type ProductFilterOptionsFlagsFragmentApi = { __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } };

export type ProductFilterOptionsFragmentApi = { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> };

export type ProductFilterOptionsParametersCheckboxFragmentApi = { __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> };

export type ProductFilterOptionsParametersColorFragmentApi = { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> };

export type ProductFilterOptionsParametersSliderFragmentApi = { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> };

type ComparedProductFragment_MainVariant_Api = { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> };

type ComparedProductFragment_RegularProduct_Api = { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> };

type ComparedProductFragment_Variant_Api = { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> };

export type ComparedProductFragmentApi = ComparedProductFragment_MainVariant_Api | ComparedProductFragment_RegularProduct_Api | ComparedProductFragment_Variant_Api;

export type ListedProductConnectionFragmentApi = { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> }, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: Maybe<string>, endCursor: Maybe<string> }, edges: Maybe<Array<Maybe<{ __typename: 'ProductEdge', node: Maybe<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }> }>>> };

export type ListedProductConnectionPreviewFragmentApi = { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> } };

type ListedProductFragment_MainVariant_Api = { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> };

type ListedProductFragment_RegularProduct_Api = { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> };

type ListedProductFragment_Variant_Api = { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> };

export type ListedProductFragmentApi = ListedProductFragment_MainVariant_Api | ListedProductFragment_RegularProduct_Api | ListedProductFragment_Variant_Api;

export type MainVariantDetailFragmentApi = { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: Maybe<string>, nameSuffix: Maybe<string>, catalogNumber: string, ean: Maybe<string>, description: Maybe<string>, stockQuantity: number, isSellingDenied: boolean, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, isMainVariant: boolean, variants: Array<{ __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, storeAvailabilities: Array<{ __typename: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: AvailabilityStatusEnumApi, store: Maybe<{ __typename: 'Store', uuid: string, slug: string, description: Maybe<string>, street: string, city: string, postcode: string, openingHours: Maybe<string>, contactInfo: Maybe<string>, specialMessage: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, storeName: string, country: { __typename: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }>, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }> };

export type ProductDetailFragmentApi = { __typename: 'RegularProduct', shortDescription: Maybe<string>, availableStoresCount: number, exposedStoresCount: number, id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: Maybe<string>, nameSuffix: Maybe<string>, catalogNumber: string, ean: Maybe<string>, description: Maybe<string>, stockQuantity: number, isSellingDenied: boolean, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, isMainVariant: boolean, storeAvailabilities: Array<{ __typename: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: AvailabilityStatusEnumApi, store: Maybe<{ __typename: 'Store', uuid: string, slug: string, description: Maybe<string>, street: string, city: string, postcode: string, openingHours: Maybe<string>, contactInfo: Maybe<string>, specialMessage: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, storeName: string, country: { __typename: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> }>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }>, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }> };

type ProductDetailInterfaceFragment_MainVariant_Api = { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: Maybe<string>, nameSuffix: Maybe<string>, catalogNumber: string, ean: Maybe<string>, description: Maybe<string>, stockQuantity: number, isSellingDenied: boolean, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, isMainVariant: boolean, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }>, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }> };

type ProductDetailInterfaceFragment_RegularProduct_Api = { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: Maybe<string>, nameSuffix: Maybe<string>, catalogNumber: string, ean: Maybe<string>, description: Maybe<string>, stockQuantity: number, isSellingDenied: boolean, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, isMainVariant: boolean, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }>, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }> };

type ProductDetailInterfaceFragment_Variant_Api = { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: Maybe<string>, nameSuffix: Maybe<string>, catalogNumber: string, ean: Maybe<string>, description: Maybe<string>, stockQuantity: number, isSellingDenied: boolean, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, isMainVariant: boolean, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }>, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }> };

export type ProductDetailInterfaceFragmentApi = ProductDetailInterfaceFragment_MainVariant_Api | ProductDetailInterfaceFragment_RegularProduct_Api | ProductDetailInterfaceFragment_Variant_Api;

export type ProductPriceFragmentApi = { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean };

export type ProductComparisonFragmentApi = { __typename: 'Comparison', uuid: string, products: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }> };

type SimpleProductFragment_MainVariant_Api = { __typename: 'MainVariant', id: number, uuid: string, catalogNumber: string, fullName: string, slug: string, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi } };

type SimpleProductFragment_RegularProduct_Api = { __typename: 'RegularProduct', id: number, uuid: string, catalogNumber: string, fullName: string, slug: string, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi } };

type SimpleProductFragment_Variant_Api = { __typename: 'Variant', id: number, uuid: string, catalogNumber: string, fullName: string, slug: string, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, unit: { __typename?: 'Unit', name: string }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi } };

export type SimpleProductFragmentApi = SimpleProductFragment_MainVariant_Api | SimpleProductFragment_RegularProduct_Api | SimpleProductFragment_Variant_Api;

export type VideoTokenFragmentApi = { __typename: 'VideoToken', description: string, token: string };

export type AddProductToComparisonMutationVariablesApi = Exact<{
  productUuid: Scalars['Uuid'];
  comparisonUuid: Maybe<Scalars['Uuid']>;
}>;


export type AddProductToComparisonMutationApi = { __typename?: 'Mutation', addProductToComparison: { __typename: 'Comparison', uuid: string, products: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }> } };

export type CleanComparisonMutationVariablesApi = Exact<{
  comparisonUuid: Maybe<Scalars['Uuid']>;
}>;


export type CleanComparisonMutationApi = { __typename?: 'Mutation', cleanComparison: string };

export type RemoveProductFromComparisonMutationVariablesApi = Exact<{
  productUuid: Scalars['Uuid'];
  comparisonUuid: Maybe<Scalars['Uuid']>;
}>;


export type RemoveProductFromComparisonMutationApi = { __typename?: 'Mutation', removeProductFromComparison: Maybe<{ __typename: 'Comparison', uuid: string, products: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }> }> };

export type BrandProductsQueryVariablesApi = Exact<{
  endCursor: Scalars['String'];
  orderingMode: Maybe<ProductOrderingModeEnumApi>;
  filter: Maybe<ProductFilterApi>;
  urlSlug: Maybe<Scalars['String']>;
  pageSize: Maybe<Scalars['Int']>;
}>;


export type BrandProductsQueryApi = { __typename?: 'Query', products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> }, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: Maybe<string>, endCursor: Maybe<string> }, edges: Maybe<Array<Maybe<{ __typename: 'ProductEdge', node: Maybe<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }> }>>> } };

export type CategoryProductsQueryVariablesApi = Exact<{
  endCursor: Scalars['String'];
  orderingMode: Maybe<ProductOrderingModeEnumApi>;
  filter: Maybe<ProductFilterApi>;
  urlSlug: Maybe<Scalars['String']>;
  pageSize: Maybe<Scalars['Int']>;
}>;


export type CategoryProductsQueryApi = { __typename?: 'Query', products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> }, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: Maybe<string>, endCursor: Maybe<string> }, edges: Maybe<Array<Maybe<{ __typename: 'ProductEdge', node: Maybe<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }> }>>> } };

export type ComparisonQueryVariablesApi = Exact<{
  comparisonUuid: Maybe<Scalars['Uuid']>;
}>;


export type ComparisonQueryApi = { __typename?: 'Query', comparison: Maybe<{ __typename: 'Comparison', uuid: string, products: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }> }> };

export type FlagProductsQueryVariablesApi = Exact<{
  endCursor: Scalars['String'];
  orderingMode: Maybe<ProductOrderingModeEnumApi>;
  filter: Maybe<ProductFilterApi>;
  urlSlug: Maybe<Scalars['String']>;
  pageSize: Maybe<Scalars['Int']>;
}>;


export type FlagProductsQueryApi = { __typename?: 'Query', products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> }, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: Maybe<string>, endCursor: Maybe<string> }, edges: Maybe<Array<Maybe<{ __typename: 'ProductEdge', node: Maybe<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }> }>>> } };

export type ProductDetailQueryVariablesApi = Exact<{
  urlSlug: Maybe<Scalars['String']>;
}>;


export type ProductDetailQueryApi = { __typename?: 'Query', product: Maybe<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: Maybe<string>, nameSuffix: Maybe<string>, catalogNumber: string, ean: Maybe<string>, description: Maybe<string>, stockQuantity: number, isSellingDenied: boolean, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, isMainVariant: boolean, variants: Array<{ __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, storeAvailabilities: Array<{ __typename: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: AvailabilityStatusEnumApi, store: Maybe<{ __typename: 'Store', uuid: string, slug: string, description: Maybe<string>, street: string, city: string, postcode: string, openingHours: Maybe<string>, contactInfo: Maybe<string>, specialMessage: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, storeName: string, country: { __typename: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }>, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }> } | { __typename: 'RegularProduct', shortDescription: Maybe<string>, availableStoresCount: number, exposedStoresCount: number, id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: Maybe<string>, nameSuffix: Maybe<string>, catalogNumber: string, ean: Maybe<string>, description: Maybe<string>, stockQuantity: number, isSellingDenied: boolean, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, isMainVariant: boolean, storeAvailabilities: Array<{ __typename: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: AvailabilityStatusEnumApi, store: Maybe<{ __typename: 'Store', uuid: string, slug: string, description: Maybe<string>, street: string, city: string, postcode: string, openingHours: Maybe<string>, contactInfo: Maybe<string>, specialMessage: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, storeName: string, country: { __typename: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> }>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }>, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }> } | { __typename?: 'Variant', mainVariant: Maybe<{ __typename?: 'MainVariant', slug: string }> }> };

export type ProductsByCatnumsVariablesApi = Exact<{
  catnums: Array<Scalars['String']> | Scalars['String'];
}>;


export type ProductsByCatnumsApi = { __typename?: 'Query', productsByCatnums: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }> };

export type PromotedProductsQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type PromotedProductsQueryApi = { __typename?: 'Query', promotedProducts: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }> };

export type SearchProductsQueryVariablesApi = Exact<{
  endCursor: Scalars['String'];
  orderingMode: Maybe<ProductOrderingModeEnumApi>;
  filter: Maybe<ProductFilterApi>;
  search: Scalars['String'];
  pageSize: Maybe<Scalars['Int']>;
}>;


export type SearchProductsQueryApi = { __typename?: 'Query', products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> }, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: Maybe<string>, endCursor: Maybe<string> }, edges: Maybe<Array<Maybe<{ __typename: 'ProductEdge', node: Maybe<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }> }>>> } };

export type RegistrationMutationVariablesApi = Exact<{
  firstName: Scalars['String'];
  lastName: Scalars['String'];
  email: Scalars['String'];
  password: Scalars['Password'];
  telephone: Scalars['String'];
  street: Scalars['String'];
  city: Scalars['String'];
  postcode: Scalars['String'];
  country: Scalars['String'];
  companyCustomer: Scalars['Boolean'];
  companyName: Maybe<Scalars['String']>;
  companyNumber: Maybe<Scalars['String']>;
  companyTaxNumber: Maybe<Scalars['String']>;
  newsletterSubscription: Scalars['Boolean'];
  previousCartUuid: Maybe<Scalars['Uuid']>;
  lastOrderUuid: Maybe<Scalars['Uuid']>;
}>;


export type RegistrationMutationApi = { __typename?: 'Mutation', Register: { __typename?: 'LoginResult', showCartMergeInfo: boolean, tokens: { __typename?: 'Token', accessToken: string, refreshToken: string } } };

export type AutocompleteSearchQueryVariablesApi = Exact<{
  search: Scalars['String'];
  maxProductCount: Maybe<Scalars['Int']>;
  maxCategoryCount: Maybe<Scalars['Int']>;
}>;


export type AutocompleteSearchQueryApi = { __typename?: 'Query', articlesSearch: Array<{ __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean } | { __typename: 'BlogArticle', name: string, slug: string, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }>, brandSearch: Array<{ __typename: 'Brand', name: string, slug: string }>, categoriesSearch: { __typename: 'CategoryConnection', totalCount: number, edges: Maybe<Array<Maybe<{ __typename: 'CategoryEdge', node: Maybe<{ __typename: 'Category', uuid: string, name: string, slug: string }> }>>> }, productsSearch: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> }, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: Maybe<string>, endCursor: Maybe<string> }, edges: Maybe<Array<Maybe<{ __typename: 'ProductEdge', node: Maybe<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }> }>>> } };

export type SearchQueryVariablesApi = Exact<{
  search: Scalars['String'];
  orderingMode: Maybe<ProductOrderingModeEnumApi>;
  filter: Maybe<ProductFilterApi>;
  pageSize: Maybe<Scalars['Int']>;
}>;


export type SearchQueryApi = { __typename?: 'Query', articlesSearch: Array<{ __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean } | { __typename: 'BlogArticle', name: string, slug: string, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }>, brandSearch: Array<{ __typename: 'Brand', uuid: string, name: string, slug: string, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }>, categoriesSearch: { __typename: 'CategoryConnection', totalCount: number, edges: Maybe<Array<Maybe<{ __typename: 'CategoryEdge', node: Maybe<{ __typename: 'Category', uuid: string, name: string, slug: string, products: { __typename: 'ProductConnection', totalCount: number }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> }>>> }, productsSearch: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> } } };

export type SeoPageFragmentApi = { __typename: 'SeoPage', title: Maybe<string>, metaDescription: Maybe<string>, canonicalUrl: Maybe<string>, ogTitle: Maybe<string>, ogDescription: Maybe<string>, ogImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type SeoPageQueryVariablesApi = Exact<{
  pageSlug: Scalars['String'];
}>;


export type SeoPageQueryApi = { __typename?: 'Query', seoPage: Maybe<{ __typename: 'SeoPage', title: Maybe<string>, metaDescription: Maybe<string>, canonicalUrl: Maybe<string>, ogTitle: Maybe<string>, ogDescription: Maybe<string>, ogImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> };

export type PricingSettingFragmentApi = { __typename: 'PricingSetting', defaultCurrencyCode: string, minimumFractionDigits: number };

export type SeoSettingFragmentApi = { __typename: 'SeoSetting', title: string, titleAddOn: string, metaDescription: string };

export type SettingsQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type SettingsQueryApi = { __typename?: 'Query', settings: Maybe<{ __typename?: 'Settings', contactFormMainText: string, pricing: { __typename: 'PricingSetting', defaultCurrencyCode: string, minimumFractionDigits: number }, seo: { __typename: 'SeoSetting', title: string, titleAddOn: string, metaDescription: string } }> };

export type SliderItemFragmentApi = { __typename: 'SliderItem', uuid: string, name: string, link: string, extendedText: Maybe<string>, extendedTextLink: Maybe<string>, webMainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, mobileMainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type SliderItemImagesMobileDefaultFragmentApi = { __typename?: 'SliderItem', mobileMainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type SliderItemImagesWebDefaultFragmentApi = { __typename?: 'SliderItem', webMainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type SliderItemsQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type SliderItemsQueryApi = { __typename?: 'Query', sliderItems: Array<{ __typename: 'SliderItem', uuid: string, name: string, link: string, extendedText: Maybe<string>, extendedTextLink: Maybe<string>, webMainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, mobileMainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> };

export type SlugTypeQueryVariablesApi = Exact<{
  slug: Scalars['String'];
}>;


export type SlugTypeQueryApi = { __typename?: 'Query', slug: Maybe<{ __typename: 'ArticleSite' } | { __typename: 'BlogArticle' } | { __typename: 'BlogCategory' } | { __typename: 'Brand' } | { __typename: 'Category' } | { __typename: 'Flag' } | { __typename: 'MainVariant' } | { __typename: 'RegularProduct' } | { __typename: 'Store' } | { __typename: 'Variant' }> };

export type SlugQueryVariablesApi = Exact<{
  slug: Scalars['String'];
  orderingMode: Maybe<ProductOrderingModeEnumApi>;
  filter: Maybe<ProductFilterApi>;
}>;


export type SlugQueryApi = { __typename?: 'Query', slug: Maybe<{ __typename: 'ArticleSite', uuid: string, slug: string, placement: string, text: Maybe<string>, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, createdAt: any, articleName: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }> } | { __typename: 'BlogArticle', id: number, uuid: string, name: string, slug: string, link: string, text: Maybe<string>, publishDate: any, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> } | { __typename: 'BlogCategory', uuid: string, name: string, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, articlesTotalCount: number, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, blogCategoriesTree: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }>, parent: Maybe<{ __typename?: 'BlogCategory', name: string }> }> } | { __typename: 'Brand', id: number, uuid: string, slug: string, name: string, seoH1: Maybe<string>, description: Maybe<string>, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> } } } | { __typename: 'Category', id: number, uuid: string, slug: string, originalCategorySlug: Maybe<string>, name: string, description: Maybe<string>, seoH1: Maybe<string>, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, children: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, products: { __typename: 'ProductConnection', totalCount: number }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }>, linkedCategories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, products: { __typename: 'ProductConnection', totalCount: number }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }>, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> } }, readyCategorySeoMixLinks: Array<{ __typename: 'Link', name: string, slug: string }> } | { __typename: 'Flag', uuid: string, slug: string, name: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, products: { __typename: 'ProductConnection', orderingMode: ProductOrderingModeEnumApi, defaultOrderingMode: Maybe<ProductOrderingModeEnumApi>, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Maybe<Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }>>, flags: Maybe<Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }>>, parameters: Maybe<Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: Maybe<string>, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: Maybe<number>, unit: Maybe<{ __typename: 'Unit', name: string }> }>> } } } | { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: Maybe<string>, nameSuffix: Maybe<string>, catalogNumber: string, ean: Maybe<string>, description: Maybe<string>, stockQuantity: number, isSellingDenied: boolean, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, isMainVariant: boolean, variants: Array<{ __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, storeAvailabilities: Array<{ __typename: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: AvailabilityStatusEnumApi, store: Maybe<{ __typename: 'Store', uuid: string, slug: string, description: Maybe<string>, street: string, city: string, postcode: string, openingHours: Maybe<string>, contactInfo: Maybe<string>, specialMessage: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, storeName: string, country: { __typename: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }>, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }> } | { __typename: 'RegularProduct', shortDescription: Maybe<string>, availableStoresCount: number, exposedStoresCount: number, id: number, uuid: string, slug: string, fullName: string, name: string, namePrefix: Maybe<string>, nameSuffix: Maybe<string>, catalogNumber: string, ean: Maybe<string>, description: Maybe<string>, stockQuantity: number, isSellingDenied: boolean, seoTitle: Maybe<string>, seoMetaDescription: Maybe<string>, isMainVariant: boolean, storeAvailabilities: Array<{ __typename: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: AvailabilityStatusEnumApi, store: Maybe<{ __typename: 'Store', uuid: string, slug: string, description: Maybe<string>, street: string, city: string, postcode: string, openingHours: Maybe<string>, contactInfo: Maybe<string>, specialMessage: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, storeName: string, country: { __typename: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> }>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, images: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename: 'Category', name: string }> }>, brand: Maybe<{ __typename: 'Brand', name: string, slug: string }>, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: AvailabilityStatusEnumApi }, productVideos: Array<{ __typename: 'VideoToken', description: string, token: string }> } | { __typename: 'Store', uuid: string, slug: string, description: Maybe<string>, street: string, city: string, postcode: string, openingHours: Maybe<string>, contactInfo: Maybe<string>, specialMessage: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, storeName: string, country: { __typename: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> } | { __typename: 'Variant', mainVariant: Maybe<{ __typename?: 'MainVariant', slug: string }> }> };

export type StoreAvailabilityFragmentApi = { __typename: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: AvailabilityStatusEnumApi, store: Maybe<{ __typename: 'Store', uuid: string, slug: string, description: Maybe<string>, street: string, city: string, postcode: string, openingHours: Maybe<string>, contactInfo: Maybe<string>, specialMessage: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, storeName: string, country: { __typename: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> };

export type ListedStoreConnectionFragmentApi = { __typename: 'StoreConnection', edges: Maybe<Array<Maybe<{ __typename: 'StoreEdge', node: Maybe<{ __typename: 'Store', slug: string, name: string, description: Maybe<string>, openingHoursHtml: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, street: string, postcode: string, city: string, identifier: string, country: { __typename: 'Country', name: string, code: string } }> }>>> };

export type ListedStoreFragmentApi = { __typename: 'Store', slug: string, name: string, description: Maybe<string>, openingHoursHtml: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, street: string, postcode: string, city: string, identifier: string, country: { __typename: 'Country', name: string, code: string } };

export type StoreDetailFragmentApi = { __typename: 'Store', uuid: string, slug: string, description: Maybe<string>, street: string, city: string, postcode: string, openingHours: Maybe<string>, contactInfo: Maybe<string>, specialMessage: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, storeName: string, country: { __typename: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> };

export type StoreDetailQueryVariablesApi = Exact<{
  urlSlug: Maybe<Scalars['String']>;
}>;


export type StoreDetailQueryApi = { __typename?: 'Query', store: Maybe<{ __typename: 'Store', uuid: string, slug: string, description: Maybe<string>, street: string, city: string, postcode: string, openingHours: Maybe<string>, contactInfo: Maybe<string>, specialMessage: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, storeName: string, country: { __typename: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }> }> };

export type StoreQueryVariablesApi = Exact<{
  uuid: Maybe<Scalars['Uuid']>;
}>;


export type StoreQueryApi = { __typename?: 'Query', store: Maybe<{ __typename: 'Store', slug: string, name: string, description: Maybe<string>, openingHoursHtml: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, street: string, postcode: string, city: string, identifier: string, country: { __typename: 'Country', name: string, code: string } }> };

export type StoresQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type StoresQueryApi = { __typename?: 'Query', stores: { __typename: 'StoreConnection', edges: Maybe<Array<Maybe<{ __typename: 'StoreEdge', node: Maybe<{ __typename: 'Store', slug: string, name: string, description: Maybe<string>, openingHoursHtml: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, street: string, postcode: string, city: string, identifier: string, country: { __typename: 'Country', name: string, code: string } }> }>>> } };

export type SimpleTransportFragmentApi = { __typename: 'Transport', uuid: string, name: string, description: Maybe<string> };

export type TransportWithAvailablePaymentsAndStoresFragmentApi = { __typename: 'Transport', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }>, stores: Maybe<{ __typename: 'StoreConnection', edges: Maybe<Array<Maybe<{ __typename: 'StoreEdge', node: Maybe<{ __typename: 'Store', slug: string, name: string, description: Maybe<string>, openingHoursHtml: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, street: string, postcode: string, city: string, identifier: string, country: { __typename: 'Country', name: string, code: string } }> }>>> }>, transportType: { __typename: 'TransportType', code: string } };

export type TransportsQueryVariablesApi = Exact<{
  cartUuid: Maybe<Scalars['Uuid']>;
}>;


export type TransportsQueryApi = { __typename?: 'Query', transports: Array<{ __typename: 'Transport', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, daysUntilDelivery: number, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: Maybe<string>, instruction: Maybe<string>, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: Maybe<{ __typename: 'Image', name: Maybe<string>, sizes: Array<{ __typename: 'ImageSize', size: string, url: string, width: Maybe<number>, height: Maybe<number>, additionalSizes: Array<{ __typename: 'AdditionalSize', height: Maybe<number>, media: string, url: string, width: Maybe<number> }> }> }>, goPayPaymentMethod: Maybe<{ __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string }> }>, stores: Maybe<{ __typename: 'StoreConnection', edges: Maybe<Array<Maybe<{ __typename: 'StoreEdge', node: Maybe<{ __typename: 'Store', slug: string, name: string, description: Maybe<string>, openingHoursHtml: Maybe<string>, locationLatitude: Maybe<string>, locationLongitude: Maybe<string>, street: string, postcode: string, city: string, identifier: string, country: { __typename: 'Country', name: string, code: string } }> }>>> }>, transportType: { __typename: 'TransportType', code: string } }> };


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
export const AdditionalSizeFragmentApi = gql`
    fragment AdditionalSizeFragment on AdditionalSize {
  __typename
  height
  media
  url
  width
}
    `;
export const ImageSizeFragmentApi = gql`
    fragment ImageSizeFragment on ImageSize {
  __typename
  size
  url
  width
  height
  additionalSizes {
    ...AdditionalSizeFragment
  }
}
    ${AdditionalSizeFragmentApi}`;
export const ImageSizesFragmentApi = gql`
    fragment ImageSizesFragment on Image {
  __typename
  name
  sizes {
    ...ImageSizeFragment
  }
}
    ${ImageSizeFragmentApi}`;
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
      position
      ...ImageSizesFragment
    }
    mainImageMobile: mainImage(type: "mobile") {
      position
      ...ImageSizesFragment
    }
  }
}
    ${SimpleCategoryFragmentApi}
${ImageSizesFragmentApi}`;
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
  startCursor
  endCursor
}
    `;
export const BlogArticleImageListFragmentApi = gql`
    fragment BlogArticleImageListFragment on BlogArticle {
  mainImage {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
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
  ...BlogArticleImageListFragment
  publishDate
  perex
  slug
  blogCategories {
    ...SimpleBlogCategoryFragment
  }
}
    ${BlogArticleImageListFragmentApi}
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
export const BlogArticleImageListGridFragmentApi = gql`
    fragment BlogArticleImageListGridFragment on BlogArticle {
  mainImage {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
export const BlogArticleDetailFragmentApi = gql`
    fragment BlogArticleDetailFragment on BlogArticle {
  __typename
  id
  uuid
  name
  slug
  link
  ...BlogArticleImageListGridFragment
  breadcrumb {
    ...BreadcrumbFragment
  }
  text
  publishDate
  seoTitle
  seoMetaDescription
}
    ${BlogArticleImageListGridFragmentApi}
${BreadcrumbFragmentApi}`;
export const SimpleBlogArticleFragmentApi = gql`
    fragment SimpleBlogArticleFragment on BlogArticle {
  __typename
  name
  slug
  ...BlogArticleImageListFragment
}
    ${BlogArticleImageListFragmentApi}`;
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
  blogCategoriesTree {
    ...BlogCategoriesFragment
  }
  articlesTotalCount
}
    ${BreadcrumbFragmentApi}
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
  description
  mainImage {
    ...ImageSizesFragment
  }
  products(orderingMode: $orderingMode, filter: $filter) {
    ...ListedProductConnectionPreviewFragment
  }
  seoTitle
  seoMetaDescription
}
    ${BreadcrumbFragmentApi}
${ImageSizesFragmentApi}
${ListedProductConnectionPreviewFragmentApi}`;
export const BrandImageDefaultFragmentApi = gql`
    fragment BrandImageDefaultFragment on Brand {
  mainImage {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
export const ListedBrandFragmentApi = gql`
    fragment ListedBrandFragment on Brand {
  __typename
  uuid
  name
  slug
  ...BrandImageDefaultFragment
}
    ${BrandImageDefaultFragmentApi}`;
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
    fullName
    catalogNumber
    stockQuantity
    flags {
      ...SimpleFlagFragment
    }
    mainImage {
      ...ImageSizesFragment
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
${ImageSizesFragmentApi}
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
    ...ImageSizesFragment
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
${ImageSizesFragmentApi}`;
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
  openingHoursHtml
  locationLatitude
  locationLongitude
  street
  postcode
  city
  country {
    ...CountryFragment
  }
}
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
    ...ImageSizesFragment
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
${ImageSizesFragmentApi}
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
}
    ${CartItemFragmentApi}
${PriceFragmentApi}
${CartModificationsFragmentApi}
${TransportWithAvailablePaymentsAndStoresFragmentApi}
${SimplePaymentFragmentApi}`;
export const CategoryImagesDefaultFragmentApi = gql`
    fragment CategoryImagesDefaultFragment on Category {
  mainImage {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
export const CategoryPreviewFragmentApi = gql`
    fragment CategoryPreviewFragment on Category {
  __typename
  uuid
  name
  slug
  ...CategoryImagesDefaultFragment
  products {
    __typename
    totalCount
  }
}
    ${CategoryImagesDefaultFragmentApi}`;
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
  breadcrumb {
    ...BreadcrumbFragment
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
  readyCategorySeoMixLinks {
    __typename
    name
    slug
  }
  seoTitle
  seoMetaDescription
}
    ${BreadcrumbFragmentApi}
${CategoryPreviewFragmentApi}
${ListedProductConnectionPreviewFragmentApi}`;
export const ListedCategoryFragmentApi = gql`
    fragment ListedCategoryFragment on Category {
  __typename
  uuid
  name
  slug
  ...CategoryImagesDefaultFragment
  products {
    __typename
    totalCount
  }
}
    ${CategoryImagesDefaultFragmentApi}`;
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
}
    ${BreadcrumbFragmentApi}
${ListedProductConnectionPreviewFragmentApi}`;
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
  ...CategoryImagesDefaultFragment
  ...NavigationSubCategoriesLinkFragment
}
    ${CategoryImagesDefaultFragmentApi}
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
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
export const SimpleTransportFragmentApi = gql`
    fragment SimpleTransportFragment on Transport {
  __typename
  uuid
  name
  description
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
}
    ${OrderDetailItemFragmentApi}`;
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
      ...ImageSizesFragment
    }
  }
  payment {
    __typename
    name
  }
  totalPrice {
    ...PriceFragment
  }
}
    ${ImageSizesFragmentApi}
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
    ...ImageSizesFragment
  }
  price {
    ...ProductPriceFragment
  }
  availability {
    ...AvailabilityFragment
  }
  availableStoresCount
  exposedStoresCount
  catalogNumber
  brand {
    ...SimpleBrandFragment
  }
  categories {
    __typename
    name
  }
  isMainVariant
}
    ${SimpleFlagFragmentApi}
${ImageSizesFragmentApi}
${ProductPriceFragmentApi}
${AvailabilityFragmentApi}
${SimpleBrandFragmentApi}`;
export const ListedProductConnectionFragmentApi = gql`
    fragment ListedProductConnectionFragment on ProductConnection {
  __typename
  orderingMode
  defaultOrderingMode
  totalCount
  productFilterOptions {
    ...ProductFilterOptionsFragment
  }
  pageInfo {
    ...PageInfoFragment
  }
  edges {
    __typename
    node {
      ...ListedProductFragment
    }
  }
}
    ${ProductFilterOptionsFragmentApi}
${PageInfoFragmentApi}
${ListedProductFragmentApi}`;
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
    ...ImageSizesFragment
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
  isMainVariant
  productVideos {
    ...VideoTokenFragment
  }
}
    ${BreadcrumbFragmentApi}
${ImageSizesFragmentApi}
${ProductPriceFragmentApi}
${ParameterFragmentApi}
${ListedProductFragmentApi}
${SimpleBrandFragmentApi}
${SimpleFlagFragmentApi}
${AvailabilityFragmentApi}
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
  openingHours
  contactInfo
  specialMessage
  locationLatitude
  locationLongitude
  breadcrumb {
    ...BreadcrumbFragment
  }
  storeImages: images(sizes: ["default", "thumbnail"]) {
    ...ImageSizesFragment
  }
}
    ${CountryFragmentApi}
${BreadcrumbFragmentApi}
${ImageSizesFragmentApi}`;
export const StoreAvailabilityFragmentApi = gql`
    fragment StoreAvailabilityFragment on StoreAvailability {
  __typename
  exposed
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
  storeAvailabilities {
    ...StoreAvailabilityFragment
  }
  availableStoresCount
  exposedStoresCount
}
    ${ProductDetailInterfaceFragmentApi}
${StoreAvailabilityFragmentApi}`;
export const ComparedProductFragmentApi = gql`
    fragment ComparedProductFragment on Product {
  ...ListedProductFragment
  parameters {
    ...ParameterFragment
  }
}
    ${ListedProductFragmentApi}
${ParameterFragmentApi}`;
export const ProductComparisonFragmentApi = gql`
    fragment ProductComparisonFragment on Comparison {
  __typename
  uuid
  products {
    ...ComparedProductFragment
  }
}
    ${ComparedProductFragmentApi}`;
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
    ...ImageSizesFragment
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
${ImageSizesFragmentApi}
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
  ogImage(size: "default") {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
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
export const SliderItemImagesWebDefaultFragmentApi = gql`
    fragment SliderItemImagesWebDefaultFragment on SliderItem {
  webMainImage: mainImage(type: "web", size: "default") {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
export const SliderItemImagesMobileDefaultFragmentApi = gql`
    fragment SliderItemImagesMobileDefaultFragment on SliderItem {
  mobileMainImage: mainImage(type: "mobile", size: "default") {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
export const SliderItemFragmentApi = gql`
    fragment SliderItemFragment on SliderItem {
  __typename
  uuid
  name
  link
  extendedText
  extendedTextLink
  ...SliderItemImagesWebDefaultFragment
  ...SliderItemImagesMobileDefaultFragment
}
    ${SliderItemImagesWebDefaultFragmentApi}
${SliderItemImagesMobileDefaultFragmentApi}`;
export const AdvertsQueryDocumentApi = gql`
    query AdvertsQuery {
  adverts {
    ...AdvertsFragment
  }
}
    ${AdvertsFragmentApi}`;

export function useAdvertsQueryApi(options: Omit<Urql.UseQueryArgs<AdvertsQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<AdvertsQueryApi>({ query: AdvertsQueryDocumentApi, ...options });
};
export const ArticleDetailQueryDocumentApi = gql`
    query ArticleDetailQuery($urlSlug: String) {
  article(urlSlug: $urlSlug) {
    ...ArticleDetailFragment
  }
}
    ${ArticleDetailFragmentApi}`;

export function useArticleDetailQueryApi(options: Omit<Urql.UseQueryArgs<ArticleDetailQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<ArticleDetailQueryApi>({ query: ArticleDetailQueryDocumentApi, ...options });
};
export const CookiesArticleUrlQueryDocumentApi = gql`
    query CookiesArticleUrlQuery {
  cookiesArticle {
    slug
  }
}
    `;

export function useCookiesArticleUrlQueryApi(options: Omit<Urql.UseQueryArgs<CookiesArticleUrlQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<CookiesArticleUrlQueryApi>({ query: CookiesArticleUrlQueryDocumentApi, ...options });
};
export const PrivacyPolicyArticleUrlQueryDocumentApi = gql`
    query PrivacyPolicyArticleUrlQuery {
  privacyPolicyArticle {
    slug
  }
}
    `;

export function usePrivacyPolicyArticleUrlQueryApi(options: Omit<Urql.UseQueryArgs<PrivacyPolicyArticleUrlQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<PrivacyPolicyArticleUrlQueryApi>({ query: PrivacyPolicyArticleUrlQueryDocumentApi, ...options });
};
export const TermsAndConditionsArticleUrlQueryDocumentApi = gql`
    query TermsAndConditionsArticleUrlQuery {
  termsAndConditionsArticle {
    slug
  }
}
    `;

export function useTermsAndConditionsArticleUrlQueryApi(options: Omit<Urql.UseQueryArgs<TermsAndConditionsArticleUrlQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<TermsAndConditionsArticleUrlQueryApi>({ query: TermsAndConditionsArticleUrlQueryDocumentApi, ...options });
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

export function useArticlesQueryApi(options: Omit<Urql.UseQueryArgs<ArticlesQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<ArticlesQueryApi>({ query: ArticlesQueryDocumentApi, ...options });
};
export const BlogArticleDetailQueryDocumentApi = gql`
    query BlogArticleDetailQuery($urlSlug: String) {
  blogArticle(urlSlug: $urlSlug) {
    ...BlogArticleDetailFragment
  }
}
    ${BlogArticleDetailFragmentApi}`;

export function useBlogArticleDetailQueryApi(options: Omit<Urql.UseQueryArgs<BlogArticleDetailQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<BlogArticleDetailQueryApi>({ query: BlogArticleDetailQueryDocumentApi, ...options });
};
export const BlogArticlesQueryDocumentApi = gql`
    query BlogArticlesQuery($first: Int, $onlyHomepageArticles: Boolean) @redisCache(ttl: 3600) {
  blogArticles(first: $first, onlyHomepageArticles: $onlyHomepageArticles) {
    ...BlogArticleConnectionFragment
  }
}
    ${BlogArticleConnectionFragmentApi}`;

export function useBlogArticlesQueryApi(options: Omit<Urql.UseQueryArgs<BlogArticlesQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<BlogArticlesQueryApi>({ query: BlogArticlesQueryDocumentApi, ...options });
};
export const LoginDocumentApi = gql`
    mutation Login($email: String!, $password: Password!, $previousCartUuid: Uuid) {
  Login(input: {email: $email, password: $password, cartUuid: $previousCartUuid}) {
    tokens {
      ...TokenFragments
    }
    showCartMergeInfo
  }
}
    ${TokenFragmentsApi}`;

export function useLoginApi() {
  return Urql.useMutation<LoginApi, LoginVariablesApi>(LoginDocumentApi);
};
export const LogoutDocumentApi = gql`
    mutation Logout {
  Logout
}
    `;

export function useLogoutApi() {
  return Urql.useMutation<LogoutApi, LogoutVariablesApi>(LogoutDocumentApi);
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

export function useBlogCategoriesApi(options: Omit<Urql.UseQueryArgs<BlogCategoriesVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<BlogCategoriesApi>({ query: BlogCategoriesDocumentApi, ...options });
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

export function useBlogCategoryArticlesApi(options: Omit<Urql.UseQueryArgs<BlogCategoryArticlesVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<BlogCategoryArticlesApi>({ query: BlogCategoryArticlesDocumentApi, ...options });
};
export const BlogCategoryQueryDocumentApi = gql`
    query BlogCategoryQuery($urlSlug: String) {
  blogCategory(urlSlug: $urlSlug) {
    ...BlogCategoryDetailFragment
  }
}
    ${BlogCategoryDetailFragmentApi}`;

export function useBlogCategoryQueryApi(options: Omit<Urql.UseQueryArgs<BlogCategoryQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<BlogCategoryQueryApi>({ query: BlogCategoryQueryDocumentApi, ...options });
};
export const BlogUrlQueryDocumentApi = gql`
    query BlogUrlQuery {
  blogCategories {
    link
  }
}
    `;

export function useBlogUrlQueryApi(options: Omit<Urql.UseQueryArgs<BlogUrlQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<BlogUrlQueryApi>({ query: BlogUrlQueryDocumentApi, ...options });
};
export const BrandDetailQueryDocumentApi = gql`
    query BrandDetailQuery($urlSlug: String, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter) {
  brand(urlSlug: $urlSlug) {
    ...BrandDetailFragment
  }
}
    ${BrandDetailFragmentApi}`;

export function useBrandDetailQueryApi(options: Omit<Urql.UseQueryArgs<BrandDetailQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<BrandDetailQueryApi>({ query: BrandDetailQueryDocumentApi, ...options });
};
export const BrandsQueryDocumentApi = gql`
    query BrandsQuery {
  brands {
    ...ListedBrandFragment
  }
}
    ${ListedBrandFragmentApi}`;

export function useBrandsQueryApi(options: Omit<Urql.UseQueryArgs<BrandsQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<BrandsQueryApi>({ query: BrandsQueryDocumentApi, ...options });
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

export function useCartQueryApi(options: Omit<Urql.UseQueryArgs<CartQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<CartQueryApi>({ query: CartQueryDocumentApi, ...options });
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

export function useMinimalCartQueryApi(options: Omit<Urql.UseQueryArgs<MinimalCartQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<MinimalCartQueryApi>({ query: MinimalCartQueryDocumentApi, ...options });
};
export const CategoryDetailQueryDocumentApi = gql`
    query CategoryDetailQuery($urlSlug: String, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter) {
  category(urlSlug: $urlSlug) {
    ...CategoryDetailFragment
  }
}
    ${CategoryDetailFragmentApi}`;

export function useCategoryDetailQueryApi(options: Omit<Urql.UseQueryArgs<CategoryDetailQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<CategoryDetailQueryApi>({ query: CategoryDetailQueryDocumentApi, ...options });
};
export const PromotedCategoriesQueryDocumentApi = gql`
    query PromotedCategoriesQuery {
  promotedCategories {
    ...ListedCategoryFragment
  }
}
    ${ListedCategoryFragmentApi}`;

export function usePromotedCategoriesQueryApi(options: Omit<Urql.UseQueryArgs<PromotedCategoriesQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<PromotedCategoriesQueryApi>({ query: PromotedCategoriesQueryDocumentApi, ...options });
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

export function useCountriesQueryApi(options: Omit<Urql.UseQueryArgs<CountriesQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<CountriesQueryApi>({ query: CountriesQueryDocumentApi, ...options });
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

export function useCurrentCustomerUserQueryApi(options: Omit<Urql.UseQueryArgs<CurrentCustomerUserQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<CurrentCustomerUserQueryApi>({ query: CurrentCustomerUserQueryDocumentApi, ...options });
};
export const IsCustomerUserRegisteredQueryDocumentApi = gql`
    query IsCustomerUserRegisteredQuery($email: String!) {
  isCustomerUserRegistered(email: $email)
}
    `;

export function useIsCustomerUserRegisteredQueryApi(options: Omit<Urql.UseQueryArgs<IsCustomerUserRegisteredQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<IsCustomerUserRegisteredQueryApi>({ query: IsCustomerUserRegisteredQueryDocumentApi, ...options });
};
export const FlagDetailQueryDocumentApi = gql`
    query FlagDetailQuery($urlSlug: String, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter) {
  flag(urlSlug: $urlSlug) {
    ...FlagDetailFragment
  }
}
    ${FlagDetailFragmentApi}`;

export function useFlagDetailQueryApi(options: Omit<Urql.UseQueryArgs<FlagDetailQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<FlagDetailQueryApi>({ query: FlagDetailQueryDocumentApi, ...options });
};
export const NavigationQueryDocumentApi = gql`
    query NavigationQuery @redisCache(ttl: 3600) {
  navigation {
    ...CategoriesByColumnFragment
  }
}
    ${CategoriesByColumnFragmentApi}`;

export function useNavigationQueryApi(options: Omit<Urql.UseQueryArgs<NavigationQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<NavigationQueryApi>({ query: NavigationQueryDocumentApi, ...options });
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

export function useNotificationBarsApi(options: Omit<Urql.UseQueryArgs<NotificationBarsVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<NotificationBarsApi>({ query: NotificationBarsDocumentApi, ...options });
};
export const CheckPaymentStatusMutationDocumentApi = gql`
    mutation CheckPaymentStatusMutation($orderUuid: Uuid!) {
  CheckPaymentStatus(orderUuid: $orderUuid)
}
    `;

export function useCheckPaymentStatusMutationApi() {
  return Urql.useMutation<CheckPaymentStatusMutationApi, CheckPaymentStatusMutationVariablesApi>(CheckPaymentStatusMutationDocumentApi);
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
export const LastOrderQueryDocumentApi = gql`
    query LastOrderQuery {
  lastOrder {
    ...LastOrderFragment
  }
}
    ${LastOrderFragmentApi}`;

export function useLastOrderQueryApi(options: Omit<Urql.UseQueryArgs<LastOrderQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<LastOrderQueryApi>({ query: LastOrderQueryDocumentApi, ...options });
};
export const OrderDetailByHashQueryDocumentApi = gql`
    query OrderDetailByHashQuery($urlHash: String) {
  order(urlHash: $urlHash) {
    ...OrderDetailFragment
  }
}
    ${OrderDetailFragmentApi}`;

export function useOrderDetailByHashQueryApi(options: Omit<Urql.UseQueryArgs<OrderDetailByHashQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<OrderDetailByHashQueryApi>({ query: OrderDetailByHashQueryDocumentApi, ...options });
};
export const OrderDetailQueryDocumentApi = gql`
    query OrderDetailQuery($orderNumber: String) {
  order(orderNumber: $orderNumber) {
    ...OrderDetailFragment
  }
}
    ${OrderDetailFragmentApi}`;

export function useOrderDetailQueryApi(options: Omit<Urql.UseQueryArgs<OrderDetailQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<OrderDetailQueryApi>({ query: OrderDetailQueryDocumentApi, ...options });
};
export const OrderSentPageContentDocumentApi = gql`
    query OrderSentPageContent($orderUuid: Uuid!) {
  orderSentPageContent(orderUuid: $orderUuid)
}
    `;

export function useOrderSentPageContentApi(options: Omit<Urql.UseQueryArgs<OrderSentPageContentVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<OrderSentPageContentApi>({ query: OrderSentPageContentDocumentApi, ...options });
};
export const OrdersQueryDocumentApi = gql`
    query OrdersQuery($after: String, $first: Int) {
  orders(after: $after, first: $first) {
    ...OrderListFragment
  }
}
    ${OrderListFragmentApi}`;

export function useOrdersQueryApi(options: Omit<Urql.UseQueryArgs<OrdersQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<OrdersQueryApi>({ query: OrdersQueryDocumentApi, ...options });
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

export function useGoPaySwiftsQueryApi(options: Omit<Urql.UseQueryArgs<GoPaySwiftsQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<GoPaySwiftsQueryApi>({ query: GoPaySwiftsQueryDocumentApi, ...options });
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

export function usePersonalDataDetailQueryApi(options: Omit<Urql.UseQueryArgs<PersonalDataDetailQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<PersonalDataDetailQueryApi>({ query: PersonalDataDetailQueryDocumentApi, ...options });
};
export const PersonalDataPageTextQueryDocumentApi = gql`
    query PersonalDataPageTextQuery {
  personalDataPage {
    displaySiteContent
    exportSiteContent
  }
}
    `;

export function usePersonalDataPageTextQueryApi(options: Omit<Urql.UseQueryArgs<PersonalDataPageTextQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<PersonalDataPageTextQueryApi>({ query: PersonalDataPageTextQueryDocumentApi, ...options });
};
export const AddProductToComparisonMutationDocumentApi = gql`
    mutation AddProductToComparisonMutation($productUuid: Uuid!, $comparisonUuid: Uuid) {
  addProductToComparison(
    productUuid: $productUuid
    comparisonUuid: $comparisonUuid
  ) {
    ...ProductComparisonFragment
  }
}
    ${ProductComparisonFragmentApi}`;

export function useAddProductToComparisonMutationApi() {
  return Urql.useMutation<AddProductToComparisonMutationApi, AddProductToComparisonMutationVariablesApi>(AddProductToComparisonMutationDocumentApi);
};
export const CleanComparisonMutationDocumentApi = gql`
    mutation CleanComparisonMutation($comparisonUuid: Uuid) {
  cleanComparison(comparisonUuid: $comparisonUuid)
}
    `;

export function useCleanComparisonMutationApi() {
  return Urql.useMutation<CleanComparisonMutationApi, CleanComparisonMutationVariablesApi>(CleanComparisonMutationDocumentApi);
};
export const RemoveProductFromComparisonMutationDocumentApi = gql`
    mutation RemoveProductFromComparisonMutation($productUuid: Uuid!, $comparisonUuid: Uuid) {
  removeProductFromComparison(
    productUuid: $productUuid
    comparisonUuid: $comparisonUuid
  ) {
    ...ProductComparisonFragment
  }
}
    ${ProductComparisonFragmentApi}`;

export function useRemoveProductFromComparisonMutationApi() {
  return Urql.useMutation<RemoveProductFromComparisonMutationApi, RemoveProductFromComparisonMutationVariablesApi>(RemoveProductFromComparisonMutationDocumentApi);
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

export function useBrandProductsQueryApi(options: Omit<Urql.UseQueryArgs<BrandProductsQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<BrandProductsQueryApi>({ query: BrandProductsQueryDocumentApi, ...options });
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

export function useCategoryProductsQueryApi(options: Omit<Urql.UseQueryArgs<CategoryProductsQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<CategoryProductsQueryApi>({ query: CategoryProductsQueryDocumentApi, ...options });
};
export const ComparisonQueryDocumentApi = gql`
    query ComparisonQuery($comparisonUuid: Uuid) {
  comparison(uuid: $comparisonUuid) {
    ...ProductComparisonFragment
  }
}
    ${ProductComparisonFragmentApi}`;

export function useComparisonQueryApi(options: Omit<Urql.UseQueryArgs<ComparisonQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<ComparisonQueryApi>({ query: ComparisonQueryDocumentApi, ...options });
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

export function useFlagProductsQueryApi(options: Omit<Urql.UseQueryArgs<FlagProductsQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<FlagProductsQueryApi>({ query: FlagProductsQueryDocumentApi, ...options });
};
export const ProductDetailQueryDocumentApi = gql`
    query ProductDetailQuery($urlSlug: String) {
  product(urlSlug: $urlSlug) {
    ... on Product {
      ...ProductDetailFragment
    }
    ... on MainVariant {
      ...MainVariantDetailFragment
    }
    ... on Variant {
      mainVariant {
        slug
      }
    }
  }
}
    ${ProductDetailFragmentApi}
${MainVariantDetailFragmentApi}`;

export function useProductDetailQueryApi(options: Omit<Urql.UseQueryArgs<ProductDetailQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<ProductDetailQueryApi>({ query: ProductDetailQueryDocumentApi, ...options });
};
export const ProductsByCatnumsDocumentApi = gql`
    query ProductsByCatnums($catnums: [String!]!) {
  productsByCatnums(catnums: $catnums) {
    ...ListedProductFragment
  }
}
    ${ListedProductFragmentApi}`;

export function useProductsByCatnumsApi(options: Omit<Urql.UseQueryArgs<ProductsByCatnumsVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<ProductsByCatnumsApi>({ query: ProductsByCatnumsDocumentApi, ...options });
};
export const PromotedProductsQueryDocumentApi = gql`
    query PromotedProductsQuery {
  promotedProducts {
    ...ListedProductFragment
  }
}
    ${ListedProductFragmentApi}`;

export function usePromotedProductsQueryApi(options: Omit<Urql.UseQueryArgs<PromotedProductsQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<PromotedProductsQueryApi>({ query: PromotedProductsQueryDocumentApi, ...options });
};
export const SearchProductsQueryDocumentApi = gql`
    query SearchProductsQuery($endCursor: String!, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter, $search: String!, $pageSize: Int) {
  products(
    after: $endCursor
    orderingMode: $orderingMode
    filter: $filter
    search: $search
    first: $pageSize
  ) {
    ...ListedProductConnectionFragment
  }
}
    ${ListedProductConnectionFragmentApi}`;

export function useSearchProductsQueryApi(options: Omit<Urql.UseQueryArgs<SearchProductsQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<SearchProductsQueryApi>({ query: SearchProductsQueryDocumentApi, ...options });
};
export const RegistrationMutationDocumentApi = gql`
    mutation RegistrationMutation($firstName: String!, $lastName: String!, $email: String!, $password: Password!, $telephone: String!, $street: String!, $city: String!, $postcode: String!, $country: String!, $companyCustomer: Boolean!, $companyName: String, $companyNumber: String, $companyTaxNumber: String, $newsletterSubscription: Boolean!, $previousCartUuid: Uuid, $lastOrderUuid: Uuid) {
  Register(
    input: {firstName: $firstName, lastName: $lastName, email: $email, password: $password, telephone: $telephone, street: $street, city: $city, postcode: $postcode, country: $country, companyCustomer: $companyCustomer, companyName: $companyName, companyNumber: $companyNumber, companyTaxNumber: $companyTaxNumber, newsletterSubscription: $newsletterSubscription, cartUuid: $previousCartUuid, lastOrderUuid: $lastOrderUuid}
  ) {
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
export const AutocompleteSearchQueryDocumentApi = gql`
    query AutocompleteSearchQuery($search: String!, $maxProductCount: Int, $maxCategoryCount: Int) {
  articlesSearch(search: $search) {
    ...SimpleArticleInterfaceFragment
  }
  brandSearch(search: $search) {
    ...SimpleBrandFragment
  }
  categoriesSearch(search: $search, first: $maxCategoryCount) {
    ...SimpleCategoryConnectionFragment
  }
  productsSearch: products(search: $search, first: $maxProductCount) {
    ...ListedProductConnectionFragment
  }
}
    ${SimpleArticleInterfaceFragmentApi}
${SimpleBrandFragmentApi}
${SimpleCategoryConnectionFragmentApi}
${ListedProductConnectionFragmentApi}`;

export function useAutocompleteSearchQueryApi(options: Omit<Urql.UseQueryArgs<AutocompleteSearchQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<AutocompleteSearchQueryApi>({ query: AutocompleteSearchQueryDocumentApi, ...options });
};
export const SearchQueryDocumentApi = gql`
    query SearchQuery($search: String!, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter, $pageSize: Int) {
  articlesSearch(search: $search) {
    ...SimpleArticleInterfaceFragment
  }
  brandSearch(search: $search) {
    ...ListedBrandFragment
  }
  categoriesSearch(search: $search) {
    ...ListedCategoryConnectionFragment
  }
  productsSearch: products(
    search: $search
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

export function useSearchQueryApi(options: Omit<Urql.UseQueryArgs<SearchQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<SearchQueryApi>({ query: SearchQueryDocumentApi, ...options });
};
export const SeoPageQueryDocumentApi = gql`
    query SeoPageQuery($pageSlug: String!) {
  seoPage(pageSlug: $pageSlug) {
    ...SeoPageFragment
  }
}
    ${SeoPageFragmentApi}`;

export function useSeoPageQueryApi(options: Omit<Urql.UseQueryArgs<SeoPageQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<SeoPageQueryApi>({ query: SeoPageQueryDocumentApi, ...options });
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
  }
}
    ${PricingSettingFragmentApi}
${SeoSettingFragmentApi}`;

export function useSettingsQueryApi(options: Omit<Urql.UseQueryArgs<SettingsQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<SettingsQueryApi>({ query: SettingsQueryDocumentApi, ...options });
};
export const SliderItemsQueryDocumentApi = gql`
    query SliderItemsQuery {
  sliderItems {
    ...SliderItemFragment
  }
}
    ${SliderItemFragmentApi}`;

export function useSliderItemsQueryApi(options: Omit<Urql.UseQueryArgs<SliderItemsQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<SliderItemsQueryApi>({ query: SliderItemsQueryDocumentApi, ...options });
};
export const SlugTypeQueryDocumentApi = gql`
    query SlugTypeQuery($slug: String!) {
  slug(slug: $slug) {
    __typename
  }
}
    `;

export function useSlugTypeQueryApi(options: Omit<Urql.UseQueryArgs<SlugTypeQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<SlugTypeQueryApi>({ query: SlugTypeQueryDocumentApi, ...options });
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

export function useSlugQueryApi(options: Omit<Urql.UseQueryArgs<SlugQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<SlugQueryApi>({ query: SlugQueryDocumentApi, ...options });
};
export const StoreDetailQueryDocumentApi = gql`
    query StoreDetailQuery($urlSlug: String) {
  store(urlSlug: $urlSlug) {
    ...StoreDetailFragment
  }
}
    ${StoreDetailFragmentApi}`;

export function useStoreDetailQueryApi(options: Omit<Urql.UseQueryArgs<StoreDetailQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<StoreDetailQueryApi>({ query: StoreDetailQueryDocumentApi, ...options });
};
export const StoreQueryDocumentApi = gql`
    query StoreQuery($uuid: Uuid) {
  store(uuid: $uuid) {
    ...ListedStoreFragment
  }
}
    ${ListedStoreFragmentApi}`;

export function useStoreQueryApi(options: Omit<Urql.UseQueryArgs<StoreQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<StoreQueryApi>({ query: StoreQueryDocumentApi, ...options });
};
export const StoresQueryDocumentApi = gql`
    query StoresQuery {
  stores {
    ...ListedStoreConnectionFragment
  }
}
    ${ListedStoreConnectionFragmentApi}`;

export function useStoresQueryApi(options: Omit<Urql.UseQueryArgs<StoresQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<StoresQueryApi>({ query: StoresQueryDocumentApi, ...options });
};
export const TransportsQueryDocumentApi = gql`
    query TransportsQuery($cartUuid: Uuid) {
  transports(cartUuid: $cartUuid) {
    ...TransportWithAvailablePaymentsAndStoresFragment
  }
}
    ${TransportWithAvailablePaymentsAndStoresFragmentApi}`;

export function useTransportsQueryApi(options: Omit<Urql.UseQueryArgs<TransportsQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<TransportsQueryApi>({ query: TransportsQueryDocumentApi, ...options });
};