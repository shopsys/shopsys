import * as Types from '../../types';

import gql from 'graphql-tag';
import { PricingSettingFragmentApi } from '../fragments/PricingSettingFragment.generated';
import { SeoSettingFragmentApi } from '../fragments/SeoSettingFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type SettingsQueryVariablesApi = Types.Exact<{ [key: string]: never }>;

export type SettingsQueryApi = {
    __typename?: 'Query';
    settings: {
        __typename?: 'Settings';
        contactFormMainText: string;
        pricing: { __typename: 'PricingSetting'; defaultCurrencyCode: string; minimumFractionDigits: number };
        seo: { __typename: 'SeoSetting'; title: string; titleAddOn: string; metaDescription: string };
    } | null;
};

export const SettingsQueryDocumentApi = gql`
    query SettingsQuery @_redisCache(ttl: 3600) {
        settings {
            pricing {
                ...PricingSettingFragment
            }
            seo {
                ...SeoSettingFragment
            }
            contactFormMainText
        }
    }
    ${PricingSettingFragmentApi}
    ${SeoSettingFragmentApi}
`;

export function useSettingsQueryApi(options?: Omit<Urql.UseQueryArgs<SettingsQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<SettingsQueryApi, SettingsQueryVariablesApi>({ query: SettingsQueryDocumentApi, ...options });
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
