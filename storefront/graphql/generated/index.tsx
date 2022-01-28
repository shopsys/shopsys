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
  isNew: Scalars['Boolean'];
  isQuantityOverLimit: Maybe<Scalars['Boolean']>;
  notOnStockQuantity: Scalars['Int'];
  overLimitQuantity: Maybe<Scalars['Int']>;
};

export type AddToCartInputApi = {
  /** Cart identifier, new cart will be created if not provided and customer is not logged in */
  cartUuid: Maybe<Scalars['Uuid']>;
  /**
   * True if quantity should be set no matter the current state of the cart. False
   * if quantity should be added to the already existing same item in the cart
   */
  isAbsoluteQuantity: Maybe<Scalars['Boolean']>;
  /** Represents a payment in order */
  payment: Maybe<PaymentInputApi>;
  /** Product UUID */
  productUuid: Scalars['Uuid'];
  promoCode: Maybe<Scalars['String']>;
  /** Item quantity */
  quantity: Scalars['Int'];
  /** Represents a transport in order */
  transport: Maybe<TransportInputApi>;
};

export type AddToCartResultApi = CartInterfaceApi & {
  __typename?: 'AddToCartResult';
  addProductResult: AddProductResultApi;
  /** All items in the cart */
  items: Array<CartItemApi>;
  modifications: CartModificationsResultApi;
  /** Selected payment if payment provided */
  payment: Maybe<PaymentApi>;
  /** Applied promo code if provided */
  promoCode: Maybe<Scalars['String']>;
  /** Remaining amount for free transport and payment; null = transport cannot be free */
  remainingAmountWithVatForFreeTransport: Maybe<Scalars['Money']>;
  /** Selected pickup place identifier if provided */
  selectedPickupPlaceIdentifier: Maybe<Scalars['String']>;
  totalDiscountPrice: PriceApi;
  totalPrice: PriceApi;
  /** Selected transport if transport provided */
  transport: Maybe<TransportApi>;
  /** UUID of the cart, null for authenticated user */
  uuid: Maybe<Scalars['Uuid']>;
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
  /** Advert image */
  image: Array<ImageApi>;
  /** Advert link */
  link: Maybe<Scalars['String']>;
  /** Name of advert */
  name: Scalars['String'];
  /** Position of advert */
  positionName: Scalars['String'];
  /** Type of advert */
  type: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};


export type AdvertImageImageArgsApi = {
  size?: Maybe<Scalars['String']>;
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};

export type AdvertPositionApi = {
  __typename?: 'AdvertPosition';
  /** Desription of advert position */
  description: Scalars['String'];
  /** Position of advert */
  positionName: Scalars['String'];
};

