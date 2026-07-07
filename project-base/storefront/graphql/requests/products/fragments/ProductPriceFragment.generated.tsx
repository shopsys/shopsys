// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeProductPriceFragment = { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } };

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