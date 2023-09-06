# GTM Objects

These objects are used all across the GTM module for representation of various objects mapped according to what the data layer requires.

## GtmReviewConsentsType

Object representing user consent for tracking of different third-party services. Contains boolean properties seznam, google, and heureka to indicate if the user has consented to tracking by these services.

```ts
export type GtmReviewConsentsType = {
  seznam: boolean; // boolean pointer saying if the user has consented to Seznam tracking
  google: boolean; // boolean pointer saying if the user has consented to Google tracking
  heureka: boolean; // boolean pointer saying if the user has consented to Heureka tracking
};
```

## GtmPageInfoInterface

Interface representing basic information about a web page.

```ts
export type GtmPageInfoInterface<
  PageType = GtmPageType,
  ExtendedPageProperties = object
> = ExtendedPageProperties & {
  type: PageType; // type of the page, can be GtmPageType.category_detail or GtmPageType.seo_category_detail, GtmPageType.blog_article_detail or GtmPageType.brand_detail
  pageId: string; // unique identifier of the page
  breadcrumbs: BreadcrumbFragmentApi[]; // breadcrumbs to the current page
};
```

## GtmCategoryDetailPageInfoType

Object representing additional information about a web page that displays a category or subcategory. Extends the GtmPageInfoInterface.

```ts
export type GtmCategoryDetailPageInfoType = GtmPageInfoInterface<
  GtmPageType.category_detail | GtmPageType.seo_category_detail, // type of the page, can be either GtmPageType.category_detail or GtmPageType.seo_category_detail
  {
    category: string[]; // array of strings representing the category hierarchy of the page
    categoryId: number[]; // array of category IDs representing the category hierarchy of the page
  }
>;
```

## GtmBlogArticleDetailPageInfoType

Object representing additional information about a web page that displays a blog article. Extends the GtmPageInfoInterface.

```ts
export type GtmBlogArticleDetailPageInfoType = GtmPageInfoInterface<
  GtmPageType.blog_article_detail, // type of the page, should always be GtmPageType.blog_article_detail
  {
    articleId: number; // unique identifier of the blog article
  }
>;
```

## GtmBrandDetailPageInfoType

Object representing additional information about a web page that displays a brand or manufacturer. Extends the GtmPageInfoInterface.

```ts
export type GtmBrandDetailPageInfoType = GtmPageInfoInterface<
  GtmPageType.brand_detail, // type of the page, should always be GtmPageType.brand_detail
  {
    brandId: number; // unique identifier of the brand
  }
>;
```

## GtmPageInfoType

Union type representing all possible types of web pages. Can be either GtmCategoryDetailPageInfoType, GtmBlogArticleDetailPageInfoType, GtmBrandDetailPageInfoType, or GtmPageInfoInterface.

```ts
export type GtmPageInfoType =
  | GtmCategoryDetailPageInfoType // page information for category detail or SEO category detail pages
  | GtmBlogArticleDetailPageInfoType // page information for blog article detail pages
  | GtmBrandDetailPageInfoType // page information for brand detail pages
  | GtmPageInfoInterface; // basic page information without additional properties
```

## GtmCartInfoType

Object representing information about a user's cart.

```ts
export type GtmCartInfoType = {
  abandonedCartUrl: string | undefined; // URL of the cart which can be used for recovery of an abandoned cart, optional
  currencyCode: string; // the code of the currency used on the domain
  valueWithoutVat: number; // total value of the cart without VAT
  valueWithVat: number; // total value of the cart with VAT
  products: GtmCartItemType[] | undefined; // array of products in the cart, if available
  promoCodes?: string[]; // array of promo codes applied to the cart, optional
};
```

## GtmUserInfoType

Object representing information about a user.

```ts
export type GtmUserInfoType = {
  id?: string; // ID of the user, optional
  email?: string; // email of the user, optional
  emailHash?: string; // SHA256 hashed email of the user, optional
  firstName?: string; // first name of the user, optional
  lastName?: string; // last name of the user, optional
  telephone?: string; // telephone number of the user, optional
  street?: string; // street address of the user, optional
  city?: string; // city of the user, optional
  postcode?: string; // postal code of the user, optional
  country?: string; // country of the user, optional
  type?: GtmUserType; // type of the user (e.g. B2B or B2C), optional
  status: GtmUserStatus; // status of the user (e.g. visitor or customer)
  group?: string; // group of the user, optional
};
```

## GtmConsentInfoType

Object representing a user's consent for tracking in different categories.

```ts
export type GtmConsentInfoType = {
  statistics: GtmConsent; // user consent status for statistics tracking
  marketing: GtmConsent; // user consent status for marketing tracking
  preferences: GtmConsent; // user consent status for preference tracking
};
```

## GtmProductInterface

Interface representing information about a product. Is extended for specific cases of products, such as listed products or cart items.

```ts
export type GtmProductInterface = {
  id: number; // product ID
  name: string; // product name
  availability: string; // product availability status
  flags: string[]; // array of product flags
  priceWithoutVat: number; // product price without VAT
  priceWithVat: number; // product price with VAT
  vatAmount: number; // VAT amount
  sku: string; // product catalog number
  url: string; // product URL
  brand: string; // product brand name
  categories: string[]; // array of product categories
  imageUrl?: string; // optional product image URL
};
```

## GtmListedProductType

Type that extends GtmProductInterface and adds an optional listIndex property, which represents the index of the product in a list (such as search results).

```ts
export type GtmListedProductType = GtmProductInterface & {
  listIndex?: number; // index of the product in a list (e.g., search results)
};
```

## GtmCartItemType

Type that extends GtmListedProductType and adds a required quantity property, which represents the quantity of the product in the cart. GtmListedProductType, as described above, extends GtmProductInterface and adds an optional listIndex property. Therefore, GtmCartItemType includes all properties defined in GtmProductInterface, GtmListedProductType, and adds the quantity property.

```ts
export type GtmCartItemType = GtmListedProductType & {
  quantity: number; // product quantity in the cart
};
```

## GtmShippingInfoType

Type that represents extra transport details.

```ts
export type GtmShippingInfoType = {
  transportDetail: string; // transport method details
  transportExtra: string[]; // array of extra transport details
};
```
