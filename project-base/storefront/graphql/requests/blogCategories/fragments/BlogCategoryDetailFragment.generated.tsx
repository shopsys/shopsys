import * as Types from '../../types';

import gql from 'graphql-tag';
import { BreadcrumbFragmentApi } from '../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { BlogCategoriesFragmentApi } from './BlogCategoriesFragment.generated';
export type BlogCategoryDetailFragmentApi = {
    __typename: 'BlogCategory';
    uuid: string;
    name: string;
    seoTitle: string | null;
    seoMetaDescription: string | null;
    articlesTotalCount: number;
    breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
    blogCategoriesTree: Array<{
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

export const BlogCategoryDetailFragmentApi = gql`
    fragment BlogCategoryDetailFragment on BlogCategory {
        __typename
        uuid
        name
        breadcrumb {
            ...BreadcrumbFragment
        }
        seoTitle
        seoMetaDescription
        blogCategoriesTree {
            ...BlogCategoriesFragment
        }
        articlesTotalCount
    }
    ${BreadcrumbFragmentApi}
    ${BlogCategoriesFragmentApi}
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
