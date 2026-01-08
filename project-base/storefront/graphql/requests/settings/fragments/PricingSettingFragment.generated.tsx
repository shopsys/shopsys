// @ts-nocheck
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