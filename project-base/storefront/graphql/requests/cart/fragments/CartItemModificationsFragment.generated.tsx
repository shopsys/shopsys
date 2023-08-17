import * as Types from '../../types';

import gql from 'graphql-tag';
import { CartItemFragmentApi } from './CartItemFragment.generated';
export type CartItemModificationsFragmentApi = {
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
                  availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
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
                  availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
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
                  availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
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
                  availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
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
                  availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
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
                  availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
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
                  availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
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
                  availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
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
                  availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
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
                  availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
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
                  availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
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
                  availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
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
    ${CartItemFragmentApi}
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
