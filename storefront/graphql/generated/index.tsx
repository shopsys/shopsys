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
  Uuid: any;
};

export type AddProductResultApi = {
  __typename?: 'AddProductResult';
  addedQuantity: Scalars['Int'];
  isNew: Scalars['Boolean'];
  isQuantityOverLimit?: Maybe<Scalars['Boolean']>;
  notOnStockQuantity: Scalars['Int'];
  overLimitQuantity?: Maybe<Scalars['Int']>;
};

export type AddToCartInputApi = {
  /** Cart identifier, new cart will be created if not provided and customer is not logged in */
  cartUuid?: Maybe<Scalars['Uuid']>;
  /**
   * True if quantity should be set no matter the current state of the cart. False
   * if quantity should be added to the already existing same item in the cart
   */
  isAbsoluteQuantity?: Maybe<Scalars['Boolean']>;
  /** Represents a payment in order */
  payment?: Maybe<PaymentInputApi>;
  /** Product UUID */
  productUuid: Scalars['Uuid'];
  promoCode?: Maybe<Scalars['String']>;
  /** Item quantity */
  quantity: Scalars['Int'];
  /** Represents a transport in order */
  transport?: Maybe<TransportInputApi>;
};

export type AddToCartResultApi = {
  __typename?: 'AddToCartResult';
  addProductResult: AddProductResultApi;
  /** All items in the cart */
  items: Array<CartItemApi>;
  modifications: CartModificationsResultApi;
  /** Selected payment if payment provided */
  payment?: Maybe<PaymentApi>;
  /** Remaining amount for free transport and payment; null = transport cannot be free */
  remainingAmountWithVatForFreeTransport?: Maybe<Scalars['Money']>;
  totalDiscountPrice: PriceApi;
  totalPrice: PriceApi;
  /** Selected transport if transport provided */
  transport?: Maybe<TransportApi>;
  /** UUID of the cart, null for authenticated user */
  uuid?: Maybe<Scalars['Uuid']>;
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
  image: Array<Maybe<ImageApi>>;
  /** Advert link */
  link?: Maybe<Scalars['String']>;
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
  seoH1?: Maybe<Scalars['String']>;
  /** Seo meta description of article */
  seoMetaDescription?: Maybe<Scalars['String']>;
  /** Seo title of article */
  seoTitle?: Maybe<Scalars['String']>;
  /** Article URL slug */
  slug: Scalars['String'];
  /** Text of article */
  text?: Maybe<Scalars['String']>;
  /** UUID */
  uuid: Scalars['Uuid'];
};

/** A connection to a list of items. */
export type ArticleConnectionApi = {
  __typename?: 'ArticleConnection';
  /** Information to aid in pagination. */
  edges?: Maybe<Array<Maybe<ArticleEdgeApi>>>;
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
  node?: Maybe<ArticleApi>;
};

/** Represents entity that is considered to be an article on the eshop */
export type ArticleInterfaceApi = {
  breadcrumb: Array<LinkApi>;
  name?: Maybe<Scalars['String']>;
  seoH1?: Maybe<Scalars['String']>;
  seoMetaDescription?: Maybe<Scalars['String']>;
  seoTitle?: Maybe<Scalars['String']>;
  slug: Scalars['String'];
  text?: Maybe<Scalars['String']>;
  uuid: Scalars['Uuid'];
};

/** Represents an availability */
export type AvailabilityApi = {
  __typename?: 'Availability';
  /** Localized availability name (domain dependent) */
  name?: Maybe<Scalars['String']>;
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
  /** Blog article image */
  image?: Maybe<ImageApi>;
  /** The blog article absolute URL */
  link: Scalars['String'];
  /** The blog article title */
  name: Scalars['String'];
  /** The blog article perex */
  perex?: Maybe<Scalars['String']>;
  /** The list of the products assigned to the blog article */
  products: Array<ProductApi>;
  /** Date and time of the blog article publishing */
  publishDate: Scalars['DateTime'];
  /** The blog article SEO H1 heading */
  seoH1?: Maybe<Scalars['String']>;
  /** The blog article SEO meta description */
  seoMetaDescription?: Maybe<Scalars['String']>;
  /** The blog article SEO title */
  seoTitle?: Maybe<Scalars['String']>;
  /** The blog article URL slug */
  slug: Scalars['String'];
  /** The blog article text */
  text?: Maybe<Scalars['String']>;
  /** The blog article UUID */
  uuid: Scalars['Uuid'];
  /** Indicates whether the blog article is displayed on homepage */
  visibleOnHomepage: Scalars['Boolean'];
};


export type BlogArticleImageArgsApi = {
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};

/** A connection to a list of items. */
export type BlogArticleConnectionApi = {
  __typename?: 'BlogArticleConnection';
  /** Information to aid in pagination. */
  edges?: Maybe<Array<Maybe<BlogArticleEdgeApi>>>;
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
  node?: Maybe<BlogArticleApi>;
};

export type BlogCategoryApi = BreadcrumbApi & SlugApi & {
  __typename?: 'BlogCategory';
  /** Paginated blog articles of the given blog category */
  blogArticles?: Maybe<BlogArticleConnectionApi>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** The blog category children */
  children: Array<BlogCategoryApi>;
  /** The blog category description */
  description?: Maybe<Scalars['String']>;
  /** The blog category absolute URL */
  link: Scalars['String'];
  /** The blog category name */
  name: Scalars['String'];
  /** The blog category parent */
  parent?: Maybe<BlogCategoryApi>;
  /** The blog category SEO H1 heading */
  seoH1?: Maybe<Scalars['String']>;
  /** The blog category SEO meta description */
  seoMetaDescription?: Maybe<Scalars['String']>;
  /** The blog category SEO title */
  seoTitle?: Maybe<Scalars['String']>;
  /** The blog category URL slug */
  slug: Scalars['String'];
  /** The blog category UUID */
  uuid: Scalars['Uuid'];
};


export type BlogCategoryBlogArticlesArgsApi = {
  after?: Maybe<Scalars['String']>;
  before?: Maybe<Scalars['String']>;
  first?: Maybe<Scalars['Int']>;
  last?: Maybe<Scalars['Int']>;
};

