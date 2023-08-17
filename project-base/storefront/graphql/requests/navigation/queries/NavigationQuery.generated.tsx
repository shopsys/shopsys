import * as Types from '../../types';

import gql from 'graphql-tag';
import { CategoriesByColumnFragmentApi } from '../fragments/CategoriesByColumnsFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type NavigationQueryVariablesApi = Types.Exact<{ [key: string]: never }>;

export type NavigationQueryApi = {
    __typename?: 'Query';
    navigation: Array<{
        __typename: 'NavigationItem';
        name: string;
        link: string;
        categoriesByColumns: Array<{
            __typename: 'NavigationItemCategoriesByColumns';
            columnNumber: number;
            categories: Array<{
                __typename: 'Category';
                uuid: string;
                name: string;
                slug: string;
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
                children: Array<{ __typename: 'Category'; name: string; slug: string }>;
            }>;
        }>;
    }>;
};

export const NavigationQueryDocumentApi = gql`
    query NavigationQuery @_redisCache(ttl: 3600) {
        navigation {
            ...CategoriesByColumnFragment
        }
    }
    ${CategoriesByColumnFragmentApi}
`;

export function useNavigationQueryApi(options?: Omit<Urql.UseQueryArgs<NavigationQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<NavigationQueryApi, NavigationQueryVariablesApi>({
        query: NavigationQueryDocumentApi,
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
