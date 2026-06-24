// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypePricingSettingFragment = { __typename: 'PricingSetting', defaultCurrencyCode: string, minimumFractionDigits: number };

export const PricingSettingFragment = gql`
    fragment PricingSettingFragment on PricingSetting {
  __typename
  defaultCurrencyCode
  minimumFractionDigits
}
    `;