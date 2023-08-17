import * as Types from '../../types';

import gql from 'graphql-tag';
import { PageInfoFragmentApi } from '../../pageInfo/fragments/PageInfoFragment.generated';
import { ListedOrderFragmentApi } from './ListedOrderFragment.generated';
export type OrderListFragmentApi = {
    __typename: 'OrderConnection';
    totalCount: number;
    pageInfo: { __typename: 'PageInfo'; hasNextPage: boolean; hasPreviousPage: boolean; endCursor: string | null };
    edges: Array<{
        __typename: 'OrderEdge';
        cursor: string;
        node: {
            __typename: 'Order';
            uuid: string;
            number: string;
            creationDate: any;
            productItems: Array<{ __typename: 'OrderItem'; quantity: number }>;
            transport: {
                __typename: 'Transport';
                name: string;
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
            payment: { __typename: 'Payment'; name: string };
            totalPrice: { __typename: 'Price'; priceWithVat: string; priceWithoutVat: string; vatAmount: string };
        } | null;
    } | null> | null;
};

export const OrderListFragmentApi = gql`
    fragment OrderListFragment on OrderConnection {
        __typename
        totalCount
        pageInfo {
            ...PageInfoFragment
        }
        edges {
            __typename
            node {
                ...ListedOrderFragment
            }
            cursor
        }
    }
    ${PageInfoFragmentApi}
    ${ListedOrderFragmentApi}
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
