// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeListedProductPriceFragment = (
  { __typename: 'ProductPrice' }
  & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'percentageDiscount'>
  & { basicPrice: (
    { __typename: 'Price' }
    & Pick<Types.TypePrice, 'priceWithVat'>
  ) }
);

export const ListedProductPriceFragment = gql`
    fragment ListedProductPriceFragment on ProductPrice {
  __typename
  priceWithVat
  priceWithoutVat
  vatAmount
  currencyCode
  isPriceFrom
  percentageDiscount
  basicPrice {
    __typename
    priceWithVat
  }
}
    `;