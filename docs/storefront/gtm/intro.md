# GTM Core Functions and Helpers

These functions and helpers are responsible for some of the main logic in GTM. It is in these functions where things like:

- cart mapping
- user information mapping
- page information mapping
  are happening.

It is very likely that if your project requires customization of the logic, this is the file where the modifications are going to take place. Below, you can see descriptions of almost all functions. Only self-descriptive and primitive functions are omitted.

## useGtmCartInfo

The hook allows the application to work with cart information mapped according to the GTM requirements. It is a hook wrapper of the underlying `getGtmMappedCart` function, which takes care of the mapping itself. The hook handles the default state where no cart is available.

```ts
export const useGtmCartInfo = (): {
  gtmCartInfo: GtmCartInfoType | null;
  isCartLoaded: boolean;
} => {
  // code omitted for simplification

  return useMemo(
    () => {
      if ((cartUuid === null && !isUserLoggedIn) || cart === null) {
        return { gtmCartInfo: null, isCartLoaded: !isFetching };
      }

      return {
        gtmCartInfo: getGtmMappedCart(
          cart,
          promoCode,
          isUserLoggedIn,
          domain,
          cartUuid
        ),
        isCartLoaded: !isFetching
      };
    },
    [
      // code omitted for simplification
    ]
  );
};
```

## getGtmMappedCart

The function used to map the cart information to be suitable for GTM. It uses other helpers to get different parts of the mapped object.

```ts
export const getGtmMappedCart = (
  cart: CartFragmentApi,
  promoCode: string | null,
  isUserLoggedIn: boolean,
  domain: DomainConfigType,
  cartUuid: string | null
): GtmCartInfoType => {
  // function body omitted out for simplification
};
```

## getAbandonedCartUrl

The function is used to generate an abandoned cart URL.

```ts
const getAbandonedCartUrl = (
  isUserLoggedIn: boolean,
  domain: DomainConfigType,
  cartUuid: string | null
) => {
  if (isUserLoggedIn) {
    const [loginRelativeUrl, cartRelativeUrl] = getInternationalizedStaticUrls(
      ['/login', '/cart'],
      domain.url
    );

    return (
      domain.url +
      getStringWithoutLeadingSlash(loginRelativeUrl) +
      '?r=' +
      cartRelativeUrl
    );
  }

  const [abandonedCartRelativeUrl] = getInternationalizedStaticUrls(
    [{ url: '/abandoned-cart/:cartUuid', param: cartUuid }],
    domain.url
  );

  return domain.url + getStringWithoutLeadingSlash(abandonedCartRelativeUrl);
};
```

## getGtmPageInfoTypeForFriendlyUrl

Function used to create page info objects for various entities, which can be displayed on the friendly URL page. It handles special cases, such as category detail, blog article detail, brand detail, and so on. If, for some reason, page type cannot be determined, it returns a default page info object.

```ts
export const getGtmPageInfoTypeForFriendlyUrl = (
  friendlyUrlPageData: FriendlyUrlPageType | null | undefined
): GtmPageInfoType => {
  let pageInfo = getGtmPageInfoType(
    GtmPageType.not_found,
    friendlyUrlPageData?.breadcrumb
  );

  if (friendlyUrlPageData === undefined) {
    return pageInfo;
  }

  switch (
    friendlyUrlPageData?.__typename
    // code omitted for simplification
  ) {
  }

  return pageInfo;
};
```

## getPageInfoForCategoryDetailPage, getPageInfoForBlogArticleDetailPage, getPageInfoForBrandDetailPage

Helper functions are used to generate specific properties for the friendly URL page if the displayed entity is of type:

- category
- blog article
- brand

```ts
const getPageInfoForCategoryDetailPage = (
  defaultPageInfo: GtmPageInfoInterface,
  categoryDetailData: CategoryDetailFragmentApi
): GtmCategoryDetailPageInfoType => ({
  // function body omitted for simplification
});

const getPageInfoForBlogArticleDetailPage = (
  defaultPageInfo: GtmPageInfoType,
  blogArticleDetailData: BlogArticleDetailFragmentApi
): GtmBlogArticleDetailPageInfoType => ({
  // function body omitted for simplification
});

const getPageInfoForBrandDetailPage = (
  defaultPageInfo: GtmPageInfoType,
  brandDetailData: BrandDetailFragmentApi
): GtmBrandDetailPageInfoType => ({
  // function body omitted for simplification
});
```

## gtmSafePushEvent

The essential function is used to push all events to the data layer.

```ts
export const gtmSafePushEvent = (
  event: GtmEventInterface<GtmEventType, unknown>
): void => {
  if (canUseDom()) {
    window.dataLayer = window.dataLayer ?? [];
    window.dataLayer.push(event);
  }
};
```

## getGtmUserInfo

The basic function is used to get the user information in a GTM-suitable format. It dispatches the initial creation based on the contact information from the order and handles overwriting of this information with the credentials of an authenticated customer.

```ts
export const getGtmUserInfo = (
  currentSignedInCustomer: CurrentCustomerType | null | undefined,
  userContactInformation: ContactInformation
): GtmUserInfoType => {
  const userInfo: GtmUserInfoType = getGtmUserInfoForVisitor(
    userContactInformation
  );

  if (currentSignedInCustomer) {
    overwriteGtmUserInfoWithLoggedCustomer(
      userInfo,
      currentSignedInCustomer,
      userContactInformation
    );
  }

  return userInfo;
};
```

## getGtmUserInfoForVisitor

It gets the basic information about the user. Because of the fact that untouched fields from the contact information form are not stored as null or undefined, but as empty string, it must check for the length of the string and only include the datapoint if it is really filled.

```ts
const getGtmUserInfoForVisitor = (
  userContactInformation: ContactInformation
) => ({
  status: GtmUserStatus.visitor,
  ...(userContactInformation.city.length > 0 && {
    city: userContactInformation.city
  }),
  // code omitted for simplification
  type: getGtmUserType(userContactInformation.customer)
});
```

## getGtmUserType

Method used to help differentiate between B2B and B2C customers. Prepared for further extension of the logic.

```ts
const getGtmUserType = (
  customerType: CustomerTypeEnum | undefined
): GtmUserType | undefined => {
  if (customerType === undefined) {
    return undefined;
  }

  if (customerType === CustomerTypeEnum.CompanyCustomer) {
    return GtmUserType.b2b;
  }

  return GtmUserType.b2c;
};
```

## overwriteGtmUserInfoWithLoggedCustomer

Method used to overwrite default information about the customer with the information from his account. Here, the logic is as follows:

- `status`, `id`, and `group` are always overwritten
- other properties are only overwritten if they haven't been filled before
- `type` is filled in based on the previous value of the field and on the value of the currently authenticated customer

```ts
const overwriteGtmUserInfoWithLoggedCustomer = (
  userInfo: GtmUserInfoType,
  currentSignedInCustomer: CurrentCustomerType,
  userContactInformation: ContactInformation
) => {
  userInfo.status = GtmUserStatus.customer;
  userInfo.id = currentSignedInCustomer.uuid;
  userInfo.group = currentSignedInCustomer.pricingGroup;

  if (userInfo.street === undefined || userInfo.street.length === 0) {
    userInfo.street = currentSignedInCustomer.street;
  }
  // code omitted for simplification

  if (userInfo.type !== undefined) {
    return;
  }

  if (currentSignedInCustomer.companyCustomer) {
    userInfo.type = GtmUserType.b2b;
  } else {
    userInfo.type = GtmUserType.b2c;
  }
};
```
