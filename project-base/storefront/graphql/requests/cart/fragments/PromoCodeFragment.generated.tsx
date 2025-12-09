import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypePromoCodeFragment = { __typename: 'PromoCode', code: string, type: Types.TypePromoCodeTypeEnum, discountPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } };

export const PromoCodeFragment = gql`
    fragment PromoCodeFragment on PromoCode {
  __typename
  code
  type
  discountPrice {
    priceWithVat
    priceWithoutVat
    vatAmount
  }
}
    `;