/** Represents a brand */
export type BrandApi = BreadcrumbApi & SlugApi & {
  __typename?: 'Brand';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Brand description */
  description?: Maybe<Scalars['String']>;
  /** Brand images */
  images: Array<Maybe<ImageApi>>;
  /** Brand main URL */
  link: Scalars['String'];
  /** Brand name */
  name: Scalars['String'];
  /** Paginated and ordered products of brand */
  products?: Maybe<ProductConnectionApi>;
  /** Brand SEO H1 */
  seoH1?: Maybe<Scalars['String']>;
  /** Brand SEO meta description */
  seoMetaDescription?: Maybe<Scalars['String']>;
  /** Brand SEO title */
  seoTitle?: Maybe<Scalars['String']>;
  /** Brand URL slug */
  slug: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};


/** Represents a brand */
export type BrandImagesArgsApi = {
  size?: Maybe<Scalars['String']>;
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};


/** Represents a brand */
export type BrandProductsArgsApi = {
  after?: Maybe<Scalars['String']>;
  before?: Maybe<Scalars['String']>;
  filter?: Maybe<ProductFilterApi>;
  first?: Maybe<Scalars['Int']>;
  last?: Maybe<Scalars['Int']>;
  orderingMode?: Maybe<ProductOrderingModeEnumApi>;
  search?: Maybe<Scalars['String']>;
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

export type CartApi = {
  __typename?: 'Cart';
  /** All items in the cart */
  items: Array<CartItemApi>;
  modifications: CartModificationsResultApi;
  /** Selected payment if payment provided */
  payment?: Maybe<PaymentApi>;
  /** Remaining amount for free transport and payment; null = transport cannot be free */
  remainingAmountWithVatForFreeTransport?: Maybe<Scalars['Money']>;
  totalDiscountPrice: PriceApi;
  totalPrice: PriceApi;
  /** Selected transport if transport provided */
  transport?: Maybe<TransportApi>;
  /** UUID of the cart, null for authenticated user */
  uuid?: Maybe<Scalars['Uuid']>;
};

export type CartInputApi = {
  /** Cart identifier, new cart will be created if not provided and customer is not logged in */
  cartUuid?: Maybe<Scalars['Uuid']>;
  /** Represents a payment in order */
  payment?: Maybe<PaymentInputApi>;
  promoCode?: Maybe<Scalars['String']>;
  /** Represents a transport in order */
  transport?: Maybe<TransportInputApi>;
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
export type CategoryApi = BreadcrumbApi & SlugApi & {
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
  name?: Maybe<Scalars['String']>;
  /** Ancestor category */
  parent?: Maybe<CategoryApi>;
  /** Paginated and ordered products of category */
  products?: Maybe<ProductConnectionApi>;
  /** An array of links of prepared category SEO mixes of a given category */
  readyCategorySeoMixLinks: Array<LinkApi>;
  /** Seo first level heading of category */
  seoH1?: Maybe<Scalars['String']>;
  /** Seo meta description of category */
  seoMetaDescription?: Maybe<Scalars['String']>;
  /** Seo title of category */
  seoTitle?: Maybe<Scalars['String']>;
  /** Category URL slug */
  slug: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};


/** Represents a category */
export type CategoryImagesArgsApi = {
  size?: Maybe<Scalars['String']>;
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};


/** Represents a category */
export type CategoryProductsArgsApi = {
  after?: Maybe<Scalars['String']>;
  before?: Maybe<Scalars['String']>;
  filter?: Maybe<ProductFilterApi>;
  first?: Maybe<Scalars['Int']>;
  last?: Maybe<Scalars['Int']>;
  orderingMode?: Maybe<ProductOrderingModeEnumApi>;
  search?: Maybe<Scalars['String']>;
};

/** A connection to a list of items. */
export type CategoryConnectionApi = {
  __typename?: 'CategoryConnection';
  /** Information to aid in pagination. */
  edges?: Maybe<Array<Maybe<CategoryEdgeApi>>>;
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
  node?: Maybe<CategoryApi>;
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
  companyName?: Maybe<Scalars['String']>;
  /** The customer’s company identification number (only when customer is a company) */
  companyNumber?: Maybe<Scalars['String']>;
  /** The customer’s company tax number (only when customer is a company) */
  companyTaxNumber?: Maybe<Scalars['String']>;
  /** Billing address country code */
  country: Scalars['String'];
  /** Default customer delivery addresses */
  defaultDeliveryAddress?: Maybe<DeliveryAddressApi>;
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
  telephone?: Maybe<Scalars['String']>;
  /** UUID */
  uuid: Scalars['Uuid'];
};

/** Represents an currently logged customer user */
export type CustomerUserApi = {
  /** Billing address city name */
  city: Scalars['String'];
  /** Billing address country code */
  country: Scalars['String'];
  /** Default customer delivery addresses */
  defaultDeliveryAddress?: Maybe<DeliveryAddressApi>;
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
  telephone?: Maybe<Scalars['String']>;
  /** UUID */
  uuid: Scalars['Uuid'];
};

export type DeliveryAddressApi = {
  __typename?: 'DeliveryAddress';
  /** Delivery address city name */
  city: Scalars['String'];
  /** Delivery address company name */
  companyName: Scalars['String'];
  /** Delivery address country code */
  country: Scalars['String'];
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
  uuid?: Maybe<Scalars['Uuid']>;
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
export type FlagApi = BreadcrumbApi & SlugApi & {
  __typename?: 'Flag';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Categories containing at least one product with flag */
  categories: Array<CategoryApi>;
  /** Localized flag name (domain dependent) */
  name?: Maybe<Scalars['String']>;
  /** Paginated and ordered products of flag */
  products?: Maybe<ProductConnectionApi>;
  /** Flag color in rgb format */
  rgbColor: Scalars['String'];
  /** URL slug of flag */
  slug: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};


/** Represents a flag */
export type FlagCategoriesArgsApi = {
  productFilter?: Maybe<ProductFilterApi>;
};


/** Represents a flag */
export type FlagProductsArgsApi = {
  after?: Maybe<Scalars['String']>;
  before?: Maybe<Scalars['String']>;
  filter?: Maybe<ProductFilterApi>;
  first?: Maybe<Scalars['Int']>;
  last?: Maybe<Scalars['Int']>;
  orderingMode?: Maybe<ProductOrderingModeEnumApi>;
  search?: Maybe<Scalars['String']>;
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
  position?: Maybe<Scalars['Int']>;
  sizes: Array<ImageSizeApi>;
  /** Image type */
  type?: Maybe<Scalars['String']>;
};

/** Represents a single image size */
export type ImageSizeApi = {
  __typename?: 'ImageSize';
  /** Height in pixels defined in images.yaml */
  height?: Maybe<Scalars['Int']>;
  /** Image size defined in images.yaml */
  size: Scalars['String'];
  /** URL address of image */
  url: Scalars['String'];
  /** Width in pixels defined in images.yaml */
  width?: Maybe<Scalars['Int']>;
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
  /** The user email. */
  email: Scalars['String'];
  /** The user password. */
  password: Scalars['Password'];
};

/** Represents a product */
export type MainVariantApi = BreadcrumbApi & ProductApi & SlugApi & {
  __typename?: 'MainVariant';
  accessories?: Maybe<Array<Maybe<ProductApi>>>;
  availability: AvailabilityApi;
  /** Number of the stores where the product is available */
  availableStoresCount: Scalars['Int'];
  /** Brand of product */
  brand?: Maybe<BrandApi>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Product catalog number */
  catalogNumber: Scalars['String'];
  /** List of categories */
  categories: Array<CategoryApi>;
  description?: Maybe<Scalars['String']>;
  /** EAN */
  ean?: Maybe<Scalars['String']>;
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
  name?: Maybe<Scalars['String']>;
  /** Name prefix */
  namePrefix?: Maybe<Scalars['String']>;
  /** Name suffix */
  nameSuffix?: Maybe<Scalars['String']>;
  orderingPriority: Scalars['Int'];
  parameters: Array<Maybe<ParameterApi>>;
  /** Product part number */
  partNumber?: Maybe<Scalars['String']>;
  /** Product price */
  price?: Maybe<ProductPriceApi>;
  /** List of related products */
  relatedProducts: Array<ProductApi>;
  /** Seo first level heading of product */
  seoH1?: Maybe<Scalars['String']>;
  /** Seo meta description of product */
  seoMetaDescription?: Maybe<Scalars['String']>;
  /** Seo title of product */
  seoTitle?: Maybe<Scalars['String']>;
  /** Localized product short description (domain dependent) */
  shortDescription?: Maybe<Scalars['String']>;
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
  size?: Maybe<Scalars['String']>;
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
  /** Localized navigation item name (domain dependent) */
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
  images: Array<Maybe<ImageApi>>;
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
  companyName?: Maybe<Scalars['String']>;
  /** The customer’s company identification number (only when ordered on the company behalf) */
  companyNumber?: Maybe<Scalars['String']>;
  /** The customer’s company tax number (only when ordered on the company behalf) */
  companyTaxNumber?: Maybe<Scalars['String']>;
  /** Billing address country code */
  country: Scalars['String'];
  /** Date and time when the order was created */
  creationDate: Scalars['DateTime'];
  /** City name for delivery */
  deliveryCity?: Maybe<Scalars['String']>;
  /** Company name for delivery */
  deliveryCompanyName?: Maybe<Scalars['String']>;
  /** Country code for delivery */
  deliveryCountry?: Maybe<Scalars['String']>;
  /** First name of the contact person for delivery */
  deliveryFirstName?: Maybe<Scalars['String']>;
  /** Last name of the contact person for delivery */
  deliveryLastName?: Maybe<Scalars['String']>;
  /** Zip code for delivery */
  deliveryPostcode?: Maybe<Scalars['String']>;
  /** Street name for delivery */
  deliveryStreet?: Maybe<Scalars['String']>;
  /** Contact telephone number for delivery */
  deliveryTelephone?: Maybe<Scalars['String']>;
  /** Indicates whether the billing address is other than a delivery address */
  differentDeliveryAddress: Scalars['Boolean'];
  /** The customer's email address */
  email: Scalars['String'];
  /** The customer's first name */
  firstName?: Maybe<Scalars['String']>;
  /** All items in the order including payment and transport */
  items: Array<OrderItemApi>;
  /** The customer's last name */
  lastName?: Maybe<Scalars['String']>;
  /** Other information related to the order */
  note?: Maybe<Scalars['String']>;
  /** Unique order number */
  number: Scalars['String'];
  /** Payment method applied to the order */
  payment: PaymentApi;
  /** Billing address zip code */
  postcode: Scalars['String'];
  /** Promo code (coupon) used in the order */
  promoCode?: Maybe<Scalars['String']>;
  /** Current status of the order */
  status: Scalars['String'];
  /** Billing address street name  */
  street: Scalars['String'];
  /** The customer's telephone number */
  telephone: Scalars['String'];
  /** Total price of the order including transport and payment prices */
  totalPrice: PriceApi;
  /** The order tracking number */
  trackingNumber?: Maybe<Scalars['String']>;
  /** The order tracking link */
  trackingUrl?: Maybe<Scalars['String']>;
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
  edges?: Maybe<Array<Maybe<OrderEdgeApi>>>;
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
  node?: Maybe<OrderApi>;
};

/** Represents the main input object to create orders */
export type OrderInputApi = {
  /** Cart identifier used for getting carts of not logged customers */
  cartUuid?: Maybe<Scalars['Uuid']>;
  /** Billing address city name (will be on the tax invoice) */
  city: Scalars['String'];
  /** The customer’s company name (required when onCompanyBehalf is true) */
  companyName?: Maybe<Scalars['String']>;
  /** The customer’s company identification number (required when onCompanyBehalf is true) */
  companyNumber?: Maybe<Scalars['String']>;
  /** The customer’s company tax number (required when onCompanyBehalf is true) */
  companyTaxNumber?: Maybe<Scalars['String']>;
  /** Billing address country code in ISO 3166-1 alpha-2 (Country will be on the tax invoice) */
  country: Scalars['String'];
  /** City name for delivery (required when differentDeliveryAddress is true) */
  deliveryCity?: Maybe<Scalars['String']>;
  /** Company name for delivery */
  deliveryCompanyName?: Maybe<Scalars['String']>;
  /** Country code in ISO 3166-1 alpha-2 for delivery (required when differentDeliveryAddress is true) */
  deliveryCountry?: Maybe<Scalars['String']>;
  /** First name of the contact person for delivery (required when differentDeliveryAddress is true) */
  deliveryFirstName?: Maybe<Scalars['String']>;
  /** Last name of the contact person for delivery (required when differentDeliveryAddress is true) */
  deliveryLastName?: Maybe<Scalars['String']>;
  /** Zip code for delivery (required when differentDeliveryAddress is true) */
  deliveryPostcode?: Maybe<Scalars['String']>;
  /** Street name for delivery (required when differentDeliveryAddress is true) */
  deliveryStreet?: Maybe<Scalars['String']>;
  /** Contact telephone number for delivery */
  deliveryTelephone?: Maybe<Scalars['String']>;
  /** Determines whether to deliver products to a different address than the billing one */
  differentDeliveryAddress: Scalars['Boolean'];
  /** The customer's email address */
  email: Scalars['String'];
  /** The customer's first name */
  firstName: Scalars['String'];
  /** The customer's last name */
  lastName: Scalars['String'];
  /** Other information related to the order */
  note?: Maybe<Scalars['String']>;
  /** Determines whether the order is made on the company behalf. */
  onCompanyBehalf: Scalars['Boolean'];
  /** Payment method applied to the order */
  payment: PaymentInputApi;
  /** Billing address zip code (will be on the tax invoice) */
  postcode: Scalars['String'];
  /** Deprecated, this field is not used, the products are taken from the server cart instead. */
  products?: Maybe<Array<OrderProductInputApi>>;
  /** The promo code used in the order */
  promoCode?: Maybe<Scalars['String']>;
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
  unit?: Maybe<Scalars['String']>;
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
  endCursor?: Maybe<Scalars['String']>;
  /** When paginating forwards, are there more items? */
  hasNextPage: Scalars['Boolean'];
  /** When paginating backwards, are there more items? */
  hasPreviousPage: Scalars['Boolean'];
  /** When paginating backwards, the cursor to continue. */
  startCursor?: Maybe<Scalars['String']>;
};

/** Represents a parameter */
export type ParameterApi = {
  __typename?: 'Parameter';
  /** Parameter group to which the parameter is assigned */
  group?: Maybe<Scalars['String']>;
  /** Parameter name */
  name: Scalars['String'];
  /** Unit of the parameter */
  unit?: Maybe<UnitApi>;
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
  unit?: Maybe<UnitApi>;
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
  rgbHex?: Maybe<Scalars['String']>;
  /** Parameter value */
  text: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};

/** Represents a payment */
export type PaymentApi = {
  __typename?: 'Payment';
  /** Localized payment description (domain dependent) */
  description?: Maybe<Scalars['String']>;
  /** Additional data for GoPay payment */
  goPayPaymentMethod?: Maybe<GoPayPaymentMethodApi>;
  /** Payment images */
  images: Array<Maybe<ImageApi>>;
  /** Localized payment instruction (domain dependent) */
  instruction?: Maybe<Scalars['String']>;
  /** Payment name */
  name: Scalars['String'];
  /** Payment position */
  position: Scalars['Int'];
  /** Payment price */
  price?: Maybe<PriceApi>;
  /** List of assigned transports */
  transports: Array<TransportApi>;
  /** Type of payment */
  type: Scalars['String'];
  /** UUID */
  uuid: Scalars['Uuid'];
};


/** Represents a payment */
export type PaymentImagesArgsApi = {
  size?: Maybe<Scalars['String']>;
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
  customerUser?: Maybe<CustomerUserApi>;
  /** Newsletter subscription */
  newsletterSubscriber?: Maybe<NewsletterSubscriberApi>;
  /** Customer orders */
  orders: Array<OrderApi>;
};

export type PersonalDataAccessRequestInputApi = {
  /** The customer's email address */
  email: Scalars['String'];
  /** One of two possible types for personal data access request - display or export */
  type?: Maybe<PersonalDataAccessRequestTypeEnumApi>;
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
export type PriceApi = {
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

/** Represents a product */
export type ProductApi = {
  accessories?: Maybe<Array<Maybe<ProductApi>>>;
  availability: AvailabilityApi;
  /** Number of the stores where the product is available */
  availableStoresCount: Scalars['Int'];
  /** Brand of product */
  brand?: Maybe<BrandApi>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Product catalog number */
  catalogNumber: Scalars['String'];
  /** List of categories */
  categories: Array<CategoryApi>;
  description?: Maybe<Scalars['String']>;
  /** EAN */
  ean?: Maybe<Scalars['String']>;
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
  name?: Maybe<Scalars['String']>;
  /** Name prefix */
  namePrefix?: Maybe<Scalars['String']>;
  /** Name suffix */
  nameSuffix?: Maybe<Scalars['String']>;
  orderingPriority: Scalars['Int'];
  parameters: Array<Maybe<ParameterApi>>;
  /** Product part number */
  partNumber?: Maybe<Scalars['String']>;
  /** Product price */
  price?: Maybe<ProductPriceApi>;
  /** List of related products */
  relatedProducts: Array<ProductApi>;
  /** Seo first level heading of product */
  seoH1?: Maybe<Scalars['String']>;
  /** Seo meta description of product */
  seoMetaDescription?: Maybe<Scalars['String']>;
  /** Seo title of product */
  seoTitle?: Maybe<Scalars['String']>;
  /** Localized product short description (domain dependent) */
  shortDescription?: Maybe<Scalars['String']>;
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
  size?: Maybe<Scalars['String']>;
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};

/** A connection to a list of items. */
export type ProductConnectionApi = {
  __typename?: 'ProductConnection';
  /** Information to aid in pagination. */
  edges?: Maybe<Array<Maybe<ProductEdgeApi>>>;
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
  node?: Maybe<ProductApi>;
};

/** Represents a product filter */
export type ProductFilterApi = {
  /** Array of uuids of brands filter */
  brands?: Maybe<Array<Scalars['Uuid']>>;
  /** Array of uuids of flags filter */
  flags?: Maybe<Array<Scalars['Uuid']>>;
  /** Maximal price filter */
  maximalPrice?: Maybe<Scalars['Money']>;
  /** Minimal price filter */
  minimalPrice?: Maybe<Scalars['Money']>;
  /** Only in stock filter */
  onlyInStock?: Maybe<Scalars['Boolean']>;
  /** Parameter filter */
  parameters?: Maybe<Array<ParameterFilterApi>>;
};

/** Represents a product filter options */
export type ProductFilterOptionsApi = {
  __typename?: 'ProductFilterOptions';
  /** Brands filter options */
  brands?: Maybe<Array<BrandFilterOptionApi>>;
  /** Flags filter options */
  flags?: Maybe<Array<FlagFilterOptionApi>>;
  /** Number of products in stock that will be filtered */
  inStock: Scalars['Int'];
  /** Maximal price of products for filtering */
  maximalPrice: Scalars['Money'];
  /** Minimal price of products for filtering */
  minimalPrice: Scalars['Money'];
  /** Parameter filter options */
  parameters?: Maybe<Array<ParameterFilterOptionApi>>;
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
export type ProductPriceApi = {
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
  AdvertCode?: Maybe<AdvertCodeApi>;
  AdvertImage?: Maybe<AdvertImageApi>;
  CompanyCustomerUser?: Maybe<CompanyCustomerUserApi>;
  MainVariant?: Maybe<MainVariantApi>;
  RegularCustomerUser?: Maybe<RegularCustomerUserApi>;
  RegularProduct?: Maybe<RegularProductApi>;
  Variant?: Maybe<VariantApi>;
  /** Access personal data using hash received in email from personal data access request */
  accessPersonalData: PersonalDataApi;
  /** Returns list of advert positions. */
  advertPositions: Array<AdvertPositionApi>;
  /** Returns list of adverts, optionally filtered by `positionName` */
  adverts: Array<AdvertApi>;
  /** Returns article filtered using UUID or URL slug */
  article?: Maybe<ArticleApi>;
  /**
   * Returns list of articles that can be paginated using `first`, `last`, `before`
   * and `after` keywords and filtered by `placement`
   */
  articles?: Maybe<ArticleConnectionApi>;
  /** Returns list of searched articles and blog articles */
  articlesSearch?: Maybe<Array<Maybe<ArticleInterfaceApi>>>;
  /** Returns blog article filtered using UUID or URL slug */
  blogArticle?: Maybe<BlogArticleApi>;
  /** Returns a list of the blog articles that can be paginated using `first`, `last`, `before` and `after` keywords */
  blogArticles?: Maybe<BlogArticleConnectionApi>;
  /** Returns a complete list of the blog categories */
  blogCategories: Array<BlogCategoryApi>;
  /** Returns blog category filtered using UUID or URL slug */
  blogCategory?: Maybe<BlogCategoryApi>;
  /** Returns brand filtered using UUID or URL slug */
  brand?: Maybe<BrandApi>;
  /** Returns complete list of brands */
  brands: Array<BrandApi>;
  /** Return cart of logged customer or cart by UUID for anonymous user */
  cart?: Maybe<CartApi>;
  /** Returns complete list of categories */
  categories: Array<CategoryApi>;
  /** Returns list of searched categories that can be paginated using `first`, `last`, `before` and `after` keywords */
  categoriesSearch?: Maybe<CategoryConnectionApi>;
  /** Returns category filtered using UUID or URL slug */
  category?: Maybe<CategoryApi>;
  /** Returns information about cookies article */
  cookiesArticle?: Maybe<ArticleApi>;
  /** Returns currently logged in customer user */
  currentCustomerUser: CustomerUserApi;
  /** Returns a flag by uuid or url slug */
  flag?: Maybe<FlagApi>;
  /** Returns a complete list of the flags */
  flags?: Maybe<Array<FlagApi>>;
  /** Returns complete navigation menu */
  navigation: Array<NavigationItemApi>;
  /** Returns a list of notifications supposed to be displayed on all pages */
  notificationBars?: Maybe<Array<NotificationBarApi>>;
  /** Returns order filtered using UUID or urlHash */
  order?: Maybe<OrderApi>;
  /** Returns list of orders that can be paginated using `first`, `last`, `before` and `after` keywords */
  orders?: Maybe<OrderConnectionApi>;
  /** Returns payment filtered using UUID */
  payment?: Maybe<PaymentApi>;
  /** Returns complete list of payment methods */
  payments: Array<PaymentApi>;
  /** Return personal data page content and URL */
  personalDataPage?: Maybe<PersonalDataPageApi>;
  /** Returns privacy policy article */
  privacyPolicyArticle?: Maybe<ArticleApi>;
  /** Returns product filtered using UUID or URL slug */
  product?: Maybe<ProductApi>;
  /** Returns list of ordered products that can be paginated using `first`, `last`, `before` and `after` keywords */
  products?: Maybe<ProductConnectionApi>;
  /** Returns promoted categories */
  promotedCategories: Array<CategoryApi>;
  /** Returns promoted products */
  promotedProducts: Array<ProductApi>;
  /** Returns a complete list of the slider items */
  sliderItems: Array<SliderItemApi>;
  /** Returns entity by slug */
  slug?: Maybe<SlugApi>;
  /** Returns store filtered using UUID or URL slug */
  store?: Maybe<StoreApi>;
  /** Returns list of stores that can be paginated using `first`, `last`, `before` and `after` keywords */
  stores?: Maybe<StoreConnectionApi>;
  /** Returns Terms and Conditions article */
  termsAndConditionsArticle?: Maybe<ArticleApi>;
  /** Returns complete list of transport methods */
  transport?: Maybe<TransportApi>;
  /** Returns available transport methods based on the current cart state */
  transports: Array<TransportApi>;
};


export type QueryAccessPersonalDataArgsApi = {
  hash: Scalars['String'];
};


export type QueryAdvertsArgsApi = {
  positionName?: Maybe<Scalars['String']>;
};


export type QueryArticleArgsApi = {
  urlSlug?: Maybe<Scalars['String']>;
  uuid?: Maybe<Scalars['Uuid']>;
};


export type QueryArticlesArgsApi = {
  after?: Maybe<Scalars['String']>;
  before?: Maybe<Scalars['String']>;
  first?: Maybe<Scalars['Int']>;
  last?: Maybe<Scalars['Int']>;
  placement?: Maybe<Scalars['String']>;
};


export type QueryArticlesSearchArgsApi = {
  search: Scalars['String'];
};


export type QueryBlogArticleArgsApi = {
  urlSlug?: Maybe<Scalars['String']>;
  uuid?: Maybe<Scalars['Uuid']>;
};


export type QueryBlogArticlesArgsApi = {
  after?: Maybe<Scalars['String']>;
  before?: Maybe<Scalars['String']>;
  first?: Maybe<Scalars['Int']>;
  last?: Maybe<Scalars['Int']>;
};


export type QueryBlogCategoryArgsApi = {
  urlSlug?: Maybe<Scalars['String']>;
  uuid?: Maybe<Scalars['Uuid']>;
};


export type QueryBrandArgsApi = {
  urlSlug?: Maybe<Scalars['String']>;
  uuid?: Maybe<Scalars['Uuid']>;
};


export type QueryCartArgsApi = {
  cartInput?: Maybe<CartInputApi>;
};


export type QueryCategoriesSearchArgsApi = {
  after?: Maybe<Scalars['String']>;
  before?: Maybe<Scalars['String']>;
  first?: Maybe<Scalars['Int']>;
  last?: Maybe<Scalars['Int']>;
  search: Scalars['String'];
};


export type QueryCategoryArgsApi = {
  urlSlug?: Maybe<Scalars['String']>;
  uuid?: Maybe<Scalars['Uuid']>;
};


export type QueryFlagArgsApi = {
  urlSlug?: Maybe<Scalars['String']>;
  uuid?: Maybe<Scalars['Uuid']>;
};


export type QueryOrderArgsApi = {
  urlHash?: Maybe<Scalars['String']>;
  uuid?: Maybe<Scalars['Uuid']>;
};


export type QueryOrdersArgsApi = {
  after?: Maybe<Scalars['String']>;
  before?: Maybe<Scalars['String']>;
  first?: Maybe<Scalars['Int']>;
  last?: Maybe<Scalars['Int']>;
};


export type QueryPaymentArgsApi = {
  uuid: Scalars['Uuid'];
};


export type QueryProductArgsApi = {
  urlSlug?: Maybe<Scalars['String']>;
  uuid?: Maybe<Scalars['Uuid']>;
};


export type QueryProductsArgsApi = {
  after?: Maybe<Scalars['String']>;
  before?: Maybe<Scalars['String']>;
  filter?: Maybe<ProductFilterApi>;
  first?: Maybe<Scalars['Int']>;
  last?: Maybe<Scalars['Int']>;
  orderingMode?: Maybe<ProductOrderingModeEnumApi>;
  search?: Maybe<Scalars['String']>;
};


export type QuerySlugArgsApi = {
  slug: Scalars['String'];
};


export type QueryStoreArgsApi = {
  urlSlug?: Maybe<Scalars['String']>;
  uuid?: Maybe<Scalars['Uuid']>;
};


export type QueryStoresArgsApi = {
  after?: Maybe<Scalars['String']>;
  before?: Maybe<Scalars['String']>;
  first?: Maybe<Scalars['Int']>;
  last?: Maybe<Scalars['Int']>;
};


export type QueryTransportArgsApi = {
  uuid: Scalars['Uuid'];
};


export type QueryTransportsArgsApi = {
  cartUuid?: Maybe<Scalars['Uuid']>;
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
  /** Billing address city name (will be on the tax invoice) */
  city: Scalars['String'];
  /** Determines whether the registered customer is a company or not. */
  companyCustomer: Scalars['Boolean'];
  /** The customer’s company name (required when companyCustomer is true) */
  companyName?: Maybe<Scalars['String']>;
  /** The customer’s company identification number (required when companyCustomer is true) */
  companyNumber?: Maybe<Scalars['String']>;
  /** The customer’s company tax number (required when companyCustomer is true) */
  companyTaxNumber?: Maybe<Scalars['String']>;
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
  /** Billing address country code */
  country: Scalars['String'];
  /** Default customer delivery addresses */
  defaultDeliveryAddress?: Maybe<DeliveryAddressApi>;
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
  telephone?: Maybe<Scalars['String']>;
  /** UUID */
  uuid: Scalars['Uuid'];
};

/** Represents a product */
export type RegularProductApi = BreadcrumbApi & ProductApi & SlugApi & {
  __typename?: 'RegularProduct';
  accessories?: Maybe<Array<Maybe<ProductApi>>>;
  availability: AvailabilityApi;
  /** Number of the stores where the product is available */
  availableStoresCount: Scalars['Int'];
  /** Brand of product */
  brand?: Maybe<BrandApi>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Product catalog number */
  catalogNumber: Scalars['String'];
  /** List of categories */
  categories: Array<CategoryApi>;
  description?: Maybe<Scalars['String']>;
  /** EAN */
  ean?: Maybe<Scalars['String']>;
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
  name?: Maybe<Scalars['String']>;
  /** Name prefix */
  namePrefix?: Maybe<Scalars['String']>;
  /** Name suffix */
  nameSuffix?: Maybe<Scalars['String']>;
  orderingPriority: Scalars['Int'];
  parameters: Array<Maybe<ParameterApi>>;
  /** Product part number */
  partNumber?: Maybe<Scalars['String']>;
  /** Product price */
  price?: Maybe<ProductPriceApi>;
  /** List of related products */
  relatedProducts: Array<ProductApi>;
  /** Seo first level heading of product */
  seoH1?: Maybe<Scalars['String']>;
  /** Seo meta description of product */
  seoMetaDescription?: Maybe<Scalars['String']>;
  /** Seo title of product */
  seoTitle?: Maybe<Scalars['String']>;
  /** Localized product short description (domain dependent) */
  shortDescription?: Maybe<Scalars['String']>;
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
  size?: Maybe<Scalars['String']>;
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};

export type RemoveFromCartInputApi = {
  /** Cart item UUID */
  cartItemUuid: Scalars['Uuid'];
  /** Cart identifier, new cart will be created if not provided and customer is not logged in */
  cartUuid?: Maybe<Scalars['Uuid']>;
  /** Represents a payment in order */
  payment?: Maybe<PaymentInputApi>;
  promoCode?: Maybe<Scalars['String']>;
  /** Represents a transport in order */
  transport?: Maybe<TransportInputApi>;
};

export type SliderItemApi = {
  __typename?: 'SliderItem';
  /** Text below slider */
  extendedText?: Maybe<Scalars['String']>;
  /** Target link of text below slider */
  extendedTextLink?: Maybe<Scalars['String']>;
  /** GTM creative */
  gtmCreative?: Maybe<Scalars['String']>;
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
  name?: Maybe<Scalars['String']>;
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
  contactInfo?: Maybe<Scalars['String']>;
  /** Store address country code */
  country: Scalars['String'];
  /** Store description */
  description?: Maybe<Scalars['String']>;
  /** Is set as default store */
  isDefault: Scalars['Boolean'];
  /** Store location latitude */
  locationLatitude?: Maybe<Scalars['String']>;
  /** Store location longitude */
  locationLongitude?: Maybe<Scalars['String']>;
  /** Store name */
  name: Scalars['String'];
  /** Store opening hours */
  openingHours?: Maybe<Scalars['String']>;
  /** Store address postcode */
  postcode: Scalars['String'];
  /** Store URL slug */
  slug: Scalars['String'];
  specialMessage?: Maybe<Scalars['String']>;
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
  /** Name of the store */
  storeName: Scalars['String'];
};

/** A connection to a list of items. */
export type StoreConnectionApi = {
  __typename?: 'StoreConnection';
  /** Information to aid in pagination. */
  edges?: Maybe<Array<Maybe<StoreEdgeApi>>>;
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
  node?: Maybe<StoreApi>;
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
  description?: Maybe<Scalars['String']>;
  /** Transport images */
  images: Array<Maybe<ImageApi>>;
  /** Localized transport instruction (domain dependent) */
  instruction?: Maybe<Scalars['String']>;
  /** Transport name */
  name: Scalars['String'];
  /** List of assigned payments */
  payments: Array<PaymentApi>;
  /** Transport position */
  position: Scalars['Int'];
  /** Transport price */
  price?: Maybe<PriceApi>;
  /** Stores available for personal pickup */
  stores?: Maybe<StoreConnectionApi>;
  /** Type of transport */
  transportType: TransportTypeApi;
  /** UUID */
  uuid: Scalars['Uuid'];
};


/** Represents a transport */
export type TransportImagesArgsApi = {
  size?: Maybe<Scalars['String']>;
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};


/** Represents a transport */
export type TransportPriceArgsApi = {
  cartUuid?: Maybe<Scalars['Uuid']>;
};

/** Represents a transport in order */
export type TransportInputApi = {
  /** The identifier of selected personal pickup store */
  personalPickupStoreUuid?: Maybe<Scalars['Uuid']>;
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
  name?: Maybe<Scalars['String']>;
};

/** Represents a product */
export type VariantApi = BreadcrumbApi & ProductApi & SlugApi & {
  __typename?: 'Variant';
  accessories?: Maybe<Array<Maybe<ProductApi>>>;
  availability: AvailabilityApi;
  /** Number of the stores where the product is available */
  availableStoresCount: Scalars['Int'];
  /** Brand of product */
  brand?: Maybe<BrandApi>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<LinkApi>;
  /** Product catalog number */
  catalogNumber: Scalars['String'];
  /** List of categories */
  categories: Array<CategoryApi>;
  description?: Maybe<Scalars['String']>;
  /** EAN */
  ean?: Maybe<Scalars['String']>;
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
  mainVariant?: Maybe<MainVariantApi>;
  /** Localized product name (domain dependent) */
  name?: Maybe<Scalars['String']>;
  /** Name prefix */
  namePrefix?: Maybe<Scalars['String']>;
  /** Name suffix */
  nameSuffix?: Maybe<Scalars['String']>;
  orderingPriority: Scalars['Int'];
  parameters: Array<Maybe<ParameterApi>>;
  /** Product part number */
  partNumber?: Maybe<Scalars['String']>;
  /** Product price */
  price?: Maybe<ProductPriceApi>;
  /** List of related products */
  relatedProducts: Array<ProductApi>;
  /** Seo first level heading of product */
  seoH1?: Maybe<Scalars['String']>;
  /** Seo meta description of product */
  seoMetaDescription?: Maybe<Scalars['String']>;
  /** Seo title of product */
  seoTitle?: Maybe<Scalars['String']>;
  /** Localized product short description (domain dependent) */
  shortDescription?: Maybe<Scalars['String']>;
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
  size?: Maybe<Scalars['String']>;
  sizes?: Maybe<Array<Scalars['String']>>;
  type?: Maybe<Scalars['String']>;
};

export type AvailabilityNameFragmentApi = { __typename?: 'Availability', name?: string | null | undefined };

export type ImagesDefaultFragmentApi = { __typename?: 'Category', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> }> };

export type NavigationSubCategoriesLinkFragmentApi = { __typename?: 'Category', children: Array<{ __typename?: 'Category', name?: string | null | undefined, slug: string }> };

export type PromotedCategoriesQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type PromotedCategoriesQueryApi = { __typename?: 'Query', promotedCategories: Array<{ __typename?: 'Category', uuid: any, name?: string | null | undefined, slug: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> }> }> };

export type FlagLabelFragmentApi = { __typename?: 'Flag', name?: string | null | undefined, rgbColor: string };

export type ImageSizesFragmentApi = { __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> };

export type CategoriesByColumnFragmentApi = { __typename?: 'NavigationItem', categoriesByColumns: Array<{ __typename?: 'NavigationItemCategoriesByColumns', columnNumber: number, categories: Array<{ __typename?: 'Category', name?: string | null | undefined, slug: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> }>, children: Array<{ __typename?: 'Category', name?: string | null | undefined, slug: string }> }> }> };

export type ColumnCategoriesFragmentApi = { __typename?: 'NavigationItemCategoriesByColumns', categories: Array<{ __typename?: 'Category', name?: string | null | undefined, slug: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> }>, children: Array<{ __typename?: 'Category', name?: string | null | undefined, slug: string }> }> };

export type NavigationQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type NavigationQueryApi = { __typename?: 'Query', navigation: Array<{ __typename?: 'NavigationItem', name: string, link: string, categoriesByColumns: Array<{ __typename?: 'NavigationItemCategoriesByColumns', columnNumber: number, categories: Array<{ __typename?: 'Category', name?: string | null | undefined, slug: string, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> }>, children: Array<{ __typename?: 'Category', name?: string | null | undefined, slug: string }> }> }> }> };

export type NewsletterSubscribeMutationVariablesApi = Exact<{
  email: Scalars['String'];
}>;


export type NewsletterSubscribeMutationApi = { __typename?: 'Mutation', NewsletterSubscribe: boolean };

type ImageListFragment_MainVariant_Api = { __typename?: 'MainVariant', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> }> };

