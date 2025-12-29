// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeProductPriceFragment = { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } };

export const ProductPriceFragment = gql`
    fragment ProductPriceFragment on ProductPrice {
  __typename
  priceWithVat
  priceWithoutVat
  vatAmount
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