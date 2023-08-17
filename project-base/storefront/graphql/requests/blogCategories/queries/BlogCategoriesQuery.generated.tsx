import * as Types from '../../types';

import gql from 'graphql-tag';
import { BlogCategoriesFragmentApi } from '../fragments/BlogCategoriesFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type BlogCategoriesVariablesApi = Types.Exact<{ [key: string]: never }>;

export type BlogCategoriesApi = {
    __typename?: 'Query';
    blogCategories: Array<{
        __typename: 'BlogCategory';
        uuid: string;
        name: string;
        link: string;
        children: Array<{
            __typename: 'BlogCategory';
            uuid: string;
            name: string;
            link: string;
            children: Array<{
                __typename: 'BlogCategory';
                uuid: string;
                name: string;
                link: string;
                children: Array<{
                    __typename: 'BlogCategory';
                    uuid: string;
                    name: string;
                    link: string;
                    children: Array<{
                        __typename: 'BlogCategory';
                        uuid: string;
                        name: string;
                        link: string;
                        parent: { __typename?: 'BlogCategory'; name: string } | null;
                    }>;
                    parent: { __typename?: 'BlogCategory'; name: string } | null;
                }>;
                parent: { __typename?: 'BlogCategory'; name: string } | null;
            }>;
            parent: { __typename?: 'BlogCategory'; name: string } | null;
        }>;
        parent: { __typename?: 'BlogCategory'; name: string } | null;
    }>;
};

export const BlogCategoriesDocumentApi = gql`
    query BlogCategories {
        blogCategories {
            ...BlogCategoriesFragment
        }
    }
    ${BlogCategoriesFragmentApi}
`;

export function useBlogCategoriesApi(options?: Omit<Urql.UseQueryArgs<BlogCategoriesVariablesApi>, 'query'>) {
    return Urql.useQuery<BlogCategoriesApi, BlogCategoriesVariablesApi>({
        query: BlogCategoriesDocumentApi,
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