type ImageListFragment_RegularProduct_Api = { __typename?: 'RegularProduct', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> }> };

type ImageListFragment_Variant_Api = { __typename?: 'Variant', images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> }> };

export type ImageListFragmentApi = ImageListFragment_MainVariant_Api | ImageListFragment_RegularProduct_Api | ImageListFragment_Variant_Api;

type ProductPriceFragment_MainVariant_Api = { __typename?: 'MainVariant', price?: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } | null | undefined };

type ProductPriceFragment_RegularProduct_Api = { __typename?: 'RegularProduct', price?: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } | null | undefined };

type ProductPriceFragment_Variant_Api = { __typename?: 'Variant', price?: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } | null | undefined };

export type ProductPriceFragmentApi = ProductPriceFragment_MainVariant_Api | ProductPriceFragment_RegularProduct_Api | ProductPriceFragment_Variant_Api;

type SliderProductFragment_MainVariant_Api = { __typename: 'MainVariant', uuid: any, slug: string, name?: string | null | undefined, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, flags: Array<{ __typename?: 'Flag', name?: string | null | undefined, rgbColor: string }>, availability: { __typename?: 'Availability', name?: string | null | undefined }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> }>, price?: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } | null | undefined };

type SliderProductFragment_RegularProduct_Api = { __typename: 'RegularProduct', uuid: any, slug: string, name?: string | null | undefined, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, flags: Array<{ __typename?: 'Flag', name?: string | null | undefined, rgbColor: string }>, availability: { __typename?: 'Availability', name?: string | null | undefined }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> }>, price?: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } | null | undefined };

