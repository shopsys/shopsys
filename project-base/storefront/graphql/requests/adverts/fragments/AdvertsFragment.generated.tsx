import * as Types from '../../types';

import gql from 'graphql-tag';
import { SimpleCategoryFragmentApi } from '../../categories/fragments/SimpleCategoryFragment.generated';
import { ImageSizesFragmentApi } from '../../images/fragments/ImageSizesFragment.generated';
export type AdvertsFragment_AdvertCode_Api = {
    __typename: 'AdvertCode';
    code: string;
    uuid: string;
    name: string;
    positionName: string;
    type: string;
    categories: Array<{ __typename: 'Category'; uuid: string; name: string; slug: string }>;
};

export type AdvertsFragment_AdvertImage_Api = {
    __typename: 'AdvertImage';
    link: string | null;
    uuid: string;
    name: string;
    positionName: string;
    type: string;
    mainImage: {
        __typename: 'Image';
        position: number | null;
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
    mainImageMobile: {
        __typename: 'Image';
        position: number | null;
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
    categories: Array<{ __typename: 'Category'; uuid: string; name: string; slug: string }>;
};

export type AdvertsFragmentApi = AdvertsFragment_AdvertCode_Api | AdvertsFragment_AdvertImage_Api;

export const AdvertsFragmentApi = gql`
    fragment AdvertsFragment on Advert {
        __typename
        uuid
        name
        positionName
        type
        categories {
            ...SimpleCategoryFragment
        }
        ... on AdvertCode {
            code
        }
        ... on AdvertImage {
            link
            mainImage(type: "web") {
                position
                ...ImageSizesFragment
            }
            mainImageMobile: mainImage(type: "mobile") {
                position
                ...ImageSizesFragment
            }
        }
    }
    ${SimpleCategoryFragmentApi}
    ${ImageSizesFragmentApi}
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
