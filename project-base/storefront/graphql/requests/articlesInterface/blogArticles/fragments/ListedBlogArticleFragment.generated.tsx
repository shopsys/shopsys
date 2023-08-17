import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BlogArticleImageListFragmentApi } from './images/BlogArticleImageListFragment.generated';
import { SimpleBlogCategoryFragmentApi } from '../../../blogCategories/fragments/SimpleBlogCategoryFragment.generated';
export type ListedBlogArticleFragmentApi = {
    __typename: 'BlogArticle';
    uuid: string;
    name: string;
    link: string;
    publishDate: any;
    perex: string | null;
    slug: string;
    blogCategories: Array<{
        __typename: 'BlogCategory';
        uuid: string;
        name: string;
        link: string;
        parent: { __typename?: 'BlogCategory'; name: string } | null;
    }>;
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
};

export const ListedBlogArticleFragmentApi = gql`
    fragment ListedBlogArticleFragment on BlogArticle {
        __typename
        uuid
        name
        link
        ...BlogArticleImageListFragment
        publishDate
        perex
        slug
        blogCategories {
            ...SimpleBlogCategoryFragment
        }
    }
    ${BlogArticleImageListFragmentApi}
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
