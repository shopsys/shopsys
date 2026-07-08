// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeProductPriceFragment = (
  { __typename: 'ProductPrice' }
  & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
  & { basicPrice: (
    { __typename?: 'Price' }
    & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
  ) }
);

export const ProductPriceFragment = gql`
    fragment ProductPriceFragment on ProductPrice {
  __typename
  priceWithVat
  priceWithoutVat
  vatAmount
  currencyCode
  isPriceFrom
  nextPriceChange
  percentageDiscount
  basicPrice {
    priceWithVat
    priceWithoutVat
    vatAmount
  }
}
    `;