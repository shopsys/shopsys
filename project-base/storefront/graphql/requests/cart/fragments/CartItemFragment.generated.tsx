import * as Types from '../../types';

import gql from 'graphql-tag';
import { SimpleFlagFragmentApi } from '../../flags/fragments/SimpleFlagFragment.generated';
import { ImageSizesFragmentApi } from '../../images/fragments/ImageSizesFragment.generated';
import { AvailabilityFragmentApi } from '../../availabilities/fragments/AvailabilityFragment.generated';
import { ProductPriceFragmentApi } from '../../products/fragments/ProductPriceFragment.generated';
import { SimpleBrandFragmentApi } from '../../brands/fragments/SimpleBrandFragment.generated';
export type CartItemFragmentApi = {
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
};

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
    ${SimpleBrandFragmentApi}
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
