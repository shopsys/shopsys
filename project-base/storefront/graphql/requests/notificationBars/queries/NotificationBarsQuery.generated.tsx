import * as Types from '../../types';

import gql from 'graphql-tag';
import { NotificationBarsFragmentApi } from '../fragments/NotificationBarsFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type NotificationBarsVariablesApi = Types.Exact<{ [key: string]: never }>;

export type NotificationBarsApi = {
    __typename?: 'Query';
    notificationBars: Array<{
        __typename: 'NotificationBar';
        text: string;
        rgbColor: string;
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
    }> | null;
};

export const NotificationBarsDocumentApi = gql`
    query NotificationBars @_redisCache(ttl: 3600) {
        notificationBars {
            ...NotificationBarsFragment
        }
    }
    ${NotificationBarsFragmentApi}
`;

export function useNotificationBarsApi(options?: Omit<Urql.UseQueryArgs<NotificationBarsVariablesApi>, 'query'>) {
    return Urql.useQuery<NotificationBarsApi, NotificationBarsVariablesApi>({
        query: NotificationBarsDocumentApi,
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
