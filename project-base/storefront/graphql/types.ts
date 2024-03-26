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

export type AddOrderItemsToCartInput = {
  /** Cart identifier or null if customer is logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
  /** UUID of the order based on which the cart should be prefilled */
  orderUuid: Scalars['Uuid']['input'];
  /** Information if the prefilled cart should be merged with the current cart */
  shouldMerge: InputMaybe<Scalars['Boolean']['input']>;
};

export type AddProductResult = {
  __typename?: 'AddProductResult';
  addedQuantity: Scalars['Int']['output'];
  cartItem: CartItem;
  isNew: Scalars['Boolean']['output'];
  notOnStockQuantity: Scalars['Int']['output'];
};

export type AddToCartInput = {
  /** Cart identifier, new cart will be created if not provided and customer is not logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
  /** True if quantity should be set no matter the current state of the cart. False if quantity should be added to the already existing same item in the cart */
  isAbsoluteQuantity: InputMaybe<Scalars['Boolean']['input']>;
  /** Product UUID */
  productUuid: Scalars['Uuid']['input'];
  /** Item quantity */
  quantity: Scalars['Int']['input'];
};

export type AddToCartResult = {
  __typename?: 'AddToCartResult';
  addProductResult: AddProductResult;
  cart: Cart;
};

export type Advert = {
  /** Restricted categories of the advert (the advert is shown in these categories only) */
  categories: Array<Category>;
  /** Name of advert */
  name: Scalars['String']['output'];
  /** Position of advert */
  positionName: Scalars['String']['output'];
  /** Type of advert */
  type: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};

