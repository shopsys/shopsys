import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleArticleSiteFragmentApi } from './SimpleArticleSiteFragment.generated';
import { SimpleArticleLinkFragmentApi } from './SimpleArticleLinkFragment.generated';
export type SimpleNotBlogArticleFragment_ArticleLink_Api = {
    __typename: 'ArticleLink';
    uuid: string;
    name: string;
    url: string;
    placement: string;
    external: boolean;
};

export type SimpleNotBlogArticleFragment_ArticleSite_Api = {
    __typename: 'ArticleSite';
    uuid: string;
    name: string;
    slug: string;
    placement: string;
    external: boolean;
};

export type SimpleNotBlogArticleFragmentApi =
    | SimpleNotBlogArticleFragment_ArticleLink_Api
    | SimpleNotBlogArticleFragment_ArticleSite_Api;

export const SimpleNotBlogArticleFragmentApi = gql`
    fragment SimpleNotBlogArticleFragment on NotBlogArticleInterface {
        __typename
        ...SimpleArticleSiteFragment
        ...SimpleArticleLinkFragment
    }
    ${SimpleArticleSiteFragmentApi}
    ${SimpleArticleLinkFragmentApi}
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
