import * as Types from '../../types';

import gql from 'graphql-tag';
import { ListedCategoryFragmentApi } from '../fragments/ListedCategoryFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type PromotedCategoriesQueryVariablesApi = Types.Exact<{ [key: string]: never }>;

export type PromotedCategoriesQueryApi = {
    __typename?: 'Query';
    promotedCategories: Array<{
        __typename: 'Category';
        uuid: string;
        name: string;
        slug: string;
        products: { __typename: 'ProductConnection'; totalCount: number };
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
    }>;
};

export const PromotedCategoriesQueryDocumentApi = gql`
    query PromotedCategoriesQuery {
        promotedCategories {
            ...ListedCategoryFragment
        }
    }
    ${ListedCategoryFragmentApi}
`;

export function usePromotedCategoriesQueryApi(
    options?: Omit<Urql.UseQueryArgs<PromotedCategoriesQueryVariablesApi>, 'query'>,
) {
    return Urql.useQuery<PromotedCategoriesQueryApi, PromotedCategoriesQueryVariablesApi>({
        query: PromotedCategoriesQueryDocumentApi,
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
