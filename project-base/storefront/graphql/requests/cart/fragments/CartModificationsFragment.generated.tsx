import * as Types from '../../types';

import gql from 'graphql-tag';
import { CartItemModificationsFragmentApi } from './CartItemModificationsFragment.generated';
import { CartTransportModificationsFragmentApi } from './CartTransportModificationsFragment.generated';
import { CartPaymentModificationsFragmentApi } from './CartPaymentModificationsFragment.generated';
import { CartPromoCodeModificationsFragmentApi } from './CartPromoCodeModificationsFragment.generated';
export type CartModificationsFragmentApi = {
    __typename: 'CartModificationsResult';
    someProductWasRemovedFromEshop: boolean;
    itemModifications: {
        __typename: 'CartItemModificationsResult';
        noLongerListableCartItems: Array<{
            __typename: 'CartItem';
            uuid: string;
            quantity: number;
            product:
                | {
                      __typename: 'MainVariant';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      catalogNumber: string;
                      stockQuantity: number;
                      availableStoresCount: number;
                      flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                      mainImage: {
                          __typename: 'Image';
                          name: string | null;
                          sizes: Array<{
                              __typename: 'ImageSize';
                              size: string;
                              url: string;
                              width: number | null;
                              height: number | null;
                              additionalSizes: Array<{
                                  __typename: 'AdditionalSize';
                                  height: number | null;
                                  media: string;
                                  url: string;
                                  width: number | null;
                              }>;
                          }>;
                      } | null;
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      unit: { __typename?: 'Unit'; name: string };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename?: 'Category'; name: string }>;
                  }
                | {
                      __typename: 'RegularProduct';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      catalogNumber: string;
                      stockQuantity: number;
                      availableStoresCount: number;
                      flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                      mainImage: {
                          __typename: 'Image';
                          name: string | null;
                          sizes: Array<{
                              __typename: 'ImageSize';
                              size: string;
                              url: string;
                              width: number | null;
                              height: number | null;
                              additionalSizes: Array<{
                                  __typename: 'AdditionalSize';
                                  height: number | null;
                                  media: string;
                                  url: string;
                                  width: number | null;
                              }>;
                          }>;
                      } | null;
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      unit: { __typename?: 'Unit'; name: string };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename?: 'Category'; name: string }>;
                  }
                | {
                      __typename: 'Variant';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      catalogNumber: string;
                      stockQuantity: number;
                      availableStoresCount: number;
                      flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                      mainImage: {
                          __typename: 'Image';
                          name: string | null;
                          sizes: Array<{
                              __typename: 'ImageSize';
                              size: string;
                              url: string;
                              width: number | null;
                              height: number | null;
                              additionalSizes: Array<{
                                  __typename: 'AdditionalSize';
                                  height: number | null;
                                  media: string;
                                  url: string;
                                  width: number | null;
                              }>;
                          }>;
                      } | null;
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      unit: { __typename?: 'Unit'; name: string };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename?: 'Category'; name: string }>;
                  };
        }>;
        cartItemsWithModifiedPrice: Array<{
            __typename: 'CartItem';
            uuid: string;
            quantity: number;
            product:
                | {
                      __typename: 'MainVariant';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      catalogNumber: string;
                      stockQuantity: number;
                      availableStoresCount: number;
                      flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                      mainImage: {
                          __typename: 'Image';
                          name: string | null;
                          sizes: Array<{
                              __typename: 'ImageSize';
                              size: string;
                              url: string;
                              width: number | null;
                              height: number | null;
                              additionalSizes: Array<{
                                  __typename: 'AdditionalSize';
                                  height: number | null;
                                  media: string;
                                  url: string;
                                  width: number | null;
                              }>;
                          }>;
                      } | null;
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      unit: { __typename?: 'Unit'; name: string };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename?: 'Category'; name: string }>;
                  }
                | {
                      __typename: 'RegularProduct';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      catalogNumber: string;
                      stockQuantity: number;
                      availableStoresCount: number;
                      flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                      mainImage: {
                          __typename: 'Image';
                          name: string | null;
                          sizes: Array<{
                              __typename: 'ImageSize';
                              size: string;
                              url: string;
                              width: number | null;
                              height: number | null;
                              additionalSizes: Array<{
                                  __typename: 'AdditionalSize';
                                  height: number | null;
                                  media: string;
                                  url: string;
                                  width: number | null;
                              }>;
                          }>;
                      } | null;
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      unit: { __typename?: 'Unit'; name: string };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename?: 'Category'; name: string }>;
                  }
                | {
                      __typename: 'Variant';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      catalogNumber: string;
                      stockQuantity: number;
                      availableStoresCount: number;
                      flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                      mainImage: {
                          __typename: 'Image';
                          name: string | null;
                          sizes: Array<{
                              __typename: 'ImageSize';
                              size: string;
                              url: string;
                              width: number | null;
                              height: number | null;
                              additionalSizes: Array<{
                                  __typename: 'AdditionalSize';
                                  height: number | null;
                                  media: string;
                                  url: string;
                                  width: number | null;
                              }>;
                          }>;
                      } | null;
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      unit: { __typename?: 'Unit'; name: string };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename?: 'Category'; name: string }>;
                  };
        }>;
        cartItemsWithChangedQuantity: Array<{
            __typename: 'CartItem';
            uuid: string;
            quantity: number;
            product:
                | {
                      __typename: 'MainVariant';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      catalogNumber: string;
                      stockQuantity: number;
                      availableStoresCount: number;
                      flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                      mainImage: {
                          __typename: 'Image';
                          name: string | null;
                          sizes: Array<{
                              __typename: 'ImageSize';
                              size: string;
                              url: string;
                              width: number | null;
                              height: number | null;
                              additionalSizes: Array<{
                                  __typename: 'AdditionalSize';
                                  height: number | null;
                                  media: string;
                                  url: string;
                                  width: number | null;
                              }>;
                          }>;
                      } | null;
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      unit: { __typename?: 'Unit'; name: string };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename?: 'Category'; name: string }>;
                  }
                | {
                      __typename: 'RegularProduct';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      catalogNumber: string;
                      stockQuantity: number;
                      availableStoresCount: number;
                      flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                      mainImage: {
                          __typename: 'Image';
                          name: string | null;
                          sizes: Array<{
                              __typename: 'ImageSize';
                              size: string;
                              url: string;
                              width: number | null;
                              height: number | null;
                              additionalSizes: Array<{
                                  __typename: 'AdditionalSize';
                                  height: number | null;
                                  media: string;
                                  url: string;
                                  width: number | null;
                              }>;
                          }>;
                      } | null;
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      unit: { __typename?: 'Unit'; name: string };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename?: 'Category'; name: string }>;
                  }
                | {
                      __typename: 'Variant';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      catalogNumber: string;
                      stockQuantity: number;
                      availableStoresCount: number;
                      flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                      mainImage: {
                          __typename: 'Image';
                          name: string | null;
                          sizes: Array<{
                              __typename: 'ImageSize';
                              size: string;
                              url: string;
                              width: number | null;
                              height: number | null;
                              additionalSizes: Array<{
                                  __typename: 'AdditionalSize';
                                  height: number | null;
                                  media: string;
                                  url: string;
                                  width: number | null;
                              }>;
                          }>;
                      } | null;
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      unit: { __typename?: 'Unit'; name: string };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename?: 'Category'; name: string }>;
                  };
        }>;
        noLongerAvailableCartItemsDueToQuantity: Array<{
            __typename: 'CartItem';
            uuid: string;
            quantity: number;
            product:
                | {
                      __typename: 'MainVariant';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      catalogNumber: string;
                      stockQuantity: number;
                      availableStoresCount: number;
                      flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                      mainImage: {
                          __typename: 'Image';
                          name: string | null;
                          sizes: Array<{
                              __typename: 'ImageSize';
                              size: string;
                              url: string;
                              width: number | null;
                              height: number | null;
                              additionalSizes: Array<{
                                  __typename: 'AdditionalSize';
                                  height: number | null;
                                  media: string;
                                  url: string;
                                  width: number | null;
                              }>;
                          }>;
                      } | null;
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      unit: { __typename?: 'Unit'; name: string };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename?: 'Category'; name: string }>;
                  }
                | {
                      __typename: 'RegularProduct';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      catalogNumber: string;
                      stockQuantity: number;
                      availableStoresCount: number;
                      flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                      mainImage: {
                          __typename: 'Image';
                          name: string | null;
                          sizes: Array<{
                              __typename: 'ImageSize';
                              size: string;
                              url: string;
                              width: number | null;
                              height: number | null;
                              additionalSizes: Array<{
                                  __typename: 'AdditionalSize';
                                  height: number | null;
                                  media: string;
                                  url: string;
                                  width: number | null;
                              }>;
                          }>;
                      } | null;
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      unit: { __typename?: 'Unit'; name: string };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename?: 'Category'; name: string }>;
                  }
                | {
                      __typename: 'Variant';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      catalogNumber: string;
                      stockQuantity: number;
                      availableStoresCount: number;
                      flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                      mainImage: {
                          __typename: 'Image';
                          name: string | null;
                          sizes: Array<{
                              __typename: 'ImageSize';
                              size: string;
                              url: string;
                              width: number | null;
                              height: number | null;
                              additionalSizes: Array<{
                                  __typename: 'AdditionalSize';
                                  height: number | null;
                                  media: string;
                                  url: string;
                                  width: number | null;
                              }>;
                          }>;
                      } | null;
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      unit: { __typename?: 'Unit'; name: string };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename?: 'Category'; name: string }>;
                  };
        }>;
    };
    transportModifications: {
        __typename: 'CartTransportModificationsResult';
        transportPriceChanged: boolean;
        transportUnavailable: boolean;
        transportWeightLimitExceeded: boolean;
        personalPickupStoreUnavailable: boolean;
    };
    paymentModifications: {
        __typename: 'CartPaymentModificationsResult';
        paymentPriceChanged: boolean;
        paymentUnavailable: boolean;
    };
    promoCodeModifications: {
        __typename: 'CartPromoCodeModificationsResult';
        noLongerApplicablePromoCode: Array<string>;
    };
};

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
    ${CartPromoCodeModificationsFragmentApi}
`;

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
