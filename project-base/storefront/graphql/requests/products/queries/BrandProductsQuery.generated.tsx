import * as Types from '../../types';

import gql from 'graphql-tag';
import { ListedProductConnectionFragmentApi } from '../fragments/ListedProductConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type BrandProductsQueryVariablesApi = Types.Exact<{
    endCursor: Types.Scalars['String']['input'];
    orderingMode: Types.InputMaybe<Types.ProductOrderingModeEnumApi>;
    filter: Types.InputMaybe<Types.ProductFilterApi>;
    urlSlug: Types.InputMaybe<Types.Scalars['String']['input']>;
    pageSize: Types.InputMaybe<Types.Scalars['Int']['input']>;
}>;

export type BrandProductsQueryApi = {
    __typename?: 'Query';
    products: {
        __typename: 'ProductConnection';
        pageInfo: { __typename?: 'PageInfo'; hasNextPage: boolean };
        edges: Array<{
            __typename: 'ProductEdge';
            node:
                | {
                      __typename: 'MainVariant';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      name: string;
                      stockQuantity: number;
                      isSellingDenied: boolean;
                      availableStoresCount: number;
                      exposedStoresCount: number;
                      catalogNumber: string;
                      isMainVariant: boolean;
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
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename: 'Category'; name: string }>;
                  }
                | {
                      __typename: 'RegularProduct';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      name: string;
                      stockQuantity: number;
                      isSellingDenied: boolean;
                      availableStoresCount: number;
                      exposedStoresCount: number;
                      catalogNumber: string;
                      isMainVariant: boolean;
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
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename: 'Category'; name: string }>;
                  }
                | {
                      __typename: 'Variant';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      name: string;
                      stockQuantity: number;
                      isSellingDenied: boolean;
                      availableStoresCount: number;
                      exposedStoresCount: number;
                      catalogNumber: string;
                      isMainVariant: boolean;
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
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename: 'Category'; name: string }>;
                  }
                | null;
        } | null> | null;
    };
};

export const BrandProductsQueryDocumentApi = gql`
    query BrandProductsQuery(
        $endCursor: String!
        $orderingMode: ProductOrderingModeEnum
        $filter: ProductFilter
        $urlSlug: String
        $pageSize: Int
    ) {
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
    ${ListedProductConnectionFragmentApi}
`;

export function useBrandProductsQueryApi(options: Omit<Urql.UseQueryArgs<BrandProductsQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<BrandProductsQueryApi, BrandProductsQueryVariablesApi>({
        query: BrandProductsQueryDocumentApi,
        ...options,
    });
}

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