type SliderProductFragment_Variant_Api = { __typename: 'Variant', uuid: any, slug: string, name?: string | null | undefined, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, flags: Array<{ __typename?: 'Flag', name?: string | null | undefined, rgbColor: string }>, availability: { __typename?: 'Availability', name?: string | null | undefined }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> }>, price?: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } | null | undefined };

export type SliderProductFragmentApi = SliderProductFragment_MainVariant_Api | SliderProductFragment_RegularProduct_Api | SliderProductFragment_Variant_Api;

export type PromotedProductsQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type PromotedProductsQueryApi = { __typename?: 'Query', promotedProducts: Array<{ __typename: 'MainVariant', uuid: any, slug: string, name?: string | null | undefined, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, flags: Array<{ __typename?: 'Flag', name?: string | null | undefined, rgbColor: string }>, availability: { __typename?: 'Availability', name?: string | null | undefined }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> }>, price?: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } | null | undefined } | { __typename: 'RegularProduct', uuid: any, slug: string, name?: string | null | undefined, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, flags: Array<{ __typename?: 'Flag', name?: string | null | undefined, rgbColor: string }>, availability: { __typename?: 'Availability', name?: string | null | undefined }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> }>, price?: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } | null | undefined } | { __typename: 'Variant', uuid: any, slug: string, name?: string | null | undefined, stockQuantity: number, availableStoresCount: number, exposedStoresCount: number, flags: Array<{ __typename?: 'Flag', name?: string | null | undefined, rgbColor: string }>, availability: { __typename?: 'Availability', name?: string | null | undefined }, images: Array<{ __typename?: 'Image', sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> }>, price?: { __typename?: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean } | null | undefined }> };

