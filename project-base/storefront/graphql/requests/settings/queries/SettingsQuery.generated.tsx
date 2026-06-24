// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PricingSettingFragment } from '../fragments/PricingSettingFragment.generated';
import { SeoSettingFragment } from '../fragments/SeoSettingFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** One of the possible methods of the customer user login */
export type TypeLoginTypeEnum =
  | 'admin'
  | 'facebook'
  | 'google'
  | 'seznam'
  | 'web';

export type TypeSettingsQueryVariables = Exact<{ [key: string]: never; }>;


export type TypeSettingsQuery = { settings: { contactFormMainText: string | null, displayTimezone: string, heurekaEnabled: boolean, privacyPolicyArticleUrl: string | null, termsAndConditionsArticleUrl: string | null, userConsentPolicyArticleUrl: string | null, socialNetworkLoginConfig: Array<Types.TypeLoginTypeEnum>, cspHeader: string, defaultPricingGroupId: number, pricing: { __typename: 'PricingSetting', defaultCurrencyCode: string, minimumFractionDigits: number }, seo: { __typename: 'SeoSetting', title: string | null, titleAddOn: string | null, metaDescription: string | null }, mainBlogCategoryData: { mainBlogCategoryUrl: string | null, mainBlogCategoryMainImage: { url: string } | null } } | null };


export const SettingsQueryDocument = gql`
    query SettingsQuery @redisCache(ttl: 3600) {
  settings {
    pricing {
      ...PricingSettingFragment
    }
    seo {
      ...SeoSettingFragment
    }
    contactFormMainText
    displayTimezone
    heurekaEnabled
    mainBlogCategoryData {
      mainBlogCategoryUrl
      mainBlogCategoryMainImage {
        url
      }
    }
    privacyPolicyArticleUrl
    termsAndConditionsArticleUrl
    userConsentPolicyArticleUrl
    socialNetworkLoginConfig
    cspHeader
    defaultPricingGroupId
  }
}
    ${PricingSettingFragment}
${SeoSettingFragment}`;

export function useSettingsQuery(options?: Omit<Urql.UseQueryArgs<TypeSettingsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeSettingsQuery, TypeSettingsQueryVariables>({ query: SettingsQueryDocument, ...options });
};