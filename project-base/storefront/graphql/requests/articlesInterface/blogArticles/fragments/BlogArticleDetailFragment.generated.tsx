import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BlogArticleImageListGridFragmentApi } from './images/BlogArticleImageListGridFragment.generated';
import { BreadcrumbFragmentApi } from '../../../breadcrumbs/fragments/BreadcrumbFragment.generated';
export type BlogArticleDetailFragmentApi = {
    __typename: 'BlogArticle';
    id: number;
    uuid: string;
    name: string;
    slug: string;
    link: string;
    text: string | null;
    publishDate: any;
    seoTitle: string | null;
    seoMetaDescription: string | null;
    seoH1: string | null;
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
};

export const BlogArticleDetailFragmentApi = gql`
    fragment BlogArticleDetailFragment on BlogArticle {
        __typename
        id
        uuid
        name
        slug
        link
        ...BlogArticleImageListGridFragment
        breadcrumb {
            ...BreadcrumbFragment
        }
        text
        publishDate
        seoTitle
        seoMetaDescription
        seoH1
    }
    ${BlogArticleImageListGridFragmentApi}
    ${BreadcrumbFragmentApi}
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
