import * as Types from '../../types';

import gql from 'graphql-tag';
import { SimpleBlogCategoryFragmentApi } from './SimpleBlogCategoryFragment.generated';
export type BlogCategoriesFragmentApi = {
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
};

export const BlogCategoriesFragmentApi = gql`
    fragment BlogCategoriesFragment on BlogCategory {
        ...SimpleBlogCategoryFragment
        children {
            ...SimpleBlogCategoryFragment
            children {
                ...SimpleBlogCategoryFragment
                children {
                    ...SimpleBlogCategoryFragment
                    children {
                        ...SimpleBlogCategoryFragment
                    }
                }
            }
        }
    }
    ${SimpleBlogCategoryFragmentApi}
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
