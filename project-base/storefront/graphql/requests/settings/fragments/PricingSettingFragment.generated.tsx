// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypePricingSettingFragment = (
  { __typename: 'PricingSetting' }
  & Pick<Types.TypePricingSetting, 'defaultCurrencyCode' | 'currentCurrencyCode' | 'minimumFractionDigits'>
  & { availableCurrencies: Array<(
    { __typename: 'CurrencySetting' }
    & Pick<Types.TypeCurrencySetting, 'code' | 'name' | 'minFractionDigits'>
  )> }
);

export const PricingSettingFragment = gql`
    fragment PricingSettingFragment on PricingSetting {
  __typename
  defaultCurrencyCode
  currentCurrencyCode
  minimumFractionDigits
  availableCurrencies {
    __typename
    code
    name
    minFractionDigits
  }
}
    `;