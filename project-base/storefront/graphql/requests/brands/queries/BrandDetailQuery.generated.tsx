import * as Types from '../../types';

import gql from 'graphql-tag';
import { BrandDetailFragmentApi } from '../fragments/BrandDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type BrandDetailQueryVariablesApi = Types.Exact<{
    urlSlug: Types.InputMaybe<Types.Scalars['String']['input']>;
    orderingMode: Types.InputMaybe<Types.ProductOrderingModeEnumApi>;
    filter: Types.InputMaybe<Types.ProductFilterApi>;
}>;

export type BrandDetailQueryApi = {
    __typename?: 'Query';
    brand: {
        __typename: 'Brand';
        id: number;
        uuid: string;
        slug: string;
        name: string;
        seoH1: string | null;
        description: string | null;
        seoTitle: string | null;
        seoMetaDescription: string | null;
        breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
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
        products: {
            __typename: 'ProductConnection';
            orderingMode: Types.ProductOrderingModeEnumApi;
            defaultOrderingMode: Types.ProductOrderingModeEnumApi | null;
            totalCount: number;
            productFilterOptions: {
                __typename: 'ProductFilterOptions';
                minimalPrice: string;
                maximalPrice: string;
                inStock: number;
                brands: Array<{
                    __typename: 'BrandFilterOption';
                    count: number;
                    brand: { __typename: 'Brand'; uuid: string; name: string };
                }> | null;
                flags: Array<{
                    __typename: 'FlagFilterOption';
                    count: number;
                    isSelected: boolean;
                    flag: { __typename: 'Flag'; uuid: string; name: string; rgbColor: string };
                }> | null;
                parameters: Array<
                    | {
                          __typename: 'ParameterCheckboxFilterOption';
                          name: string;
                          uuid: string;
                          isCollapsed: boolean;
                          values: Array<{
                              __typename: 'ParameterValueFilterOption';
                              uuid: string;
                              text: string;
                              count: number;
                              isSelected: boolean;
                          }>;
                      }
                    | {
                          __typename: 'ParameterColorFilterOption';
                          name: string;
                          uuid: string;
                          isCollapsed: boolean;
                          values: Array<{
                              __typename: 'ParameterValueColorFilterOption';
                              uuid: string;
                              text: string;
                              count: number;
                              rgbHex: string | null;
                              isSelected: boolean;
                          }>;
                      }
                    | {
                          __typename: 'ParameterSliderFilterOption';
                          name: string;
                          uuid: string;
                          minimalValue: number;
                          maximalValue: number;
                          isCollapsed: boolean;
                          selectedValue: number | null;
                          isSelectable: boolean;
                          unit: { __typename: 'Unit'; name: string } | null;
                      }
                > | null;
            };
        };
    } | null;
};

export const BrandDetailQueryDocumentApi = gql`
    query BrandDetailQuery($urlSlug: String, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter) {
        brand(urlSlug: $urlSlug) {
            ...BrandDetailFragment
        }
    }
    ${BrandDetailFragmentApi}
`;

export function useBrandDetailQueryApi(options?: Omit<Urql.UseQueryArgs<BrandDetailQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<BrandDetailQueryApi, BrandDetailQueryVariablesApi>({
        query: BrandDetailQueryDocumentApi,
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