export type AdvertCode = Advert & {
  __typename?: 'AdvertCode';
  /** Restricted categories of the advert (the advert is shown in these categories only) */
  categories: Array<Category>;
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

export type AdvertImage = Advert & {
  __typename?: 'AdvertImage';
  /** Restricted categories of the advert (the advert is shown in these categories only) */
  categories: Array<Category>;
  /** Advert images */
  images: Array<Image>;
  /** Advert link */
  link: Maybe<Scalars['String']['output']>;
  /** Adverts first image by params */
  mainImage: Maybe<Image>;
  /** Name of advert */
  name: Scalars['String']['output'];
  /** Position of advert */
  positionName: Scalars['String']['output'];
  /** Type of advert */
  type: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};


export type AdvertImageImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


export type AdvertImageMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

export type AdvertPosition = {
  __typename?: 'AdvertPosition';
  /** Desription of advert position */
  description: Scalars['String']['output'];
  /** Position of advert */
  positionName: Scalars['String']['output'];
};

export type ApplyPromoCodeToCartInput = {
  /** Cart identifier or null if customer is logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
  /** Promo code to be used after checkout */
  promoCode: Scalars['String']['input'];
};

/** A connection to a list of items. */
export type ArticleConnection = {
  __typename?: 'ArticleConnection';
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<ArticleEdge>>>;
  /** Information to aid in pagination. */
  pageInfo: PageInfo;
  /** Total number of articles */
  totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type ArticleEdge = {
  __typename?: 'ArticleEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String']['output'];
  /** The item at the end of the edge. */
  node: Maybe<NotBlogArticleInterface>;
};

/** Represents entity that is considered to be an article on the eshop */
export type ArticleInterface = {
  breadcrumb: Array<Link>;
  name: Scalars['String']['output'];
  seoH1: Maybe<Scalars['String']['output']>;
  seoMetaDescription: Maybe<Scalars['String']['output']>;
  seoTitle: Maybe<Scalars['String']['output']>;
  slug: Scalars['String']['output'];
  text: Maybe<Scalars['String']['output']>;
  uuid: Scalars['Uuid']['output'];
};

export type ArticleLink = NotBlogArticleInterface & {
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
export enum ArticlePlacementTypeEnum {
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

export type ArticleSite = ArticleInterface & Breadcrumb & NotBlogArticleInterface & Slug & {
  __typename?: 'ArticleSite';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<Link>;
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
export type Availability = {
  __typename?: 'Availability';
  /** Localized availability name (domain dependent) */
  name: Scalars['String']['output'];
  /** Availability status in a format suitable for usage in the code */
  status: AvailabilityStatusEnum;
};

/** Product Availability statuses */
export enum AvailabilityStatusEnum {
  /** Product availability status in stock */
  InStock = 'InStock',
  /** Product availability status out of stock */
  OutOfStock = 'OutOfStock'
}

export type BlogArticle = ArticleInterface & Breadcrumb & Hreflang & Slug & {
  __typename?: 'BlogArticle';
  /** The list of the blog article blog categories */
  blogCategories: Array<BlogCategory>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<Link>;
  /** Date and time of the blog article creation */
  createdAt: Scalars['DateTime']['output'];
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLink>;
  /** ID of category */
  id: Scalars['Int']['output'];
  /** Blog article images */
  images: Array<Image>;
  /** The blog article absolute URL */
  link: Scalars['String']['output'];
  /** Blog article image by params */
  mainImage: Maybe<Image>;
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


export type BlogArticleImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


export type BlogArticleMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

/** A connection to a list of items. */
export type BlogArticleConnection = {
  __typename?: 'BlogArticleConnection';
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<BlogArticleEdge>>>;
  /** Information to aid in pagination. */
  pageInfo: PageInfo;
  /** Total number of the blog articles */
  totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type BlogArticleEdge = {
  __typename?: 'BlogArticleEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String']['output'];
  /** The item at the end of the edge. */
  node: Maybe<BlogArticle>;
};

export type BlogCategory = Breadcrumb & Hreflang & Slug & {
  __typename?: 'BlogCategory';
  /** Total count of blog articles in this category */
  articlesTotalCount: Scalars['Int']['output'];
  /** Paginated blog articles of the given blog category */
  blogArticles: BlogArticleConnection;
  /** Tho whole blog categories tree (used for blog navigation rendering) */
  blogCategoriesTree: Array<BlogCategory>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<Link>;
  /** The blog category children */
  children: Array<BlogCategory>;
  /** The blog category description */
  description: Maybe<Scalars['String']['output']>;
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLink>;
  /** The blog category absolute URL */
  link: Scalars['String']['output'];
  /** The blog category name */
  name: Scalars['String']['output'];
  /** The blog category parent */
  parent: Maybe<BlogCategory>;
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


export type BlogCategoryBlogArticlesArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  first: InputMaybe<Scalars['Int']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  onlyHomepageArticles?: InputMaybe<Scalars['Boolean']['input']>;
};

/** Represents a brand */
export type Brand = Breadcrumb & Hreflang & ProductListable & Slug & {
  __typename?: 'Brand';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<Link>;
  /** Brand description */
  description: Maybe<Scalars['String']['output']>;
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLink>;
  /** ID of category */
  id: Scalars['Int']['output'];
  /** Brand images */
  images: Array<Image>;
  /** Brand main URL */
  link: Scalars['String']['output'];
  /** Brand image by params */
  mainImage: Maybe<Image>;
  /** Brand name */
  name: Scalars['String']['output'];
  /** Paginated and ordered products of brand */
  products: ProductConnection;
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
export type BrandImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a brand */
export type BrandMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a brand */
export type BrandProductsArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  brandSlug: InputMaybe<Scalars['String']['input']>;
  categorySlug: InputMaybe<Scalars['String']['input']>;
  filter: InputMaybe<ProductFilter>;
  first: InputMaybe<Scalars['Int']['input']>;
  flagSlug: InputMaybe<Scalars['String']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  orderingMode: InputMaybe<ProductOrderingModeEnum>;
};

/** Brand filter option */
export type BrandFilterOption = {
  __typename?: 'BrandFilterOption';
  /** Brand */
  brand: Brand;
  /** Count of products that will be filtered if this filter option is applied. */
  count: Scalars['Int']['output'];
  /** If true than count parameter is number of products that will be displayed if this filter option is applied, if false count parameter is number of products that will be added to current products result. */
  isAbsolute: Scalars['Boolean']['output'];
};

/** Represents entity able to return breadcrumb */
export type Breadcrumb = {
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<Link>;
};

export type Cart = CartInterface & {
  __typename?: 'Cart';
  /** All items in the cart */
  items: Array<CartItem>;
  modifications: CartModificationsResult;
  /** Selected payment if payment provided */
  payment: Maybe<Payment>;
  /** Selected bank swift code of goPay payment bank transfer */
  paymentGoPayBankSwift: Maybe<Scalars['String']['output']>;
  /** Applied promo code if provided */
  promoCode: Maybe<Scalars['String']['output']>;
  /** Remaining amount for free transport and payment; null = transport cannot be free */
  remainingAmountWithVatForFreeTransport: Maybe<Scalars['Money']['output']>;
  /** Rounding amount if payment has rounding allowed */
  roundingPrice: Maybe<Price>;
  /** Selected pickup place identifier if provided */
  selectedPickupPlaceIdentifier: Maybe<Scalars['String']['output']>;
  totalDiscountPrice: Price;
  /** Total items price (excluding transport and payment) */
  totalItemsPrice: Price;
  /** Total price including transport and payment */
  totalPrice: Price;
  /** Total price (exluding discount, transport and payment) */
  totalPriceWithoutDiscountTransportAndPayment: Price;
  /** Selected transport if transport provided */
  transport: Maybe<Transport>;
  /** UUID of the cart, null for authenticated user */
  uuid: Maybe<Scalars['Uuid']['output']>;
};

export type CartInput = {
  /** Cart identifier, new cart will be created if not provided and customer is not logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type CartInterface = {
  items: Array<CartItem>;
  modifications: CartModificationsResult;
  payment: Maybe<Payment>;
  paymentGoPayBankSwift: Maybe<Scalars['String']['output']>;
  promoCode: Maybe<Scalars['String']['output']>;
  remainingAmountWithVatForFreeTransport: Maybe<Scalars['Money']['output']>;
  /** Rounding amount if payment has rounding allowed */
  roundingPrice: Maybe<Price>;
  selectedPickupPlaceIdentifier: Maybe<Scalars['String']['output']>;
  totalDiscountPrice: Price;
  /** Total items price (excluding transport and payment) */
  totalItemsPrice: Price;
  /** Total price including transport and payment */
  totalPrice: Price;
  transport: Maybe<Transport>;
  uuid: Maybe<Scalars['Uuid']['output']>;
};

/** Represent one item in the cart */
export type CartItem = {
  __typename?: 'CartItem';
  /** Product in the cart */
  product: Product;
  /** Quantity of items in the cart */
  quantity: Scalars['Int']['output'];
  /** Cart item UUID */
  uuid: Scalars['Uuid']['output'];
};

export type CartItemModificationsResult = {
  __typename?: 'CartItemModificationsResult';
  cartItemsWithChangedQuantity: Array<CartItem>;
  cartItemsWithModifiedPrice: Array<CartItem>;
  noLongerAvailableCartItemsDueToQuantity: Array<CartItem>;
  noLongerListableCartItems: Array<CartItem>;
};

export type CartModificationsResult = {
  __typename?: 'CartModificationsResult';
  itemModifications: CartItemModificationsResult;
  multipleAddedProductModifications: CartMultipleAddedProductModificationsResult;
  paymentModifications: CartPaymentModificationsResult;
  promoCodeModifications: CartPromoCodeModificationsResult;
  someProductWasRemovedFromEshop: Scalars['Boolean']['output'];
  transportModifications: CartTransportModificationsResult;
};

export type CartMultipleAddedProductModificationsResult = {
  __typename?: 'CartMultipleAddedProductModificationsResult';
  notAddedProducts: Array<Product>;
};

export type CartPaymentModificationsResult = {
  __typename?: 'CartPaymentModificationsResult';
  paymentPriceChanged: Scalars['Boolean']['output'];
  paymentUnavailable: Scalars['Boolean']['output'];
};

export type CartPromoCodeModificationsResult = {
  __typename?: 'CartPromoCodeModificationsResult';
  noLongerApplicablePromoCode: Array<Scalars['String']['output']>;
};

export type CartTransportModificationsResult = {
  __typename?: 'CartTransportModificationsResult';
  personalPickupStoreUnavailable: Scalars['Boolean']['output'];
  transportPriceChanged: Scalars['Boolean']['output'];
  transportUnavailable: Scalars['Boolean']['output'];
  transportWeightLimitExceeded: Scalars['Boolean']['output'];
};

/** Represents a category */
export type Category = Breadcrumb & ProductListable & Slug & {
  __typename?: 'Category';
  /** Best selling products */
  bestsellers: Array<Product>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<Link>;
  /** All parent category names with their IDs and UUIDs */
  categoryHierarchy: Array<CategoryHierarchyItem>;
  /** Descendant categories */
  children: Array<Category>;
  /** Localized category description (domain dependent) */
  description: Maybe<Scalars['String']['output']>;
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLink>;
  /** ID of category */
  id: Scalars['Int']['output'];
  /** Category images */
  images: Array<Image>;
  /** A list of categories linked to the given category */
  linkedCategories: Array<Category>;
  /** Category image by params */
  mainImage: Maybe<Image>;
  /** Localized category name (domain dependent) */
  name: Scalars['String']['output'];
  /** Original category URL slug (for CategorySeoMixes slug of assigned category is returned, null is returned for regular category) */
  originalCategorySlug: Maybe<Scalars['String']['output']>;
  /** Ancestor category */
  parent: Maybe<Category>;
  /** Paginated and ordered products of category */
  products: ProductConnection;
  /** An array of links of prepared category SEO mixes of a given category */
  readyCategorySeoMixLinks: Array<Link>;
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
export type CategoryImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a category */
export type CategoryMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a category */
export type CategoryProductsArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  brandSlug: InputMaybe<Scalars['String']['input']>;
  categorySlug: InputMaybe<Scalars['String']['input']>;
  filter: InputMaybe<ProductFilter>;
  first: InputMaybe<Scalars['Int']['input']>;
  flagSlug: InputMaybe<Scalars['String']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  orderingMode: InputMaybe<ProductOrderingModeEnum>;
};

/** A connection to a list of items. */
export type CategoryConnection = {
  __typename?: 'CategoryConnection';
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<CategoryEdge>>>;
  /** Information to aid in pagination. */
  pageInfo: PageInfo;
  /** Total number of categories */
  totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type CategoryEdge = {
  __typename?: 'CategoryEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String']['output'];
  /** The item at the end of the edge. */
  node: Maybe<Category>;
};

export type CategoryHierarchyItem = {
  __typename?: 'CategoryHierarchyItem';
  /** ID of the category */
  id: Scalars['Int']['output'];
  /** Localized category name (domain dependent) */
  name: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};

export type ChangePasswordInput = {
  /** Customer user email. */
  email: Scalars['String']['input'];
  /** New customer user password. */
  newPassword: Scalars['Password']['input'];
  /** Current customer user password. */
  oldPassword: Scalars['Password']['input'];
};

export type ChangePaymentInCartInput = {
  /** Cart identifier or null if customer is logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
  /** Selected bank swift code of goPay payment bank transfer */
  paymentGoPayBankSwift: InputMaybe<Scalars['String']['input']>;
  /** UUID of a payment that should be added to the cart. If this is set to null, the payment is removed from the cart */
  paymentUuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type ChangePaymentInOrderInput = {
  /** Order identifier */
  orderUuid: Scalars['Uuid']['input'];
  /** Selected bank swift code of goPay payment bank transfer */
  paymentGoPayBankSwift: InputMaybe<Scalars['String']['input']>;
  /** UUID of a payment that should be assigned to the order. */
  paymentUuid: Scalars['Uuid']['input'];
};

export type ChangePersonalDataInput = {
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

export type ChangeTransportInCartInput = {
  /** Cart identifier or null if customer is logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
  /** The identifier of selected personal pickup place */
  pickupPlaceIdentifier: InputMaybe<Scalars['String']['input']>;
  /** UUID of a transport that should be added to the cart. If this is set to null, the transport is removed from the cart */
  transportUuid: InputMaybe<Scalars['Uuid']['input']>;
};

/** Represents an currently logged customer user */
export type CompanyCustomerUser = CustomerUser & {
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
  country: Country;
  /** Default customer delivery addresses */
  defaultDeliveryAddress: Maybe<DeliveryAddress>;
  /** List of delivery addresses */
  deliveryAddresses: Array<DeliveryAddress>;
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

export type ContactInput = {
  /** Email address of the sender */
  email: Scalars['String']['input'];
  /** Message sent to recipient */
  message: Scalars['String']['input'];
  /** Name of the sender */
  name: Scalars['String']['input'];
};

/** Represents country */
export type Country = {
  __typename?: 'Country';
  /** Country code in ISO 3166-1 alpha-2 */
  code: Scalars['String']['output'];
  /** Localized country name */
  name: Scalars['String']['output'];
};

export type CreateOrderResult = {
  __typename?: 'CreateOrderResult';
  cart: Maybe<Cart>;
  order: Maybe<Order>;
  orderCreated: Scalars['Boolean']['output'];
};

/** Represents an currently logged customer user */
export type CustomerUser = {
  /** Billing address city name */
  city: Scalars['String']['output'];
  /** Billing address country */
  country: Country;
  /** Default customer delivery addresses */
  defaultDeliveryAddress: Maybe<DeliveryAddress>;
  /** List of delivery addresses */
  deliveryAddresses: Array<DeliveryAddress>;
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

export type DeliveryAddress = {
  __typename?: 'DeliveryAddress';
  /** Delivery address city name */
  city: Maybe<Scalars['String']['output']>;
  /** Delivery address company name */
  companyName: Maybe<Scalars['String']['output']>;
  /** Delivery address country */
  country: Maybe<Country>;
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

export type DeliveryAddressInput = {
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
export type Flag = Breadcrumb & Hreflang & ProductListable & Slug & {
  __typename?: 'Flag';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<Link>;
  /** Categories containing at least one product with flag */
  categories: Array<Category>;
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLink>;
  /** Localized flag name (domain dependent) */
  name: Scalars['String']['output'];
  /** Paginated and ordered products of flag */
  products: ProductConnection;
  /** Flag color in rgb format */
  rgbColor: Scalars['String']['output'];
  /** URL slug of flag */
  slug: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};


/** Represents a flag */
export type FlagCategoriesArgs = {
  productFilter: InputMaybe<ProductFilter>;
};


/** Represents a flag */
export type FlagProductsArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  brandSlug: InputMaybe<Scalars['String']['input']>;
  categorySlug: InputMaybe<Scalars['String']['input']>;
  filter: InputMaybe<ProductFilter>;
  first: InputMaybe<Scalars['Int']['input']>;
  flagSlug: InputMaybe<Scalars['String']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  orderingMode: InputMaybe<ProductOrderingModeEnum>;
};

/** Flag filter option */
export type FlagFilterOption = {
  __typename?: 'FlagFilterOption';
  /** Count of products that will be filtered if this filter option is applied. */
  count: Scalars['Int']['output'];
  /** Flag */
  flag: Flag;
  /** If true than count parameter is number of products that will be displayed if this filter option is applied, if false count parameter is number of products that will be added to current products result. */
  isAbsolute: Scalars['Boolean']['output'];
  /** Indicator whether the option is already selected (used for "ready category seo mixes") */
  isSelected: Scalars['Boolean']['output'];
};

export type GoPayBankSwift = {
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

export type GoPayCreatePaymentSetup = {
  __typename?: 'GoPayCreatePaymentSetup';
  /** url of gopay embedJs file */
  embedJs: Scalars['String']['output'];
  /** redirect URL to payment gateway */
  gatewayUrl: Scalars['String']['output'];
  /** payment transaction identifier */
  goPayId: Scalars['String']['output'];
};

export type GoPayPaymentMethod = {
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
export type Hreflang = {
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLink>;
};

export type HreflangLink = {
  __typename?: 'HreflangLink';
  /** URL for hreflang meta tag */
  href: Scalars['String']['output'];
  /** Language code for hreflang meta tag */
  hreflang: Scalars['String']['output'];
};

/** Represents an image */
export type Image = {
  __typename?: 'Image';
  /** Name of the image usable as an alternative text */
  name: Maybe<Scalars['String']['output']>;
  /** URL address of the image */
  url: Scalars['String']['output'];
};

/** Represents a single user translation of language constant */
export type LanguageConstant = {
  __typename?: 'LanguageConstant';
  /** Translation key */
  key: Scalars['String']['output'];
  /** User translation */
  translation: Scalars['String']['output'];
};

/** Represents an internal link */
export type Link = {
  __typename?: 'Link';
  /** Clickable text for a hyperlink */
  name: Scalars['String']['output'];
  /** Target URL slug */
  slug: Scalars['String']['output'];
};

export type LoginInput = {
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

export type LoginResult = {
  __typename?: 'LoginResult';
  showCartMergeInfo: Scalars['Boolean']['output'];
  tokens: Token;
};

/** Represents a product */
export type MainVariant = Breadcrumb & Hreflang & Product & Slug & {
  __typename?: 'MainVariant';
  accessories: Array<Product>;
  availability: Availability;
  /** Number of the stores where the product is available */
  availableStoresCount: Scalars['Int']['output'];
  /** Brand of product */
  brand: Maybe<Brand>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<Link>;
  /** Product catalog number */
  catalogNumber: Scalars['String']['output'];
  /** List of categories */
  categories: Array<Category>;
  description: Maybe<Scalars['String']['output']>;
  /** EAN */
  ean: Maybe<Scalars['String']['output']>;
  /** List of flags */
  flags: Array<Flag>;
  /** The full name of the product, which consists of a prefix, name, and a suffix */
  fullName: Scalars['String']['output'];
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLink>;
  /** Product id */
  id: Scalars['Int']['output'];
  /** Product images */
  images: Array<Image>;
  isMainVariant: Scalars['Boolean']['output'];
  isSellingDenied: Scalars['Boolean']['output'];
  /** Product link */
  link: Scalars['String']['output'];
  /** Product image by params */
  mainImage: Maybe<Image>;
  /** Localized product name (domain dependent) */
  name: Scalars['String']['output'];
  /** Name prefix */
  namePrefix: Maybe<Scalars['String']['output']>;
  /** Name suffix */
  nameSuffix: Maybe<Scalars['String']['output']>;
  orderingPriority: Scalars['Int']['output'];
  parameters: Array<Parameter>;
  /** Product part number */
  partNumber: Maybe<Scalars['String']['output']>;
  /** Product price */
  price: ProductPrice;
  productVideos: Array<VideoToken>;
  /** List of related products */
  relatedProducts: Array<Product>;
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
  storeAvailabilities: Array<StoreAvailability>;
  unit: Unit;
  /** List of product's unique selling propositions */
  usps: Array<Scalars['String']['output']>;
  /** UUID */
  uuid: Scalars['Uuid']['output'];
  variants: Array<Variant>;
};


/** Represents a product */
export type MainVariantImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a product */
export type MainVariantMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

export type Mutation = {
  __typename?: 'Mutation';
  /** Fills cart based on a given order, possibly merging it with the current cart */
  AddOrderItemsToCart: Cart;
  /** Adds a product to a product list */
  AddProductToList: ProductList;
  /** Add product to cart for future checkout */
  AddToCart: AddToCartResult;
  /** Apply new promo code for the future checkout */
  ApplyPromoCodeToCart: Cart;
  /** Changes customer user password */
  ChangePassword: CustomerUser;
  /** Add a payment to the cart, or remove a payment from the cart */
  ChangePaymentInCart: Cart;
  /** change payment in an order after the order creation (available for unpaid GoPay orders only) */
  ChangePaymentInOrder: Order;
  /** Changes customer user personal data */
  ChangePersonalData: CustomerUser;
  /** Add a transport to the cart, or remove a transport from the cart */
  ChangeTransportInCart: Cart;
  /** Send message to the site owner */
  Contact: Scalars['Boolean']['output'];
  /** Creates complete order with products and addresses */
  CreateOrder: CreateOrderResult;
  /** Delete delivery address by Uuid */
  DeleteDeliveryAddress: Array<DeliveryAddress>;
  /** Edit delivery address by Uuid */
  EditDeliveryAddress: Array<DeliveryAddress>;
  /** Login customer user */
  Login: LoginResult;
  /** Logout user */
  Logout: Scalars['Boolean']['output'];
  /** Subscribe for e-mail newsletter */
  NewsletterSubscribe: Scalars['Boolean']['output'];
  /** Pay order(create payment transaction in payment gateway) and get payment setup data for redirect or creating JS payment gateway layer */
  PayOrder: PaymentSetupCreationData;
  /** Recover password using hash required from RequestPasswordRecovery */
  RecoverPassword: LoginResult;
  /** Refreshes access and refresh tokens */
  RefreshTokens: Token;
  /** Register new customer user */
  Register: LoginResult;
  /** Remove product from cart */
  RemoveFromCart: Cart;
  /** Removes a product from a product list */
  RemoveProductFromList: Maybe<ProductList>;
  /** Removes the product list */
  RemoveProductList: Maybe<ProductList>;
  /** Remove already used promo code from cart */
  RemovePromoCodeFromCart: Cart;
  /** Request password recovery - email with hash will be sent */
  RequestPasswordRecovery: Scalars['String']['output'];
  /** Request access to personal data */
  RequestPersonalDataAccess: PersonalDataPage;
  /** Set default delivery address by Uuid */
  SetDefaultDeliveryAddress: CustomerUser;
  /** check payment status of order after callback from payment service */
  UpdatePaymentStatus: Order;
};


export type MutationAddOrderItemsToCartArgs = {
  input: AddOrderItemsToCartInput;
};


export type MutationAddProductToListArgs = {
  input: ProductListUpdateInput;
};


export type MutationAddToCartArgs = {
  input: AddToCartInput;
};


export type MutationApplyPromoCodeToCartArgs = {
  input: ApplyPromoCodeToCartInput;
};


export type MutationChangePasswordArgs = {
  input: ChangePasswordInput;
};


export type MutationChangePaymentInCartArgs = {
  input: ChangePaymentInCartInput;
};


export type MutationChangePaymentInOrderArgs = {
  input: ChangePaymentInOrderInput;
};


export type MutationChangePersonalDataArgs = {
  input: ChangePersonalDataInput;
};


export type MutationChangeTransportInCartArgs = {
  input: ChangeTransportInCartInput;
};


export type MutationContactArgs = {
  input: ContactInput;
};


export type MutationCreateOrderArgs = {
  input: OrderInput;
};


export type MutationDeleteDeliveryAddressArgs = {
  deliveryAddressUuid: Scalars['Uuid']['input'];
};


export type MutationEditDeliveryAddressArgs = {
  input: DeliveryAddressInput;
};


export type MutationLoginArgs = {
  input: LoginInput;
};


export type MutationNewsletterSubscribeArgs = {
  input: NewsletterSubscriptionDataInput;
};


export type MutationPayOrderArgs = {
  orderUuid: Scalars['Uuid']['input'];
};


export type MutationRecoverPasswordArgs = {
  input: RecoverPasswordInput;
};


export type MutationRefreshTokensArgs = {
  input: RefreshTokenInput;
};


export type MutationRegisterArgs = {
  input: RegistrationDataInput;
};


export type MutationRemoveFromCartArgs = {
  input: RemoveFromCartInput;
};


export type MutationRemoveProductFromListArgs = {
  input: ProductListUpdateInput;
};


export type MutationRemoveProductListArgs = {
  input: ProductListInput;
};


export type MutationRemovePromoCodeFromCartArgs = {
  input: RemovePromoCodeFromCartInput;
};


export type MutationRequestPasswordRecoveryArgs = {
  email: Scalars['String']['input'];
};


export type MutationRequestPersonalDataAccessArgs = {
  input: PersonalDataAccessRequestInput;
};


export type MutationSetDefaultDeliveryAddressArgs = {
  deliveryAddressUuid: Scalars['Uuid']['input'];
};


export type MutationUpdatePaymentStatusArgs = {
  orderPaymentStatusPageValidityHash: InputMaybe<Scalars['String']['input']>;
  orderUuid: Scalars['Uuid']['input'];
};

/** Represents a navigation structure item */
export type NavigationItem = {
  __typename?: 'NavigationItem';
  /** Categories separated into columns */
  categoriesByColumns: Array<NavigationItemCategoriesByColumns>;
  /** Target URL */
  link: Scalars['String']['output'];
  /** Navigation item name */
  name: Scalars['String']['output'];
};

/** Represents a single column inside the navigation item */
export type NavigationItemCategoriesByColumns = {
  __typename?: 'NavigationItemCategoriesByColumns';
  /** Categories */
  categories: Array<Category>;
  /** Column number */
  columnNumber: Scalars['Int']['output'];
};

export type NewsletterSubscriber = {
  __typename?: 'NewsletterSubscriber';
  /** Date and time of subscription */
  createdAt: Scalars['DateTime']['output'];
  /** Subscribed email address */
  email: Scalars['String']['output'];
};

/** Represents the main input object to subscribe for e-mail newsletter */
export type NewsletterSubscriptionDataInput = {
  email: Scalars['String']['input'];
};

/** Represents an article that is not a blog article */
export type NotBlogArticleInterface = {
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
export type NotificationBar = {
  __typename?: 'NotificationBar';
  /** Notification bar images */
  images: Array<Image>;
  /** Notification bar image by params */
  mainImage: Maybe<Image>;
  /** Color of the notification */
  rgbColor: Scalars['String']['output'];
  /** Message of the notification */
  text: Scalars['String']['output'];
};


/** Represents a notification supposed to be displayed on all pages */
export type NotificationBarImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a notification supposed to be displayed on all pages */
export type NotificationBarMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents store opening hours */
export type OpeningHours = {
  __typename?: 'OpeningHours';
  /** Current day of the week */
  dayOfWeek: Scalars['Int']['output'];
  /** Is store currently open? */
  isOpen: Scalars['Boolean']['output'];
  /** Opening hours for every day of the week (1 for Monday 7 for Sunday) */
  openingHoursOfDays: Array<OpeningHoursOfDay>;
};

/** Represents store opening hours for a specific day */
export type OpeningHoursOfDay = {
  __typename?: 'OpeningHoursOfDay';
  /** Date of day with display timezone for domain */
  date: Scalars['DateTime']['output'];
  /** Day of the week */
  dayOfWeek: Scalars['Int']['output'];
  /** An array of opening hours ranges (each range contains opening and closing time) */
  openingHoursRanges: Array<OpeningHoursRange>;
};

/** Represents a time period when a store is open */
export type OpeningHoursRange = {
  __typename?: 'OpeningHoursRange';
  /** Closing time */
  closingTime: Scalars['String']['output'];
  /** Opening time */
  openingTime: Scalars['String']['output'];
};

export type Order = {
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
  country: Country;
  /** Date and time when the order was created */
  creationDate: Scalars['DateTime']['output'];
  /** City name for delivery */
  deliveryCity: Maybe<Scalars['String']['output']>;
  /** Company name for delivery */
  deliveryCompanyName: Maybe<Scalars['String']['output']>;
  /** Country for delivery */
  deliveryCountry: Maybe<Country>;
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
  items: Array<OrderItem>;
  /** The customer's last name */
  lastName: Maybe<Scalars['String']['output']>;
  /** Other information related to the order */
  note: Maybe<Scalars['String']['output']>;
  /** Unique order number */
  number: Scalars['String']['output'];
  /** Payment method applied to the order */
  payment: Payment;
  /** Count of the payment transactions related to the order */
  paymentTransactionsCount: Scalars['Int']['output'];
  /** Selected pickup place identifier */
  pickupPlaceIdentifier: Maybe<Scalars['String']['output']>;
  /** Billing address zip code */
  postcode: Scalars['String']['output'];
  /** All product items in the order */
  productItems: Array<OrderItem>;
  /** Promo code (coupon) used in the order */
  promoCode: Maybe<Scalars['String']['output']>;
  /** Current status of the order */
  status: Scalars['String']['output'];
  /** Billing address street name  */
  street: Scalars['String']['output'];
  /** The customer's telephone number */
  telephone: Scalars['String']['output'];
  /** Total price of the order including transport and payment prices */
  totalPrice: Price;
  /** The order tracking number */
  trackingNumber: Maybe<Scalars['String']['output']>;
  /** The order tracking link */
  trackingUrl: Maybe<Scalars['String']['output']>;
  /** Transport method applied to the order */
  transport: Transport;
  /** Unique url hash that can be used to  */
  urlHash: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};

/** A connection to a list of items. */
export type OrderConnection = {
  __typename?: 'OrderConnection';
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<OrderEdge>>>;
  /** Information to aid in pagination. */
  pageInfo: PageInfo;
  /** Total number of orders */
  totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type OrderEdge = {
  __typename?: 'OrderEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String']['output'];
  /** The item at the end of the edge. */
  node: Maybe<Order>;
};

/** Represents the main input object to create orders */
export type OrderInput = {
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
  payment: InputMaybe<PaymentInput>;
  /** Billing address zip code (will be on the tax invoice) */
  postcode: Scalars['String']['input'];
  /** Deprecated, this field is not used, the products are taken from the server cart instead. */
  products: InputMaybe<Array<OrderProductInput>>;
  /** Billing address street name (will be on the tax invoice) */
  street: Scalars['String']['input'];
  /** The customer's phone number */
  telephone: Scalars['String']['input'];
  /** Deprecated, this field is not used, the transport is taken from the server cart instead. */
  transport: InputMaybe<TransportInput>;
};

/** Represent one item in the order */
export type OrderItem = {
  __typename?: 'OrderItem';
  /** Name of the order item */
  name: Scalars['String']['output'];
  /** Quantity of order items in the order */
  quantity: Scalars['Int']['output'];
  /** Total price for the quantity of order item */
  totalPrice: Price;
  /** Unit of measurement used for the order item */
  unit: Maybe<Scalars['String']['output']>;
  /** Order item price per unit */
  unitPrice: Price;
  /** Applied VAT rate percentage applied to the order item */
  vatRate: Scalars['String']['output'];
};

export type OrderPaymentsConfig = {
  __typename?: 'OrderPaymentsConfig';
  /** All available payment methods for the order (excluding the current one) */
  availablePayments: Array<Payment>;
  /** Current payment method used in the order */
  currentPayment: Payment;
};

/** Represents a product in order */
export type OrderProductInput = {
  /** Quantity of products */
  quantity: Scalars['Int']['input'];
  /** Product price per unit */
  unitPrice: PriceInput;
  /** UUID */
  uuid: Scalars['Uuid']['input'];
};

/** Information about pagination in a connection. */
export type PageInfo = {
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
export type Parameter = {
  __typename?: 'Parameter';
  /** Parameter group to which the parameter is assigned */
  group: Maybe<Scalars['String']['output']>;
  /** Parameter name */
  name: Scalars['String']['output'];
  /** Unit of the parameter */
  unit: Maybe<Unit>;
  /** UUID */
  uuid: Scalars['Uuid']['output'];
  values: Array<ParameterValue>;
  visible: Scalars['Boolean']['output'];
};

/** Parameter filter option */
export type ParameterCheckboxFilterOption = ParameterFilterOptionInterface & {
  __typename?: 'ParameterCheckboxFilterOption';
  /** Indicator whether the parameter should be collapsed based on the current category setting */
  isCollapsed: Scalars['Boolean']['output'];
  /** The parameter name */
  name: Scalars['String']['output'];
  /** The parameter unit */
  unit: Maybe<Unit>;
  /** The parameter UUID */
  uuid: Scalars['Uuid']['output'];
  /** Filter options of parameter values */
  values: Array<ParameterValueFilterOption>;
};

/** Parameter filter option */
export type ParameterColorFilterOption = ParameterFilterOptionInterface & {
  __typename?: 'ParameterColorFilterOption';
  /** Indicator whether the parameter should be collapsed based on the current category setting */
  isCollapsed: Scalars['Boolean']['output'];
  /** The parameter name */
  name: Scalars['String']['output'];
  /** The parameter unit */
  unit: Maybe<Unit>;
  /** The parameter UUID */
  uuid: Scalars['Uuid']['output'];
  /** Filter options of parameter values */
  values: Array<ParameterValueColorFilterOption>;
};

/** Represents a parameter filter */
export type ParameterFilter = {
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
export type ParameterFilterOptionInterface = {
  /** Indicator whether the parameter should be collapsed based on the current category setting */
  isCollapsed: Scalars['Boolean']['output'];
  /** The parameter name */
  name: Scalars['String']['output'];
  /** The parameter unit */
  unit: Maybe<Unit>;
  /** The parameter UUID */
  uuid: Scalars['Uuid']['output'];
};

/** Parameter filter option */
export type ParameterSliderFilterOption = ParameterFilterOptionInterface & {
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
  unit: Maybe<Unit>;
  /** The parameter UUID */
  uuid: Scalars['Uuid']['output'];
};

/** Represents a parameter value */
export type ParameterValue = {
  __typename?: 'ParameterValue';
  /** Parameter value */
  text: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};

/** Parameter value filter option */
export type ParameterValueColorFilterOption = {
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
export type ParameterValueFilterOption = {
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
export type Payment = {
  __typename?: 'Payment';
  /** Localized payment description (domain dependent) */
  description: Maybe<Scalars['String']['output']>;
  /** Additional data for GoPay payment */
  goPayPaymentMethod: Maybe<GoPayPaymentMethod>;
  /** Payment images */
  images: Array<Image>;
  /** Localized payment instruction (domain dependent) */
  instruction: Maybe<Scalars['String']['output']>;
  /** Payment image by params */
  mainImage: Maybe<Image>;
  /** Payment name */
  name: Scalars['String']['output'];
  /** Payment position */
  position: Scalars['Int']['output'];
  /** Payment price */
  price: Price;
  /** List of assigned transports */
  transports: Array<Transport>;
  /** Type of payment */
  type: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};


/** Represents a payment */
export type PaymentImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a payment */
export type PaymentMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a payment */
export type PaymentPriceArgs = {
  cartUuid?: InputMaybe<Scalars['Uuid']['input']>;
};

/** Represents a payment in order */
export type PaymentInput = {
  /** Price for payment */
  price: PriceInput;
  /** UUID */
  uuid: Scalars['Uuid']['input'];
};

export type PaymentSetupCreationData = {
  __typename?: 'PaymentSetupCreationData';
  /** Identifiers of GoPay payment method */
  goPayCreatePaymentSetup: Maybe<GoPayCreatePaymentSetup>;
};

export type PersonalData = {
  __typename?: 'PersonalData';
  /** Customer user data */
  customerUser: Maybe<CustomerUser>;
  /** A link for downloading the personal data in an XML file */
  exportLink: Scalars['String']['output'];
  /** Newsletter subscription */
  newsletterSubscriber: Maybe<NewsletterSubscriber>;
  /** Customer orders */
  orders: Array<Order>;
};

export type PersonalDataAccessRequestInput = {
  /** The customer's email address */
  email: Scalars['String']['input'];
  /** One of two possible types for personal data access request - display or export */
  type: InputMaybe<PersonalDataAccessRequestTypeEnum>;
};

/** One of two possible types for personal data access request */
export enum PersonalDataAccessRequestTypeEnum {
  /** Display data */
  Display = 'display',
  /** Export data */
  Export = 'export'
}

export type PersonalDataPage = {
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
export type Price = PriceInterface & {
  __typename?: 'Price';
  /** Price with VAT */
  priceWithVat: Scalars['Money']['output'];
  /** Price without VAT */
  priceWithoutVat: Scalars['Money']['output'];
  /** Total value of VAT */
  vatAmount: Scalars['Money']['output'];
};

/** Represents the price */
export type PriceInput = {
  /** Price with VAT */
  priceWithVat: Scalars['Money']['input'];
  /** Price without VAT */
  priceWithoutVat: Scalars['Money']['input'];
  /** Total value of VAT */
  vatAmount: Scalars['Money']['input'];
};

/** Represents the price */
export type PriceInterface = {
  /** Price with VAT */
  priceWithVat: Scalars['Money']['output'];
  /** Price without VAT */
  priceWithoutVat: Scalars['Money']['output'];
  /** Total value of VAT */
  vatAmount: Scalars['Money']['output'];
};

/** Represents setting of pricing */
export type PricingSetting = {
  __typename?: 'PricingSetting';
  /** Code of the default currency used on the current domain */
  defaultCurrencyCode: Scalars['String']['output'];
  /** Minimum number of decimal places for the price on the current domain */
  minimumFractionDigits: Scalars['Int']['output'];
};

/** Represents a product */
export type Product = {
  accessories: Array<Product>;
  availability: Availability;
  /** Number of the stores where the product is available */
  availableStoresCount: Scalars['Int']['output'];
  /** Brand of product */
  brand: Maybe<Brand>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<Link>;
  /** Product catalog number */
  catalogNumber: Scalars['String']['output'];
  /** List of categories */
  categories: Array<Category>;
  description: Maybe<Scalars['String']['output']>;
  /** EAN */
  ean: Maybe<Scalars['String']['output']>;
  /** List of flags */
  flags: Array<Flag>;
  /** The full name of the product, which consists of a prefix, name, and a suffix */
  fullName: Scalars['String']['output'];
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLink>;
  /** Product id */
  id: Scalars['Int']['output'];
  /** Product images */
  images: Array<Image>;
  isMainVariant: Scalars['Boolean']['output'];
  isSellingDenied: Scalars['Boolean']['output'];
  /** Product link */
  link: Scalars['String']['output'];
  /** Product image by params */
  mainImage: Maybe<Image>;
  /** Localized product name (domain dependent) */
  name: Scalars['String']['output'];
  /** Name prefix */
  namePrefix: Maybe<Scalars['String']['output']>;
  /** Name suffix */
  nameSuffix: Maybe<Scalars['String']['output']>;
  orderingPriority: Scalars['Int']['output'];
  parameters: Array<Parameter>;
  /** Product part number */
  partNumber: Maybe<Scalars['String']['output']>;
  /** Product price */
  price: ProductPrice;
  productVideos: Array<VideoToken>;
  /** List of related products */
  relatedProducts: Array<Product>;
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
  storeAvailabilities: Array<StoreAvailability>;
  unit: Unit;
  /** List of product's unique selling propositions */
  usps: Array<Scalars['String']['output']>;
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};


/** Represents a product */
export type ProductImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a product */
export type ProductMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

/** A connection to a list of items. */
export type ProductConnection = {
  __typename?: 'ProductConnection';
  /** The default ordering mode that is set for the given connection (e.g. in a category, search page, or ready category SEO mix) */
  defaultOrderingMode: Maybe<ProductOrderingModeEnum>;
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<ProductEdge>>>;
  /** The current ordering mode */
  orderingMode: ProductOrderingModeEnum;
  /** Information to aid in pagination. */
  pageInfo: PageInfo;
  productFilterOptions: ProductFilterOptions;
  /** Total number of products */
  totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type ProductEdge = {
  __typename?: 'ProductEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String']['output'];
  /** The item at the end of the edge. */
  node: Maybe<Product>;
};

/** Represents a product filter */
export type ProductFilter = {
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
  parameters: InputMaybe<Array<ParameterFilter>>;
};

/** Represents a product filter options */
export type ProductFilterOptions = {
  __typename?: 'ProductFilterOptions';
  /** Brands filter options */
  brands: Maybe<Array<BrandFilterOption>>;
  /** Flags filter options */
  flags: Maybe<Array<FlagFilterOption>>;
  /** Number of products in stock that will be filtered */
  inStock: Scalars['Int']['output'];
  /** Maximal price of products for filtering */
  maximalPrice: Scalars['Money']['output'];
  /** Minimal price of products for filtering */
  minimalPrice: Scalars['Money']['output'];
  /** Parameter filter options */
  parameters: Maybe<Array<ParameterFilterOptionInterface>>;
};

export type ProductList = {
  __typename?: 'ProductList';
  /** An array of the products in the list */
  products: Array<Product>;
  /** Product list type */
  type: ProductListTypeEnum;
  /** Product list identifier */
  uuid: Scalars['Uuid']['output'];
};

export type ProductListInput = {
  /** Product list type */
  type: ProductListTypeEnum;
  /** Product list identifier */
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};

/** One of possible types of the product list */
export enum ProductListTypeEnum {
  Comparison = 'COMPARISON',
  Wishlist = 'WISHLIST'
}

export type ProductListUpdateInput = {
  productListInput: ProductListInput;
  /** Product identifier */
  productUuid: Scalars['Uuid']['input'];
};

/** Paginated and ordered products */
export type ProductListable = {
  /** Paginated and ordered products */
  products: ProductConnection;
};


/** Paginated and ordered products */
export type ProductListableProductsArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  brandSlug: InputMaybe<Scalars['String']['input']>;
  categorySlug: InputMaybe<Scalars['String']['input']>;
  filter: InputMaybe<ProductFilter>;
  first: InputMaybe<Scalars['Int']['input']>;
  flagSlug: InputMaybe<Scalars['String']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  orderingMode: InputMaybe<ProductOrderingModeEnum>;
};

/** One of possible ordering modes for product */
export enum ProductOrderingModeEnum {
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
export type ProductPrice = PriceInterface & {
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

export type Query = {
  __typename?: 'Query';
  AdvertCode: Maybe<AdvertCode>;
  AdvertImage: Maybe<AdvertImage>;
  ArticleLink: Maybe<ArticleLink>;
  ArticleSite: Maybe<ArticleSite>;
  CompanyCustomerUser: Maybe<CompanyCustomerUser>;
  /** List of available banks for GoPay bank transfer payment */
  GoPaySwifts: Array<GoPayBankSwift>;
  MainVariant: Maybe<MainVariant>;
  ParameterCheckboxFilterOption: Maybe<ParameterCheckboxFilterOption>;
  ParameterColorFilterOption: Maybe<ParameterColorFilterOption>;
  ParameterSliderFilterOption: Maybe<ParameterSliderFilterOption>;
  RegularCustomerUser: Maybe<RegularCustomerUser>;
  RegularProduct: Maybe<RegularProduct>;
  Variant: Maybe<Variant>;
  /** Access personal data using hash received in email from personal data access request */
  accessPersonalData: PersonalData;
  /** Returns list of advert positions. */
  advertPositions: Array<AdvertPosition>;
  /** Returns list of adverts, optionally filtered by `positionName` */
  adverts: Array<Advert>;
  /** Returns article filtered using UUID or URL slug */
  article: Maybe<NotBlogArticleInterface>;
  /** Returns list of articles that can be paginated using `first`, `last`, `before` and `after` keywords and filtered by `placement` */
  articles: ArticleConnection;
  /** Returns list of searched articles and blog articles */
  articlesSearch: Array<ArticleInterface>;
  /** Returns blog article filtered using UUID or URL slug */
  blogArticle: Maybe<BlogArticle>;
  /** Returns a list of the blog articles that can be paginated using `first`, `last`, `before` and `after` keywords */
  blogArticles: BlogArticleConnection;
  /** Returns a complete list of the blog categories */
  blogCategories: Array<BlogCategory>;
  /** Returns blog category filtered using UUID or URL slug */
  blogCategory: Maybe<BlogCategory>;
  /** Returns brand filtered using UUID or URL slug */
  brand: Maybe<Brand>;
  /** Returns list of searched brands */
  brandSearch: Array<Brand>;
  /** Returns complete list of brands */
  brands: Array<Brand>;
  /** Return cart of logged customer or cart by UUID for anonymous user */
  cart: Maybe<Cart>;
  /** Returns complete list of categories */
  categories: Array<Category>;
  /** Returns list of searched categories that can be paginated using `first`, `last`, `before` and `after` keywords */
  categoriesSearch: CategoryConnection;
  /** Returns category filtered using UUID or URL slug */
  category: Maybe<Category>;
  /** Returns information about cookies article */
  cookiesArticle: Maybe<ArticleSite>;
  /** Returns available countries */
  countries: Array<Country>;
  /** Returns currently logged in customer user */
  currentCustomerUser: Maybe<CustomerUser>;
  /** Returns a flag by uuid or url slug */
  flag: Maybe<Flag>;
  /** Returns a complete list of the flags */
  flags: Maybe<Array<Flag>>;
  /** Check if email is registered */
  isCustomerUserRegistered: Scalars['Boolean']['output'];
  /** Return user translated language constants for current domain locale */
  languageConstants: Array<LanguageConstant>;
  /** Returns last order of the user or null if no order was placed yet */
  lastOrder: Maybe<Order>;
  /** Returns complete navigation menu */
  navigation: Array<NavigationItem>;
  /** Returns a list of notifications supposed to be displayed on all pages */
  notificationBars: Maybe<Array<NotificationBar>>;
  /** Returns order filtered using UUID, orderNumber, or urlHash */
  order: Maybe<Order>;
  /** Returns HTML content for order with failed payment. */
  orderPaymentFailedContent: Scalars['String']['output'];
  /** Returns HTML content for order with successful payment. */
  orderPaymentSuccessfulContent: Scalars['String']['output'];
  /** Returns payments available for the given order */
  orderPayments: OrderPaymentsConfig;
  /** Returns HTML content for order sent page. */
  orderSentPageContent: Scalars['String']['output'];
  /** Returns list of orders that can be paginated using `first`, `last`, `before` and `after` keywords */
  orders: Maybe<OrderConnection>;
  /** Returns payment filtered using UUID */
  payment: Maybe<Payment>;
  /** Returns complete list of payment methods */
  payments: Array<Payment>;
  /** Return personal data page content and URL */
  personalDataPage: Maybe<PersonalDataPage>;
  /** Returns privacy policy article */
  privacyPolicyArticle: Maybe<ArticleSite>;
  /** Returns product filtered using UUID or URL slug */
  product: Maybe<Product>;
  /** Find product list by UUID and type or if customer is logged, try find the the oldest list of the given type for the logged customer. The logged customer can also optionally pass the UUID of his product list. */
  productList: Maybe<ProductList>;
  productListsByType: Array<ProductList>;
  /** Returns list of ordered products that can be paginated using `first`, `last`, `before` and `after` keywords */
  products: ProductConnection;
  /** Returns list of products by catalog numbers */
  productsByCatnums: Array<Product>;
  /** Returns list of searched products that can be paginated using `first`, `last`, `before` and `after` keywords */
  productsSearch: ProductConnection;
  /** Returns promoted categories */
  promotedCategories: Array<Category>;
  /** Returns promoted products */
  promotedProducts: Array<Product>;
  /** Returns SEO settings for a specific page based on the url slug of that page */
  seoPage: Maybe<SeoPage>;
  /** Returns current settings */
  settings: Maybe<Settings>;
  /** Returns a complete list of the slider items */
  sliderItems: Array<SliderItem>;
  /** Returns entity by slug */
  slug: Maybe<Slug>;
  /** Returns store filtered using UUID or URL slug */
  store: Maybe<Store>;
  /** Returns list of stores that can be paginated using `first`, `last`, `before` and `after` keywords */
  stores: StoreConnection;
  /** Returns Terms and Conditions article */
  termsAndConditionsArticle: Maybe<ArticleSite>;
  /** Returns complete list of transport methods */
  transport: Maybe<Transport>;
  /** Returns available transport methods based on the current cart state */
  transports: Array<Transport>;
};


export type QueryGoPaySwiftsArgs = {
  currencyCode: Scalars['String']['input'];
};


export type QueryAccessPersonalDataArgs = {
  hash: Scalars['String']['input'];
};


export type QueryAdvertsArgs = {
  categoryUuid: InputMaybe<Scalars['Uuid']['input']>;
  positionName: InputMaybe<Scalars['String']['input']>;
};


export type QueryArticleArgs = {
  urlSlug: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type QueryArticlesArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  first: InputMaybe<Scalars['Int']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  placement?: InputMaybe<Array<ArticlePlacementTypeEnum>>;
};


export type QueryArticlesSearchArgs = {
  searchInput: SearchInput;
};


export type QueryBlogArticleArgs = {
  urlSlug: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type QueryBlogArticlesArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  first: InputMaybe<Scalars['Int']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  onlyHomepageArticles?: InputMaybe<Scalars['Boolean']['input']>;
};


export type QueryBlogCategoryArgs = {
  urlSlug: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type QueryBrandArgs = {
  urlSlug: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type QueryBrandSearchArgs = {
  searchInput: SearchInput;
};


export type QueryCartArgs = {
  cartInput: InputMaybe<CartInput>;
};


export type QueryCategoriesSearchArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  first: InputMaybe<Scalars['Int']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  searchInput: SearchInput;
};


export type QueryCategoryArgs = {
  filter: InputMaybe<ProductFilter>;
  orderingMode: InputMaybe<ProductOrderingModeEnum>;
  urlSlug: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type QueryFlagArgs = {
  urlSlug: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type QueryIsCustomerUserRegisteredArgs = {
  email: Scalars['String']['input'];
};


export type QueryOrderArgs = {
  orderNumber: InputMaybe<Scalars['String']['input']>;
  urlHash: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type QueryOrderPaymentFailedContentArgs = {
  orderUuid: Scalars['Uuid']['input'];
};


export type QueryOrderPaymentSuccessfulContentArgs = {
  orderUuid: Scalars['Uuid']['input'];
};


export type QueryOrderPaymentsArgs = {
  orderUuid: Scalars['Uuid']['input'];
};


export type QueryOrderSentPageContentArgs = {
  orderUuid: Scalars['Uuid']['input'];
};


export type QueryOrdersArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  first: InputMaybe<Scalars['Int']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
};


export type QueryPaymentArgs = {
  uuid: Scalars['Uuid']['input'];
};


export type QueryProductArgs = {
  urlSlug: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type QueryProductListArgs = {
  input: ProductListInput;
};


export type QueryProductListsByTypeArgs = {
  productListType: ProductListTypeEnum;
};


export type QueryProductsArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  brandSlug: InputMaybe<Scalars['String']['input']>;
  categorySlug: InputMaybe<Scalars['String']['input']>;
  filter: InputMaybe<ProductFilter>;
  first: InputMaybe<Scalars['Int']['input']>;
  flagSlug: InputMaybe<Scalars['String']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  orderingMode: InputMaybe<ProductOrderingModeEnum>;
};


export type QueryProductsByCatnumsArgs = {
  catnums: Array<Scalars['String']['input']>;
};


export type QueryProductsSearchArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  filter: InputMaybe<ProductFilter>;
  first: InputMaybe<Scalars['Int']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
  orderingMode: InputMaybe<ProductOrderingModeEnum>;
  search: InputMaybe<Scalars['String']['input']>;
  searchInput: SearchInput;
};


export type QuerySeoPageArgs = {
  pageSlug: Scalars['String']['input'];
};


export type QuerySlugArgs = {
  slug: Scalars['String']['input'];
};


export type QueryStoreArgs = {
  urlSlug: InputMaybe<Scalars['String']['input']>;
  uuid: InputMaybe<Scalars['Uuid']['input']>;
};


export type QueryStoresArgs = {
  after: InputMaybe<Scalars['String']['input']>;
  before: InputMaybe<Scalars['String']['input']>;
  first: InputMaybe<Scalars['Int']['input']>;
  last: InputMaybe<Scalars['Int']['input']>;
};


export type QueryTransportArgs = {
  uuid: Scalars['Uuid']['input'];
};


export type QueryTransportsArgs = {
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type RecoverPasswordInput = {
  /** Customer user email. */
  email: Scalars['String']['input'];
  /** Hash */
  hash: Scalars['String']['input'];
  /** New customer user password. */
  newPassword: Scalars['Password']['input'];
};

export type RefreshTokenInput = {
  /** The refresh token. */
  refreshToken: Scalars['String']['input'];
};

/** Represents the main input object to register customer user */
export type RegistrationDataInput = {
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
export type RegularCustomerUser = CustomerUser & {
  __typename?: 'RegularCustomerUser';
  /** Billing address city name */
  city: Scalars['String']['output'];
  /** Billing address country */
  country: Country;
  /** Default customer delivery addresses */
  defaultDeliveryAddress: Maybe<DeliveryAddress>;
  /** List of delivery addresses */
  deliveryAddresses: Array<DeliveryAddress>;
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
export type RegularProduct = Breadcrumb & Hreflang & Product & Slug & {
  __typename?: 'RegularProduct';
  accessories: Array<Product>;
  availability: Availability;
  /** Number of the stores where the product is available */
  availableStoresCount: Scalars['Int']['output'];
  /** Brand of product */
  brand: Maybe<Brand>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<Link>;
  /** Product catalog number */
  catalogNumber: Scalars['String']['output'];
  /** List of categories */
  categories: Array<Category>;
  description: Maybe<Scalars['String']['output']>;
  /** EAN */
  ean: Maybe<Scalars['String']['output']>;
  /** List of flags */
  flags: Array<Flag>;
  /** The full name of the product, which consists of a prefix, name, and a suffix */
  fullName: Scalars['String']['output'];
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLink>;
  /** Product id */
  id: Scalars['Int']['output'];
  /** Product images */
  images: Array<Image>;
  isMainVariant: Scalars['Boolean']['output'];
  isSellingDenied: Scalars['Boolean']['output'];
  /** Product link */
  link: Scalars['String']['output'];
  /** Product image by params */
  mainImage: Maybe<Image>;
  /** Localized product name (domain dependent) */
  name: Scalars['String']['output'];
  /** Name prefix */
  namePrefix: Maybe<Scalars['String']['output']>;
  /** Name suffix */
  nameSuffix: Maybe<Scalars['String']['output']>;
  orderingPriority: Scalars['Int']['output'];
  parameters: Array<Parameter>;
  /** Product part number */
  partNumber: Maybe<Scalars['String']['output']>;
  /** Product price */
  price: ProductPrice;
  productVideos: Array<VideoToken>;
  /** List of related products */
  relatedProducts: Array<Product>;
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
  storeAvailabilities: Array<StoreAvailability>;
  unit: Unit;
  /** List of product's unique selling propositions */
  usps: Array<Scalars['String']['output']>;
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};


/** Represents a product */
export type RegularProductImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a product */
export type RegularProductMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

export type RemoveFromCartInput = {
  /** Cart item UUID */
  cartItemUuid: Scalars['Uuid']['input'];
  /** Cart identifier, new cart will be created if not provided and customer is not logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
};

export type RemovePromoCodeFromCartInput = {
  /** Cart identifier or null if customer is logged in */
  cartUuid: InputMaybe<Scalars['Uuid']['input']>;
  /** Promo code to be removed */
  promoCode: Scalars['String']['input'];
};

/** Represents search input object */
export type SearchInput = {
  isAutocomplete: Scalars['Boolean']['input'];
  search: Scalars['String']['input'];
  /** Unique identifier of the user who initiated the search in format UUID version 4 (^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[1-8][0-9A-Fa-f]{3}-[ABab89][0-9A-Fa-f]{3}-[0-9A-Fa-f]{12}$/) */
  userIdentifier: Scalars['Uuid']['input'];
};

/** Represents SEO settings for specific page */
export type SeoPage = Hreflang & {
  __typename?: 'SeoPage';
  /** Page's canonical link */
  canonicalUrl: Maybe<Scalars['String']['output']>;
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLink>;
  /** Description for meta tag */
  metaDescription: Maybe<Scalars['String']['output']>;
  /** Description for og:description meta tag */
  ogDescription: Maybe<Scalars['String']['output']>;
  /** Image for og image meta tag by params */
  ogImage: Maybe<Image>;
  /** Title for og:title meta tag */
  ogTitle: Maybe<Scalars['String']['output']>;
  /** Document's title that is shown in a browser's title */
  title: Maybe<Scalars['String']['output']>;
};

/** Represents setting of SEO */
export type SeoSetting = {
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
export type Settings = {
  __typename?: 'Settings';
  /** Main text for contact form */
  contactFormMainText: Scalars['String']['output'];
  /** Timezone that is used for displaying time */
  displayTimezone: Scalars['String']['output'];
  /** Max allowed payment transactions (how many times is user allowed to try the same payment) */
  maxAllowedPaymentTransactions: Scalars['Int']['output'];
  /** Settings related to pricing */
  pricing: PricingSetting;
  /** Settings related to SEO */
  seo: SeoSetting;
};

export type SliderItem = {
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
  images: Array<Image>;
  /** Target link */
  link: Scalars['String']['output'];
  /** Slider item image by params */
  mainImage: Maybe<Image>;
  /** Slider name */
  name: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};


export type SliderItemImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


export type SliderItemMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents entity retrievable by slug */
export type Slug = {
  name: Maybe<Scalars['String']['output']>;
  slug: Scalars['String']['output'];
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};

export type Store = Breadcrumb & Slug & {
  __typename?: 'Store';
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<Link>;
  /** Store address city */
  city: Scalars['String']['output'];
  contactInfo: Maybe<Scalars['String']['output']>;
  /** Store address country */
  country: Country;
  /** Store description */
  description: Maybe<Scalars['String']['output']>;
  /** Store images */
  images: Array<Image>;
  /** Is set as default store */
  isDefault: Scalars['Boolean']['output'];
  /** Store location latitude */
  locationLatitude: Maybe<Scalars['String']['output']>;
  /** Store location longitude */
  locationLongitude: Maybe<Scalars['String']['output']>;
  /** Store name */
  name: Scalars['String']['output'];
  /** Store opening hours */
  openingHours: OpeningHours;
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


export type StoreImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

/** Represents an availability in an individual store */
export type StoreAvailability = {
  __typename?: 'StoreAvailability';
  /** Detailed information about availability */
  availabilityInformation: Scalars['String']['output'];
  /** Availability status in a format suitable for usage in the code */
  availabilityStatus: AvailabilityStatusEnum;
  /** Store */
  store: Maybe<Store>;
};

/** A connection to a list of items. */
export type StoreConnection = {
  __typename?: 'StoreConnection';
  /** Information to aid in pagination. */
  edges: Maybe<Array<Maybe<StoreEdge>>>;
  /** Information to aid in pagination. */
  pageInfo: PageInfo;
  /** Total number of stores */
  totalCount: Scalars['Int']['output'];
};

/** An edge in a connection. */
export type StoreEdge = {
  __typename?: 'StoreEdge';
  /** A cursor for use in pagination. */
  cursor: Scalars['String']['output'];
  /** The item at the end of the edge. */
  node: Maybe<Store>;
};

export type Token = {
  __typename?: 'Token';
  accessToken: Scalars['String']['output'];
  refreshToken: Scalars['String']['output'];
};

/** Represents a transport */
export type Transport = {
  __typename?: 'Transport';
  /** Number of days until goods are delivered */
  daysUntilDelivery: Scalars['Int']['output'];
  /** Localized transport description (domain dependent) */
  description: Maybe<Scalars['String']['output']>;
  /** Transport images */
  images: Array<Image>;
  /** Localized transport instruction (domain dependent) */
  instruction: Maybe<Scalars['String']['output']>;
  /** Pointer telling if the transport is of type personal pickup */
  isPersonalPickup: Scalars['Boolean']['output'];
  /** Transport image by params */
  mainImage: Maybe<Image>;
  /** Transport name */
  name: Scalars['String']['output'];
  /** List of assigned payments */
  payments: Array<Payment>;
  /** Transport position */
  position: Scalars['Int']['output'];
  /** Transport price */
  price: Price;
  /** Stores available for personal pickup */
  stores: Maybe<StoreConnection>;
  /** Type of transport */
  transportType: TransportType;
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};


/** Represents a transport */
export type TransportImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a transport */
export type TransportMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a transport */
export type TransportPriceArgs = {
  cartUuid?: InputMaybe<Scalars['Uuid']['input']>;
};

/** Represents a transport in order */
export type TransportInput = {
  /** Price for transport */
  price: PriceInput;
  /** UUID */
  uuid: Scalars['Uuid']['input'];
};

/** Represents a transport type */
export type TransportType = {
  __typename?: 'TransportType';
  /** Code of transport */
  code: Scalars['String']['output'];
  /** Name of transport type */
  name: Scalars['String']['output'];
};

/** Represents a unit */
export type Unit = {
  __typename?: 'Unit';
  /** Localized unit name (domain dependent) */
  name: Scalars['String']['output'];
};

/** Represents a product */
export type Variant = Breadcrumb & Hreflang & Product & Slug & {
  __typename?: 'Variant';
  accessories: Array<Product>;
  availability: Availability;
  /** Number of the stores where the product is available */
  availableStoresCount: Scalars['Int']['output'];
  /** Brand of product */
  brand: Maybe<Brand>;
  /** Hierarchy of the current element in relation to the structure */
  breadcrumb: Array<Link>;
  /** Product catalog number */
  catalogNumber: Scalars['String']['output'];
  /** List of categories */
  categories: Array<Category>;
  description: Maybe<Scalars['String']['output']>;
  /** EAN */
  ean: Maybe<Scalars['String']['output']>;
  /** List of flags */
  flags: Array<Flag>;
  /** The full name of the product, which consists of a prefix, name, and a suffix */
  fullName: Scalars['String']['output'];
  /** Alternate links for hreflang meta tags */
  hreflangLinks: Array<HreflangLink>;
  /** Product id */
  id: Scalars['Int']['output'];
  /** Product images */
  images: Array<Image>;
  isMainVariant: Scalars['Boolean']['output'];
  isSellingDenied: Scalars['Boolean']['output'];
  /** Product link */
  link: Scalars['String']['output'];
  /** Product image by params */
  mainImage: Maybe<Image>;
  mainVariant: Maybe<MainVariant>;
  /** Localized product name (domain dependent) */
  name: Scalars['String']['output'];
  /** Name prefix */
  namePrefix: Maybe<Scalars['String']['output']>;
  /** Name suffix */
  nameSuffix: Maybe<Scalars['String']['output']>;
  orderingPriority: Scalars['Int']['output'];
  parameters: Array<Parameter>;
  /** Product part number */
  partNumber: Maybe<Scalars['String']['output']>;
  /** Product price */
  price: ProductPrice;
  productVideos: Array<VideoToken>;
  /** List of related products */
  relatedProducts: Array<Product>;
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
  storeAvailabilities: Array<StoreAvailability>;
  unit: Unit;
  /** List of product's unique selling propositions */
  usps: Array<Scalars['String']['output']>;
  /** UUID */
  uuid: Scalars['Uuid']['output'];
};


/** Represents a product */
export type VariantImagesArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};


/** Represents a product */
export type VariantMainImageArgs = {
  type?: InputMaybe<Scalars['String']['input']>;
};

export type VideoToken = {
  __typename?: 'VideoToken';
  description: Scalars['String']['output'];
  token: Scalars['String']['output'];
};
