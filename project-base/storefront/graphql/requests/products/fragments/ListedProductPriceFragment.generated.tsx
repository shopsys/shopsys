// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeListedProductPriceFragment = { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } };

export const ListedProductPriceFragment = gql`
    fragment ListedProductPriceFragment on ProductPrice {
  __typename
  priceWithVat
  priceWithoutVat
  vatAmount
  isPriceFrom
  percentageDiscount
  basicPrice {
    __typename
    priceWithVat
  }
}
    `;