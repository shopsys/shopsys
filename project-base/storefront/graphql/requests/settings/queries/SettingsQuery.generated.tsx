// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PricingSettingFragment } from '../fragments/PricingSettingFragment.generated';
import { SeoSettingFragment } from '../fragments/SeoSettingFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeSettingsQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeSettingsQuery = (
  { __typename?: 'Query' }
  & { settings: Types.Maybe<(
    { __typename?: 'Settings' }
    & Pick<Types.TypeSettings, 'contactFormMainText' | 'displayTimezone' | 'heurekaEnabled' | 'privacyPolicyArticleUrl' | 'termsAndConditionsArticleUrl' | 'userConsentPolicyArticleUrl' | 'socialNetworkLoginConfig' | 'cspHeader' | 'defaultPricingGroupId'>
    & { pricing: (
      { __typename: 'PricingSetting' }
      & Pick<Types.TypePricingSetting, 'defaultCurrencyCode' | 'currentCurrencyCode' | 'minimumFractionDigits'>
      & { availableCurrencies: Array<(
        { __typename: 'CurrencySetting' }
        & Pick<Types.TypeCurrencySetting, 'code' | 'name' | 'minFractionDigits'>
      )> }
    ), seo: (
      { __typename: 'SeoSetting' }
      & Pick<Types.TypeSeoSetting, 'title' | 'titleAddOn' | 'metaDescription'>
    ), mainBlogCategoryData: (
      { __typename?: 'MainBlogCategoryData' }
      & Pick<Types.TypeMainBlogCategoryData, 'mainBlogCategoryUrl'>
      & { mainBlogCategoryMainImage: Types.Maybe<(
        { __typename?: 'Image' }
        & Pick<Types.TypeImage, 'url'>
      )> }
    ) }
  )> }
);


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