export type ImagesWebDefaultFragmentApi = { __typename?: 'SliderItem', images: Array<{ __typename?: 'Image', position?: number | null | undefined, sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> }> };

export type SliderItemsQueryVariablesApi = Exact<{ [key: string]: never; }>;


export type SliderItemsQueryApi = { __typename?: 'Query', sliderItems: Array<{ __typename?: 'SliderItem', uuid: any, name: string, link: string, extendedText?: string | null | undefined, extendedTextLink?: string | null | undefined, images: Array<{ __typename?: 'Image', position?: number | null | undefined, sizes: Array<{ __typename?: 'ImageSize', size: string, url: string, width?: number | null | undefined, height?: number | null | undefined }> }> }> };


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
    "CustomerUser": [
      "CompanyCustomerUser",
      "RegularCustomerUser"
    ],
    "Product": [
      "MainVariant",
      "RegularProduct",
      "Variant"
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
    
export const ImageSizesFragmentApi = gql`
    fragment ImageSizesFragment on Image {
  sizes {
    size
    url
    width
    height
  }
}
    `;
export const ImagesDefaultFragmentApi = gql`
    fragment ImagesDefaultFragment on Category {
  images(size: "default") {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
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
    ...ImagesDefaultFragment
    ...NavigationSubCategoriesLinkFragment
  }
}
    ${ImagesDefaultFragmentApi}
${NavigationSubCategoriesLinkFragmentApi}`;
export const CategoriesByColumnFragmentApi = gql`
    fragment CategoriesByColumnFragment on NavigationItem {
  categoriesByColumns {
    columnNumber
    ...ColumnCategoriesFragment
  }
}
    ${ColumnCategoriesFragmentApi}`;
export const FlagLabelFragmentApi = gql`
    fragment FlagLabelFragment on Flag {
  name
  rgbColor
}
    `;
export const ImageListFragmentApi = gql`
    fragment ImageListFragment on Product {
  images(sizes: "list") {
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
export const AvailabilityNameFragmentApi = gql`
    fragment AvailabilityNameFragment on Availability {
  name
}
    `;
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
export const SliderProductFragmentApi = gql`
    fragment SliderProductFragment on Product {
  __typename
  uuid
  slug
  name
  stockQuantity
  flags {
    ...FlagLabelFragment
  }
  ...ImageListFragment
  availability {
    ...AvailabilityNameFragment
  }
  ...ProductPriceFragment
  availableStoresCount
  exposedStoresCount
}
    ${FlagLabelFragmentApi}
${ImageListFragmentApi}
${AvailabilityNameFragmentApi}
${ProductPriceFragmentApi}`;
export const ImagesWebDefaultFragmentApi = gql`
    fragment ImagesWebDefaultFragment on SliderItem {
  images(type: "web", sizes: "default") {
    position
    ...ImageSizesFragment
  }
}
    ${ImageSizesFragmentApi}`;
export const PromotedCategoriesQueryDocumentApi = gql`
    query PromotedCategoriesQuery {
  promotedCategories {
    uuid
    name
    slug
    ...ImagesDefaultFragment
  }
}
    ${ImagesDefaultFragmentApi}`;

export function usePromotedCategoriesQueryApi(options: Omit<Urql.UseQueryArgs<PromotedCategoriesQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<PromotedCategoriesQueryApi>({ query: PromotedCategoriesQueryDocumentApi, ...options });
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
export const SliderItemsQueryDocumentApi = gql`
    query SliderItemsQuery {
  sliderItems {
    uuid
    name
    link
    extendedText
    extendedTextLink
    ...ImagesWebDefaultFragment
  }
}
    ${ImagesWebDefaultFragmentApi}`;

export function useSliderItemsQueryApi(options: Omit<Urql.UseQueryArgs<SliderItemsQueryVariablesApi>, 'query'> = {}) {
  return Urql.useQuery<SliderItemsQueryApi>({ query: SliderItemsQueryDocumentApi, ...options });
};