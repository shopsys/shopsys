import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PageInfoFragmentApi } from '../../../pageInfo/fragments/PageInfoFragment.generated';
import { ListedBlogArticleFragmentApi } from './ListedBlogArticleFragment.generated';
export type BlogArticleConnectionFragmentApi = {
    __typename: 'BlogArticleConnection';
    totalCount: number;
    pageInfo: { __typename: 'PageInfo'; hasNextPage: boolean; hasPreviousPage: boolean; endCursor: string | null };
    edges: Array<{
        __typename: 'BlogArticleEdge';
        node: {
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
        } | null;
    } | null> | null;
};

export const BlogArticleConnectionFragmentApi = gql`
    fragment BlogArticleConnectionFragment on BlogArticleConnection {
        __typename
        totalCount
        pageInfo {
            ...PageInfoFragment
        }
        edges {
            __typename
            node {
                ...ListedBlogArticleFragment
            }
        }
    }
    ${PageInfoFragmentApi}
    ${ListedBlogArticleFragmentApi}
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
