// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSeoSettingFragment = { __typename: 'SeoSetting', title: string | null, titleAddOn: string | null, metaDescription: string | null, organization: { name: string | null, vatId: string | null, companyNumber: string | null, description: string | null, streetAddress: string | null, addressLocality: string | null, postalCode: string | null, addressCountry: string | null, logo: string | null, sameAs: Array<string> } };

export const SeoSettingFragment = gql`
    fragment SeoSettingFragment on SeoSetting {
  __typename
  organization {
    name
    vatId
    companyNumber
    description
    streetAddress
    addressLocality
    postalCode
    addressCountry
    logo
    sameAs
  }
  title
  titleAddOn
  metaDescription
}
    `;