export type ArticleApi = ArticleInterfaceApi & BreadcrumbApi & SlugApi & {
  __typename?: 'Article';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
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
  node: Maybe<ArticleApi>;
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

/** Represents an availability */
export type AvailabilityApi = {
  __typename?: 'Availability';
  /** Localized availability name (domain dependent) */
  name: Scalars['String'];
  /** Availability status in a format suitable for usage in the code */
  status: Scalars['String'];
};

export type BlogArticleApi = ArticleInterfaceApi & BreadcrumbApi & SlugApi & {
  __typename?: 'BlogArticle';
  /** The list of the blog article blog categories */
  blogCategories: Array<BlogCategoryApi>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Date and time of the blog article creation */
  createdAt: Scalars['DateTime'];
  /** Blog article images */
  images: Array<ImageApi>;
  /** The blog article absolute URL */
  link: Scalars['String'];
  /** The blog article title */
  name: Scalars['String'];
  /** The blog article perex */
  perex: Maybe<Scalars['String']>;
  /** The list of the products assigned to the blog article */
  products: Array<ProductApi>;
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
  /** Paginated blog articles of the given blog category */
  blogArticles: BlogArticleConnectionApi;
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
  /** Brand images */
  images: Array<ImageApi>;
  /** Brand main URL */
  link: Scalars['String'];
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
export type BrandProductsArgsApi = {
  after: Maybe<Scalars['String']>;
  before: Maybe<Scalars['String']>;
  filter: Maybe<ProductFilterApi>;
  first: Maybe<Scalars['Int']>;
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
  /** Applied promo code if provided */
  promoCode: Maybe<Scalars['String']>;
  /** Remaining amount for free transport and payment; null = transport cannot be free */
  remainingAmountWithVatForFreeTransport: Maybe<Scalars['Money']>;
  /** Selected pickup place identifier if provided */
  selectedPickupPlaceIdentifier: Maybe<Scalars['String']>;
  totalDiscountPrice: PriceApi;
  totalPrice: PriceApi;
  /** Selected transport if transport provided */
  transport: Maybe<TransportApi>;
  /** UUID of the cart, null for authenticated user */
  uuid: Maybe<Scalars['Uuid']>;
};

export type CartInputApi = {
  /** Cart identifier, new cart will be created if not provided and customer is not logged in */
  cartUuid: Maybe<Scalars['Uuid']>;
  /** Represents a payment in order */
  payment: Maybe<PaymentInputApi>;
  promoCode: Maybe<Scalars['String']>;
  /** Represents a transport in order */
  transport: Maybe<TransportInputApi>;
};

export type CartInterfaceApi = {
  items: Array<CartItemApi>;
  modifications: CartModificationsResultApi;
  payment: Maybe<PaymentApi>;
  promoCode: Maybe<Scalars['String']>;
  remainingAmountWithVatForFreeTransport: Maybe<Scalars['Money']>;
  selectedPickupPlaceIdentifier: Maybe<Scalars['String']>;
  totalDiscountPrice: PriceApi;
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
  transportModifications: CartTransportModificationsResultApi;
};

export type CartPaymentModificationsResultApi = {
  __typename?: 'CartPaymentModificationsResult';
  paymentPriceChanged: Scalars['Boolean'];
  paymentUnavailable: Scalars['Boolean'];
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
  /** Descendant categories */
  children: Array<CategoryApi>;
  /** Category images */
  images: Array<ImageApi>;
  /** A list of categories linked to the given category */
  linkedCategories: Array<CategoryApi>;
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
export type CategoryProductsArgsApi = {
  after: Maybe<Scalars['String']>;
  before: Maybe<Scalars['String']>;
  filter: Maybe<ProductFilterApi>;
  first: Maybe<Scalars['Int']>;
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

export type ChangePasswordInputApi = {
  /** Customer user email. */
  email: Scalars['String'];
  /** New customer user password. */
  newPassword: Scalars['Password'];
  /** Current customer user password. */
  oldPassword: Scalars['Password'];
};

export type ChangePersonalDataInputApi = {
  /** Billing address city name (will be on the tax invoice) */
  city: Scalars['String'];
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
  /** Billing address street name */
  street: Scalars['String'];
  /** Phone number */
  telephone: Maybe<Scalars['String']>;
  /** UUID */
  uuid: Scalars['Uuid'];
};

/** Represents country */
export type CountryApi = {
  __typename?: 'Country';
  /** Country code in ISO 3166-1 alpha-2 */
  code: Scalars['String'];
  /** Localized country name */
  name: Scalars['String'];
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
  city: Scalars['String'];
  /** Delivery address company name */
  companyName: Scalars['String'];
  /** Delivery address country */
  country: CountryApi;
  /** Delivery address firstname */
  firstName: Scalars['String'];
  /** Delivery address lastname */
  lastName: Scalars['String'];
  /** Delivery address zip code */
  postcode: Scalars['String'];
  /** Delivery address street name */
  street: Scalars['String'];
  /** Delivery address telephone */
  telephone: Scalars['String'];
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
  filter: Maybe<ProductFilterApi>;
  first: Maybe<Scalars['Int']>;
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
  /** Distinguishes if the product can be bought */
  hasSaleExclusion: Scalars['Boolean'];
  /** Product images */
  images: Array<ImageApi>;
  isSellingDenied: Scalars['Boolean'];
  isUsingStock: Scalars['Boolean'];
  /** Product link */
  link: Scalars['String'];
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

export type MutationApi = {
  __typename?: 'Mutation';
  /** Add product to cart for future checkout */
  AddToCart: AddToCartResultApi;
  /** Changes customer user password */
  ChangePassword: CustomerUserApi;
  /** Changes customer user personal data */
  ChangePersonalData: CustomerUserApi;
  /** Creates complete order with products and addresses */
  CreateOrder: OrderApi;
  /** Delete delivery address by Uuid */
  DeleteDeliveryAddress: Array<DeliveryAddressApi>;
  /** Login user and return access and refresh tokens */
  Login: TokenApi;
  /** Logout user */
  Logout: Scalars['Boolean'];
  /** Subscribe for e-mail newsletter */
  NewsletterSubscribe: Scalars['Boolean'];
  /** Recover password using hash required from RequestPasswordRecovery */
  RecoverPassword: TokenApi;
  /** Refreshes access and refresh tokens */
  RefreshTokens: TokenApi;
  /** Register new customer user */
  Register: TokenApi;
  /** Remove product from cart */
  RemoveFromCart: CartApi;
  /** Request password recovery - email with hash will be sent */
  RequestPasswordRecovery: Scalars['String'];
  /** Request access to personal data */
  RequestPersonalDataAccess: PersonalDataPageApi;
};


export type MutationAddToCartArgsApi = {
  input: AddToCartInputApi;
};


export type MutationChangePasswordArgsApi = {
  input: ChangePasswordInputApi;
};


export type MutationChangePersonalDataArgsApi = {
  input: ChangePersonalDataInputApi;
};


export type MutationCreateOrderArgsApi = {
  input: OrderInputApi;
};


export type MutationDeleteDeliveryAddressArgsApi = {
  deliveryAddressUuid: Scalars['Uuid'];
};


export type MutationLoginArgsApi = {
  input: LoginInputApi;
};


export type MutationNewsletterSubscribeArgsApi = {
  input: NewsletterSubscriptionDataInputApi;
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


export type MutationRequestPasswordRecoveryArgsApi = {
  email: Scalars['String'];
};


export type MutationRequestPersonalDataAccessArgsApi = {
  input: PersonalDataAccessRequestInputApi;
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

/** Represents a notification supposed to be displayed on all pages */
export type NotificationBarApi = {
  __typename?: 'NotificationBar';
  /** Notification bar images */
  images: Array<ImageApi>;
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
  /** Billing address zip code */
  postcode: Scalars['String'];
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
  /** Other information related to the order */
  note: Maybe<Scalars['String']>;
  /** Determines whether the order is made on the company behalf. */
  onCompanyBehalf: Scalars['Boolean'];
  /** Payment method applied to the order */
  payment: PaymentInputApi;
  /** Billing address zip code (will be on the tax invoice) */
  postcode: Scalars['String'];
  /** Deprecated, this field is not used, the products are taken from the server cart instead. */
  products: Maybe<Array<OrderProductInputApi>>;
  /** The promo code used in the order */
  promoCode: Maybe<Scalars['String']>;
  /** Billing address street name (will be on the tax invoice) */
  street: Scalars['String'];
  /** The customer's phone number */
  telephone: Scalars['String'];
  /** Transport method applied to the order */
  transport: TransportInputApi;
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

/** Represents a parameter filter */
export type ParameterFilterApi = {
  /** Uuid of filtered parameter */
  parameter: Scalars['Uuid'];
  /** Array of uuids filtered parameter values */
  values: Array<Scalars['Uuid']>;
};

/** Parameter filter option */
export type ParameterFilterOptionApi = {
  __typename?: 'ParameterFilterOption';
  /** Parameter name */
  name: Scalars['String'];
  type: Scalars['String'];
  unit: Maybe<UnitApi>;
  /** UUID */
  uuid: Scalars['Uuid'];
  /** Filter options of parameter values */
  values: Array<ParameterValueFilterOptionApi>;
  /** Is parameter visible for customers */
  visible: Scalars['Boolean'];
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
  /** RGB hex of color parameter */
  rgbHex: Maybe<Scalars['String']>;
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

export type PersonalDataApi = {
  __typename?: 'PersonalData';
  /** Customer user data */
  customerUser: Maybe<CustomerUserApi>;
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
  /** Distinguishes if the product can be bought */
  hasSaleExclusion: Scalars['Boolean'];
  /** Product images */
  images: Array<ImageApi>;
  isSellingDenied: Scalars['Boolean'];
  isUsingStock: Scalars['Boolean'];
  /** Product link */
  link: Scalars['String'];
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

/** A connection to a list of items. */
export type ProductConnectionApi = {
  __typename?: 'ProductConnection';
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<ProductEdgeApi>>>;
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
  parameters: Maybe<Array<ParameterFilterOptionApi>>;
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
  filter: Maybe<ProductFilterApi>;
  first: Maybe<Scalars['Int']>;
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
  CompanyCustomerUser: Maybe<CompanyCustomerUserApi>;
  MainVariant: Maybe<MainVariantApi>;
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
  article: Maybe<ArticleApi>;
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
  /** Returns information about cookies article */
  cookiesArticle: Maybe<ArticleApi>;
  /** Returns available countries */
  countries: Array<CountryApi>;
  /** Returns currently logged in customer user */
  currentCustomerUser: CustomerUserApi;
  /** Returns a flag by uuid or url slug */
  flag: Maybe<FlagApi>;
  /** Returns a complete list of the flags */
  flags: Maybe<Array<FlagApi>>;
  /** Returns complete navigation menu */
  navigation: Array<NavigationItemApi>;
  /** Returns a list of notifications supposed to be displayed on all pages */
  notificationBars: Maybe<Array<NotificationBarApi>>;
  /** Returns order filtered using UUID, orderNumber, or urlHash */
  order: Maybe<OrderApi>;
  /** Returns list of orders that can be paginated using `first`, `last`, `before` and `after` keywords */
  orders: Maybe<OrderConnectionApi>;
  /** Returns payment filtered using UUID */
  payment: Maybe<PaymentApi>;
  /** Returns complete list of payment methods */
  payments: Array<PaymentApi>;
  /** Return personal data page content and URL */
  personalDataPage: Maybe<PersonalDataPageApi>;
  /** Returns privacy policy article */
  privacyPolicyArticle: Maybe<ArticleApi>;
  /** Returns product filtered using UUID or URL slug */
  product: Maybe<ProductApi>;
  /** Returns list of ordered products that can be paginated using `first`, `last`, `before` and `after` keywords */
  products: ProductConnectionApi;
  /** Returns promoted categories */
  promotedCategories: Array<CategoryApi>;
  /** Returns promoted products */
  promotedProducts: Array<ProductApi>;
  /** Returns a complete list of the slider items */
  sliderItems: Array<SliderItemApi>;
  /** Returns entity by slug */
  slug: Maybe<SlugApi>;
  /** Returns store filtered using UUID or URL slug */
  store: Maybe<StoreApi>;
  /** Returns list of stores that can be paginated using `first`, `last`, `before` and `after` keywords */
  stores: StoreConnectionApi;
  /** Returns Terms and Conditions article */
  termsAndConditionsArticle: Maybe<ArticleApi>;
  /** Returns complete list of transport methods */
  transport: Maybe<TransportApi>;
  /** Returns available transport methods based on the current cart state */
  transports: Array<TransportApi>;
};


export type QueryAccessPersonalDataArgsApi = {
  hash: Scalars['String'];
};


export type QueryAdvertsArgsApi = {
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
  placement: Maybe<Scalars['String']>;
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
  cartInput?: Maybe<CartInputApi>;
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


export type QueryFlagArgsApi = {
  urlSlug: Maybe<Scalars['String']>;
  uuid: Maybe<Scalars['Uuid']>;
};


export type QueryOrderArgsApi = {
  orderNumber: Maybe<Scalars['String']>;
  urlHash: Maybe<Scalars['String']>;
  uuid: Maybe<Scalars['Uuid']>;
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
  filter: Maybe<ProductFilterApi>;
  first: Maybe<Scalars['Int']>;
  last: Maybe<Scalars['Int']>;
  orderingMode: Maybe<ProductOrderingModeEnumApi>;
  search: Maybe<Scalars['String']>;
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
  /** Determines whether the registered customer is a company or not. */
  companyCustomer: Scalars['Boolean'];
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
  /** Distinguishes if the product can be bought */
  hasSaleExclusion: Scalars['Boolean'];
  /** Product images */
  images: Array<ImageApi>;
  isSellingDenied: Scalars['Boolean'];
  isUsingStock: Scalars['Boolean'];
  /** Product link */
  link: Scalars['String'];
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

export type RemoveFromCartInputApi = {
  /** Cart item UUID */
  cartItemUuid: Scalars['Uuid'];
  /** Cart identifier, new cart will be created if not provided and customer is not logged in */
  cartUuid: Maybe<Scalars['Uuid']>;
  /** Represents a payment in order */
  payment: Maybe<PaymentInputApi>;
  promoCode: Maybe<Scalars['String']>;
  /** Represents a transport in order */
  transport: Maybe<TransportInputApi>;
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
  /** Slider name */
  name: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};


export type SliderItemImagesArgsApi = {
  sizes?: Maybe<Array<Scalars['String']>>;
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

/** Represents an availability in an individual store */
export type StoreAvailabilityApi = {
  __typename?: 'StoreAvailability';
  /** Detailed information about availability */
  availabilityInformation: Scalars['String'];
  /** Availability status in a format suitable for usage in the code */
  availabilityStatus: Scalars['String'];
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
export type TransportPriceArgsApi = {
  cartUuid?: Maybe<Scalars['Uuid']>;
};

/** Represents a transport in order */
export type TransportInputApi = {
  /** The identifier of selected personal pickup place */
  pickupPlaceIdentifier: Maybe<Scalars['String']>;
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
  /** Distinguishes if the product can be bought */
  hasSaleExclusion: Scalars['Boolean'];
  /** Product images */
  images: Array<ImageApi>;
  isSellingDenied: Scalars['Boolean'];
  isUsingStock: Scalars['Boolean'];
  /** Product link */
  link: Scalars['String'];
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

export type ArticleDetailFragmentApi = { __typename?: 'Article', uuid: string, slug: string, placement: string, text: string | null, articleName: string, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> };

export type SimpleArticleFragmentApi = { __typename?: 'Article', name: string, slug: string };

export type BlogArticleConnectionFragmentApi = { __typename?: 'BlogArticleConnection', totalCount: number, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'BlogArticleEdge', node: { __typename?: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any, perex: string | null, slug: string, blogCategories: Array<{ __typename?: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | null } | null> | null };

export type BlogArticleDetailFragmentApi = { __typename?: 'BlogArticle', uuid: string, name: string, slug: string, link: string, text: string | null, publishDate: any, blogArticleProducts: Array<{ __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } }>, blogArticlesGridImages: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> };

export type ListedBlogArticleFragmentApi = { __typename?: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any, perex: string | null, slug: string, blogCategories: Array<{ __typename?: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

export type SimpleBlogArticleFragmentApi = { __typename?: 'BlogArticle', name: string, slug: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

export type BlogArticleImageListFragmentApi = { __typename?: 'BlogArticle', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

export type BlogArticleImageListGridFragmentApi = { __typename?: 'BlogArticle', blogArticlesGridImages: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

export type BlogArticlesQueryVariablesApi = Exact<{
  first: Maybe<Scalars['Int']>;
  onlyHomepageArticles: Maybe<Scalars['Boolean']>;
}>;


export type BlogArticlesQueryApi = { __typename?: 'Query', blogArticles: { __typename?: 'BlogArticleConnection', totalCount: number, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'BlogArticleEdge', node: { __typename?: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any, perex: string | null, slug: string, blogCategories: Array<{ __typename?: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | null } | null> | null } };

type SimpleArticleInterfaceFragment_Article_Api = { __typename: 'Article', name: string, slug: string };

type SimpleArticleInterfaceFragment_BlogArticle_Api = { __typename: 'BlogArticle', name: string, slug: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

export type SimpleArticleInterfaceFragmentApi = SimpleArticleInterfaceFragment_Article_Api | SimpleArticleInterfaceFragment_BlogArticle_Api;

export type LoginVariablesApi = Exact<{
  email: Scalars['String'];
  password: Scalars['Password'];
  previousCartUuid: Maybe<Scalars['Uuid']>;
}>;


export type LoginApi = { __typename?: 'Mutation', Login: { __typename?: 'Token', accessToken: string, refreshToken: string } };

export type LogoutVariablesApi = Exact<{ [key: string]: never; }>;


export type LogoutApi = { __typename?: 'Mutation', Logout: boolean };

export type RefreshTokensVariablesApi = Exact<{
  refreshToken: Scalars['String'];
}>;


export type RefreshTokensApi = { __typename?: 'Mutation', RefreshTokens: { __typename?: 'Token', accessToken: string, refreshToken: string } };

export type AvailabilityFragmentApi = { __typename?: 'Availability', name: string, status: string };

export type BlogCategoryDetailFragmentApi = { __typename?: 'BlogCategory', uuid: string, name: string, blogArticles: { __typename?: 'BlogArticleConnection', totalCount: number, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'BlogArticleEdge', node: { __typename?: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any, perex: string | null, slug: string, blogCategories: Array<{ __typename?: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | null } | null> | null }, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> };

export type SimpleBlogCategoryFragmentApi = { __typename?: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null };

export type BlogCategoriesVariablesApi = Exact<{ [key: string]: never; }>;


export type BlogCategoriesApi = { __typename?: 'Query', blogCategories: Array<{ __typename?: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename?: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename?: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename?: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename?: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }> };

export type BrandDetailFragmentApi = { __typename?: 'Brand', uuid: string, slug: string, name: string, seoH1: string | null, description: string | null, brandImages: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }>, products: { __typename?: 'ProductConnection', totalCount: number, productFilterOptions: { __typename?: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename?: 'BrandFilterOption', count: number, brand: { __typename?: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename?: 'FlagFilterOption', count: number, flag: { __typename?: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename?: 'ParameterFilterOption', name: string, uuid: string, type: string, values: Array<{ __typename?: 'ParameterValueFilterOption', uuid: string, text: string, count: number, rgbHex: string | null }> }> | null }, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'ProductEdge', node: { __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | null } | null> | null } };

export type ListedBrandFragmentApi = { __typename?: 'Brand', uuid: string, name: string, slug: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

export type SimpleBrandFragmentApi = { __typename?: 'Brand', name: string, slug: string };

export type BrandImageDefaultFragmentApi = { __typename?: 'Brand', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

export type BrandsQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type BrandsQueryApi = { __typename?: 'Query', brands: Array<{ __typename?: 'Brand', uuid: string, name: string, slug: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }> };

type BreadcrumbFragment_Article_Api = { __typename?: 'Article', breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> };

type BreadcrumbFragment_BlogArticle_Api = { __typename?: 'BlogArticle', breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> };

type BreadcrumbFragment_BlogCategory_Api = { __typename?: 'BlogCategory', breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> };

type BreadcrumbFragment_Brand_Api = { __typename?: 'Brand', breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> };

type BreadcrumbFragment_Category_Api = { __typename?: 'Category', breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> };

type BreadcrumbFragment_Flag_Api = { __typename?: 'Flag', breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> };

type BreadcrumbFragment_MainVariant_Api = { __typename?: 'MainVariant', breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> };

type BreadcrumbFragment_RegularProduct_Api = { __typename?: 'RegularProduct', breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> };

type BreadcrumbFragment_Store_Api = { __typename?: 'Store', breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> };

type BreadcrumbFragment_Variant_Api = { __typename?: 'Variant', breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> };

export type BreadcrumbFragmentApi = BreadcrumbFragment_Article_Api | BreadcrumbFragment_BlogArticle_Api | BreadcrumbFragment_BlogCategory_Api | BreadcrumbFragment_Brand_Api | BreadcrumbFragment_Category_Api | BreadcrumbFragment_Flag_Api | BreadcrumbFragment_MainVariant_Api | BreadcrumbFragment_RegularProduct_Api | BreadcrumbFragment_Store_Api | BreadcrumbFragment_Variant_Api;

type CartFragment_AddToCartResult_Api = { __typename?: 'AddToCartResult', uuid: string | null, remainingAmountWithVatForFreeTransport: string | null, promoCode: string | null, selectedPickupPlaceIdentifier: string | null, items: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, totalPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, transport: { __typename?: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, payments: Array<{ __typename?: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }>, stores: { __typename?: 'StoreConnection', edges: Array<{ __typename?: 'StoreEdge', node: { __typename?: 'Store', slug: string, uuid: string, name: string, description: string | null, openingHoursHtml: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, country: { __typename?: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename?: 'TransportType', code: string } } | null, payment: { __typename?: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | null, modifications: { __typename?: 'CartModificationsResult', itemModifications: { __typename?: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, cartItemsWithModifiedPrice: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, cartItemsWithChangedQuantity: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }> }, transportModifications: { __typename?: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean }, paymentModifications: { __typename?: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean } } };

type CartFragment_Cart_Api = { __typename?: 'Cart', uuid: string | null, remainingAmountWithVatForFreeTransport: string | null, promoCode: string | null, selectedPickupPlaceIdentifier: string | null, items: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, totalPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, transport: { __typename?: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, payments: Array<{ __typename?: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }>, stores: { __typename?: 'StoreConnection', edges: Array<{ __typename?: 'StoreEdge', node: { __typename?: 'Store', slug: string, uuid: string, name: string, description: string | null, openingHoursHtml: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, country: { __typename?: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename?: 'TransportType', code: string } } | null, payment: { __typename?: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | null, modifications: { __typename?: 'CartModificationsResult', itemModifications: { __typename?: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, cartItemsWithModifiedPrice: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, cartItemsWithChangedQuantity: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }> }, transportModifications: { __typename?: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean }, paymentModifications: { __typename?: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean } } };

export type CartFragmentApi = CartFragment_AddToCartResult_Api | CartFragment_Cart_Api;

export type CartItemFragmentApi = { __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } };

type CartModificationsFragment_AddToCartResult_Api = { __typename?: 'AddToCartResult', modifications: { __typename?: 'CartModificationsResult', itemModifications: { __typename?: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, cartItemsWithModifiedPrice: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, cartItemsWithChangedQuantity: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }> }, transportModifications: { __typename?: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean }, paymentModifications: { __typename?: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean } } };

type CartModificationsFragment_Cart_Api = { __typename?: 'Cart', modifications: { __typename?: 'CartModificationsResult', itemModifications: { __typename?: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, cartItemsWithModifiedPrice: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, cartItemsWithChangedQuantity: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }> }, transportModifications: { __typename?: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean }, paymentModifications: { __typename?: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean } } };

export type CartModificationsFragmentApi = CartModificationsFragment_AddToCartResult_Api | CartModificationsFragment_Cart_Api;

export type AddToCartMutationVariablesApi = Exact<{
  cartUuid: Maybe<Scalars['Uuid']>;
  transport: Maybe<TransportInputApi>;
  payment: Maybe<PaymentInputApi>;
  promoCode: Maybe<Scalars['String']>;
  isAbsoluteQuantity: Maybe<Scalars['Boolean']>;
  productUuid: Scalars['Uuid'];
  quantity: Scalars['Int'];
}>;


export type AddToCartMutationApi = { __typename?: 'Mutation', AddToCart: { __typename?: 'AddToCartResult', uuid: string | null, remainingAmountWithVatForFreeTransport: string | null, promoCode: string | null, selectedPickupPlaceIdentifier: string | null, addProductResult: { __typename?: 'AddProductResult', addedQuantity: number, isNew: boolean, isQuantityOverLimit: boolean | null, notOnStockQuantity: number, overLimitQuantity: number | null }, items: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, totalPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, transport: { __typename?: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, payments: Array<{ __typename?: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }>, stores: { __typename?: 'StoreConnection', edges: Array<{ __typename?: 'StoreEdge', node: { __typename?: 'Store', slug: string, uuid: string, name: string, description: string | null, openingHoursHtml: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, country: { __typename?: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename?: 'TransportType', code: string } } | null, payment: { __typename?: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | null, modifications: { __typename?: 'CartModificationsResult', itemModifications: { __typename?: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, cartItemsWithModifiedPrice: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, cartItemsWithChangedQuantity: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }> }, transportModifications: { __typename?: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean }, paymentModifications: { __typename?: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean } } } };

export type RemoveFromCartMutationVariablesApi = Exact<{
  cartUuid: Maybe<Scalars['Uuid']>;
  cartItemUuid: Scalars['Uuid'];
  transport: Maybe<TransportInputApi>;
  payment: Maybe<PaymentInputApi>;
  promoCode: Maybe<Scalars['String']>;
}>;


export type RemoveFromCartMutationApi = { __typename?: 'Mutation', RemoveFromCart: { __typename?: 'Cart', uuid: string | null, remainingAmountWithVatForFreeTransport: string | null, promoCode: string | null, selectedPickupPlaceIdentifier: string | null, items: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, totalPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, transport: { __typename?: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, payments: Array<{ __typename?: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }>, stores: { __typename?: 'StoreConnection', edges: Array<{ __typename?: 'StoreEdge', node: { __typename?: 'Store', slug: string, uuid: string, name: string, description: string | null, openingHoursHtml: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, country: { __typename?: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename?: 'TransportType', code: string } } | null, payment: { __typename?: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | null, modifications: { __typename?: 'CartModificationsResult', itemModifications: { __typename?: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, cartItemsWithModifiedPrice: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, cartItemsWithChangedQuantity: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }> }, transportModifications: { __typename?: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean }, paymentModifications: { __typename?: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean } } } };

export type CartQueryVariablesApi = Exact<{
  cartUuid: Maybe<Scalars['Uuid']>;
  transport: Maybe<TransportInputApi>;
  payment: Maybe<PaymentInputApi>;
  promoCode: Maybe<Scalars['String']>;
}>;


export type CartQueryApi = { __typename?: 'Query', cart: { __typename?: 'Cart', uuid: string | null, remainingAmountWithVatForFreeTransport: string | null, promoCode: string | null, selectedPickupPlaceIdentifier: string | null, items: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, totalPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, transport: { __typename?: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, payments: Array<{ __typename?: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }>, stores: { __typename?: 'StoreConnection', edges: Array<{ __typename?: 'StoreEdge', node: { __typename?: 'Store', slug: string, uuid: string, name: string, description: string | null, openingHoursHtml: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, country: { __typename?: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename?: 'TransportType', code: string } } | null, payment: { __typename?: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | null, modifications: { __typename?: 'CartModificationsResult', itemModifications: { __typename?: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, cartItemsWithModifiedPrice: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, cartItemsWithChangedQuantity: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename?: 'CartItem', uuid: string, quantity: number, product: { __typename?: 'MainVariant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, unit: { __typename?: 'Unit', name: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } }> }, transportModifications: { __typename?: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean }, paymentModifications: { __typename?: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean } } } | null };

export type CategoryDetailFragmentApi = { __typename?: 'Category', uuid: string, slug: string, originalCategorySlug: string | null, name: string, seoH1: string | null, children: Array<{ __typename?: 'Category', uuid: string, name: string, slug: string, products: { __typename?: 'ProductConnection', totalCount: number }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }>, linkedCategories: Array<{ __typename?: 'Category', uuid: string, name: string, slug: string, products: { __typename?: 'ProductConnection', totalCount: number }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }>, readyCategorySeoMixLinks: Array<{ __typename?: 'Link', name: string, slug: string }>, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }>, products: { __typename?: 'ProductConnection', totalCount: number, productFilterOptions: { __typename?: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename?: 'BrandFilterOption', count: number, brand: { __typename?: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename?: 'FlagFilterOption', count: number, flag: { __typename?: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename?: 'ParameterFilterOption', name: string, uuid: string, type: string, values: Array<{ __typename?: 'ParameterValueFilterOption', uuid: string, text: string, count: number, rgbHex: string | null }> }> | null }, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'ProductEdge', node: { __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | null } | null> | null } };

export type CategoryImagesDefaultFragmentApi = { __typename?: 'Category', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

export type CategoryPreviewFragmentApi = { __typename?: 'Category', uuid: string, name: string, slug: string, products: { __typename?: 'ProductConnection', totalCount: number }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

export type ListedCategoryConnectionFragmentApi = { __typename?: 'CategoryConnection', totalCount: number, edges: Array<{ __typename?: 'CategoryEdge', node: { __typename?: 'Category', uuid: string, name: string, slug: string, products: { __typename?: 'ProductConnection', totalCount: number }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | null } | null> | null };

export type ListedCategoryFragmentApi = { __typename?: 'Category', uuid: string, name: string, slug: string, products: { __typename?: 'ProductConnection', totalCount: number }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

export type NavigationSubCategoriesLinkFragmentApi = { __typename?: 'Category', children: Array<{ __typename?: 'Category', name: string, slug: string }> };

export type SimpleCategoryConnectionFragmentApi = { __typename?: 'CategoryConnection', totalCount: number, edges: Array<{ __typename?: 'CategoryEdge', node: { __typename?: 'Category', name: string, slug: string } | null } | null> | null };

export type SimpleCategoryFragmentApi = { __typename?: 'Category', name: string, slug: string };

export type PromotedCategoriesQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type PromotedCategoriesQueryApi = { __typename?: 'Query', promotedCategories: Array<{ __typename?: 'Category', uuid: string, name: string, slug: string, products: { __typename?: 'ProductConnection', totalCount: number }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }> };

export type CountryFragmentApi = { __typename?: 'Country', name: string, code: string };

export type CountriesQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type CountriesQueryApi = { __typename?: 'Query', countries: Array<{ __typename?: 'Country', name: string, code: string }> };

export type CurrentCustomerUserQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type CurrentCustomerUserQueryApi = { __typename?: 'Query', currentCustomerUser: { __typename: 'CompanyCustomerUser', companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, firstName: string, lastName: string, email: string, telephone: string | null, street: string, city: string, postcode: string, newsletterSubscription: boolean, country: { __typename?: 'Country', name: string, code: string }, deliveryAddresses: Array<{ __typename?: 'DeliveryAddress', uuid: string | null, companyName: string, street: string, city: string, postcode: string, telephone: string, firstName: string, lastName: string, country: { __typename?: 'Country', name: string, code: string } }> } | { __typename: 'RegularCustomerUser', firstName: string, lastName: string, email: string, telephone: string | null, street: string, city: string, postcode: string, newsletterSubscription: boolean, country: { __typename?: 'Country', name: string, code: string }, deliveryAddresses: Array<{ __typename?: 'DeliveryAddress', uuid: string | null, companyName: string, street: string, city: string, postcode: string, telephone: string, firstName: string, lastName: string, country: { __typename?: 'Country', name: string, code: string } }> } };

export type DeliveryAddressFragmentApi = { __typename?: 'DeliveryAddress', uuid: string | null, companyName: string, street: string, city: string, postcode: string, telephone: string, firstName: string, lastName: string, country: { __typename?: 'Country', name: string, code: string } };

export type FlagDetailFragmentApi = { __typename?: 'Flag', uuid: string, slug: string, name: string, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }>, products: { __typename?: 'ProductConnection', totalCount: number, productFilterOptions: { __typename?: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename?: 'BrandFilterOption', count: number, brand: { __typename?: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename?: 'FlagFilterOption', count: number, flag: { __typename?: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename?: 'ParameterFilterOption', name: string, uuid: string, type: string, values: Array<{ __typename?: 'ParameterValueFilterOption', uuid: string, text: string, count: number, rgbHex: string | null }> }> | null }, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'ProductEdge', node: { __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | null } | null> | null } };

export type SimpleFlagFragmentApi = { __typename?: 'Flag', uuid: string, name: string, rgbColor: string };

export type ImageSizeFragmentApi = { __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null };

export type ImageSizesFragmentApi = { __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> };

export type CategoriesByColumnFragmentApi = { __typename?: 'NavigationItem', categoriesByColumns: Array<{ __typename?: 'NavigationItemCategoriesByColumns', columnNumber: number, categories: Array<{ __typename?: 'Category', name: string, slug: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, children: Array<{ __typename?: 'Category', name: string, slug: string }> }> }> };

export type ColumnCategoriesFragmentApi = { __typename?: 'NavigationItemCategoriesByColumns', categories: Array<{ __typename?: 'Category', name: string, slug: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, children: Array<{ __typename?: 'Category', name: string, slug: string }> }> };

export type NavigationQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type NavigationQueryApi = { __typename?: 'Query', navigation: Array<{ __typename?: 'NavigationItem', name: string, link: string, categoriesByColumns: Array<{ __typename?: 'NavigationItemCategoriesByColumns', columnNumber: number, categories: Array<{ __typename?: 'Category', name: string, slug: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, children: Array<{ __typename?: 'Category', name: string, slug: string }> }> }> }> };

export type NewsletterSubscribeMutationVariablesApi = Exact<{
  email: Scalars['String'];
}>;


export type NewsletterSubscribeMutationApi = { __typename?: 'Mutation', NewsletterSubscribe: boolean };

export type ListedOrderFragmentApi = { __typename?: 'Order', uuid: string, number: string, creationDate: any, items: Array<{ __typename?: 'OrderItem', quantity: number }>, transport: { __typename?: 'Transport', name: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }, payment: { __typename?: 'Payment', name: string }, totalPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } };

export type OrderDetailFragmentApi = { __typename?: 'Order', uuid: string, number: string, creationDate: any, status: string, firstName: string | null, lastName: string | null, email: string, telephone: string, companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, street: string, city: string, postcode: string, differentDeliveryAddress: boolean, deliveryFirstName: string | null, deliveryLastName: string | null, deliveryCompanyName: string | null, deliveryTelephone: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, note: string | null, urlHash: string, promoCode: string | null, trackingNumber: string | null, trackingUrl: string | null, items: Array<{ __typename?: 'OrderItem', name: string, vatRate: string, quantity: number, unit: string | null, unitPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }>, transport: { __typename?: 'Transport', name: string }, payment: { __typename?: 'Payment', name: string }, country: { __typename?: 'Country', name: string }, deliveryCountry: { __typename?: 'Country', name: string } | null };

export type OrderDetailItemFragmentApi = { __typename?: 'OrderItem', name: string, vatRate: string, quantity: number, unit: string | null, unitPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } };

export type OrderListFragmentApi = { __typename?: 'OrderConnection', totalCount: number, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'OrderEdge', cursor: string, node: { __typename?: 'Order', uuid: string, number: string, creationDate: any, items: Array<{ __typename?: 'OrderItem', quantity: number }>, transport: { __typename?: 'Transport', name: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }, payment: { __typename?: 'Payment', name: string }, totalPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } } | null } | null> | null };

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
  note: Maybe<Scalars['String']>;
  payment: PaymentInputApi;
  transport: TransportInputApi;
  cartUuid: Maybe<Scalars['Uuid']>;
  promoCode: Maybe<Scalars['String']>;
}>;


export type CreateOrderMutationApi = { __typename?: 'Mutation', CreateOrder: { __typename?: 'Order', number: string } };

export type OrderDetailQueryVariablesApi = Exact<{
  orderNumber: Maybe<Scalars['String']>;
}>;


export type OrderDetailQueryApi = { __typename?: 'Query', order: { __typename?: 'Order', uuid: string, number: string, creationDate: any, status: string, firstName: string | null, lastName: string | null, email: string, telephone: string, companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, street: string, city: string, postcode: string, differentDeliveryAddress: boolean, deliveryFirstName: string | null, deliveryLastName: string | null, deliveryCompanyName: string | null, deliveryTelephone: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, note: string | null, urlHash: string, promoCode: string | null, trackingNumber: string | null, trackingUrl: string | null, items: Array<{ __typename?: 'OrderItem', name: string, vatRate: string, quantity: number, unit: string | null, unitPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }>, transport: { __typename?: 'Transport', name: string }, payment: { __typename?: 'Payment', name: string }, country: { __typename?: 'Country', name: string }, deliveryCountry: { __typename?: 'Country', name: string } | null } | null };

export type OrdersQueryVariablesApi = Exact<{
  after: Maybe<Scalars['String']>;
  first: Maybe<Scalars['Int']>;
}>;


export type OrdersQueryApi = { __typename?: 'Query', orders: { __typename?: 'OrderConnection', totalCount: number, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'OrderEdge', cursor: string, node: { __typename?: 'Order', uuid: string, number: string, creationDate: any, items: Array<{ __typename?: 'OrderItem', quantity: number }>, transport: { __typename?: 'Transport', name: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }, payment: { __typename?: 'Payment', name: string }, totalPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } } | null } | null> | null } | null };

export type PageInfoFragmentApi = { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null };

export type ParameterFragmentApi = { __typename?: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename?: 'ParameterValue', uuid: string, text: string }> };

export type PasswordRecoveryMutationVariablesApi = Exact<{
  email: Scalars['String'];
}>;


export type PasswordRecoveryMutationApi = { __typename?: 'Mutation', RequestPasswordRecovery: string };

export type RecoverPasswordMutationVariablesApi = Exact<{
  email: Scalars['String'];
  hash: Scalars['String'];
  newPassword: Scalars['Password'];
}>;


export type RecoverPasswordMutationApi = { __typename?: 'Mutation', RecoverPassword: { __typename?: 'Token', accessToken: string, refreshToken: string } };

export type SimplePaymentFragmentApi = { __typename?: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

export type PersonalDataRequestMutationVariablesApi = Exact<{
  email: Scalars['String'];
  type: Maybe<PersonalDataAccessRequestTypeEnumApi>;
}>;


export type PersonalDataRequestMutationApi = { __typename?: 'Mutation', RequestPersonalDataAccess: { __typename?: 'PersonalDataPage', displaySiteSlug: string, exportSiteSlug: string } };

export type PersonalDataPageTextQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type PersonalDataPageTextQueryApi = { __typename?: 'Query', personalDataPage: { __typename?: 'PersonalDataPage', displaySiteContent: string, exportSiteContent: string } | null };

export type PriceFragmentApi = { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string };

export type ProductFilterOptionsBrandsFragmentApi = { __typename?: 'BrandFilterOption', count: number, brand: { __typename?: 'Brand', uuid: string, name: string } };

export type ProductFilterOptionsFlagsFragmentApi = { __typename?: 'FlagFilterOption', count: number, flag: { __typename?: 'Flag', uuid: string, name: string, rgbColor: string } };

export type ProductFilterOptionsFragmentApi = { __typename?: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename?: 'BrandFilterOption', count: number, brand: { __typename?: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename?: 'FlagFilterOption', count: number, flag: { __typename?: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename?: 'ParameterFilterOption', name: string, uuid: string, type: string, values: Array<{ __typename?: 'ParameterValueFilterOption', uuid: string, text: string, count: number, rgbHex: string | null }> }> | null };

export type ProductFilterOptionsParametersFragmentApi = { __typename?: 'ParameterFilterOption', name: string, uuid: string, type: string, values: Array<{ __typename?: 'ParameterValueFilterOption', uuid: string, text: string, count: number, rgbHex: string | null }> };

export type ListedProductConnectionFragmentApi = { __typename?: 'ProductConnection', totalCount: number, productFilterOptions: { __typename?: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename?: 'BrandFilterOption', count: number, brand: { __typename?: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename?: 'FlagFilterOption', count: number, flag: { __typename?: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename?: 'ParameterFilterOption', name: string, uuid: string, type: string, values: Array<{ __typename?: 'ParameterValueFilterOption', uuid: string, text: string, count: number, rgbHex: string | null }> }> | null }, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'ProductEdge', node: { __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | null } | null> | null };

type ListedProductFragment_MainVariant_Api = { __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

type ListedProductFragment_RegularProduct_Api = { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

type ListedProductFragment_Variant_Api = { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

export type ListedProductFragmentApi = ListedProductFragment_MainVariant_Api | ListedProductFragment_RegularProduct_Api | ListedProductFragment_Variant_Api;

type ListedProductsFragment_Brand_Api = { __typename?: 'Brand', products: { __typename?: 'ProductConnection', totalCount: number, productFilterOptions: { __typename?: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename?: 'BrandFilterOption', count: number, brand: { __typename?: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename?: 'FlagFilterOption', count: number, flag: { __typename?: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename?: 'ParameterFilterOption', name: string, uuid: string, type: string, values: Array<{ __typename?: 'ParameterValueFilterOption', uuid: string, text: string, count: number, rgbHex: string | null }> }> | null }, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'ProductEdge', node: { __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | null } | null> | null } };

type ListedProductsFragment_Category_Api = { __typename?: 'Category', products: { __typename?: 'ProductConnection', totalCount: number, productFilterOptions: { __typename?: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename?: 'BrandFilterOption', count: number, brand: { __typename?: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename?: 'FlagFilterOption', count: number, flag: { __typename?: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename?: 'ParameterFilterOption', name: string, uuid: string, type: string, values: Array<{ __typename?: 'ParameterValueFilterOption', uuid: string, text: string, count: number, rgbHex: string | null }> }> | null }, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'ProductEdge', node: { __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | null } | null> | null } };

type ListedProductsFragment_Flag_Api = { __typename?: 'Flag', products: { __typename?: 'ProductConnection', totalCount: number, productFilterOptions: { __typename?: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename?: 'BrandFilterOption', count: number, brand: { __typename?: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename?: 'FlagFilterOption', count: number, flag: { __typename?: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename?: 'ParameterFilterOption', name: string, uuid: string, type: string, values: Array<{ __typename?: 'ParameterValueFilterOption', uuid: string, text: string, count: number, rgbHex: string | null }> }> | null }, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'ProductEdge', node: { __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | null } | null> | null } };

export type ListedProductsFragmentApi = ListedProductsFragment_Brand_Api | ListedProductsFragment_Category_Api | ListedProductsFragment_Flag_Api;

export type ListedVariantFragmentApi = { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, storeAvailabilities: Array<{ __typename?: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: string, store: { __typename?: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, openingHours: string | null, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename?: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> } | null }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

export type MainVariantDetailFragmentApi = { __typename?: 'MainVariant', uuid: string, slug: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, description: string | null, stockQuantity: number, variants: Array<{ __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, storeAvailabilities: Array<{ __typename?: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: string, store: { __typename?: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, openingHours: string | null, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename?: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> } | null }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } }>, parameters: Array<{ __typename?: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename?: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } }>, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

type ProductDetailFragment_MainVariant_Api = { __typename?: 'MainVariant', shortDescription: string | null, availableStoresCount: number, exposedStoresCount: number, uuid: string, slug: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, description: string | null, stockQuantity: number, availability: { __typename?: 'Availability', name: string, status: string }, storeAvailabilities: Array<{ __typename?: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: string, store: { __typename?: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, openingHours: string | null, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename?: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> } | null }>, parameters: Array<{ __typename?: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename?: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } }>, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

type ProductDetailFragment_RegularProduct_Api = { __typename?: 'RegularProduct', shortDescription: string | null, availableStoresCount: number, exposedStoresCount: number, uuid: string, slug: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, description: string | null, stockQuantity: number, availability: { __typename?: 'Availability', name: string, status: string }, storeAvailabilities: Array<{ __typename?: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: string, store: { __typename?: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, openingHours: string | null, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename?: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> } | null }>, parameters: Array<{ __typename?: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename?: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } }>, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

type ProductDetailFragment_Variant_Api = { __typename?: 'Variant', shortDescription: string | null, availableStoresCount: number, exposedStoresCount: number, uuid: string, slug: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, description: string | null, stockQuantity: number, availability: { __typename?: 'Availability', name: string, status: string }, storeAvailabilities: Array<{ __typename?: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: string, store: { __typename?: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, openingHours: string | null, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename?: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> } | null }>, parameters: Array<{ __typename?: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename?: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } }>, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

export type ProductDetailFragmentApi = ProductDetailFragment_MainVariant_Api | ProductDetailFragment_RegularProduct_Api | ProductDetailFragment_Variant_Api;

type ProductDetailImagesFragment_MainVariant_Api = { __typename?: 'MainVariant', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

type ProductDetailImagesFragment_RegularProduct_Api = { __typename?: 'RegularProduct', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

type ProductDetailImagesFragment_Variant_Api = { __typename?: 'Variant', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

export type ProductDetailImagesFragmentApi = ProductDetailImagesFragment_MainVariant_Api | ProductDetailImagesFragment_RegularProduct_Api | ProductDetailImagesFragment_Variant_Api;

type ProductDetailInterfaceFragment_MainVariant_Api = { __typename?: 'MainVariant', uuid: string, slug: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, description: string | null, stockQuantity: number, parameters: Array<{ __typename?: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename?: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } }>, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

type ProductDetailInterfaceFragment_RegularProduct_Api = { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, description: string | null, stockQuantity: number, parameters: Array<{ __typename?: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename?: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } }>, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

type ProductDetailInterfaceFragment_Variant_Api = { __typename?: 'Variant', uuid: string, slug: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, description: string | null, stockQuantity: number, parameters: Array<{ __typename?: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename?: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } }>, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

export type ProductDetailInterfaceFragmentApi = ProductDetailInterfaceFragment_MainVariant_Api | ProductDetailInterfaceFragment_RegularProduct_Api | ProductDetailInterfaceFragment_Variant_Api;

type ProductImagesListFragment_MainVariant_Api = { __typename?: 'MainVariant', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

type ProductImagesListFragment_RegularProduct_Api = { __typename?: 'RegularProduct', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

type ProductImagesListFragment_Variant_Api = { __typename?: 'Variant', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

export type ProductImagesListFragmentApi = ProductImagesListFragment_MainVariant_Api | ProductImagesListFragment_RegularProduct_Api | ProductImagesListFragment_Variant_Api;

type ProductImagesThumbnailFragment_MainVariant_Api = { __typename?: 'MainVariant', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

type ProductImagesThumbnailFragment_RegularProduct_Api = { __typename?: 'RegularProduct', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

type ProductImagesThumbnailFragment_Variant_Api = { __typename?: 'Variant', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

export type ProductImagesThumbnailFragmentApi = ProductImagesThumbnailFragment_MainVariant_Api | ProductImagesThumbnailFragment_RegularProduct_Api | ProductImagesThumbnailFragment_Variant_Api;

type ProductPriceFragment_MainVariant_Api = { __typename?: 'MainVariant', price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

type ProductPriceFragment_RegularProduct_Api = { __typename?: 'RegularProduct', price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

type ProductPriceFragment_Variant_Api = { __typename?: 'Variant', price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

export type ProductPriceFragmentApi = ProductPriceFragment_MainVariant_Api | ProductPriceFragment_RegularProduct_Api | ProductPriceFragment_Variant_Api;

export type SimpleProductConnectionFragmentApi = { __typename?: 'ProductConnection', totalCount: number, edges: Array<{ __typename?: 'ProductEdge', node: { __typename?: 'MainVariant', fullName: string, slug: string, unit: { __typename?: 'Unit', name: string }, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | { __typename?: 'RegularProduct', fullName: string, slug: string, unit: { __typename?: 'Unit', name: string }, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | { __typename?: 'Variant', fullName: string, slug: string, unit: { __typename?: 'Unit', name: string }, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | null } | null> | null };

type SimpleProductFragment_MainVariant_Api = { __typename?: 'MainVariant', fullName: string, slug: string, unit: { __typename?: 'Unit', name: string }, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

type SimpleProductFragment_RegularProduct_Api = { __typename?: 'RegularProduct', fullName: string, slug: string, unit: { __typename?: 'Unit', name: string }, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

type SimpleProductFragment_Variant_Api = { __typename?: 'Variant', fullName: string, slug: string, unit: { __typename?: 'Unit', name: string }, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

export type SimpleProductFragmentApi = SimpleProductFragment_MainVariant_Api | SimpleProductFragment_RegularProduct_Api | SimpleProductFragment_Variant_Api;

type SliderProductFragment_MainVariant_Api = { __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

type SliderProductFragment_RegularProduct_Api = { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

type SliderProductFragment_Variant_Api = { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } };

export type SliderProductFragmentApi = SliderProductFragment_MainVariant_Api | SliderProductFragment_RegularProduct_Api | SliderProductFragment_Variant_Api;

export type PromotedProductsQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type PromotedProductsQueryApi = { __typename?: 'Query', promotedProducts: Array<{ __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } }> };

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
}>;


export type RegistrationMutationApi = { __typename?: 'Mutation', Register: { __typename?: 'Token', accessToken: string, refreshToken: string } };

export type AutocompleteSearchQueryVariablesApi = Exact<{
  search: Scalars['String'];
  maxProductCount: Maybe<Scalars['Int']>;
  maxCategoryCount: Maybe<Scalars['Int']>;
}>;


export type AutocompleteSearchQueryApi = { __typename?: 'Query', articlesSearch: Array<{ __typename?: 'Article', name: string, slug: string } | { __typename?: 'BlogArticle', name: string, slug: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }>, brandSearch: Array<{ __typename?: 'Brand', name: string, slug: string }>, categoriesSearch: { __typename?: 'CategoryConnection', totalCount: number, edges: Array<{ __typename?: 'CategoryEdge', node: { __typename?: 'Category', name: string, slug: string } | null } | null> | null }, productsSearch: { __typename?: 'ProductConnection', totalCount: number, edges: Array<{ __typename?: 'ProductEdge', node: { __typename?: 'MainVariant', fullName: string, slug: string, unit: { __typename?: 'Unit', name: string }, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | { __typename?: 'RegularProduct', fullName: string, slug: string, unit: { __typename?: 'Unit', name: string }, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | { __typename?: 'Variant', fullName: string, slug: string, unit: { __typename?: 'Unit', name: string }, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | null } | null> | null } };

export type SearchQueryVariablesApi = Exact<{
  search: Scalars['String'];
  orderingMode: Maybe<ProductOrderingModeEnumApi>;
  after: Maybe<Scalars['String']>;
  filter: Maybe<ProductFilterApi>;
}>;


export type SearchQueryApi = { __typename?: 'Query', articlesSearch: Array<{ __typename?: 'Article', name: string, slug: string } | { __typename?: 'BlogArticle', name: string, slug: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }>, brandSearch: Array<{ __typename?: 'Brand', uuid: string, name: string, slug: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }>, categoriesSearch: { __typename?: 'CategoryConnection', totalCount: number, edges: Array<{ __typename?: 'CategoryEdge', node: { __typename?: 'Category', uuid: string, name: string, slug: string, products: { __typename?: 'ProductConnection', totalCount: number }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | null } | null> | null }, productsSearch: { __typename?: 'ProductConnection', totalCount: number, productFilterOptions: { __typename?: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename?: 'BrandFilterOption', count: number, brand: { __typename?: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename?: 'FlagFilterOption', count: number, flag: { __typename?: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename?: 'ParameterFilterOption', name: string, uuid: string, type: string, values: Array<{ __typename?: 'ParameterValueFilterOption', uuid: string, text: string, count: number, rgbHex: string | null }> }> | null }, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'ProductEdge', node: { __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | null } | null> | null } };

export type SliderItemImagesWebDefaultFragmentApi = { __typename?: 'SliderItem', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> };

export type SliderItemsQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type SliderItemsQueryApi = { __typename?: 'Query', sliderItems: Array<{ __typename?: 'SliderItem', uuid: string, name: string, link: string, extendedText: string | null, extendedTextLink: string | null, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }> };

export type SlugQueryVariablesApi = Exact<{
  slug: Scalars['String'];
  sortingMode: Maybe<ProductOrderingModeEnumApi>;
  endCursorForPagination: Maybe<Scalars['String']>;
  pageSize: Maybe<Scalars['Int']>;
  filter: Maybe<ProductFilterApi>;
}>;


export type SlugQueryApi = { __typename?: 'Query', slug: { __typename: 'Article', uuid: string, slug: string, placement: string, text: string | null, articleName: string, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> } | { __typename: 'BlogArticle', uuid: string, name: string, slug: string, link: string, text: string | null, publishDate: any, blogArticleProducts: Array<{ __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } }>, blogArticlesGridImages: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> } | { __typename: 'BlogCategory', uuid: string, name: string, blogArticles: { __typename?: 'BlogArticleConnection', totalCount: number, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'BlogArticleEdge', node: { __typename?: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any, perex: string | null, slug: string, blogCategories: Array<{ __typename?: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> } | null } | null> | null }, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> } | { __typename: 'Brand', uuid: string, slug: string, name: string, seoH1: string | null, description: string | null, brandImages: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }>, products: { __typename?: 'ProductConnection', totalCount: number, productFilterOptions: { __typename?: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename?: 'BrandFilterOption', count: number, brand: { __typename?: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename?: 'FlagFilterOption', count: number, flag: { __typename?: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename?: 'ParameterFilterOption', name: string, uuid: string, type: string, values: Array<{ __typename?: 'ParameterValueFilterOption', uuid: string, text: string, count: number, rgbHex: string | null }> }> | null }, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'ProductEdge', node: { __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | null } | null> | null } } | { __typename: 'Category', uuid: string, slug: string, originalCategorySlug: string | null, name: string, seoH1: string | null, children: Array<{ __typename?: 'Category', uuid: string, name: string, slug: string, products: { __typename?: 'ProductConnection', totalCount: number }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }>, linkedCategories: Array<{ __typename?: 'Category', uuid: string, name: string, slug: string, products: { __typename?: 'ProductConnection', totalCount: number }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }>, readyCategorySeoMixLinks: Array<{ __typename?: 'Link', name: string, slug: string }>, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }>, products: { __typename?: 'ProductConnection', totalCount: number, productFilterOptions: { __typename?: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename?: 'BrandFilterOption', count: number, brand: { __typename?: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename?: 'FlagFilterOption', count: number, flag: { __typename?: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename?: 'ParameterFilterOption', name: string, uuid: string, type: string, values: Array<{ __typename?: 'ParameterValueFilterOption', uuid: string, text: string, count: number, rgbHex: string | null }> }> | null }, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'ProductEdge', node: { __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | null } | null> | null } } | { __typename: 'Flag', uuid: string, slug: string, name: string, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }>, products: { __typename?: 'ProductConnection', totalCount: number, productFilterOptions: { __typename?: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename?: 'BrandFilterOption', count: number, brand: { __typename?: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename?: 'FlagFilterOption', count: number, flag: { __typename?: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename?: 'ParameterFilterOption', name: string, uuid: string, type: string, values: Array<{ __typename?: 'ParameterValueFilterOption', uuid: string, text: string, count: number, rgbHex: string | null }> }> | null }, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, startCursor: string | null, endCursor: string | null }, edges: Array<{ __typename?: 'ProductEdge', node: { __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | null } | null> | null } } | { __typename: 'MainVariant', shortDescription: string | null, availableStoresCount: number, exposedStoresCount: number, uuid: string, slug: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, description: string | null, stockQuantity: number, availability: { __typename?: 'Availability', name: string, status: string }, storeAvailabilities: Array<{ __typename?: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: string, store: { __typename?: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, openingHours: string | null, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename?: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> } | null }>, variants: Array<{ __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, storeAvailabilities: Array<{ __typename?: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: string, store: { __typename?: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, openingHours: string | null, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename?: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> } | null }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } }>, parameters: Array<{ __typename?: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename?: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } }>, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename: 'RegularProduct', shortDescription: string | null, availableStoresCount: number, exposedStoresCount: number, uuid: string, slug: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, description: string | null, stockQuantity: number, availability: { __typename?: 'Availability', name: string, status: string }, storeAvailabilities: Array<{ __typename?: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: string, store: { __typename?: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, openingHours: string | null, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename?: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> } | null }>, parameters: Array<{ __typename?: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename?: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } }>, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, openingHours: string | null, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename?: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> } | { __typename: 'Variant', shortDescription: string | null, availableStoresCount: number, exposedStoresCount: number, uuid: string, slug: string, name: string, namePrefix: string | null, nameSuffix: string | null, catalogNumber: string, description: string | null, stockQuantity: number, availability: { __typename?: 'Availability', name: string, status: string }, storeAvailabilities: Array<{ __typename?: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: string, store: { __typename?: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, openingHours: string | null, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename?: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> } | null }>, parameters: Array<{ __typename?: 'Parameter', uuid: string, name: string, visible: boolean, values: Array<{ __typename?: 'ParameterValue', uuid: string, text: string }> }>, accessories: Array<{ __typename?: 'MainVariant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'RegularProduct', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | { __typename?: 'Variant', uuid: string, slug: string, name: string, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, catalogNumber: string, flags: Array<{ __typename?: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename?: 'Availability', name: string, status: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } }>, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }>, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, price: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } } | null };

export type StoreAvailabilityFragmentApi = { __typename?: 'StoreAvailability', exposed: boolean, availabilityInformation: string, availabilityStatus: string, store: { __typename?: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, openingHours: string | null, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename?: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> } | null };

export type ListedStoreFragmentApi = { __typename?: 'Store', slug: string, uuid: string, name: string, description: string | null, openingHours: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string };

export type StoreDetailFragmentApi = { __typename?: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, openingHours: string | null, contactInfo: string | null, specialMessage: string | null, locationLatitude: string | null, locationLongitude: string | null, storeName: string, country: { __typename?: 'Country', name: string, code: string }, breadcrumb: Array<{ __typename?: 'Link', name: string, slug: string }> };

export type StoresQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type StoresQueryApi = { __typename?: 'Query', stores: { __typename?: 'StoreConnection', edges: Array<{ __typename?: 'StoreEdge', node: { __typename?: 'Store', slug: string, uuid: string, name: string, description: string | null, openingHours: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string } | null } | null> | null } };

export type ListedPickupPlaceFragmentApi = { __typename?: 'Store', slug: string, uuid: string, name: string, description: string | null, openingHoursHtml: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, country: { __typename?: 'Country', name: string, code: string } };

export type TransportWithAvailablePaymentsAndStoresFragmentApi = { __typename?: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, payments: Array<{ __typename?: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }>, stores: { __typename?: 'StoreConnection', edges: Array<{ __typename?: 'StoreEdge', node: { __typename?: 'Store', slug: string, uuid: string, name: string, description: string | null, openingHoursHtml: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, country: { __typename?: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename?: 'TransportType', code: string } };

export type TransportsQueryVariablesApi = Exact<{
  cartUuid: Maybe<Scalars['Uuid']>;
}>;


export type TransportsQueryApi = { __typename?: 'Query', transports: Array<{ __typename?: 'Transport', uuid: string, name: string, description: string | null, instruction: string | null, daysUntilDelivery: number, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }>, payments: Array<{ __typename?: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, price: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width: number | null, height: number | null }> }> }>, stores: { __typename?: 'StoreConnection', edges: Array<{ __typename?: 'StoreEdge', node: { __typename?: 'Store', slug: string, uuid: string, name: string, description: string | null, openingHoursHtml: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, country: { __typename?: 'Country', name: string, code: string } } | null } | null> | null } | null, transportType: { __typename?: 'TransportType', code: string } }> };


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
      "Article",
      "BlogArticle"
    ],
    "Breadcrumb": [
      "Article",
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
      "AddToCartResult",
      "Cart"
    ],
    "CustomerUser": [
      "CompanyCustomerUser",
      "RegularCustomerUser"
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
      "Article",
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

export const BreadcrumbFragmentApi = gql`
    fragment BreadcrumbFragment on Breadcrumb {
  breadcrumb {
    name
    slug
  }
}
    `;
export const ArticleDetailFragmentApi = gql`
    fragment ArticleDetailFragment on Article {
  uuid
  slug
  placement
  articleName: name
  text
  ...BreadcrumbFragment
}
    ${BreadcrumbFragmentApi}`;
export const ImageSizeFragmentApi = gql`
    fragment ImageSizeFragment on ImageSize {
  size
  url
  width
  height
}
    `;
export const ImageSizesFragmentApi = gql`
    fragment ImageSizesFragment on Image {
  sizes {
    ...ImageSizeFragment
  }
}
    ${ImageSizeFragmentApi}`;
export const BlogArticleImageListGridFragmentApi = gql`
    fragment BlogArticleImageListGridFragment on BlogArticle {
  blogArticlesGridImages: images(sizes: ["listGrid"]) {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
export const SimpleFlagFragmentApi = gql`
    fragment SimpleFlagFragment on Flag {
  uuid
  name
  rgbColor
}
    `;
export const ProductImagesListFragmentApi = gql`
    fragment ProductImagesListFragment on Product {
  images(sizes: "list") {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
export const ProductPriceFragmentApi = gql`
    fragment ProductPriceFragment on Product {
  price {
    priceWithVat
    priceWithoutVat
    vatAmount
    isPriceFrom
  }
}
    `;
export const AvailabilityFragmentApi = gql`
    fragment AvailabilityFragment on Availability {
  name
  status
}
    `;
export const ListedProductFragmentApi = gql`
    fragment ListedProductFragment on Product {
  uuid
  slug
  name
  stockQuantity
  flags {
    ...SimpleFlagFragment
  }
  ...ProductImagesListFragment
  ...ProductPriceFragment
  availability {
    ...AvailabilityFragment
  }
  availableStoresCount
  exposedStoresCount
  catalogNumber
}
    ${SimpleFlagFragmentApi}
${ProductImagesListFragmentApi}
${ProductPriceFragmentApi}
${AvailabilityFragmentApi}`;
export const BlogArticleDetailFragmentApi = gql`
    fragment BlogArticleDetailFragment on BlogArticle {
  uuid
  name
  slug
  link
  ...BlogArticleImageListGridFragment
  ...BreadcrumbFragment
  text
  publishDate
  blogArticleProducts: products {
    ...ListedProductFragment
  }
}
    ${BlogArticleImageListGridFragmentApi}
${BreadcrumbFragmentApi}
${ListedProductFragmentApi}`;
export const SimpleArticleFragmentApi = gql`
    fragment SimpleArticleFragment on Article {
  name
  slug
}
    `;
export const BlogArticleImageListFragmentApi = gql`
    fragment BlogArticleImageListFragment on BlogArticle {
  images(sizes: ["list"]) {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
export const SimpleBlogArticleFragmentApi = gql`
    fragment SimpleBlogArticleFragment on BlogArticle {
  name
  slug
  ...BlogArticleImageListFragment
}
    ${BlogArticleImageListFragmentApi}`;
export const SimpleArticleInterfaceFragmentApi = gql`
    fragment SimpleArticleInterfaceFragment on ArticleInterface {
  __typename
  ...SimpleArticleFragment
  ...SimpleBlogArticleFragment
}
    ${SimpleArticleFragmentApi}
${SimpleBlogArticleFragmentApi}`;
export const PageInfoFragmentApi = gql`
    fragment PageInfoFragment on PageInfo {
  hasNextPage
  hasPreviousPage
  startCursor
  endCursor
}
    `;
export const SimpleBlogCategoryFragmentApi = gql`
    fragment SimpleBlogCategoryFragment on BlogCategory {
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
  totalCount
  pageInfo {
    ...PageInfoFragment
  }
  edges {
    node {
      ...ListedBlogArticleFragment
    }
  }
}
    ${PageInfoFragmentApi}
${ListedBlogArticleFragmentApi}`;
export const BlogCategoryDetailFragmentApi = gql`
    fragment BlogCategoryDetailFragment on BlogCategory {
  uuid
  name
  blogArticles(after: $endCursorForPagination, first: $pageSize) {
    ...BlogArticleConnectionFragment
  }
  ...BreadcrumbFragment
}
    ${BlogArticleConnectionFragmentApi}
${BreadcrumbFragmentApi}`;
export const ProductFilterOptionsBrandsFragmentApi = gql`
    fragment ProductFilterOptionsBrandsFragment on BrandFilterOption {
  count
  brand {
    uuid
    name
  }
}
    `;
export const ProductFilterOptionsFlagsFragmentApi = gql`
    fragment ProductFilterOptionsFlagsFragment on FlagFilterOption {
  count
  flag {
    ...SimpleFlagFragment
  }
}
    ${SimpleFlagFragmentApi}`;
export const ProductFilterOptionsParametersFragmentApi = gql`
    fragment ProductFilterOptionsParametersFragment on ParameterFilterOption {
  name
  uuid
  type
  values {
    uuid
    text
    count
    rgbHex
  }
}
    `;
export const ProductFilterOptionsFragmentApi = gql`
    fragment ProductFilterOptionsFragment on ProductFilterOptions {
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
    ...ProductFilterOptionsParametersFragment
  }
}
    ${ProductFilterOptionsBrandsFragmentApi}
${ProductFilterOptionsFlagsFragmentApi}
${ProductFilterOptionsParametersFragmentApi}`;
export const ListedProductConnectionFragmentApi = gql`
    fragment ListedProductConnectionFragment on ProductConnection {
  totalCount
  productFilterOptions {
    ...ProductFilterOptionsFragment
  }
  pageInfo {
    ...PageInfoFragment
  }
  edges {
    node {
      ...ListedProductFragment
    }
  }
}
    ${ProductFilterOptionsFragmentApi}
${PageInfoFragmentApi}
${ListedProductFragmentApi}`;
export const ListedProductsFragmentApi = gql`
    fragment ListedProductsFragment on ProductListable {
  products(
    orderingMode: $sortingMode
    after: $endCursorForPagination
    first: $pageSize
    filter: $filter
  ) {
    ...ListedProductConnectionFragment
  }
}
    ${ListedProductConnectionFragmentApi}`;
export const BrandDetailFragmentApi = gql`
    fragment BrandDetailFragment on Brand {
  uuid
  slug
  ...BreadcrumbFragment
  name
  seoH1
  description
  brandImages: images(sizes: ["default"]) {
    ...ImageSizesFragment
  }
  ...ListedProductsFragment
}
    ${BreadcrumbFragmentApi}
${ImageSizesFragmentApi}
${ListedProductsFragmentApi}`;
export const BrandImageDefaultFragmentApi = gql`
    fragment BrandImageDefaultFragment on Brand {
  images(sizes: ["default"]) {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
export const ListedBrandFragmentApi = gql`
    fragment ListedBrandFragment on Brand {
  uuid
  name
  slug
  ...BrandImageDefaultFragment
}
    ${BrandImageDefaultFragmentApi}`;
export const SimpleBrandFragmentApi = gql`
    fragment SimpleBrandFragment on Brand {
  name
  slug
}
    `;
export const CartItemFragmentApi = gql`
    fragment CartItemFragment on CartItem {
  uuid
  quantity
  product {
    uuid
    slug
    fullName
    catalogNumber
    stockQuantity
    flags {
      ...SimpleFlagFragment
    }
    ...ProductImagesListFragment
    stockQuantity
    availability {
      ...AvailabilityFragment
    }
    ...ProductPriceFragment
    availableStoresCount
    unit {
      name
    }
  }
}
    ${SimpleFlagFragmentApi}
${ProductImagesListFragmentApi}
${AvailabilityFragmentApi}
${ProductPriceFragmentApi}`;
export const PriceFragmentApi = gql`
    fragment PriceFragment on Price {
  priceWithVat
  priceWithoutVat
  vatAmount
}
    `;
export const CartModificationsFragmentApi = gql`
    fragment CartModificationsFragment on CartInterface {
  modifications {
    itemModifications {
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
    transportModifications {
      transportPriceChanged
      transportUnavailable
      transportWeightLimitExceeded
    }
    paymentModifications {
      paymentPriceChanged
      paymentUnavailable
    }
  }
}
    ${CartItemFragmentApi}`;
export const SimplePaymentFragmentApi = gql`
    fragment SimplePaymentFragment on Payment {
  uuid
  name
  description
  instruction
  price {
    ...PriceFragment
  }
  images {
    ...ImageSizesFragment
  }
}
    ${PriceFragmentApi}
${ImageSizesFragmentApi}`;
export const CountryFragmentApi = gql`
    fragment CountryFragment on Country {
  name
  code
}
    `;
export const ListedPickupPlaceFragmentApi = gql`
    fragment ListedPickupPlaceFragment on Store {
  slug
  uuid
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
export const TransportWithAvailablePaymentsAndStoresFragmentApi = gql`
    fragment TransportWithAvailablePaymentsAndStoresFragment on Transport {
  uuid
  name
  description
  instruction
  price {
    ...PriceFragment
  }
  images {
    ...ImageSizesFragment
  }
  payments {
    ...SimplePaymentFragment
  }
  daysUntilDelivery
  stores {
    edges {
      node {
        ...ListedPickupPlaceFragment
      }
    }
  }
  transportType {
    code
  }
}
    ${PriceFragmentApi}
${ImageSizesFragmentApi}
${SimplePaymentFragmentApi}
${ListedPickupPlaceFragmentApi}`;
export const CartFragmentApi = gql`
    fragment CartFragment on CartInterface {
  uuid
  items {
    ...CartItemFragment
  }
  totalPrice {
    ...PriceFragment
  }
  totalDiscountPrice {
    ...PriceFragment
  }
  ...CartModificationsFragment
  remainingAmountWithVatForFreeTransport
  transport {
    ...TransportWithAvailablePaymentsAndStoresFragment
  }
  payment {
    ...SimplePaymentFragment
  }
  promoCode
  selectedPickupPlaceIdentifier
}
    ${CartItemFragmentApi}
${PriceFragmentApi}
${CartModificationsFragmentApi}
${TransportWithAvailablePaymentsAndStoresFragmentApi}
${SimplePaymentFragmentApi}`;
export const CategoryImagesDefaultFragmentApi = gql`
    fragment CategoryImagesDefaultFragment on Category {
  images(size: "default") {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
export const CategoryPreviewFragmentApi = gql`
    fragment CategoryPreviewFragment on Category {
  uuid
  name
  slug
  ...CategoryImagesDefaultFragment
  products {
    totalCount
  }
}
    ${CategoryImagesDefaultFragmentApi}`;
export const CategoryDetailFragmentApi = gql`
    fragment CategoryDetailFragment on Category {
  uuid
  slug
  originalCategorySlug
  name
  seoH1
  ...BreadcrumbFragment
  children {
    ...CategoryPreviewFragment
  }
  linkedCategories {
    ...CategoryPreviewFragment
  }
  ...ListedProductsFragment
  readyCategorySeoMixLinks {
    name
    slug
  }
}
    ${BreadcrumbFragmentApi}
${CategoryPreviewFragmentApi}
${ListedProductsFragmentApi}`;
export const ListedCategoryFragmentApi = gql`
    fragment ListedCategoryFragment on Category {
  uuid
  name
  slug
  ...CategoryImagesDefaultFragment
  products {
    totalCount
  }
}
    ${CategoryImagesDefaultFragmentApi}`;
export const ListedCategoryConnectionFragmentApi = gql`
    fragment ListedCategoryConnectionFragment on CategoryConnection {
  totalCount
  edges {
    node {
      ...ListedCategoryFragment
    }
  }
}
    ${ListedCategoryFragmentApi}`;
export const SimpleCategoryFragmentApi = gql`
    fragment SimpleCategoryFragment on Category {
  name
  slug
}
    `;
export const SimpleCategoryConnectionFragmentApi = gql`
    fragment SimpleCategoryConnectionFragment on CategoryConnection {
  totalCount
  edges {
    node {
      ...SimpleCategoryFragment
    }
  }
}
    ${SimpleCategoryFragmentApi}`;
export const DeliveryAddressFragmentApi = gql`
    fragment DeliveryAddressFragment on DeliveryAddress {
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
export const FlagDetailFragmentApi = gql`
    fragment FlagDetailFragment on Flag {
  uuid
  slug
  ...BreadcrumbFragment
  name
  ...ListedProductsFragment
}
    ${BreadcrumbFragmentApi}
${ListedProductsFragmentApi}`;
export const NavigationSubCategoriesLinkFragmentApi = gql`
    fragment NavigationSubCategoriesLinkFragment on Category {
  children {
    name
    slug
  }
}
    `;
export const ColumnCategoriesFragmentApi = gql`
    fragment ColumnCategoriesFragment on NavigationItemCategoriesByColumns {
  categories {
    name
    slug
    ...CategoryImagesDefaultFragment
    ...NavigationSubCategoriesLinkFragment
  }
}
    ${CategoryImagesDefaultFragmentApi}
${NavigationSubCategoriesLinkFragmentApi}`;
export const CategoriesByColumnFragmentApi = gql`
    fragment CategoriesByColumnFragment on NavigationItem {
  categoriesByColumns {
    columnNumber
    ...ColumnCategoriesFragment
  }
}
    ${ColumnCategoriesFragmentApi}`;
export const OrderDetailItemFragmentApi = gql`
    fragment OrderDetailItemFragment on OrderItem {
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
  uuid
  number
  creationDate
  items {
    ...OrderDetailItemFragment
  }
  transport {
    name
  }
  payment {
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
  uuid
  number
  creationDate
  items {
    quantity
  }
  transport {
    name
    images(sizes: ["default"]) {
      ...ImageSizesFragment
    }
  }
  payment {
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
  totalCount
  pageInfo {
    ...PageInfoFragment
  }
  edges {
    node {
      ...ListedOrderFragment
    }
    cursor
  }
}
    ${PageInfoFragmentApi}
${ListedOrderFragmentApi}`;
export const ProductDetailImagesFragmentApi = gql`
    fragment ProductDetailImagesFragment on Product {
  images(sizes: ["default", "galleryThumbnail"]) {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
export const ParameterFragmentApi = gql`
    fragment ParameterFragment on Parameter {
  uuid
  name
  visible
  values {
    uuid
    text
  }
}
    `;
export const SliderProductFragmentApi = gql`
    fragment SliderProductFragment on Product {
  uuid
  slug
  name
  stockQuantity
  flags {
    ...SimpleFlagFragment
  }
  ...ProductImagesListFragment
  ...ProductPriceFragment
  availability {
    ...AvailabilityFragment
  }
  availableStoresCount
  exposedStoresCount
  catalogNumber
}
    ${SimpleFlagFragmentApi}
${ProductImagesListFragmentApi}
${ProductPriceFragmentApi}
${AvailabilityFragmentApi}`;
export const ProductDetailInterfaceFragmentApi = gql`
    fragment ProductDetailInterfaceFragment on Product {
  uuid
  slug
  name
  namePrefix
  nameSuffix
  ...BreadcrumbFragment
  catalogNumber
  description
  ...ProductDetailImagesFragment
  ...ProductPriceFragment
  parameters {
    ...ParameterFragment
  }
  stockQuantity
  accessories {
    ...SliderProductFragment
  }
}
    ${BreadcrumbFragmentApi}
${ProductDetailImagesFragmentApi}
${ProductPriceFragmentApi}
${ParameterFragmentApi}
${SliderProductFragmentApi}`;
export const ProductImagesThumbnailFragmentApi = gql`
    fragment ProductImagesThumbnailFragment on Product {
  images(sizes: "galleryThumbnail") {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
export const StoreDetailFragmentApi = gql`
    fragment StoreDetailFragment on Store {
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
  ...BreadcrumbFragment
}
    ${CountryFragmentApi}
${BreadcrumbFragmentApi}`;
export const StoreAvailabilityFragmentApi = gql`
    fragment StoreAvailabilityFragment on StoreAvailability {
  exposed
  availabilityInformation
  availabilityStatus
  store {
    ...StoreDetailFragment
  }
}
    ${StoreDetailFragmentApi}`;
export const ListedVariantFragmentApi = gql`
    fragment ListedVariantFragment on Variant {
  uuid
  slug
  name
  stockQuantity
  flags {
    ...SimpleFlagFragment
  }
  ...ProductImagesThumbnailFragment
  ...ProductPriceFragment
  availability {
    ...AvailabilityFragment
  }
  availableStoresCount
  exposedStoresCount
  catalogNumber
  storeAvailabilities {
    ...StoreAvailabilityFragment
  }
}
    ${SimpleFlagFragmentApi}
${ProductImagesThumbnailFragmentApi}
${ProductPriceFragmentApi}
${AvailabilityFragmentApi}
${StoreAvailabilityFragmentApi}`;
export const MainVariantDetailFragmentApi = gql`
    fragment MainVariantDetailFragment on MainVariant {
  ...ProductDetailInterfaceFragment
  variants {
    ...ListedVariantFragment
  }
}
    ${ProductDetailInterfaceFragmentApi}
${ListedVariantFragmentApi}`;
export const ProductDetailFragmentApi = gql`
    fragment ProductDetailFragment on Product {
  ...ProductDetailInterfaceFragment
  shortDescription
  availability {
    ...AvailabilityFragment
  }
  storeAvailabilities {
    ...StoreAvailabilityFragment
  }
  availableStoresCount
  exposedStoresCount
}
    ${ProductDetailInterfaceFragmentApi}
${AvailabilityFragmentApi}
${StoreAvailabilityFragmentApi}`;
export const SimpleProductFragmentApi = gql`
    fragment SimpleProductFragment on Product {
  fullName
  slug
  ...ProductPriceFragment
  ...ProductImagesListFragment
  unit {
    name
  }
}
    ${ProductPriceFragmentApi}
${ProductImagesListFragmentApi}`;
export const SimpleProductConnectionFragmentApi = gql`
    fragment SimpleProductConnectionFragment on ProductConnection {
  totalCount
  edges {
    node {
      ...SimpleProductFragment
    }
  }
}
    ${SimpleProductFragmentApi}`;
export const SliderItemImagesWebDefaultFragmentApi = gql`
    fragment SliderItemImagesWebDefaultFragment on SliderItem {
  images(type: "web", sizes: "default") {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
export const ListedStoreFragmentApi = gql`
    fragment ListedStoreFragment on Store {
  slug
  uuid
  name
  description
  openingHours
  locationLatitude
  locationLongitude
  street
  postcode
  city
}
    `;
export const BlogArticlesQueryDocumentApi = gql`
    query BlogArticlesQuery($first: Int, $onlyHomepageArticles: Boolean) {
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
    accessToken
    refreshToken
  }
}
    `;

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
    accessToken
    refreshToken
  }
}
    `;

export function useRefreshTokensApi() {
  return Urql.useMutation<RefreshTokensApi, RefreshTokensVariablesApi>(RefreshTokensDocumentApi);
};
export const BlogCategoriesDocumentApi = gql`
    query BlogCategories {
  blogCategories {
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
}
    ${SimpleBlogCategoryFragmentApi}`;

export function useBlogCategoriesApi(options: Omit<Urql.UseQueryArgs<BlogCategoriesVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<BlogCategoriesApi>({ query: BlogCategoriesDocumentApi, ...options });
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
    mutation AddToCartMutation($cartUuid: Uuid, $transport: TransportInput, $payment: PaymentInput, $promoCode: String, $isAbsoluteQuantity: Boolean, $productUuid: Uuid!, $quantity: Int!) {
  AddToCart(
    input: {cartUuid: $cartUuid, transport: $transport, payment: $payment, promoCode: $promoCode, isAbsoluteQuantity: $isAbsoluteQuantity, productUuid: $productUuid, quantity: $quantity}
  ) {
    ...CartFragment
    addProductResult {
      addedQuantity
      isNew
      isQuantityOverLimit
      notOnStockQuantity
      overLimitQuantity
    }
  }
}
    ${CartFragmentApi}`;

export function useAddToCartMutationApi() {
  return Urql.useMutation<AddToCartMutationApi, AddToCartMutationVariablesApi>(AddToCartMutationDocumentApi);
};
export const RemoveFromCartMutationDocumentApi = gql`
    mutation RemoveFromCartMutation($cartUuid: Uuid, $cartItemUuid: Uuid!, $transport: TransportInput, $payment: PaymentInput, $promoCode: String) {
  RemoveFromCart(
    input: {cartUuid: $cartUuid, cartItemUuid: $cartItemUuid, transport: $transport, payment: $payment, promoCode: $promoCode}
  ) {
    ...CartFragment
  }
}
    ${CartFragmentApi}`;

export function useRemoveFromCartMutationApi() {
  return Urql.useMutation<RemoveFromCartMutationApi, RemoveFromCartMutationVariablesApi>(RemoveFromCartMutationDocumentApi);
};
export const CartQueryDocumentApi = gql`
    query CartQuery($cartUuid: Uuid, $transport: TransportInput, $payment: PaymentInput, $promoCode: String) {
  cart(
    cartInput: {cartUuid: $cartUuid, transport: $transport, payment: $payment, promoCode: $promoCode}
  ) {
    ...CartFragment
  }
}
    ${CartFragmentApi}`;

export function useCartQueryApi(options: Omit<Urql.UseQueryArgs<CartQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<CartQueryApi>({ query: CartQueryDocumentApi, ...options });
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
export const CountriesQueryDocumentApi = gql`
    query CountriesQuery {
  countries {
    name
    code
  }
}
    `;

export function useCountriesQueryApi(options: Omit<Urql.UseQueryArgs<CountriesQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<CountriesQueryApi>({ query: CountriesQueryDocumentApi, ...options });
};
export const CurrentCustomerUserQueryDocumentApi = gql`
    query CurrentCustomerUserQuery {
  currentCustomerUser {
    __typename
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
    deliveryAddresses {
      ...DeliveryAddressFragment
    }
    ... on CompanyCustomerUser {
      companyName
      companyNumber
      companyTaxNumber
    }
  }
}
    ${CountryFragmentApi}
${DeliveryAddressFragmentApi}`;

export function useCurrentCustomerUserQueryApi(options: Omit<Urql.UseQueryArgs<CurrentCustomerUserQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<CurrentCustomerUserQueryApi>({ query: CurrentCustomerUserQueryDocumentApi, ...options });
};
export const NavigationQueryDocumentApi = gql`
    query NavigationQuery {
  navigation {
    name
    link
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
export const CreateOrderMutationDocumentApi = gql`
    mutation CreateOrderMutation($firstName: String!, $lastName: String!, $email: String!, $telephone: String!, $onCompanyBehalf: Boolean!, $companyName: String, $companyNumber: String, $companyTaxNumber: String, $street: String!, $city: String!, $postcode: String!, $country: String!, $differentDeliveryAddress: Boolean!, $deliveryFirstName: String, $deliveryLastName: String, $deliveryCompanyName: String, $deliveryTelephone: String, $deliveryStreet: String, $deliveryCity: String, $deliveryPostcode: String, $deliveryCountry: String, $note: String, $payment: PaymentInput!, $transport: TransportInput!, $cartUuid: Uuid, $promoCode: String) {
  CreateOrder(
    input: {firstName: $firstName, lastName: $lastName, email: $email, telephone: $telephone, onCompanyBehalf: $onCompanyBehalf, companyName: $companyName, companyNumber: $companyNumber, companyTaxNumber: $companyTaxNumber, street: $street, city: $city, postcode: $postcode, country: $country, differentDeliveryAddress: $differentDeliveryAddress, deliveryFirstName: $deliveryFirstName, deliveryLastName: $deliveryLastName, deliveryCompanyName: $deliveryCompanyName, deliveryTelephone: $deliveryTelephone, deliveryStreet: $deliveryStreet, deliveryCity: $deliveryCity, deliveryPostcode: $deliveryPostcode, deliveryCountry: $deliveryCountry, note: $note, payment: $payment, transport: $transport, cartUuid: $cartUuid, promoCode: $promoCode}
  ) {
    number
  }
}
    `;

export function useCreateOrderMutationApi() {
  return Urql.useMutation<CreateOrderMutationApi, CreateOrderMutationVariablesApi>(CreateOrderMutationDocumentApi);
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
    accessToken
    refreshToken
  }
}
    `;

export function useRecoverPasswordMutationApi() {
  return Urql.useMutation<RecoverPasswordMutationApi, RecoverPasswordMutationVariablesApi>(RecoverPasswordMutationDocumentApi);
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
export const PromotedProductsQueryDocumentApi = gql`
    query PromotedProductsQuery {
  promotedProducts {
    ...SliderProductFragment
  }
}
    ${SliderProductFragmentApi}`;

export function usePromotedProductsQueryApi(options: Omit<Urql.UseQueryArgs<PromotedProductsQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<PromotedProductsQueryApi>({ query: PromotedProductsQueryDocumentApi, ...options });
};
export const RegistrationMutationDocumentApi = gql`
    mutation RegistrationMutation($firstName: String!, $lastName: String!, $email: String!, $password: Password!, $telephone: String!, $street: String!, $city: String!, $postcode: String!, $country: String!, $companyCustomer: Boolean!, $companyName: String, $companyNumber: String, $companyTaxNumber: String, $newsletterSubscription: Boolean!, $previousCartUuid: Uuid) {
  Register(
    input: {firstName: $firstName, lastName: $lastName, email: $email, password: $password, telephone: $telephone, street: $street, city: $city, postcode: $postcode, country: $country, companyCustomer: $companyCustomer, companyName: $companyName, companyNumber: $companyNumber, companyTaxNumber: $companyTaxNumber, newsletterSubscription: $newsletterSubscription, cartUuid: $previousCartUuid}
  ) {
    accessToken
    refreshToken
  }
}
    `;

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
    ...SimpleProductConnectionFragment
  }
}
    ${SimpleArticleInterfaceFragmentApi}
${SimpleBrandFragmentApi}
${SimpleCategoryConnectionFragmentApi}
${SimpleProductConnectionFragmentApi}`;

export function useAutocompleteSearchQueryApi(options: Omit<Urql.UseQueryArgs<AutocompleteSearchQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<AutocompleteSearchQueryApi>({ query: AutocompleteSearchQueryDocumentApi, ...options });
};
export const SearchQueryDocumentApi = gql`
    query SearchQuery($search: String!, $orderingMode: ProductOrderingModeEnum, $after: String, $filter: ProductFilter) {
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
    after: $after
    filter: $filter
  ) {
    ...ListedProductConnectionFragment
  }
}
    ${SimpleArticleInterfaceFragmentApi}
${ListedBrandFragmentApi}
${ListedCategoryConnectionFragmentApi}
${ListedProductConnectionFragmentApi}`;

export function useSearchQueryApi(options: Omit<Urql.UseQueryArgs<SearchQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<SearchQueryApi>({ query: SearchQueryDocumentApi, ...options });
};
export const SliderItemsQueryDocumentApi = gql`
    query SliderItemsQuery {
  sliderItems {
    uuid
    name
    link
    extendedText
    extendedTextLink
    ...SliderItemImagesWebDefaultFragment
  }
}
    ${SliderItemImagesWebDefaultFragmentApi}`;

export function useSliderItemsQueryApi(options: Omit<Urql.UseQueryArgs<SliderItemsQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<SliderItemsQueryApi>({ query: SliderItemsQueryDocumentApi, ...options });
};
export const SlugQueryDocumentApi = gql`
    query SlugQuery($slug: String!, $sortingMode: ProductOrderingModeEnum, $endCursorForPagination: String, $pageSize: Int, $filter: ProductFilter) {
  slug(slug: $slug) {
    __typename
    ... on Product {
      ...ProductDetailFragment
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
    ... on Article {
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
export const StoresQueryDocumentApi = gql`
    query StoresQuery {
  stores {
    edges {
      node {
        ...ListedStoreFragment
      }
    }
  }
}
    ${ListedStoreFragmentApi}`;

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
