import * as Types from '../../types';

import gql from 'graphql-tag';
import { CategoryImagesDefaultFragmentApi } from './CategoryImagesDefaultFragment.generated';
export type ListedCategoryFragmentApi = {
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
};

export const ListedCategoryFragmentApi = gql`
    fragment ListedCategoryFragment on Category {
        __typename
        uuid
        name
        slug
        ...CategoryImagesDefaultFragment
        products {
            __typename
            totalCount
        }
    }
    ${CategoryImagesDefaultFragmentApi}
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
